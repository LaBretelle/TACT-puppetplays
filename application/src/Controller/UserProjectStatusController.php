<?php

namespace App\Controller;

use App\Service\UserProjectStatusManager;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Doctrine\Common\Collections\ArrayCollection;
use App\Entity\UserProjectStatus;
use App\Entity\Project;

/**
 * @Route("/status", name="status_")
 */
class UserProjectStatusController extends Controller
{
    private $statusManager;

    public function __construct(UserProjectStatusManager $statusManager)
    {
        $this->statusManager = $statusManager;
    }

    /**
     * @Route("/{id}/toggle", name="toggle")
     */
    public function toggle(UserProjectStatus $status)
    {
        $this->statusManager->toggle($status);

        return $this->redirectToRoute('status_project', ["id" => $status->getProject()->getId()]);
    }

    /**
     * @Route("/{id}/remove", name="remove")
     */
    public function remove(UserProjectStatus $status)
    {
        $this->statusManager->remove($status);

        return $this->redirectToRoute('status_project', ["id" => $status->getProject()->getId()]);
    }

    /**
     * @Route("/project/{id}", name="project")
     */
    public function handleUserStatuses(Project $project)
    {
        return $this->render(
          'project/user-statuses.html.twig',
          ['project' => $project]
      );
    }
}
