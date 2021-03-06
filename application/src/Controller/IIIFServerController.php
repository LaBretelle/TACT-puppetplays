<?php

namespace App\Controller;

use App\Entity\IiifServer;
use App\Entity\Project;
use App\Form\IiifServerType;
use App\Service\AppEnums;
use App\Service\IiifManager;
use App\Service\PermissionManager;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Translation\TranslatorInterface;

/**
 * @Route("/iiif", name="iiif_")
 */
class IIIFServerController extends AbstractController
{
    private $iiifManager;
    private $permissionManager;
    private $translator;

    public function __construct(
        IiifManager $iiifManager,
        PermissionManager $permissionManager,
        TranslatorInterface $translator
    ) {
        $this->iiifManager = $iiifManager;
        $this->permissionManager = $permissionManager;
        $this->translator = $translator;
    }

    /**
     * @Route("/{projectId}/edit/{serverId}", name="edit", defaults={"serverId"=null} )
     * @ParamConverter("project", class="App:Project", options={"id" = "projectId"})
     * @ParamConverter("server", class="App:IiifServer", options={"id" = "serverId"})
     */
    public function edit(Project $project, IiifServer $server = null, Request $request)
    {
        if (false === $this->permissionManager->isAuthorizedOnProject($project, AppEnums::ACTION_EDIT_PROJECT)) {
            throw new AccessDeniedException($this->translator->trans('access_denied'));
        }

        if (!$server) {
            $server = $this->iiifManager->create($project);
        }

        $form = $this->createForm(IiifServerType::class, $server);
        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $this->iiifManager->save($server);

            return $this->redirectToRoute('project_edit-iiif', ['id' => $project->getId()]);
        }

        return $this->render(
            'iiif-server/edit.html.twig',
            [
            'form' => $form->createView(),
            'project' => $project,
            'server' => $server
          ]
        );
    }

    /**
     * @Route("/delete/{id}", name="delete")
     */
    public function delete(IiifServer $server)
    {
        $project = $server->getProject();
        if (false === $this->permissionManager->isAuthorizedOnProject($project, AppEnums::ACTION_EDIT_PROJECT)) {
            throw new AccessDeniedException($this->translator->trans('access_denied'));
        }

        $this->iiifManager->delete($server);

        return $this->redirectToRoute('project_edit-iiif', ['id' => $project->getId()]);
    }
}
