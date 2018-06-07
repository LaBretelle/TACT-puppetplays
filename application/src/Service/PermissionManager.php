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

    public function isAuthorizedOnProject(Project $project, $action)
    {
        $currentUser = $this->security->getUser();

        $userProjectStatus = $this->em->getRepository("App:UserProjectStatus")->findOneBy(["user"=> $currentUser, "project" =>$project]);
        $userProjectStatus = ($userProjectStatus && $userProjectStatus->getEnabled()) ? $userProjectStatus : null;
        $statusName = ($userProjectStatus) ? $userProjectStatus->getStatus()->getName() : null;
        $isAdmin =  ($this->authChecker->isGranted('ROLE_ADMIN')) ? true : false;

        switch ($action) {
          case "manageMedia":
              if ($isAdmin || $statusName === AppEnums::USER_STATUS_MANAGER_NAME) {
                  return true;
              }
              break;

          case "manageUser":
            if ($isAdmin || $statusName === AppEnums::USER_STATUS_MANAGER_NAME) {
                return true;
            }
            break;

          case "editProject":
            if ($isAdmin || $statusName === AppEnums::USER_STATUS_MANAGER_NAME) {
                return true;
            }
            break;

          case "transcribe":
            if ($isAdmin || $statusName === AppEnums::USER_STATUS_MANAGER_NAME || $statusName === AppEnums::USER_STATUS_TRANSCRIBER_NAME) {
                return true;
            }
            break;

        return false;
      }
    }
}
