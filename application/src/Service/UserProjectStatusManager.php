<?php

namespace App\Service;

use App\Entity\UserProjectStatus;
use App\Entity\User;
use App\Entity\Project;
use App\Entity\UserProject;
use App\Service\AppEnums;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Security\Core\Authorization\AuthorizationCheckerInterface;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;

class UserProjectStatusManager
{
    protected $em;
    protected $authChecker;
    protected $user;

    public function __construct(EntityManagerInterface $em, AuthorizationCheckerInterface $authChecker, TokenStorageInterface $tokenStorage)
    {
        $this->em = $em;
        $this->authChecker = $authChecker;
        $this->user = $tokenStorage->getToken()->getUser();
    }

    public function create(Project $project)
    {
        $status = $this->em->getRepository("App:UserStatus")->findOneByName(AppEnums::TRANSKEY_USER_STATUS_MANAGER_NAME);

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
        $userProjectStatus->setEnabled(!$enabled);

        $this->em->persist($userProjectStatus);
        $this->em->flush();

        return $userProjectStatus;
    }

    public function remove(UserProjectStatus $userProjectStatus)
    {
        $this->em->remove($userProjectStatus);
        $this->em->flush();

        return $userProjectStatus;
    }
}
