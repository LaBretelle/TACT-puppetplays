<?php

namespace App\Service;

use App\Entity\Project;
use Doctrine\ORM\EntityManagerInterface;

class ProjectManager
{
    private $em;

    public function __construct(EntityManagerInterface $em)
    {
        $this->em = $em;
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

        foreach ($originalStatuses as $status) {
            if (!$project->getUserStatuses()->contains($status)) {
                $project->removeUserStatus($status);
                $this->em->remove($status);
            }
        }

        $this->em->persist($project);
        $this->em->flush();

        return $project;
    }
}
