<?php

namespace App\Controller;

use App\Entity\Media;
use App\Entity\Transcription;
use App\Form\ValidationType;
use App\Service\AppEnums;
use App\Service\FlashManager;
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
    private $fm;

    public function __construct(MediaManager $mediaManager, TranscriptionManager $transcriptionManager, FlashManager $fm)
    {
        $this->mediaManager = $mediaManager;
        $this->transcriptionManager = $transcriptionManager;
        $this->fm = $fm;
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
    public function validateTranscription(Media $media, Request $request)
    {
        $form = $this->createForm(ValidationType::class);

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $isValid = $form->get('isValid')->getData();
            $comment = $form->get('comment')->getData();

            if ($isValid) {
                $this->mediaManager->validateTranscription($media);
                $this->fm->add('notice', 'transcription_validated');
            } else {
                $this->mediaManager->unvalidateTranscription($media);
                $this->fm->add('notice', 'transcription_unvalidated');
            }

            $parent = $media->getParent();
            $project = $media->getProject();

            return $this->redirectToRoute('project_transcriptions', ['id' => $project->getId(), 'parent' => $parent]);
        }

        return $this->render(
          'media/reread.html.twig',
            [
            'media' => $media,
            'form' => $form->createView()
          ]
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
        $this->fm->add('notice', 'validation_asked');

        return $this->json([], $status = 200);
    }
}
