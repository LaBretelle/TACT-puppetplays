<?php

namespace App\Controller;

use App\Entity\Media;
use App\Entity\Project;
use App\Entity\UserProjectStatus;
use App\Form\ProjectMediaType;
use App\Form\ProjectType;
use App\Service\AppEnums;
use App\Service\ProjectManager;
use Doctrine\Common\Collections\ArrayCollection;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;

/**
 * @Route("/project", name="project_")
 */
class ProjectController extends Controller
{
    private $projectManager;

    public function __construct(ProjectManager $projectManager)
    {
        $this->projectManager = $projectManager;
    }

    /**
     * @Route("/create", name="create")
     */
    public function create(Request $request)
    {
        $project = new Project();
        // add a default manager to the project
        $userStatus = new UserProjectStatus();
        $userStatus->setStatus($this->getDoctrine()->getRepository('App:UserStatus')->findOneByName(AppEnums::USER_STATUS_MANAGER_NAME));
        $userStatus->setUser($this->getUser());
        $project->addUserStatus($userStatus);
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
     * @Route("/{id}/media", name="media")
     */
    public function addProjectMedia(Request $request, Project $project)
    {
        $form = $this->createForm(ProjectMediaType::class, $project);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $media = $form->get('files')->getData();
            $this->projectManager->addProjectMedia($project, $media);
        }

        $file_limit = ini_get('max_file_uploads');

        return $this->render(
          'project/project-media.html.twig',
          [
            'form' => $form->createView(),
            'project' => $project,
            'fileLimit' => ini_get('max_file_uploads')
          ]
        );
    }

    /**
     * @Route("/{id}/transcription", name="transcriptions", options={"expose"=true})
     */
    public function displayTranscriptions(Project $project)
    {
        return $this->render(
            'media/transcriptions.html.twig',
            ['project' => $project]
        );
    }

    /**
     * @Route("/media/{id}", name="media_delete", options={"expose"=true}, methods="DELETE")
     */
    public function removeProjectMedia(Media $media)
    {
        $this->projectManager->removeProjectMedia($media);
        return $this->json([], $status = 200);
    }

    /**
     * @Route("/{id}", name="display", options={"expose"=true})
     */
    public function display(Project $project)
    {
        $projectManagerUser = $this->projectManager->getProjectManagerUser($project);
        return $this->render(
            'project/display.html.twig',
            ['project' => $project, 'manager' => $projectManagerUser]
        );
    }
}
