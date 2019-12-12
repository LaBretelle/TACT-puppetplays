<?php

namespace App\Service;

use App\Entity\EditorialContent;
use Doctrine\ORM\EntityManagerInterface;

class EditorialContentManager
{
    private $em;

    public function __construct(EntityManagerInterface $em)
    {
        $this->em = $em;
    }

    public function create($name)
    {
        $edito = new EditorialContent();
        $edito->setName($name);
        $edito->setValue("");

        $this->em->persist($edito);
        $this->em->flush();

        return $edito;
    }
}
