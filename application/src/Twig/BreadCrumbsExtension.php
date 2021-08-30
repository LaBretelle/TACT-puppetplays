<?php

namespace App\Twig;

use Twig\Extension\AbstractExtension;
use Twig\TwigFilter;
use Doctrine\ORM\EntityManagerInterface;

class BreadCrumbsExtension extends AbstractExtension
{
    protected $em;

    public function __construct(EntityManagerInterface $em)
    {
        $this->em = $em;
    }

    public function getFilters()
    {
        return array(
            new TwigFilter('mediaBreadcrumb', array($this, 'mediaBreadcrumb')),
        );
    }

    public function mediaBreadcrumb($current)
    {
        $ancestors = [];
        if ($current) {
            array_unshift($ancestors, $current);
            $ancestors = $this->getParent($current, $ancestors);
        }

        return $ancestors;
    }

    private function getParent($current, &$ancestors)
    {
        if ($current->getParent()) {
            array_unshift($ancestors, $current->getParent());
            $this->getParent($current->getParent(), $ancestors);
        }
        
        return $ancestors;
    }
}
