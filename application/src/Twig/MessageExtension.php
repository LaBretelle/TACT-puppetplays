<?php

namespace App\Twig;

use Twig\Extension\AbstractExtension;
use Twig\TwigFilter;
use Doctrine\ORM\EntityManagerInterface;

class MessageExtension extends AbstractExtension
{
    protected $em;

    public function __construct(EntityManagerInterface $em)
    {
        $this->em = $em;
    }

    public function getFilters()
    {
        return array(
            new TwigFilter('countUnread', array($this, 'countUnread')),
        );
    }

    public function countUnread($user)
    {
        $count = count($this->em->getRepository("App:Recipient")->findBy([
            'user' => $user,
            'viewed' => false
        ]));

        return $count;
    }
}
