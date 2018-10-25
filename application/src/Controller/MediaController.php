<?php

namespace App\Controller;

use App\Entity\Media;
use App\Entity\Transcription;
use App\Form\ValidationType;
use App\Service\AppEnums;
use App\Service\FileManager;
use App\Service\FlashManager;
use App\Service\MailManager;
use App\Service\MediaManager;
use App\Service\PermissionManager;
use App\Service\TranscriptionManager;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;
use Symfony\Component\Translation\TranslatorInterface;

/**
 * @Route("/media", name="media_")
 */
class MediaController extends Controller
{
    private $mediaManager;
    private $transcriptionManager;
    private $fm;
    private $mailManager;
    private $permissionManager;
    private $translator;
    private $fileManager;

    public function __construct(
      MediaManager $mediaManager,
      TranscriptionManager $transcriptionManager,
      FlashManager $fm,
      MailManager $mailManager,
      PermissionManager $permissionManager,
      TranslatorInterface $translator,
      FileManager $fileManager
    ) {
        $this->mediaManager = $mediaManager;
        $this->transcriptionManager = $transcriptionManager;
        $this->fm = $fm;
        $this->mailManager = $mailManager;
        $this->permissionManager = $permissionManager;
        $this->translator = $translator;
        $this->fileManager = $fileManager;
    }

    /**
     * @Route("/{id}/transcription/view", name="transcription_display")
     */
    public function displayTranscription(Media $media)
    {
        // deny access if project is not public and user is not a member
        if (false === $this->permissionManager->isAuthorizedOnProject($media->getProject(), AppEnums::ACTION_VIEW_TRANSCRIPTIONS)) {
            throw new AccessDeniedException($this->translator->trans('access_denied', [], 'messages'));
        }

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
        if (false === $this->permissionManager->isAuthorizedOnProject($media->getProject(), AppEnums::ACTION_TRANSCRIBE)) {
            throw new AccessDeniedException($this->translator->trans('access_denied', [], 'messages'));
        }

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
        $schema = $this->fileManager->getProjectTeiSchema($media->getProject());

        return $this->render(
            'media/transcription.html.twig',
            ['media' => $media, 'edit' => $canEdit, 'locked' => $locked, 'log' => $lockLog, 'schema' => $schema]
        );
    }

    /**
     * @Route("/{id}/transcription/reread", name="transcription_reread")
     */
    public function validateTranscription(Media $media, Request $request)
    {
        if (false === $this->permissionManager->isAuthorizedOnProject($media->getProject(), AppEnums::ACTION_VALIDATE_TRANSCRIPTION)) {
            throw new AccessDeniedException($this->translator->trans('access_denied', [], 'messages'));
        }

        $form = $this->createForm(ValidationType::class);

        $form->handleRequest($request);
        $nbCurrentValidation = $this->transcriptionManager->countValidationLog($media->getTranscription());
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

            $log = $this->transcriptionManager->getLastLogByName($media->getTranscription(), AppEnums::TRANSCRIPTION_LOG_WAITING_FOR_VALIDATION);
            if (!$isValid || $isValid && $nbCurrentValidation >= $media->getProject()->getNbValidation()) {
                $this->mailManager->sendValidationOrUnvalidationMail($log->getUser(), $media, $isValid, $comment);
            }

            $parent = $media->getParent();
            $project = $media->getProject();

            return $this->redirectToRoute('project_transcriptions', ['id' => $project->getId(), 'parent' => $parent]);
        }

        return $this->render(
          'media/reread.html.twig',
            [
            'media' => $media,
            'form' => $form->createView(),
            'nbCurrentValidation' => $nbCurrentValidation,
          ]
        );
    }

    /**
     * @Route("/{id}/transcription/save", name="transcription_save",options={"expose"=true}, methods="POST")
     */
    public function mediaTranscriptionSave(Media $media, Request $request)
    {
        if (false === $this->permissionManager->isAuthorizedOnProject($media->getProject(), AppEnums::ACTION_TRANSCRIBE)) {
            return $this->json([], $status = 403);
        }
        $content = $request->get('transcription');
        $this->mediaManager->setMediaTranscription($media, $content);

        return $this->json([], $status = 200);
    }

    /**
     * @Route("/{id}/transcription/finish", name="transcription_finish",options={"expose"=true}, methods="POST")
     */
    public function mediaTranscriptionFinish(Media $media, Request $request)
    {
        if (false === $this->permissionManager->isAuthorizedOnProject($media->getProject(), AppEnums::ACTION_TRANSCRIBE)) {
            return $this->json([], $status = 403);
        }
        $content = $request->get('transcription');
        $this->mediaManager->finishTranscription($media, $content);
        $this->fm->add('notice', 'validation_asked');

        return $this->json([], $status = 200);
    }
}
