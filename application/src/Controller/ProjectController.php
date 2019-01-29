<?php

namespace App\Controller;

use App\Entity\Directory;
use App\Entity\Media;
use App\Entity\Project;
use App\Entity\User;
use App\Entity\UserProjectStatus;
use App\Entity\UserStatus;
use App\Form\ProjectMediaType;
use App\Form\ProjectType;
use App\Service\AppEnums;
use App\Service\FileManager;
use App\Service\FlashManager;
use App\Service\PermissionManager;
use App\Service\ProjectManager;
use Doctrine\Common\Collections\ArrayCollection;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;
use Symfony\Component\Security\Core\Security;
use Symfony\Component\Translation\TranslatorInterface;

/**
 * @Route("/project", name="project_")
 */
class ProjectController extends AbstractController
{
    private $projectManager;
    private $fileManager;
    private $flashManager;
    private $permissionManager;
    private $translator;
    private $security;

    public function __construct(ProjectManager $projectManager, FileManager $fileManager, FlashManager $flashManager, PermissionManager $permissionManager, TranslatorInterface $translator, Security $security)
    {
        $this->projectManager = $projectManager;
        $this->fileManager = $fileManager;
        $this->flashManager = $flashManager;
        $this->permissionManager = $permissionManager;
        $this->translator = $translator;
        $this->security = $security;
    }

    /**
     * @Route("/create", name="create")
     */
    public function create(Request $request)
    {
        $this->denyAccessUnlessGranted('ROLE_ADMIN', null, '');
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
        if (false === $this->permissionManager->isAuthorizedOnProject($project, AppEnums::ACTION_EDIT_PROJECT)) {
            throw new AccessDeniedException($this->translator->trans('access_denied', [], 'messages'));
        }
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
        $this->denyAccessUnlessGranted('ROLE_ADMIN', null, '');
        $this->projectManager->delete($project);
        $this->flashManager->add('info', 'project_deleted');

        return $this->redirectToRoute('home');
    }

    /**
     * @Route("/{id}/media/{current}", name="media", requirements={"current"="\d+"}, defaults={"current"=null})
     * @ParamConverter("current", class="App:Directory", options={"id" = "current"})
     */
    public function manageProjectMedia(Request $request, Project $project, Directory $current = null)
    {
        if (false === $this->permissionManager->isAuthorizedOnProject($project, AppEnums::ACTION_MANAGE_MEDIA)) {
            throw new AccessDeniedException($this->translator->trans('access_denied', [], 'messages'));
        }

        $form = $this->createForm(ProjectMediaType::class, $project);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $fileType = $request->get('file-type');
            $isZip = $fileType === 'zip';
            $media = $isZip ? $form->get('zip')->getData() : $form->get('images')->getData();
            $this->projectManager->addProjectMedia($project, $media, $isZip, $current);
        }

        $file_limit = ini_get('max_file_uploads');
        return $this->render(
          'project/project-media.html.twig',
            [
            'form' => $form->createView(),
            'project' => $project,
            'fileLimit' => ini_get('max_file_uploads'),
            'current' => $current,
            'from' => 'media'
          ]
        );
    }

    /**
     * @Route("/{id}/transcription/{parent}", name="transcriptions", options={"expose"=true}, defaults={"parent"=null})
     * @ParamConverter("project", class="App:Project", options={"id" = "id"})
     * @ParamConverter("parent", class="App:Directory", options={"id" = "parent"})
     */
    public function displayTranscriptions(Project $project, Directory $parent = null)
    {
        if (false === $this->permissionManager->isAuthorizedOnProject($project, AppEnums::ACTION_VIEW_TRANSCRIPTIONS)) {
            throw new AccessDeniedException($this->translator->trans('access_denied', [], 'messages'));
        }

        $user = $this->security->getUser();
        $mediaRepo =  $this->getDoctrine()->getRepository(Media::class);
        $medias = $mediaRepo->findby(['project' => $project,'parent' => $parent ]);
        $mine = $user ? $mediaRepo->getByProjectAndUserActivity($project, $parent, $user): null;

        return $this->render(
            'transcribe/index.html.twig',
            [
              'project' => $project,
              'parent' => $parent,
              'medias' => $medias,
              'mine' => $mine,
              'from' => 'transcript'
            ]
        );
    }


    /**
     * @Route("/{id}/media-delete", name="media_delete", options={"expose"=true}, methods="POST")
     */
    public function removeProjectMediaByIds(Project $project, Request $request)
    {
        if (false === $this->permissionManager->isAuthorizedOnProject($project, AppEnums::ACTION_MANAGE_MEDIA)) {
            return $this->json([], $status = 403);
        }

        $ids = $request->request->get('ids');
        $this->projectManager->removeProjectMediaByIds($ids);

        return $this->json([], $status = 200);
    }

    /**
     * @Route("/{id}/move-media", name="move_media", options={"expose"=true}, methods="POST")
     */
    public function moveProjectMedia(Project $project, Request $request)
    {
        if (false === $this->permissionManager->isAuthorizedOnProject($project, AppEnums::ACTION_MANAGE_MEDIA)) {
            return $this->json([], $status = 403);
        }

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
        if (false === $this->permissionManager->isAuthorizedOnProject($project, AppEnums::ACTION_MANAGE_MEDIA)) {
            throw new AccessDeniedException($this->translator->trans('access_denied', [], 'messages'));
        }

        $parentId = intval($request->request->get('parent'));
        $name = $request->request->get('folderName');
        $newFolder = $this->projectManager->addFolder($project, $parentId, $name);

        return $this->redirectToRoute('project_media', ['id' => $project->getId(), 'parent' => $newFolder->getId()]);
    }

    /**
     * @Route("/{id}/update-folder-name", name="update_folder_name", options={"expose"=true}, methods="POST")
     */
    public function updateProjectFolderName(Project $project, Request $request)
    {
        if (false === $this->permissionManager->isAuthorizedOnProject($project, AppEnums::ACTION_MANAGE_MEDIA)) {
            return $this->json([], $status = 403);
        }

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
        if (false === $this->permissionManager->isAuthorizedOnProject($project, AppEnums::ACTION_MANAGE_MEDIA)) {
            throw new AccessDeniedException($this->translator->trans('access_denied', [], 'messages'));
        }

        $ids = $request->request->get('ids');
        $this->projectManager->deleteFolders($project, $ids);

        return $this->redirectToRoute('project_media', ['id' => $project->getId(), 'parent' => null]);
    }

    /**
     * @Route("/{id}/move-folders", name="move_folders", options={"expose"=true}, methods="POST")
     */
    public function moveProjectFolders(Project $project, Request $request)
    {
        if (false === $this->permissionManager->isAuthorizedOnProject($project, AppEnums::ACTION_MANAGE_MEDIA)) {
            return $this->json([], $status = 403);
        }

        $target = intval($request->request->get('dirId'));
        $ids = $request->request->get('ids');
        $this->projectManager->moveProjectFolders($target, $ids);

        return $this->redirectToRoute('project_media', ['id' => $project->getId(), 'parent' => null]);
    }

    /**
     * @Route("/{id}", name="display")
     */
    public function display(Project $project)
    {
        $projectManagerUser = $this->projectManager->getProjectManagerUser($project);
        $contributors = $this->getDoctrine()->getRepository(User::class)->getByProject($project);

        return $this->render(
            'project/display.html.twig',
            [
              'project' => $project,
              'manager' => $projectManagerUser,
              'contributors' => $contributors
            ]
        );
    }

    /**
     * @Route("{id}/image/delete", name="image_delete", options={"expose"=true}, methods="DELETE")
     */
    public function deleteImage(Project $project, Request $request)
    {
        if (false === $this->permissionManager->isAuthorizedOnProject($project, AppEnums::ACTION_EDIT_PROJECT)) {
            return $this->json([], $status = 403);
        }

        $this->projectManager->deleteImage($project);

        return $this->json([], $status = 200);
    }
}
