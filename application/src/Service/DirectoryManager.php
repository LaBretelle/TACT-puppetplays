<?php

namespace App\Service;

use App\Entity\Directory;
use Doctrine\ORM\EntityManagerInterface;

class DirectoryManager
{
    protected $em;

    public function __construct(EntityManagerInterface $em)
    {
        $this->em = $em;
    }

    public function create($name, $parent)
    {
        $dir = new Directory;
        
        $dir->setName($name);
        $dir->setParent($parent);

        $em->persist($dir);
        $em->flush();

        return $dir;
    }
}
