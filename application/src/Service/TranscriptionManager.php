<?php

namespace App\Service;

use App\Entity\Transcription;
use App\Entity\TranscriptionLog;
use App\Service\AppEnums;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;
use Symfony\Component\Security\Core\Security;

class TranscriptionManager
{
    protected $em;
    protected $security;
    protected $params;

    public function __construct(EntityManagerInterface $em, Security $security, ParameterBagInterface $params)
    {
        $this->em = $em;
        $this->security = $security;
        $this->params = $params;
    }

    public function addLog(Transcription $transcription, string $name)
    {
        $currentUser = $this->security->getUser();
        $log = new TranscriptionLog();
        $log->setUser($this->security->getUser());
        $log->setName($name);
        $transcription->addTranscriptionLog($log);
        return $log;
    }

    public function isLocked(TranscriptionLog $log)
    {
        $diff = $log->getCreatedAt()->diff(new \DateTime());
        return $diff->i <= 2;
    }

    public function userCanEditTranscription(Transcription $transcription)
    {
        $currentUser = $this->security->getUser();
        $lastLog = $this->em->getRepository(TranscriptionLog::class)->getLastLog($transcription);
        $status = $lastLog->getName();
        $logUser = $lastLog->getUser();
        return $currentUser->getId() === $logUser->getId();
    }

    public function getLastLog(Transcription $transcription)
    {
        $repository = $this->em->getRepository(TranscriptionLog::class);
        return $repository->getLastLog($transcription);
    }

    public function getLastLogByName(Transcription $transcription, string $name)
    {
        $repository = $this->em->getRepository(TranscriptionLog::class);
        return $repository->getLastLogByName($transcription, $name);
    }

    public function getLastLockLog(Transcription $transcription)
    {
        $repository = $this->em->getRepository(TranscriptionLog::class);
        return $repository->getLastLockLog($transcription);
    }

    public function countValidationLog(Transcription $transcription)
    {
        $repository = $this->em->getRepository(TranscriptionLog::class);
        return $repository->countValidationLog($transcription);
    }
}
