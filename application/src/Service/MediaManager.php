<?php

namespace App\Service;

use App\Entity\Media;
use App\Entity\Project;
use App\Entity\Transcription;
use App\Service\AppEnums;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;
use Symfony\Component\HttpFoundation\File\File;
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

    public function createFromFile(File $file, $fullPath, $parent, Project $project)
    {
        $extension = $file->guessExtension();

        $media = new Media();
        $media->setUrl($fullPath);
        $media->setName($fullPath);
        $media->setParent($parent);
        $media->setProject($project);
        $project->addMedia($media);
        $media = $this->initMediaTranscription($media);

        $this->em->persist($project);
        $this->em->persist($media);
        $this->em->flush();

        return $media;
    }

    public function initMediaTranscription(Media $media)
    {
        $transcription = new Transcription();
        $transcription->setUser($this->security->getUser());
        $transcriptionStatus = $this->em->getRepository("App:TranscriptionStatus")->findOneByName(AppEnums::TRANSCRIPTION_STATUS_NONE);
        $transcription->setStatus($transcriptionStatus);
        $transcription->setContent('');
        $transcription->setNbValidation(0);
        $media->setTranscription($transcription);
        return $media;
    }

    public function setMediaTranscription(Media $media, string $content)
    {
        $transcription = $media->getTranscription();
        $transcription->setContent($content);
        $transcription->setUser($this->security->getUser());
        $transcriptionStatus = $this->em->getRepository("App:TranscriptionStatus")->findOneByName(AppEnums::TRANSCRIPTION_STATUS_IN_PROGRESS);
        $transcription->setStatus($transcriptionStatus);
        $this->em->persist($transcription);
        $this->em->flush();
    }

    public function finishTranscription(Media $media, string $content)
    {
        $transcription = $media->getTranscription();
        $transcription->setContent($content);
        $transcription->setUser($this->security->getUser());
        $transcriptionStatus = $this->em->getRepository("App:TranscriptionStatus")->findOneByName(AppEnums::TRANSCRIPTION_STATUS_IN_REREAD);
        $transcription->setStatus($transcriptionStatus);
        $this->em->persist($transcription);
        $this->em->flush();
    }

    public function validateTranscription(Media $media, string $content)
    {
        $transcription = $media->getTranscription();
        $transcription->setContent($content);
        $transcription->setUser($this->security->getUser());
        $nbValidation = $transcription->getNbValidation();
        $nbValidation++;
        $transcription->setNbValidation($nbValidation);
        if ($nbValidation > 1) {
            $transcriptionStatus = $this->em->getRepository("App:TranscriptionStatus")->findOneByName(AppEnums::TRANSCRIPTION_STATUS_VALIDATED);
            $transcription->setStatus($transcriptionStatus);
        }
        $this->em->persist($transcription);
        $this->em->flush();
    }

    public function isTranscribable(Media $media)
    {
        $transcription = $media->getTranscription();
        if (null === $transcription) {
            return true;
        } else {
            $statusName = $transcription->getStatus()->getName();
            return $statusName === AppEnums::TRANSCRIPTION_STATUS_NONE || $statusName === AppEnums::TRANSCRIPTION_STATUS_IN_PROGRESS;
        }
        return false;
    }

    public function shouldBeValidated(Media $media)
    {
        $transcription = $media->getTranscription();
        if (null === $transcription) {
            return false;
        } else {
            $statusName = $transcription->getStatus()->getName();
            return $statusName === AppEnums::TRANSCRIPTION_STATUS_NONE || $statusName === AppEnums::TRANSCRIPTION_STATUS_IN_PROGRESS;
        }
        return false;
    }

    public function isInReread(Media $media)
    {
        $transcription = $media->getTranscription();
        if (null === $transcription) {
            return false;
        } else {
            $statusName = $transcription->getStatus()->getName();
            return $statusName === AppEnums::TRANSCRIPTION_STATUS_IN_REREAD;
        }
        return false;
    }

    public function transcriptionStatusClass(Media $media)
    {
        $transcription = $media->getTranscription();
        if ($transcription) {
            $statusName = $transcription->getStatus()->getName();
            if ($statusName === AppEnums::TRANSCRIPTION_STATUS_IN_PROGRESS) {
                return 'status in-progress';
            } elseif ($statusName === AppEnums::TRANSCRIPTION_STATUS_IN_REREAD) {
                return 'status in-reread';
            } elseif ($statusName === AppEnums::TRANSCRIPTION_STATUS_NONE) {
                return 'status none';
            } elseif ($statusName === AppEnums::TRANSCRIPTION_STATUS_VALIDATED) {
                return 'status validated';
            }
        }

        return 'status none';
    }
}
