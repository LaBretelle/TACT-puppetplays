<?php

namespace App\Controller;

use App\Entity\Directory;
use App\Entity\Media;
use App\Entity\Project;
use App\Entity\UserProjectStatus;
use App\Entity\UserStatus;
use App\Form\ProjectMediaType;
use App\Form\ProjectType;
use App\Service\AppEnums;
use App\Service\FileManager;
use App\Service\FlashManager;
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
    private $fileManager;
    private $flashManager;

    public function __construct(ProjectManager $projectManager, FileManager $fileManager, FlashManager $flashManager)
    {
        $this->projectManager = $projectManager;
        $this->fileManager = $fileManager;
        $this->flashManager = $flashManager;
    }

    /**
     * @Route("/create", name="create")
     */
    public function create(Request $request)
    {
        $project = new Project();
        // add a default manager to the project
        $userStatus = new UserProjectStatus();
        $userStatus->setStatus($this->getDoctrine()->getRepository(UserStatus::class)->findOneByName(AppEnums::USER_STATUS_MANAGER_NAME));
        $userStatus->setUser($this->getUser());
        $project->addUserStatus($userStatus);
        $form = $this->createForm(ProjectType::class, $project);


        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $image = $form->get('image')->getData();
            $previous_image = $request->get('previous_image');

            $this->projectManager->createFromForm($project);
            $this->projectManager->handleImage($project, $image, $previous_image);

            $this->flashManager->add('notice', 'project_created');

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
            $image = $form->get('image')->getData();
            $previous_image = $request->get('previous_image');

            $this->projectManager->editFromForm($project, $originalStatuses);
            $this->projectManager->handleImage($project, $image, $previous_image);

            $this->flashManager->add('notice', 'project_edited');

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
     * @Route("/{id}/delete", name="delete", methods="POST")
     */
    public function delete(Project $project)
    {
        $this->projectManager->delete($project);
        $this->flashManager->add('info', 'project_deleted');

        return $this->redirectToRoute('home');
    }

    /**
     * @Route("/{id}/media/{parent}", name="media", defaults={"parent"=null})
     */
    public function addProjectMedia(Request $request, Project $project, Directory $parent = null)
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
            'fileLimit' => ini_get('max_file_uploads'),
            'parent' => $parent
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
     * @Route("/{id}/remove-image", name="delete_image", options={"expose"=true}, methods="DELETE")
     */
    public function removeProjectImage(Project $project)
    {
        $this->projectManager->removeImage($project);

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

    /**
     * @Route("/{id}/process-media", name="process", options={"expose"=true})
     */
    public function process(Project $project)
    {
        $projectManagerUser = $this->projectManager->initMediaProcessing($project);

        return $this->render(
            'project/display.html.twig',
            ['project' => $project, 'manager' => $projectManagerUser]
        );
    }
}
