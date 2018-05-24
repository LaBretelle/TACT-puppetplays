<?php

namespace App\Twig;

use Twig\Extension\AbstractExtension;
use Twig\TwigFilter;
use Doctrine\ORM\EntityManagerInterface;

class StatusExtension extends AbstractExtension
{
    protected $em;

    public function __construct(EntityManagerInterface $em)
    {
        $this->em = $em;
    }

    public function getFilters()
    {
        return array(
            new TwigFilter('status', array($this, 'statusFilter')),
        );
    }

    public function statusFilter($user, $project)
    {
        $repo = $this->em->getRepository("App:UserProjectStatus");
        if ($status = $repo->findOneBy(["user"=> $user, "project" =>$project])) {
            return $status;
        }

        return null;
    }
}
