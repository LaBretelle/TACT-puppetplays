<?php

namespace App\Controller;

use App\Entity\Project;
use App\Entity\UserProjectStatus;
use App\Form\UserStatusType;
use App\Service\AppEnums;
use App\Service\PermissionManager;
use App\Service\UserProjectStatusManager;
use Doctrine\Common\Collections\ArrayCollection;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;
use Symfony\Component\Translation\TranslatorInterface;

/**
 * @Route("/status", name="status_")
 */
class UserProjectStatusController extends Controller
{
    private $statusManager;
    private $translator;
    private $permissionManager;

    public function __construct(UserProjectStatusManager $statusManager, TranslatorInterface $translator, PermissionManager $permissionManager)
    {
        $this->statusManager = $statusManager;
        $this->translator = $translator;
        $this->permissionManager = $permissionManager;
    }

    /**
     * @Route("/{id}/toggle", name="toggle")
     */
    public function toggle(UserProjectStatus $status)
    {
        if (false === $this->permissionManager->isAuthorizedOnProject($project, AppEnums::ACTION_MANAGE_USER)) {
            throw new AccessDeniedException($this->translator->trans('access_denied', [], 'messages'));
        }

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
        if (false === $this->permissionManager->isAuthorizedOnProject($project, AppEnums::ACTION_MANAGE_USER)) {
            throw new AccessDeniedException($this->translator->trans('access_denied', [], 'messages'));
        }

        $this->statusManager->remove($status);

        return $this->redirectToRoute('status_project', ["id" => $status->getProject()->getId()]);
    }

    /**
     * @Route("/{id}/form", name="form_get", options={"expose"=true}, methods="GET")
     */
    public function getForm(UserProjectStatus $status)
    {
        if (false === $this->permissionManager->isAuthorizedOnProject($project, AppEnums::ACTION_MANAGE_USER)) {
            throw new AccessDeniedException($this->translator->trans('access_denied', [], 'messages'));
        }

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
     * @Route("/{id}/form", name="form_post", methods="POST")
     */
    public function postForm(UserProjectStatus $userProjectStatus, Request $request)
    {
        if (false === $this->permissionManager->isAuthorizedOnProject($project, AppEnums::ACTION_MANAGE_USER)) {
            throw new AccessDeniedException($this->translator->trans('access_denied', [], 'messages'));
        }

        $oldStatus = $userProjectStatus->getStatus()->getName();

        $form = $this->createForm(UserStatusType::class, $userProjectStatus);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid() && $this->statusManager->canEdit($userProjectStatus, $oldStatus)) {
            $em = $this->getDoctrine()->getManager();
            $em->persist($userProjectStatus);
            $em->flush();
        }

        return $this->redirectToRoute('status_project', ["id" => $userProjectStatus->getProject()->getId()]);
    }

    /**
     * @Route("/project/{id}", name="project")
     */
    public function handleUserStatuses(Project $project)
    {
        if (false === $this->permissionManager->isAuthorizedOnProject($project, AppEnums::ACTION_MANAGE_USER)) {
            throw new AccessDeniedException($this->translator->trans('access_denied', [], 'messages'));
        }

        return $this->render(
          'project/user-statuses.html.twig',
          ['project' => $project]
      );
    }
}
