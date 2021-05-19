<?php

namespace App\Controller;

use App\Entity\Project;
use App\Entity\ReviewRequest;
use App\Entity\Transcription;
use App\Service\AppEnums;
use App\Service\PermissionManager;
use App\Service\ReviewRequestManager;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;

/**
 * @Route("/review_request", name="review_request_")
 */
class ReviewRequestController extends AbstractController
{
    private $reviewRequestManager;
    private $permissionManager;

    public function __construct(
        ReviewRequestManager $reviewRequestManager,
        PermissionManager $permissionManager
    ) {
        $this->reviewRequestManager = $reviewRequestManager;
        $this->permissionManager = $permissionManager;
    }

    /**
     * @Route("/create/{projectId}/{transcriptionId}", name="create")
     * @ParamConverter("project", class="App:Project", options={"id" = "projectId"})
     * @ParamConverter("transcription", class="App:Transcription", options={"id" = "transcriptionId"})
     */
    public function create(Project $project, Transcription $transcription)
    {
        if (false === $this->permissionManager->isAuthorizedOnProject($project, AppEnums::ACTION_TRANSCRIBE)) {
            return $this->json([], $status = 403);
        }

        $this->reviewRequestManager->create($transcription);
        $parent = $transcription->getMedia()->getParent();

        return $this->redirectToRoute('project_transcriptions', ['id' => $project->getId(), 'parent' => $parent ? $parent->getId() : null ]);
    }
}
