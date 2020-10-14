<?php

namespace App\Controller;

use App\Entity\Comment;
use App\Entity\Transcription;
use App\Form\CommentType;
use App\Service\AppEnums;
use App\Service\CommentManager;
use App\Service\PermissionManager;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;

/**
 * @Route("/comment", name="comment_")
 */
class CommentController extends AbstractController
{
    private $commentManager;
    private $permissionManager;

    public function __construct(CommentManager $commentManager, PermissionManager $permissionManager)
    {
        $this->commentManager = $commentManager;
        $this->permissionManager = $permissionManager;
    }

    /**
     * @Route("/create/{id}", name="create")
     */
    public function createComment(Transcription $transcription, Request $request)
    {
        $project = $transcription->getMedia()->getProject();

        if (false === $this->permissionManager->isAuthorizedOnProject($project, AppEnums::ACTION_TRANSCRIBE)) {
            throw new AccessDeniedException($this->translator->trans('access_denied', [], 'messages'));
        }
        $comment = $this->commentManager->create($transcription);
        $commentForm = $this->createForm(CommentType::class, $comment);
        $commentForm->handleRequest($request);
        if ($commentForm->isSubmitted() && $commentForm->isValid()) {
            $this->commentManager->save($comment);
        }

        return $this->redirect($request->headers->get('referer'));
    }

    /**
     * @Route("/delete/{id}", name="delete")
     */
    public function deleteComment(Comment $comment, Request $request)
    {
        $project = $comment->getTranscription()->getMedia()->getProject();

        if (false === $this->permissionManager->isAuthorizedOnProject($project, AppEnums::ACTION_DELETE_COMMENT)) {
            throw new AccessDeniedException($this->translator->trans('access_denied', [], 'messages'));
        }
        $this->commentManager->delete($comment);

        return $this->redirect($request->headers->get('referer'));
    }

    /**
     * @Route("/subscribe/{id}/{subscribe}", name="subscribe")
     */
    public function subscribe(Transcription $transcription, $subscribe, Request $request)
    {
        $project = $transcription->getMedia()->getProject();

        if (false === $this->permissionManager->isAuthorizedOnProject($project, AppEnums::ACTION_TRANSCRIBE)) {
            throw new AccessDeniedException($this->translator->trans('access_denied', [], 'messages'));
        }

        $this->commentManager->subscribe($transcription, $this->getUser(), $subscribe);

        return $this->redirect($request->headers->get('referer'));
    }
}
