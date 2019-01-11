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
use App\Service\ReviewManager;
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
    private $reviewManager;
    private $mailManager;
    private $permissionManager;
    private $translator;
    private $fileManager;

    public function __construct(
      MediaManager $mediaManager,
      TranscriptionManager $transcriptionManager,
      FlashManager $fm,
      ReviewManager $reviewManager,
      MailManager $mailManager,
      PermissionManager $permissionManager,
      TranslatorInterface $translator,
      FileManager $fileManager
    ) {
        $this->mediaManager = $mediaManager;
        $this->transcriptionManager = $transcriptionManager;
        $this->fm = $fm;
        $this->reviewManager = $reviewManager;
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
        $project = $media->getProject();

        if (false === $this->permissionManager->isAuthorizedOnProject($project, AppEnums::ACTION_TRANSCRIBE)) {
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
        $schema = $this->fileManager->getProjectTeiSchema($project);
        $logs = $this->transcriptionManager->getLogs($transcription, $project);

        return $this->render(
            'media/transcription.html.twig',
            [
              'media' => $media,
              'edit' => $canEdit,
              'locked' => $locked,
              'log' => $lockLog,
              'schema' => $schema,
              'logs' => $logs
            ]
        );
    }

    /**
     * @Route("/{id}/transcription/review", name="transcription_review")
     */
    public function reviewTranscription(Media $media, Request $request)
    {
        $project = $media->getProject();

        if (false === $this->permissionManager->isAuthorizedOnProject($project, AppEnums::ACTION_VALIDATE_TRANSCRIPTION)) {
            throw new AccessDeniedException($this->translator->trans('access_denied', [], 'messages'));
        }

        $transcription = $media->getTranscription();
        $reviewRequest = $transcription->getReviewRequest();
        $nbPositiveReview = $this->reviewManager->countReview($transcription, true);

        $form = $this->createForm(ValidationType::class);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $parent = $media->getParent();
            $isValid = $form->get('isValid')->getData();
            $comment = $form->get('comment')->getData();
            // todo > vérifier que l'utilisateur n'a pas déjà review la transcription... récupérer la review !
            $this->reviewManager->create($reviewRequest, $isValid, $comment);
            $nbPositiveReview = $this->reviewManager->countReview($transcription, true);
            if ($nbPositiveReview >= $project->getNbValidation()) {
                $this->transcriptionManager->validate($transcription, true);
                $this->mailManager->sendValidationMail($reviewRequest->getUser(), $media, $isValid, $comment);
            }

            return $this->redirectToRoute('project_transcriptions', ['id' => $project->getId(), 'parent' => $parent]);
        }

        $schema = $this->fileManager->getProjectTeiSchema($project);
        $logs = $this->transcriptionManager->getLogs($transcription, $project);

        return $this->render(
          'media/reread.html.twig',
            [
            'media' => $media,
            'form' => $form->createView(),
            'nbCurrentValidation' => $nbPositiveReview,
            'schema' => $schema,
            'logs' => $logs
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
}
