<?php

namespace App\Twig;

use App\Entity\Media;
use App\Service\WebsiteManager;
use Twig\Extension\AbstractExtension;
use Twig\TwigFilter;

class WebsiteExtension extends AbstractExtension
{
    protected $manager;

    public function __construct(WebsiteManager $manager)
    {
        $this->manager = $manager;
    }

    public function getFilters()
    {
        return array(
            new TwigFilter('getWebsiteParams', array($this, 'getWebsiteParams')),
        );
    }

    public function getWebsiteParams()
    {
        return $this->manager->getWebsiteParameters();
    }
}
