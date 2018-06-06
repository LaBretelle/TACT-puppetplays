<?php

namespace App\Controller;

use App\Entity\Transcription;
use App\Service\TranscriptionManager;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;

/**
 * @Route("/transcription", name="transcription_")
 */
class TranscriptionController extends Controller
{
    private $transcriptionManager;

    public function __construct(TranscriptionManager $transcriptionManager)
    {
        $this->transcriptionManager = $transcriptionManager;
    }
}
