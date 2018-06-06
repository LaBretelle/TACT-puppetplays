<?php

namespace App\Controller;

use App\Entity\Media;
use App\Entity\Transcription;
use App\Service\MediaManager;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;

/**
 * @Route("/media", name="media_")
 */
class MediaController extends Controller
{
    private $mediaManager;

    public function __construct(MediaManager $mediaManager)
    {
        $this->mediaManager = $mediaManager;
    }

    /**
     * @Route("/{id}/transcription", name="transcription_display")
     */
    public function displayTranscription(Media $media)
    {
        if (null === $media->getTranscription()) {
            $media = $this->mediaManager->initMediaTranscription($media);
        }

        return $this->render(
            'media/transcription.html.twig',
            ['media' => $media]
        );
    }

    /**
     * @Route("/{id}/transcription/save", name="transcription_save",options={"expose"=true}, methods="POST")
     */
    public function mediaTranscriptionSave(Media $media, Request $request)
    {
        $content = $request->get('transcription');
        $this->mediaManager->setMediaTranscription($media, $content);
        return $this->json([], $status = 200);
    }

    /**
     * @Route("/{id}/transcription/finish", name="transcription_finish",options={"expose"=true}, methods="POST")
     */
    public function mediaTranscriptionFinish(Media $media, Request $request)
    {
        $content = $request->get('transcription');
        $this->mediaManager->finishTranscription($media, $content);
        return $this->json([], $status = 200);
    }
}
