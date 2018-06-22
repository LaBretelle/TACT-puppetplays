<?php

namespace App\Controller;

use App\Entity\Media;
use App\Entity\Transcription;
use App\Service\AppEnums;
use App\Service\MediaManager;
use App\Service\TranscriptionManager;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;

/**
 * @Route("/media", name="media_")
 */
class MediaController extends Controller
{
    private $mediaManager;
    private $transcriptionManager;

    public function __construct(MediaManager $mediaManager, TranscriptionManager $transcriptionManager)
    {
        $this->mediaManager = $mediaManager;
        $this->transcriptionManager = $transcriptionManager;
    }

    /**
     * @Route("/{id}/transcription/view", name="transcription_display")
     */
    public function displayTranscription(Media $media)
    {
        return $this->render(
            'media/transcription.html.twig',
            ['media' => $media, 'edit' => false, 'locked' => false, 'log' => false]
        );
    }


    /**
     * @Route("/{id}/transcription/edit", name="transcription_edit")
     */
    public function editTranscription(Media $media)
    {
        $transcription = $media->getTranscription();
        $canEdit = true;
        $lockLog = $this->transcriptionManager->getLastLockLog($transcription);
        $locked = $lockLog ? $this->transcriptionManager->isLocked($lockLog) : false;

        // if transcription is locked only the user responsible for the lock event should be able to edit the transcription
        if ($locked) {
            $canEdit = $this->transcriptionManager->userCanEditTranscription($transcription);
        } else {
            $lockLog = $this->transcriptionManager->addLog($transcription, AppEnums::TRANSCRIPTION_LOG_LOCKED);
            $em = $this->getDoctrine()->getManager();
            $em->persist($transcription);
            $em->flush();
        }

        return $this->render(
            'media/transcription.html.twig',
            ['media' => $media, 'edit' => $canEdit, 'locked' => $locked, 'log' => $lockLog]
        );
    }

    /**
     * @Route("/{id}/transcription/reread", name="transcription_reread")
     */
    public function validateTranscription(Media $media)
    {
        return $this->render(
            'media/reread.html.twig',
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

    /**
     * @Route("/{id}/transcription/validate", name="transcription_validate",options={"expose"=true}, methods="POST")
     */
    public function mediaTranscriptionValidate(Media $media, Request $request)
    {
        $content = $request->get('transcription');
        $this->mediaManager->validateTranscription($media, $content);
        return $this->json([], $status = 200);
    }
}
