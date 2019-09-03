<?php

namespace App\Controller;

use App\Entity\Directory;
use App\Entity\Media;
use App\Entity\Project;
use App\Entity\User;
use App\Entity\UserProjectStatus;
use App\Entity\UserStatus;
use App\Form\ExportType;
use App\Form\ProjectMediaType;
use App\Form\XmlType;
use App\Form\ProjectAdvancedType;
use App\Form\ProjectBasicType;
use App\Service\AppEnums;
use App\Service\ExportManager;
use App\Service\FileManager;
use App\Service\FlashManager;
use App\Service\PermissionManager;
use App\Service\ProjectManager;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\EntityManagerInterface;
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
    private $exportManager;

    public function __construct(
        ProjectManager $projectManager,
        FileManager $fileManager,
        FlashManager $flashManager,
        PermissionManager $permissionManager,
        TranslatorInterface $translator,
        Security $security,
        ExportManager $exportManager
    ) {
        $this->projectManager = $projectManager;
        $this->fileManager = $fileManager;
        $this->flashManager = $flashManager;
        $this->permissionManager = $permissionManager;
        $this->translator = $translator;
        $this->security = $security;
        $this->exportManager = $exportManager;
    }

    /**
    * @Route("/{id}/export", name="export")
    */
    public function export(Project $project, Request $request)
    {
        if (false === $this->permissionManager->isAuthorizedOnProject($project, AppEnums::ACTION_EDIT_PROJECT)) {
            throw new AccessDeniedException($this->translator->trans('access_denied'));
        }

        $form = $this->createForm(ExportType::class);
        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $params = [
              'medias' => $form->get('medias')->getData(),
              'transcriptions' => $form->get('transcriptions')->getData(),
              'transcriptionsList' => $form->get('transcriptions_list')->getData(),
              'usersList' => $form->get('users_list')->getData(),
              'infos' => $form->get('project_infos')->getData()
            ];

            $zipName = $this->exportManager->export($project, $params);

            return $this->file($zipName);
        }

        return $this->render('project/export.html.twig', [
          'project' => $project,
          'form' => $form->createView()
        ]);
    }

    /**
     * @Route("/create", name="create")
     */
    public function create(Request $request)
    {
        $this->denyAccessUnlessGranted('ROLE_ADMIN', null, '');
        $project = new Project();

        $form = $this->createForm(ProjectBasicType::class, $project);
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

            return $this->redirectToRoute('project_edit-advanced', ['id' => $project->getId()]);
        }

        return $this->render('project/edit-basic.html.twig', [
          'project' => $project,
          'form' => $form->createView()
        ]);
    }

    /**
     * @Route("{id}/edit/choice", name="edit_choice")
     */
    public function editChoice(Project $project)
    {
        if (false === $this->permissionManager->isAuthorizedOnProject($project, AppEnums::ACTION_EDIT_PROJECT)) {
            throw new AccessDeniedException($this->translator->trans('access_denied'));
        }
      
        return $this->render('project/edit-choice.html.twig', [
          'project' => $project,
      ]);
    }

    /**
     * @Route("/{id}/edit-basic", name="edit-basic")
     */
    public function editBasic(Project $project, Request $request)
    {
        if (false === $this->permissionManager->isAuthorizedOnProject($project, AppEnums::ACTION_EDIT_PROJECT)) {
            throw new AccessDeniedException($this->translator->trans('access_denied'));
        }

        $form = $this->createForm(ProjectBasicType::class, $project);

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $image = $form->get('image')->getData();
            $previous_image = $request->get('previous_image');
            $this->projectManager->handleImage($project, $image, $previous_image);
            $this->flashManager->add('notice', 'project_edited');

            return $this->redirectToRoute('project_edit_choice', ['id' => $project->getId()]);
        }

        return $this->render('project/edit-basic.html.twig', [
          'project' => $project,
          'form' => $form->createView()
        ]);
    }

    /**
     * @Route("/{id}/edit-advanced", name="edit-advanced")
     */
    public function editAdvanced(Project $project, Request $request)
    {
        if (false === $this->permissionManager->isAuthorizedOnProject($project, AppEnums::ACTION_EDIT_PROJECT)) {
            throw new AccessDeniedException($this->translator->trans('access_denied'));
        }

        $form = $this->createForm(ProjectAdvancedType::class, $project);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $xsltExport = $form->get('xslt_export')->getData();
            $this->projectManager->handleXslExport($project, $xsltExport);
            $jsonSchema = $form->get('json_schema')->getData();
            $this->projectManager->handleJsonSchema($project, $jsonSchema);
            $this->flashManager->add('notice', 'project_edited');

            return $this->redirectToRoute('project_edit_choice', ['id' => $project->getId()]);
        }

        return $this->render('project/edit-advanced.html.twig', [
          'project' => $project,
          'form' => $form->createView()
        ]);
    }



    /**
     * @Route("/{id}/edit", name="edit")
     */
    public function edit(Project $project, Request $request)
    {
        if (false === $this->permissionManager->isAuthorizedOnProject($project, AppEnums::ACTION_EDIT_PROJECT)) {
            throw new AccessDeniedException($this->translator->trans('access_denied'));
        }

        $originalReviewLimit = $project->getnbValidation();
        $form = $this->createForm(ProjectType::class, $project);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $image = $form->get('image')->getData();
            $previous_image = $request->get('previous_image');
            $xsltExport = $form->get('xslt_export')->getData();

            $this->projectManager->save($project);
            $this->projectManager->handleImage($project, $image, $previous_image);
            $this->projectManager->handleXslExport($project, $xsltExport);
            $this->projectManager->handleReviewLimit($project, $originalReviewLimit);
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
            throw new AccessDeniedException($this->translator->trans('access_denied'));
        }

        $formMedia = $this->createForm(ProjectMediaType::class, $project);
        $formXml = $this->createForm(XmlType::class, $project);

        $formMedia->handleRequest($request);
        $formXml->handleRequest($request);

        $parameters = [];
        $parameters["createEmptyMedia"] = false;
        $parameters["overwrite"] = false;
        $parameters["validTranscript"] = false;
        $parameters["isZip"] = false;
        $parameters["updateMedia"] = false;
        $parameters["rootTag"] = "";


        if ($formMedia->isSubmitted() && $formMedia->isValid()) {
            $fileType = $request->get('file-type');
            $isZip = $fileType === 'zip';
            $updateMedia = $formMedia->get('update_media')->getData();
            $media = $isZip ? $formMedia->get('zip')->getData() : $formMedia->get('images')->getData();

            $parameters ["isZip"] = $isZip;
            $parameters ["updateMedia"] = $updateMedia;

            $this->projectManager->addProjectMedia($project, $media, $current, $parameters);
        }

        if ($formXml->isSubmitted() && $formXml->isValid()) {
            $fileType = $request->get('file-type');

            $createEmptyMedia = $formXml->get('create_empty_media')->getData();
            $overwrite = $formXml->get('overwrite')->getData();
            $rootTag = $formXml->get('rootTag')->getData();
            $validTranscript = $formXml->get('auto_valid_transcript')->getData();
            $isZip = $fileType === 'zip';

            $parameters ["createEmptyMedia"] = $createEmptyMedia;
            $parameters ["overwrite"] = $overwrite;
            $parameters ["validTranscript"] = $validTranscript;
            $parameters ["isZip"] = $isZip;
            $parameters ["rootTag"] = $rootTag;

            $xmls = $isZip ? $formXml->get('zip')->getData() : $formXml->get('xmls')->getData();
            $this->projectManager->addProjectXml($project, $xmls, $current, $parameters);
        }


        $file_limit = ini_get('max_file_uploads');

        return $this->render(
            'project/project-media.html.twig',
            [
              'form' => $formMedia->createView(),
              'formXml' => $formXml->createView(),
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
            throw new AccessDeniedException($this->translator->trans('access_denied'));
        }

        $user = $this->security->getUser();
        $mediaRepo =  $this->getDoctrine()->getRepository(Media::class);
        $medias = $mediaRepo->findby(['project' => $project,'parent' => $parent], ["name" => "ASC"]);
        $mine = $user ? $mediaRepo->getByProjectAndUserActivity($project, $parent, $user): null;
        $transcriptionsLocked = $user ? $mediaRepo->getByProjectAndLocked($project, $parent, $user): null;

        return $this->render(
            'transcribe/index.html.twig',
            [
              'project' => $project,
              'parent' => $parent,
              'medias' => $medias,
              'mine' => $mine,
              'transcriptionsLocked' =>  $transcriptionsLocked,
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
        $movedMedia = $this->projectManager->moveProjectMedia($target, $ids);

        return $this->json(['movedMedia' => $movedMedia], $status = 200);
    }

    /**
     * @Route("/{id}/add-folder", name="add_folder", methods="POST")
     */
    public function addFolderToProject(Project $project, Request $request)
    {
        if (false === $this->permissionManager->isAuthorizedOnProject($project, AppEnums::ACTION_MANAGE_MEDIA)) {
            throw new AccessDeniedException($this->translator->trans('access_denied'));
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
        $hasBeenUpdated = $this->projectManager->updateFolderName($folderId, $name);

        return $this->json(['hasBeenUpdated' => $hasBeenUpdated], $status = 200);
    }

    /**
     * @Route("/{id}/delete-folders", name="delete_folders", methods="POST")
     */
    public function deleteProjectFolders(Project $project, Request $request)
    {
        if (false === $this->permissionManager->isAuthorizedOnProject($project, AppEnums::ACTION_MANAGE_MEDIA)) {
            throw new AccessDeniedException($this->translator->trans('access_denied'));
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

    /**
     * @Route("{id}/xslt/delete", name="xslt_delete", options={"expose"=true}, methods="DELETE")
     */
    public function deleteXslt(Project $project)
    {
        if (false === $this->permissionManager->isAuthorizedOnProject($project, AppEnums::ACTION_EDIT_PROJECT)) {
            return $this->json([], $status = 403);
        }

        $this->projectManager->deleteXslt($project);

        return $this->json([], $status = 200);
    }

    /**
     * @Route("{id}/json/delete", name="json_delete", options={"expose"=true}, methods="DELETE")
     */
    public function deleteJson(Project $project)
    {
        if (false === $this->permissionManager->isAuthorizedOnProject($project, AppEnums::ACTION_EDIT_PROJECT)) {
            return $this->json([], $status = 403);
        }

        $this->projectManager->deleteJson($project);

        return $this->json([], $status = 200);
    }

    /**
     * @Route("{id}/toggle-archived", name="archived_toggle")
     */
    public function toggleArchived(Project $project, EntityManagerInterface $em)
    {
        if (false === $this->permissionManager->isAuthorizedOnProject($project, AppEnums::ACTION_ARCHIVE)) {
            return $this->json([], $status = 403);
        }

        $statusArchived = $project->getArchived();
        $project->setArchived(!$statusArchived);
        $em->persist($project);
        $em->flush();


        return $this->redirectToRoute('project_display', ['id' => $project->getId()]);
    }
}
