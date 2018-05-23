<?php

namespace App\Controller;

use App\Entity\Project;
use App\Entity\User;
use App\Form\ProjectType;
use App\Service\ProjectManager;
use App\Service\UserProjectStatusManager;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Doctrine\Common\Collections\ArrayCollection;

/**
 * @Route("/project", name="project_")
 */
class ProjectController extends Controller
{
    private $projectManager;
    private $statusManager;

    public function __construct(ProjectManager $projectManager, UserProjectStatusManager $statusManager)
    {
        $this->projectManager = $projectManager;
        $this->statusManager = $statusManager;
    }

    /**
     * @Route("/create", name="create")
     */
    public function create(Request $request)
    {
        $project = new Project();
        $form = $this->createForm(ProjectType::class, $project);

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $this->projectManager->createFromForm($project);

            return $this->redirectToRoute('project_display', ['id' => $project->getId()]);
        }

        return $this->render(
            'project/create.html.twig',
            [
              'form' => $form->createView(),
              'project' => $project
            ]
        );
    }

    /**
     * @Route("/{id}/edit", name="edit")
     */
    public function edit(Project $project, Request $request)
    {
        $form = $this->createForm(ProjectType::class, $project);

        $originalStatuses = new ArrayCollection();
        foreach ($project->getUserStatuses() as $status) {
            $originalStatuses->add($status);
        }

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $this->projectManager->editFromForm($project, $originalStatuses);

            return $this->redirectToRoute('project_display', ['id' => $project->getId()]);
        }

        return $this->render(
          'project/create.html.twig',
          [
            'form' => $form->createView(),
            'project' => $project
          ]
      );
    }

    /**
     * @Route("/{id}", name="display")
     */
    public function display(Project $project)
    {
        return $this->render(
            'project/display.html.twig',
            ['project' => $project]
        );
    }
}
