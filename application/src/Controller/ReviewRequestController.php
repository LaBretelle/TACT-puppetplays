<?php

namespace App\Controller;

use App\Entity\ReviewRequest;
use App\Service\ReviewRequestManager;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;

/**
 * @Route("/review_request", name="review_request_")
 */
class ReviewRequestController extends Controller
{
    private $reviewRequestManager;

    public function __construct(ReviewRequestManager $reviewRequestManager)
    {
        $this->reviewRequestManager = $reviewRequestManager;
    }

    /**
     * @Route("/create", name="create")
     */
    public function create(Project $project, Transcription $transcription)
    {
        if (false === $this->permissionManager->isAuthorizedOnProject($project, AppEnums::ACTION_TRANSCRIBE)) {
            return $this->json([], $status = 403);
        }

        $this->reviewRequestManager->create($transcription);

        return $this->redirectToRoute('project_display', ['id' => $project->getId()]);
    }
}
