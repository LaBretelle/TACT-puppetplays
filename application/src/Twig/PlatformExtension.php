<?php

namespace App\Twig;

use App\Service\PlatformManager;
use Twig\Extension\AbstractExtension;
use Twig\TwigFilter;

class PlatformExtension extends AbstractExtension
{
    protected $manager;

    public function __construct(PlatformManager $manager)
    {
        $this->manager = $manager;
    }

    public function getFilters()
    {
        return array(
            new TwigFilter('getPlatformParams', array($this, 'getPlatformParams')),
        );
    }

    public function getPlatformParams()
    {
        return $this->manager->getPlatformParameters();
    }
}
