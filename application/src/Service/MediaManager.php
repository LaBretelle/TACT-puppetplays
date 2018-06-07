<?php

namespace App\Service;

use App\Entity\Media;
use App\Entity\Transcription;
use App\Service\AppEnums;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;
use Symfony\Component\Security\Core\Authorization\AuthorizationCheckerInterface;
use Symfony\Component\Security\Core\Security;

class MediaManager
{
    protected $em;
    protected $authChecker;
    protected $params;
    protected $security;

    public function __construct(EntityManagerInterface $em, AuthorizationCheckerInterface $authChecker, ParameterBagInterface $params, Security $security)
    {
        $this->em = $em;
        $this->authChecker = $authChecker;
        $this->params = $params;
        $this->security = $security;
    }

    public function initMediaTranscription(Media $media)
    {
        $transcription = new Transcription();
        $transcription->setUser($this->security->getUser());
        $transcriptionStatus = $this->em->getRepository("App:TranscriptionStatus")->findOneByName(AppEnums::TRANSCRIPTION_STATUS_NONE);
        $transcription->setStatus($transcriptionStatus);
        $transcription->setContent('');
        $media->setTranscription($transcription);
        $this->em->persist($media);
        $this->em->flush();
        return $media;
    }

    public function setMediaTranscription(Media $media, string $content)
    {
        $transcription = $media->getTranscription();
        $transcription->setContent($content);
        $transcriptionStatus = $this->em->getRepository("App:TranscriptionStatus")->findOneByName(AppEnums::TRANSCRIPTION_STATUS_IN_PROGRESS);
        $transcription->setStatus($transcriptionStatus);
        $this->em->persist($transcription);
        $this->em->flush();
    }

    public function finishTranscription(Media $media, string $content)
    {
        $transcription = $media->getTranscription();
        $transcription->setContent($content);
        $transcriptionStatus = $this->em->getRepository("App:TranscriptionStatus")->findOneByName(AppEnums::TRANSCRIPTION_STATUS_IN_REREAD);
        $transcription->setStatus($transcriptionStatus);
        $this->em->persist($transcription);
        $this->em->flush();
    }
}
