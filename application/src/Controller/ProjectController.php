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

        $form = $this->createForm(ProjectType::class, $project);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $image = $form->get('image')->getData();
            $previous_image = $request->get('previous_image');

            $userStatus = new UserProjectStatus();
            $manager = $form->get('manager')->getData();
            $userStatus->setStatus($this->getDoctrine()->getRepository(UserStatus::class)->findOneByName(AppEnums::USER_STATUS_MANAGER_NAME));
            $userStatus->setUser($manager);
            $project->addUserStatus($userStatus);

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

            $this->projectManager->editFromForm($project);
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
    public function manageProjectMedia(Request $request, Project $project, Directory $parent = null)
    {
        $form = $this->createForm(ProjectMediaType::class, $project);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $fileType = $request->get('file-type');
            $isZip = $fileType === 'zip';
            $media = $isZip ? $form->get('zip')->getData() : $form->get('images')->getData();
            $this->projectManager->addProjectMedia($project, $media, $isZip, $parent);
        }

        $file_limit = ini_get('max_file_uploads');

        return $this->render(
          'project/project-media.html.twig',
            [
            'form' => $form->createView(),
            'project' => $project,
            'fileLimit' => ini_get('max_file_uploads'),
            'parent' => $parent,
            'from' => 'media'
          ]
        );
    }

    /**
     * @Route("/{id}/transcription/{parent}", name="transcriptions", options={"expose"=true}, defaults={"parent"=null})
     */
    public function displayTranscriptions(Project $project, Directory $parent = null)
    {
        return $this->render(
            'media/transcriptions.html.twig',
            [
              'project' => $project,
              'parent' => $parent,
              'from' => 'transcript'
            ]
        );
    }

    /**
     * @Route("/media-delete", name="media_delete", options={"expose"=true}, methods="POST")
     */
    public function removeProjectMedia(Request $request)
    {
        $ids = $request->request->get('ids');
        $this->projectManager->removeProjectMedia($ids);
        return $this->json([], $status = 200);
    }

    /**
     * @Route("/move-media", name="move_media", options={"expose"=true}, methods="POST")
     */
    public function moveProjectMedia(Request $request)
    {
        $target = intval($request->request->get('dirId'));
        $ids = $request->request->get('ids');
        $this->projectManager->moveProjectMedia($target, $ids);
        return $this->json(['ids' => $ids, 'target' => $target], $status = 200);
    }

    /**
     * @Route("/{id}/add-folder", name="add_folder", methods="POST")
     */
    public function addFolderToProject(Project $project, Request $request)
    {
        $parentId = intval($request->request->get('parent'));
        $name = $request->request->get('folderName');
        $newFolder = $this->projectManager->addFolder($project, $parentId, $name);
        return $this->redirectToRoute('project_media', ['id' => $project->getId(), 'parent' => $newFolder->getId()]);
    }

    /**
     * @Route("/update-folder-name", name="update_folder_name", options={"expose"=true}, methods="POST")
     */
    public function updateProjectFolderName(Request $request)
    {
        $name = $request->request->get('name');
        $folderId = intval($request->request->get('id'));
        $folder = $this->projectManager->updateFolderName($folderId, $name);
        return $this->json(['name' => $folder->getName(), 'id' => $folder->getId()], $status = 200);
    }

    /**
     * @Route("/{id}/delete-folders", name="delete_folders", methods="POST")
     */
    public function deleteProjectFolders(Project $project, Request $request)
    {
        $ids = $request->request->get('ids');
        $this->projectManager->deleteFoldersAndMedia($ids);
        return $this->redirectToRoute('project_media', ['id' => $project->getId(), 'parent' => null]);
    }

    /**
     * @Route("/{id}/move-folders", name="move_folders", options={"expose"=true}, methods="POST")
     */
    public function moveProjectFolders(Project $project, Request $request)
    {
        $target = intval($request->request->get('dirId'));
        $ids = $request->request->get('ids');
        $this->projectManager->moveProjectFolders($target, $ids);
        return $this->json(['ids' => $ids, 'target' => $target], $status = 200);
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
    /*public function process(Project $project)
    {
        $projectManagerUser = $this->projectManager->initMediaProcessing($project);

        return $this->render(
            'project/display.html.twig',
            ['project' => $project, 'manager' => $projectManagerUser]
        );
    }*/
}
