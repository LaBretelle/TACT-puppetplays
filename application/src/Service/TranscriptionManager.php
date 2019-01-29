<?php

namespace App\Service;

use App\Entity\Media;
use App\Entity\Project;
use App\Entity\Transcription;
use App\Entity\TranscriptionLog;
use App\Entity\User;
use App\Service\AppEnums;
use App\Service\MessageManager;
use App\Service\PermissionManager;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Component\Security\Core\Security;
use Symfony\Component\Translation\TranslatorInterface;

class TranscriptionManager
{
    protected $em;
    protected $security;
    protected $params;
    protected $permissionManager;
    protected $mm;
    protected $translator;
    protected $router;

    public function __construct(
      EntityManagerInterface $em,
      Security $security,
      ParameterBagInterface $params,
      PermissionManager $permissionManager,
      MessageManager $mm,
      TranslatorInterface $translator,
      UrlGeneratorInterface $router
    ) {
        $this->em = $em;
        $this->security = $security;
        $this->params = $params;
        $this->permissionManager = $permissionManager;
        $this->mm = $mm;
        $this->translator = $translator;
        $this->router = $router;
    }

    public function addLog(Transcription $transcription, string $name, $flush = false, User $user = null)
    {
        $user = (!$user) ? $this->security->getUser() : $user;
        $log = new TranscriptionLog();
        $log->setUser($user);
        $log->setName($name);
        $transcription->addTranscriptionLog($log);

        if ($flush) {
            $this->em->persist($log);
            $this->em->flush();
        }

        return $log;
    }

    public function isLocked(TranscriptionLog $log)
    {
        $diff = $log->getCreatedAt()->diff(new \DateTime());

        return $diff->i <= 2;
    }

    public function isLockedByCurrentUser(Transcription $transcription, $lockLog)
    {
        $currentUser = $this->security->getUser();
        $logUser = $lockLog->getUser();

        return $currentUser->getId() === $logUser->getId();
    }

    public function getLogs(Transcription $transcription, Project $project)
    {
        return $this->permissionManager->isAuthorizedOnProject($project, AppEnums::ACTION_VIEW_LOGS)
          ? $this->em->getRepository(TranscriptionLog::class)->getLogs($transcription)
          : null;
    }

    public function getContributors(Transcription $transcription)
    {
        return $this->em->getRepository(User::class)->getByTranscription($transcription);
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
            // delete the request
            $this->em->remove($request);
            // send to the requester a message
            $media = $transcription->getMedia();
            $url = $isValid
              ? $this->router->generate("media_transcription_display", ["id" => $media->getId()])
              : $this->router->generate("media_transcription_edit", ["id" => $media->getId()]);
            $message = $isValid ? 'transcription_validated_msg' : 'transcription_unvalidated_msg';
            $message = $this->translator->trans($message, ['%url%' => $url, '%media%' => $media->getName()]);
            $this->mm->create([$request->getUser()], $message, false);
        }

        // create log
        $logType = $isValid ? AppEnums::TRANSCRIPTION_LOG_VALIDATED : AppEnums::TRANSCRIPTION_LOG_UNVALIDATED;
        $log = $this->addLog($transcription, $logType);
        $this->em->persist($log);

        $this->em->flush();

        return;
    }

    public function report(Media $media, $reportType)
    {
        $url = $this->router->generate("media_transcription_display", ["id" => $media->getId()]);
        $currentUser = $this->security->getUser() ? $this->security->getUser()->getUserName() : "anonymous";

        $message = $this->translator->trans('transcription_report_msg', [
            '%url%' => $url,
            '%media%' => $media->getName(),
            '%username%' => $currentUser,
            '%reportType%' => $this->translator->trans($reportType)
        ]);

        $users = $this->em->getRepository(User::class)->getManagersOrAdminsByProject($media->getProject());
        $this->mm->create($users, $message, true);

        return;
    }
}
