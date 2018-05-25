<?php

namespace App\Controller;

use App\Entity\Project;
use App\Entity\UserProjectStatus;
use App\Form\UserStatusType;
use App\Service\UserProjectStatusManager;
use Doctrine\Common\Collections\ArrayCollection;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;

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
     * @Route("/{id}/request", name="request")
     */
    public function request(Project $project)
    {
        $this->statusManager->create($project);

        return $this->redirectToRoute('project_display', ["id" => $project->getId()]);
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
     * @Route("/{id}/form", name="form_get", options={"expose"=true})
     * @Method("GET")
     */
    public function getForm(UserProjectStatus $status)
    {
        $form = $this->createForm(UserStatusType::class, $status);

        return $this->render(
          'project/user-status-form.html.twig',
          [
            'form' => $form->createView(),
            'status' => $status,
          ]
      );
    }


    /**
     * @Route("/{id}/form", name="form_post")
     * @Method("POST")
     */
    public function postForm(UserProjectStatus $status, Request $request)
    {
        $form = $this->createForm(UserStatusType::class, $status);

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $em = $this->getDoctrine()->getManager();
            $em->persist($status);
            $em->flush();
            //$this->projectManager->createFromForm($project);
        }

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
