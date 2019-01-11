<?php

namespace App\Twig;

use App\Entity\Transcription;
use App\Service\TranscriptionManager;
use Twig\Extension\AbstractExtension;
use Twig\TwigFilter;

class TranscriptionExtension extends AbstractExtension
{
    protected $tm;

    public function __construct(TranscriptionManager $tm)
    {
        $this->tm = $tm;
    }

    public function getFilters()
    {
        return array(
            new TwigFilter('transcriptionStatus', array($this, 'transcriptionStatus')),
        );
    }

    public function transcriptionStatus(Transcription $transcription)
    {
        return $this->tm->getStatus($transcription);
    }
}
