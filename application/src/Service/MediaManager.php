<?php

namespace App\Service;

use App\Entity\Media;
use App\Entity\Project;
use App\Entity\Transcription;
use App\Entity\TranscriptionLog;
use App\Service\AppEnums;
use App\Service\TranscriptionManager;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\File\File;
use Symfony\Component\Security\Core\Security;

class MediaManager
{
    protected $em;
    protected $security;
    protected $tlRepository;
    protected $transcriptionManager;

    public function __construct(EntityManagerInterface $em, Security $security, TranscriptionManager $transcriptionManager)
    {
        $this->em = $em;
        $this->security = $security;
        $this->tlRepository = $this->em->getRepository(TranscriptionLog::class);
        $this->transcriptionManager = $transcriptionManager;
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
        $transcription->setContent('');
        $transcription = $this->transcriptionManager->addLog($transcription, AppEnums::TRANSCRIPTION_LOG_CREATED);
        $media->setTranscription($transcription);

        return $media;
    }

    public function setMediaTranscription(Media $media, string $content)
    {
        $transcription = $media->getTranscription();
        $transcription->setContent($content);
        $transcription = $this->transcriptionManager->addLog($transcription, AppEnums::TRANSCRIPTION_LOG_UPDATED);
        $this->em->persist($transcription);
        $this->em->flush();
    }

    public function finishTranscription(Media $media, string $content)
    {
        $transcription = $media->getTranscription();
        $transcription->setContent($content);
        $transcription = $this->transcriptionManager->addLog($transcription, AppEnums::TRANSCRIPTION_LOG_WAITING_FOR_VALIDATION);
        $this->em->persist($transcription);
        $this->em->flush();
    }

    public function validateTranscription(Media $media, string $content)
    {
        $transcription = $media->getTranscription();
        $transcription->setContent($content);

        $logName = AppEnums::TRANSCRIPTION_LOG_VALIDATION_PENDING;
        // find validation number
        if ($this->tlManager->countValidationLog($transcription) > 1) {
            $logName = AppEnums::TRANSCRIPTION_LOG_VALIDATED;
        }
        $transcription = $this->transcriptionManager->addLog($transcription, $logName);
        $this->em->persist($transcription);
        $this->em->flush();
    }

    public function isTranscribable(Media $media)
    {
        $transcription = $media->getTranscription();
        if (null === $transcription) {
            return true;
        } else {
            $lastLog = $this->tlRepository->getLastLog($transcription);
            $status = $lastLog->getName();
            return $status === AppEnums::TRANSCRIPTION_LOG_CREATED || $status === AppEnums::TRANSCRIPTION_LOG_UPDATED;
        }
        return false;
    }

    public function shouldBeValidated(Media $media)
    {
        $transcription = $media->getTranscription();
        if (null === $transcription) {
            return false;
        } else {
            $lastLog = $this->tlRepository->getLastLog($transcription);
            $status = $lastLog->getName();
            return $status === AppEnums::TRANSCRIPTION_LOG_WAITING_FOR_VALIDATION;
        }
        return false;
    }

    public function isInReread(Media $media)
    {
        $transcription = $media->getTranscription();

        if (null === $transcription) {
            return false;
        } else {
            $lastLog = $this->tlRepository->getLastLog($transcription);
            $status = $lastLog->getName();
            return $status === AppEnums::TRANSCRIPTION_LOG_VALIDATION_PENDING;
        }
        return false;
    }

    public function transcriptionStatusClass(Media $media)
    {
        $transcription = $media->getTranscription();
        if ($transcription) {
            $lastLog = $this->tlRepository->getLastLog($transcription);
            $status = $lastLog->getName();
            if ($status === AppEnums::TRANSCRIPTION_LOG_CREATED) {
                return 'status none';
            } elseif ($status === AppEnums::TRANSCRIPTION_LOG_UPDATED) {
                return 'status in-progress';
            } elseif ($status === AppEnums::TRANSCRIPTION_LOG_VALIDATION_PENDING) {
                return 'status in-reread';
            } elseif ($status === AppEnums::TRANSCRIPTION_LOG_VALIDATED) {
                return 'status validated';
            }
        }
        
        return 'status none';
    }
}
