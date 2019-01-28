<?php

namespace App\Controller;

use App\Entity\Media;
use App\Entity\Transcription;
use App\Form\ReviewType;
use App\Service\AppEnums;
use App\Service\FileManager;
use App\Service\MailManager;
use App\Service\MediaManager;
use App\Service\PermissionManager;
use App\Service\ReviewManager;
use App\Service\ReviewRequestManager;
use App\Service\TranscriptionManager;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;
use Symfony\Component\Translation\TranslatorInterface;

/**
 * @Route("/media", name="media_")
 */
class MediaController extends AbstractController
{
    private $mediaManager;
    private $transcriptionManager;
    private $reviewManager;
    private $reviewRequestManager;
    private $mailManager;
    private $permissionManager;
    private $translator;
    private $fileManager;

    public function __construct(
      MediaManager $mediaManager,
      TranscriptionManager $transcriptionManager,
      ReviewManager $reviewManager,
      ReviewRequestManager $reviewRequestManager,
      MailManager $mailManager,
      PermissionManager $permissionManager,
      TranslatorInterface $translator,
      FileManager $fileManager
    ) {
        $this->mediaManager = $mediaManager;
        $this->transcriptionManager = $transcriptionManager;
        $this->reviewManager = $reviewManager;
        $this->reviewRequestManager = $reviewRequestManager;
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
            'transcribe/transcription.html.twig',
            [
              'media' => $media,
              'edit' => false,
              'locked' => false,
              'log' => false,
              'review' => false
            ]
        );
    }

    /**
     * @Route("/{id}/transcription/edit", name="transcription_edit")
     */
    public function editTranscription(Media $media)
    {
        $project = $media->getProject();
        $transcription = $media->getTranscription();

        if (false === $this->permissionManager->isAuthorizedOnProject($project, AppEnums::ACTION_TRANSCRIBE)) {
            throw new AccessDeniedException($this->translator->trans('access_denied', [], 'messages'));
        }

        if ($transcription->getReviewRequest()) {
            return $this->redirectToRoute('media_transcription_review', ['id' => $media->getId()]);
        }

        $lockLog = $this->transcriptionManager->getLastLockLog($transcription);
        $locked = $lockLog ? $this->transcriptionManager->isLocked($lockLog) : false;
        $canEdit = (!$locked || $locked && $this->transcriptionManager->isLockedByCurrentUser($transcription, $lockLog));
        $lockLog = (!$locked) ? $this->transcriptionManager->addLog($transcription, AppEnums::TRANSCRIPTION_LOG_LOCKED, true) : $lockLog;
        $schema = $this->fileManager->getProjectTeiSchema($project);
        $logs = $this->transcriptionManager->getLogs($transcription, $project);

        return $this->render(
            'transcribe/transcription.html.twig',
            [
              'media' => $media,
              'edit' => $canEdit,
              'locked' => $locked,
              'log' => $lockLog,
              'schema' => $schema,
              'logs' => $logs,
              'review' => false
            ]
        );
    }

    /**
     * @Route("/{id}/transcription/review", name="transcription_review")
     */
    public function reviewTranscription(Media $media, Request $request)
    {
        $project = $media->getProject();
        $transcription = $media->getTranscription();

        if (false === $this->permissionManager->isAuthorizedOnProject($project, AppEnums::ACTION_VALIDATE_TRANSCRIPTION)) {
            throw new AccessDeniedException($this->translator->trans('access_denied', [], 'messages'));
        }

        if (!$transcription->getReviewRequest()) {
            return $this->redirectToRoute('media_transcription_edit', ['id' => $media->getId()]);
        }

        $reviewRequest = $transcription->getReviewRequest();
        $review = $this->reviewManager->create($reviewRequest);
        $form = $this->createForm(ReviewType::class, $review);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $this->reviewManager->save($review);
            $nbPositiveReview = $this->reviewManager->countReview($transcription, true);
            if ($nbPositiveReview >= $project->getNbValidation()) {
                $this->transcriptionManager->validate($transcription, true);
            }
            $parent = $media->getParent();

            return $this->redirectToRoute('project_transcriptions', ['id' => $project->getId(), 'parent' => $parent]);
        }

        $lockLog = $this->transcriptionManager->getLastLockLog($transcription);
        $locked = $lockLog ? $this->transcriptionManager->isLocked($lockLog) : false;
        $canEdit = (!$locked || $locked && $this->transcriptionManager->isLockedByCurrentUser($transcription, $lockLog));
        $lockLog = (!$locked) ? $this->transcriptionManager->addLog($transcription, AppEnums::TRANSCRIPTION_LOG_LOCKED, true) : $lockLog;
        $schema = $this->fileManager->getProjectTeiSchema($project);
        $logs = $this->transcriptionManager->getLogs($transcription, $project);
        $nbPositiveReview = $this->reviewManager->countReview($transcription, true);

        return $this->render(
          'review/index.html.twig',
            [
            'media' => $media,
            'form' => $form->createView(),
            'nbCurrentValidation' => $nbPositiveReview,
            'schema' => $schema,
            'logs' => $logs,
            'review' => true,
            'edit' => $canEdit,
            'locked' => $locked,
            'log' => $lockLog,
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
     * @Route("/{id}/transcription/validate/{valid}", name="transcription_validate")
     */
    public function validateTranscription(Media $media, $valid)
    {
        $project = $media->getProject();
        $parent = $media->getParent();

        if (false === $this->permissionManager->isAuthorizedOnProject($project, AppEnums::ACTION_EDIT_PROJECT)) {
            throw new AccessDeniedException($this->translator->trans('access_denied', [], 'messages'));
        }
        $this->transcriptionManager->validate($media->getTranscription(), $valid);

        return $this->redirectToRoute('project_transcriptions', ['id' => $project->getId(), 'parent' => $parent]);
    }
}
