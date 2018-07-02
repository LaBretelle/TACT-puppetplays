<?php

namespace App\Service;

use App\Entity\Directory;
use App\Entity\Project;
use Doctrine\ORM\EntityManagerInterface;

class DirectoryManager
{
    protected $em;

    public function __construct(EntityManagerInterface $em)
    {
        $this->em = $em;
    }

    public function create(Project $project, $name, $parent)
    {
        $dir = new Directory;

        $dir->setName($name);
        $dir->setParent($parent);
        $dir->setProject($project);

        $this->save($dir);

        return $dir;
    }

    public function save(Directory $dir)
    {
        $this->em->persist($dir);
        $this->em->flush();
    }
}
