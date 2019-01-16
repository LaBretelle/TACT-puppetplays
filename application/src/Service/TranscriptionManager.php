<?php

namespace App\Service;

use App\Entity\Project;
use App\Entity\Transcription;
use App\Entity\TranscriptionLog;
use App\Service\AppEnums;
use App\Service\PermissionManager;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;
use Symfony\Component\Security\Core\Security;

class TranscriptionManager
{
    protected $em;
    protected $security;
    protected $params;
    protected $permissionManager;

    public function __construct(
      EntityManagerInterface $em,
      Security $security,
      ParameterBagInterface $params,
      PermissionManager $permissionManager
    ) {
        $this->em = $em;
        $this->security = $security;
        $this->params = $params;
        $this->permissionManager = $permissionManager;
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

    public function getLogs(Transcription $transcription, Project $project)
    {
        return $this->permissionManager->isAuthorizedOnProject($project, AppEnums::ACTION_VIEW_LOGS)
          ? $this->em->getRepository(TranscriptionLog::class)->getLogs($transcription)
          : null;
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

    public function getStatus(Transcription $transcription)
    {
        if ($transcription->getIsValid()) {
            return 'validated';
        }
        if ($transcription->getReviewRequest() != null) {
            return 'in-reread';
        }
        if ($transcription->getContent() != "") {
            return 'in-progress';
        }

        return 'none';
    }

    public function validate(Transcription $transcription, $isValid)
    {
        $transcription->setIsValid($isValid);
        $this->em->persist($transcription);

        if ($request = $transcription->getReviewRequest()) {
            $this->em->remove($request);
        }

        $logType = $isValid ? AppEnums::TRANSCRIPTION_LOG_VALIDATED : AppEnums::TRANSCRIPTION_LOG_UNVALIDATED;
        $log = $this->addLog($transcription, $logType);
        $this->em->persist($log);

        $this->em->flush();

        return;
    }
}
