<?php

namespace App\Controller;

use Symfony\Component\HttpFoundation\File\File;
use Symfony\Component\Filesystem\Filesystem;
use App\Entity\Media;
use App\Entity\Comment;
use App\Form\CommentType;
use App\Form\ReviewType;
use App\Service\AppEnums;
use App\Service\FileManager;
use App\Service\ExportManager;
use App\Service\MediaManager;
use App\Service\PermissionManager;
use App\Service\ReviewManager;
use App\Service\CommentManager;
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
    private $permissionManager;
    private $translator;
    private $fileManager;
    private $exportManager;
    private $commentManager;

    public function __construct(
        MediaManager $mediaManager,
        TranscriptionManager $transcriptionManager,
        ReviewManager $reviewManager,
        ReviewRequestManager $reviewRequestManager,
        PermissionManager $permissionManager,
        TranslatorInterface $translator,
        FileManager $fileManager,
        ExportManager $exportManager,
        CommentManager $commentManager
    ) {
        $this->mediaManager = $mediaManager;
        $this->transcriptionManager = $transcriptionManager;
        $this->reviewManager = $reviewManager;
        $this->reviewRequestManager = $reviewRequestManager;
        $this->permissionManager = $permissionManager;
        $this->translator = $translator;
        $this->fileManager = $fileManager;
        $this->exportManager = $exportManager;
        $this->commentManager = $commentManager;
    }

    /**
     * @Route("/{id}/transcription/view", name="transcription_display")
     */
    public function displayTranscription(Media $media)
    {
        $transcription = $media->getTranscription();
        $project = $media->getProject();

        // deny access if project is not public and user is not a member
        if (false === $this->permissionManager->isAuthorizedOnProject($project, AppEnums::ACTION_VIEW_TRANSCRIPTIONS)) {
            throw new AccessDeniedException($this->translator->trans('access_denied', [], 'messages'));
        }

        $contributors = $this->transcriptionManager->getContributors($transcription);
        $commentForm = $this->createForm(CommentType::class, null, ["transcription" => $transcription->getId()]);
        $logs = $this->transcriptionManager->getLogs($transcription, $project);

        return $this->render(
            'transcribe/transcription.html.twig',
            [
              'media' => $media,
              'edit' => false,
              'locked' => false,
              'log' => false,
              'review' => false,
              'contributors' => $contributors,
              'commentForm' => $commentForm->createView(),
              'logs' => $logs,
            ]
        );
    }

    /**
     * @Route("/{id}/transcription/edit", name="transcription_edit")
     */
    public function editTranscription(Media $media, Request $request)
    {
        $em = $this->getDoctrine()->getManager();
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
        $contributors = $this->transcriptionManager->getContributors($transcription);

        $commentForm = $this->createForm(CommentType::class, null, ["transcription" => $transcription->getId()]);

        return $this->render(
            'transcribe/transcription.html.twig',
            [
              'media' => $media,
              'edit' => $canEdit,
              'locked' => $locked,
              'log' => $lockLog,
              'schema' => $schema,
              'logs' => $logs,
              'contributors' => $contributors,
              'review' => false,
              'commentForm' => $commentForm->createView()
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
            $this->reviewManager->testForValidation($transcription, $project);
            $parent = $media->getParent();

            return $this->redirectToRoute('project_transcriptions', ['id' => $project->getId(), 'parent' => $parent ? $parent->getId() : null ]);
        }

        $lockLog = $this->transcriptionManager->getLastLockLog($transcription);
        $locked = $lockLog ? $this->transcriptionManager->isLocked($lockLog) : false;
        $canEdit = (!$locked || $locked && $this->transcriptionManager->isLockedByCurrentUser($transcription, $lockLog));
        $lockLog = (!$locked) ? $this->transcriptionManager->addLog($transcription, AppEnums::TRANSCRIPTION_LOG_LOCKED, true) : $lockLog;
        $schema = $this->fileManager->getProjectTeiSchema($project);
        $logs = $this->transcriptionManager->getLogs($transcription, $project);
        $nbPositiveReview = $this->reviewManager->countReview($transcription, true);
        $contributors = $this->transcriptionManager->getContributors($transcription);


        $commentForm = $this->createForm(CommentType::class, null, ["transcription" => $transcription->getId()]);

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
            'contributors' => $contributors,
            'commentForm' => $commentForm->createView()
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
     * @Route("/{id}/transcription/report", name="transcription_report",options={"expose"=true}, methods="POST")
     */
    public function mediaTranscriptionReport(Media $media, Request $request)
    {
        $reportType = $request->get('reportType');
        $this->transcriptionManager->report($media, $reportType);

        return $this->json([], $status = 200);
    }


    /**
     * @Route("/{id}/transcription/validate/{valid}", name="transcription_validate")
     */
    public function validateTranscription(Media $media, $valid)
    {
        $project = $media->getProject();
        $parent = ($media->getParent())
          ? $media->getParent()->getId()
          : null;

        if (false === $this->permissionManager->isAuthorizedOnProject($project, "validateTranscription")) {
            throw new AccessDeniedException($this->translator->trans('access_denied', [], 'messages'));
        }
        $this->transcriptionManager->validate($media->getTranscription(), $valid);

        return $this->redirectToRoute('project_transcriptions', ['id' => $project->getId(), 'parent' => $parent]);
    }

    /**
     * @Route("/{id}/media/download", name="download_media")
     */
    public function mediaDownloadMedia(Media $media)
    {
        $fileSystem = new Filesystem();

        $mediaName = $this->fileManager->recreateMediaName($media);
        $mediaPath =  $this->fileManager->getMediaPath($media);
        $exportDir = $this->fileManager->createTmpDir();
        $newMediaPath = $exportDir.$mediaName;

        $fileSystem->mkdir($exportDir);
        $fileSystem->copy($mediaPath, $newMediaPath);

        return $this->file($newMediaPath);
    }

    /**
     * @Route("/{id}/transcription/download", name="download_transcription")
     */
    public function mediaDownloadTranscription(Media $media)
    {
        $fileSystem = new Filesystem();

        $xmlName = $this->fileManager->recreateXmlName($media);
        $exportDir = $this->fileManager->createTmpDir();
        $fileSystem->mkdir($exportDir);
        $fileSystem->appendToFile($exportDir.$xmlName, $this->exportManager->generateXML($media));


        return $this->file($exportDir.$xmlName);
    }
}
