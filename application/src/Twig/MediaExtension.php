<?php

namespace App\Twig;

use App\Entity\Media;
use App\Service\MediaManager;
use Twig\Extension\AbstractExtension;
use Twig\TwigFilter;

class MediaExtension extends AbstractExtension
{
    protected $manager;

    public function __construct(MediaManager $manager)
    {
        $this->manager = $manager;
    }

    public function getFilters()
    {
        return array(
            new TwigFilter('isTranscribable', array($this, 'isTranscribable')),
            new TwigFilter('isInReread', array($this, 'isInReread')),
            new TwigFilter('transcriptionStatusClass', array($this, 'transcriptionStatusClass')),
        );
    }

    public function isTranscribable(Media $media)
    {
        return $this->manager->isTranscribable($media);
    }

    public function isInReread(Media $media)
    {
        return $this->manager->isInReread($media);
    }

    public function transcriptionStatusClass(Media $media)
    {
        return $this->manager->transcriptionStatusClass($media);
    }
}
