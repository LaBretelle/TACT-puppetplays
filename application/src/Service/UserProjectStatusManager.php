<?php

namespace App\Service;

use App\Entity\UserProjectStatus;
use App\Entity\User;
use App\Entity\Project;
use App\Entity\UserProject;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Security\Core\Authorization\AuthorizationCheckerInterface;

class UserProjectStatusManager
{
    protected $em;
    protected $authChecker;

    public function __construct(EntityManagerInterface $em, AuthorizationCheckerInterface $authChecker)
    {
        $this->em = $em;
        $this->authChecker = $authChecker;
    }

    public function create(User $user, Project $project, UserStatus $status)
    {
        $userProjectStatus = new UserProjectStatus();
        $userProjectStatus->setUser($user);
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
