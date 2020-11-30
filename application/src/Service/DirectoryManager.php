<?php

namespace App\Service;

use App\Entity\Directory;
use App\Entity\Project;
use App\Service\FlashManager;
use Doctrine\ORM\EntityManagerInterface;

class DirectoryManager
{
    protected $em;
    protected $fm;

    public function __construct(EntityManagerInterface $em, FlashManager $fm)
    {
        $this->em = $em;
        $this->fm = $fm;
    }

    public function create(Project $project, $name, $parent)
    {
        $dir = new Directory;

        $dir->setName($name);
        $dir->setParent($parent);
        $dir->setProject($project);

        $this->save($dir);

        $this->fm->add('notice', 'directory_created');

        return $dir;
    }

    public function save(Directory $dir)
    {
        $this->em->persist($dir);
        $this->em->flush();
    }

    public function getSubordinates(Directory $dir)
    {
        $surbodinates = [];

        return $this->findRecursiveChildren($surbodinates, $dir);
    }

    private function findRecursiveChildren(&$surbodinates, $dir)
    {
        $surbodinates[] = $dir;
        if ($dir->getChildren()) {
            foreach ($dir->getChildren() as $children) {
                $this->findRecursiveChildren($surbodinates, $children);
            }
        }

        return $surbodinates;
    }
}
