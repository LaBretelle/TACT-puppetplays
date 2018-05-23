<?php

namespace App\Service;

use App\Entity\Project;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Security\Core\Authorization\AuthorizationCheckerInterface;

class ProjectManager
{
    protected $em;
    protected $authChecker;

    public function __construct(EntityManagerInterface $em, AuthorizationCheckerInterface $authChecker)
    {
        $this->em = $em;
        $this->authChecker = $authChecker;
    }

    public function createFromForm($project)
    {
        $project->setCreatedAt(new \DateTime);

        $this->em->persist($project);
        $this->em->flush();

        return $project;
    }

    public function editFromForm($project, $originalStatuses)
    {
        $project->setUpdatedAt(new \DateTime);

        if ($this->authChecker->isGranted('ROLE_ADMIN')) {
            foreach ($originalStatuses as $status) {
                if (!$project->getUserStatuses()->contains($status)) {
                    $project->removeUserStatus($status);
                    $this->em->remove($status);
                }
            }
        }

        $this->em->persist($project);
        $this->em->flush();

        return $project;
    }
}
