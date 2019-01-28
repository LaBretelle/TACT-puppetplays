<?php

namespace App\Service;

use App\Entity\Project;
use App\Entity\User;
use App\Entity\UserProject;
use App\Entity\UserProjectStatus;
use App\Service\AppEnums;
use App\Service\FlashManager;
use App\Service\MessageManager;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;
use Symfony\Component\Security\Core\Authorization\AuthorizationCheckerInterface;
use Symfony\Component\Translation\TranslatorInterface;

class UserProjectStatusManager
{
    protected $em;
    protected $authChecker;
    protected $user;
    protected $fm;
    protected $messageManager;
    protected $router;

    public function __construct(
      EntityManagerInterface $em,
      AuthorizationCheckerInterface $authChecker,
      TokenStorageInterface $tokenStorage,
      FlashManager $fm,
      MessageManager $messageManager,
      TranslatorInterface $translator
      ) {
        $this->em = $em;
        $this->authChecker = $authChecker;
        $this->user = $tokenStorage->getToken()->getUser();
        $this->fm = $fm;
        $this->messageManager = $messageManager;
        $this->translator = $translator;
    }

    public function create(Project $project)
    {
        $status = $this->em->getRepository("App:UserStatus")->findOneByName(AppEnums::USER_STATUS_TRANSCRIBER_NAME);

        $userProjectStatus = new UserProjectStatus();
        $userProjectStatus->setUser($this->user);
        $userProjectStatus->setProject($project);
        $userProjectStatus->setStatus($status);
        $enabled = $project->getPublic() ? true : false;
        $userProjectStatus->setEnabled($enabled);

        $this->em->persist($userProjectStatus);
        $this->em->flush();

        $text = $enabled ? "registration_validated" : "registration_waiting_for_validation";
        $this->fm->add('notice', $text);

        return $userProjectStatus;
    }

    public function checkBeforeChange(UserProjectStatus $userProjectStatus, $newStatusName)
    {
        $enabled = $userProjectStatus->getEnabled();
        $oldStatusName = $userProjectStatus->getStatus()->getName();

        return ($needCheck)
          ? $this->canEdit($userProjectStatus)
          : true;
    }

    public function toggle(UserProjectStatus $ups)
    {
        $enabled = $ups->getEnabled();

        if ($this->canEdit($ups)) {
            $ups->setEnabled(!$enabled);
            $this->em->persist($ups);
            $this->em->flush();

            $message = !$enabled
             ? "registration_validated_project"
             : "registration_unvalidated_project";

            $this->messageUser($ups, $message);
        }

        return $ups;
    }

    public function remove(UserProjectStatus $ups)
    {
        if ($this->canEdit($ups)) {
            $this->em->remove($ups);
            $this->em->flush();

            $this->messageUser($ups, "registration_deleted_project");
        }

        return;
    }

    public function messageUser(UserProjectStatus $ups, $message)
    {
        $projectName = $ups->getProject()->getName();
        $user = $ups->getUser();

        $message = $this->translator->trans($message, ['%project%' => $projectName]);
        $this->messageManager->create([$user], $message);
    }

    public function canEdit(UserProjectStatus $ups, $oldStatus = null)
    {
        $enabled = $ups->getEnabled();
        $newStatusName = $ups->getStatus()->getName();
        $statusName = ($oldStatus)
          ? $oldStatus
          : $ups->getStatus()->getName();

        if (($enabled && $statusName == AppEnums::USER_STATUS_MANAGER_NAME) || ($enabled && $statusName == AppEnums::USER_STATUS_MANAGER_NAME && $statusName != $newStatusName)) {
            $project = $ups->getProject();
            $count = $this->em->getRepository("App:UserProjectStatus")->countManagerByProject($project);
            if ($count < 2) {
                $this->fm->add('warning', 'need_at_least_one_manager');
                return false;
            }
        }

        return true;
    }
}
