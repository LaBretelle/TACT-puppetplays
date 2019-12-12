<?php

namespace App\Controller\Admin;

use App\Entity\User;
use App\Form\UserAdminType;
use App\Service\UserManager;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;

/** @Route("/admin/user", name="admin_user_") */
class UserAdminController extends AbstractController
{
    private $userManager;

    public function __construct(UserManager $userManager)
    {
        $this->userManager = $userManager;
    }

    /**
     * @Route("/", name="list", methods="GET")
     */
    public function listUsers(Request $request)
    {
        $user = new User();
        $form = $this->createForm(UserAdminType::class, $user);
        $form->handleRequest($request);
        return $this->render(
            'admin/user/users-list.html.twig',
            ['form' => $form->createView()]
        );
    }

    /**
     * @Route("/create", name="create", methods="POST")
     */
    public function createUser(Request $request)
    {
        $user = new User();
        $form = $this->createForm(UserAdminType::class, $user);
        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $formdata = $request->get('user_admin');
            $isAdmin = isset($formdata['isAdmin']);
            $this->userManager->createUserFromForm($user, $isAdmin);
        }
        return $this->redirectToRoute('admin_user_list');
    }

    /**
     *
     * @Route("/fetch", options={"expose"=true}, name="fetch", methods="POST")
     */
    public function fetchUsers(Request $request)
    {
        $repository = $this->getDoctrine()->getRepository(User::class);
        $queryString = $request->get('query');
        $users = $repository->findByQueryString($queryString);

        return $this->render(
            'admin/user/users-partial.html.twig',
            ['users' => $users]
        );
    }


    /**
     *
     * @Route("/activate/{id}", options={"expose"=true}, name="activate_account", methods="POST")
     */
    public function activateAccount(Request $request, User $user)
    {
        $isActive = $request->get('active') === 'true';
        $user->setActive($isActive);
        $this->userManager->saveUser($user);
        return $this->json([], $status = 200);
    }

    /**
     *
     * @Route("/delete/{id}", name="anonymize_account", methods="POST")
     */
    public function anonymizeUserAccount(Request $request, User $user)
    {
        $success = $this->userManager->anonymizeUserAccount($user, $request->get('type'));

        return $this->redirectToRoute('admin_user_list');
    }

    /**
     * @Route("/user/role/{id}/{role}", name="set_role")
     */
    public function setRole(User $user, $role)
    {
        $this->userManager->setRole($user, [$role]);

        return $this->redirectToRoute('admin_user_list');
    }
}
