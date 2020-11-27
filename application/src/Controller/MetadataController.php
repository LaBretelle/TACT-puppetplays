<?php

namespace App\Controller;

use App\Entity\EditorialContent;
use App\Entity\Media;
use App\Entity\Metadata;
use App\Entity\MetadataMedia;
use App\Entity\Platform;
use App\Entity\Project;
use App\Form\MediaMetadatasType;
use App\Form\MetadataMediaType;
use App\Form\MetadatasUploadType;
use App\Form\MetadataType;
use App\Service\AppEnums;
use App\Service\FlashManager;
use App\Service\MetadataManager;
use App\Service\PermissionManager;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Translation\TranslatorInterface;

/**
 * @Route("/metadata", name="metadata_")
 */
class MetadataController extends AbstractController
{
    private $flashManager;
    private $permissionManager;
    private $translator;
    private $metadataManager;

    public function __construct(
      FlashManager $flashManager,
      PermissionManager $permissionManager,
      TranslatorInterface $translator,
      MetadataManager $metadataManager
    ) {
        $this->flashManager = $flashManager;
        $this->permissionManager = $permissionManager;
        $this->translator = $translator;
        $this->metadataManager = $metadataManager;
    }



    /**
     * @Route("/{projectId}/upload", name="upload" )
     * @ParamConverter("project", class="App:Project", options={"id" = "projectId"})
     */
    public function upload(Project $project, Request $request)
    {
        if (false === $this->permissionManager->isAuthorizedOnProject($project, AppEnums::ACTION_EDIT_PROJECT)) {
            throw new AccessDeniedException($this->translator->trans('access_denied'));
        }


        $form = $this->createForm(MetadatasUploadType::class);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
          $fileContent = file_get_contents($form->get('metadatasFile')->getData());
          $this->metadataManager->import($project, $fileContent);

          return $this->redirectToRoute('project_metadatas', ['id' => $project->getId()]);
        }

        return $this->render(
          'metadata/upload.html.twig',
          [
            'form' => $form->createView(),
            'project' => $project
          ]
        );
    }

    /**
     * @Route("/{projectId}/edit/{metadataId}", name="edit", defaults={"metadataId"=null} )
     * @ParamConverter("project", class="App:Project", options={"id" = "projectId"})
     * @ParamConverter("metadata", class="App:Metadata", options={"id" = "metadataId"})
     */
    public function edit(Project $project, Metadata $metadata = null, Request $request)
    {
        if (false === $this->permissionManager->isAuthorizedOnProject($project, AppEnums::ACTION_EDIT_PROJECT)) {
            throw new AccessDeniedException($this->translator->trans('access_denied'));
        }

        $lastDefault = null;
        $options = [];
        if (!$metadata) {
            $metadata = new Metadata;
            $metadata->setProject($project);
        } else {
            $lastDefault = $metadata->getDefaultValue();
            $options["metadata"] = $metadata;
        }

        $form = $this->createForm(MetadataType::class, $metadata, $options);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $applyTo = $form->has('applyTo') ? $form->get('applyTo')->getData() : "all";
            $em = $this->getDoctrine()->getManager();

            // vérif doublon
            if ($doublon = $em->getRepository(Metadata::class)->findDuplicate($metadata)) {
                $this->flashManager->add('warning', 'metadata_already_existing');
            } else {
                $em->persist($metadata);
                $em->flush();
                $this->metadataManager->apply($metadata, $project, $lastDefault, $applyTo);
                $this->flashManager->add('notice', 'metadata_saved');

                return $this->redirectToRoute('project_metadatas', ['id' => $project->getId()]);
            }
        }

        return $this->render(
          'metadata/edit.html.twig',
          [
            'form' => $form->createView(),
            'project' => $project,
            'metadata' => $metadata
          ]
        );
    }

    /**
     * @Route("/apply/{id}", name="apply")
     */
    public function apply(Metadata $metadata)
    {
        $project = $metadata->getProject();
        if (false === $this->permissionManager->isAuthorizedOnProject($project, AppEnums::ACTION_EDIT_PROJECT)) {
            throw new AccessDeniedException($this->translator->trans('access_denied'));
        }
        $this->metadataManager->apply($metadata, $project);

        return;
    }

    /**
     * @Route("/show/{id}", name="show", options={"expose"=true})
     */
    public function show(Media $media)
    {
        $project = $media->getProject();
        if (false === $this->permissionManager->isAuthorizedOnProject($project, AppEnums::ACTION_EDIT_PROJECT)) {
            throw new AccessDeniedException($this->translator->trans('access_denied'));
        }

        $data = [];
        $data["isEmpty"] = count($media->getMetadatas()) == 0 ? true : false;
        $data["template"] = $this->renderView('metadata/show.html.twig', ['media' => $media]);

        return $this->json($data);
    }


    /**
     * @Route("/edit/{id}", name="edit_media", options={"expose"=true})
     */
    public function editMedia(Media $media, Request $request)
    {
        $project = $media->getProject();
        $parent = $media->getParent();
        if (false === $this->permissionManager->isAuthorizedOnProject($project, AppEnums::ACTION_EDIT_PROJECT)) {
            throw new AccessDeniedException($this->translator->trans('access_denied'));
        }
        $options["project"] = $project;
        $options["route"] = $this->generateUrl('metadata_edit_media', ['id' => $media->getId()]);
        $form = $this->createForm(MediaMetadatasType::class, $media, $options);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $em = $this->getDoctrine()->getManager();
            $em->persist($media);
            $em->flush();

            $this->flashManager->add('notice', 'metadata_saved');

            return $this->redirectToRoute('project_media', ['id' => $project->getId(), 'current' => $parent ? $parent->getId() : null ]);
        }

        $data = [];
        $data["template"] = $this->renderView('metadata/edit-media.html.twig', ['form' => $form->createView()]);

        return $this->json($data);
    }

    /**
     * @Route("/delete/{id}", name="delete")
     */
    public function delete(Metadata $metadata)
    {
        $project = $metadata->getProject();
        if (false === $this->permissionManager->isAuthorizedOnProject($project, AppEnums::ACTION_EDIT_PROJECT)) {
            throw new AccessDeniedException($this->translator->trans('access_denied'));
        }

        $em = $this->getDoctrine()->getManager();
        $metadatasMedia = $em->getRepository(MetadataMedia::class)->findByMetadata($metadata);
        foreach ($metadatasMedia as $metadataMedia) {
            $em->remove($metadataMedia);
        }

        $em->remove($metadata);
        $em->flush();
        $this->flashManager->add('notice', 'metadata supprimée');

        return $this->redirectToRoute('project_metadatas', ['id' => $project->getId()]);
    }
}
