<?php

namespace App\Service;

use App\Entity\UserProjectStatus;
use App\Entity\User;
use App\Entity\Project;
use App\Entity\UserProject;
use App\Service\AppEnums;
use App\Service\FlashManager;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Security\Core\Authorization\AuthorizationCheckerInterface;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;

class UserProjectStatusManager
{
    protected $em;
    protected $authChecker;
    protected $user;
    protected $fm;

    public function __construct(EntityManagerInterface $em, AuthorizationCheckerInterface $authChecker, TokenStorageInterface $tokenStorage, FlashManager $fm)
    {
        $this->em = $em;
        $this->authChecker = $authChecker;
        $this->user = $tokenStorage->getToken()->getUser();
        $this->fm = $fm;
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

        return $userProjectStatus;
    }

    public function toggle(UserProjectStatus $userProjectStatus)
    {
        $enabled = $userProjectStatus->getEnabled();
        $statusName = $userProjectStatus->getStatus()->getName();


        $canToggle = ($enabled && $statusName == AppEnums::USER_STATUS_MANAGER_NAME)
          ? $this->hasManager($userProjectStatus)
          : true;


        if ($canToggle) {
            $userProjectStatus->setEnabled(!$enabled);

            $this->em->persist($userProjectStatus);
            $this->em->flush();
        }

        return $userProjectStatus;
    }

    public function remove(UserProjectStatus $userProjectStatus)
    {
        if ($this->hasManager($userProjectStatus)) {
            $this->em->remove($userProjectStatus);
            $this->em->flush();
        }

        return;
    }

    private function hasManager(UserProjectStatus $userProjectStatus)
    {
        $project = $userProjectStatus->getProject();

        $count = $this->em->getRepository("App:UserProjectStatus")->countManagerByProject($project);

        if ($count < 2) {
            $this->fm->add('warning', 'need_at_least_one_manager');
            return false;
        }

        return true;
    }
}
