<?php

namespace App\Service;

use App\Entity\Media;
use App\Entity\Project;
use App\Entity\User;
use App\Service\AppEnums;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Security\Core\Authorization\AuthorizationCheckerInterface;
use Symfony\Component\Security\Core\Security;

class PermissionManager
{
    protected $em;
    protected $authChecker;
    protected $security;

    public function __construct(EntityManagerInterface $em, AuthorizationCheckerInterface $authChecker, Security $security)
    {
        $this->em = $em;
        $this->authChecker = $authChecker;
        $this->security = $security;
    }

    public function isAuthorizedOnProject(Project $project, string $action)
    {
        $currentUser       = $this->security->getUser();
        $userProjectStatus = $this->em->getRepository("App:UserProjectStatus")->findOneBy(["user"=> $currentUser, "project" =>$project]);
        $status            = ($userProjectStatus && $userProjectStatus->getEnabled()) ? $userProjectStatus : null;
        $statusName        = ($status) ? $status->getStatus()->getName() : null;
        $isAdmin           = ($this->authChecker->isGranted('ROLE_ADMIN')) ? true : false;
        $isPublic          = $project->getPublic();
        $isArchived        = $project->getArchived();

        switch ($action) {
          case AppEnums::ACTION_VIEW_LOGS:
            if ($isAdmin) {
                return true;
            }
            break;

          case AppEnums::ACTION_ARCHIVE:
            if ($isAdmin) {
                return true;
            }
            break;

          case AppEnums::ACTION_DELETE_COMMENT:
            if ($isAdmin || $statusName === AppEnums::USER_STATUS_MANAGER_NAME) {
                return true;
            }
            break;

          case AppEnums::ACTION_MANAGE_MEDIA:
            if (!$isArchived && ($isAdmin || $statusName === AppEnums::USER_STATUS_MANAGER_NAME)) {
                return true;
            }
            break;

          case AppEnums::ACTION_MANAGE_USER:
            if ($isAdmin || $statusName === AppEnums::USER_STATUS_MANAGER_NAME) {
                return true;
            }
            break;

          case AppEnums::ACTION_EDIT_PROJECT:
            if ($isAdmin || $statusName === AppEnums::USER_STATUS_MANAGER_NAME) {
                return true;
            }
            break;

          case AppEnums::ACTION_VALIDATE_TRANSCRIPTION:
            if (!$isArchived && ($isAdmin || $statusName === AppEnums::USER_STATUS_VALIDATOR_NAME || $statusName === AppEnums::USER_STATUS_MANAGER_NAME)) {
                return true;
            }
            break;
;
          case AppEnums::ACTION_TRANSCRIBE:
            if (!$isArchived && ($isAdmin || $statusName === AppEnums::USER_STATUS_MANAGER_NAME || $statusName === AppEnums::USER_STATUS_VALIDATOR_NAME || $statusName === AppEnums::USER_STATUS_TRANSCRIBER_NAME)) {
                return true;
            }
            break;

          case AppEnums::ACTION_VIEW_TRANSCRIPTIONS:
            if ($isPublic) {
                return true;
            } else {
                return $isAdmin || $status !== null;
            }
            break;

          case AppEnums::ACTION_REGISTER:
            if (!$isArchived && (!$userProjectStatus && $currentUser != null)) {
                return true;
            }
            break;

          default:
            return false;
        }

        return false;
    }
}
