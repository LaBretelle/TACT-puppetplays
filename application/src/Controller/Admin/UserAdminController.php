<?php

namespace App\Controller\Admin;

use App\Entity\User;
use App\Service\UserManager;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;

/** @Route("/admin/user", name="admin_user_") */
class UserAdminController extends Controller
{
    private $userManager;

    public function __construct(UserManager $userManager)
    {
        $this->userManager = $userManager;
    }

    /**
     * @Route("/", name="list", methods="GET")
     */
    public function listUsers()
    {
        return $this->render(
            'admin/user/users-list.html.twig',
            []
        );
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
     * @ParamConverter("user", class="App:User")
     */
    public function activateAccount(Request $request, User $user)
    {
        $isActive = $request->get('active') === 'true';
        $user->setActive($isActive);
        $this->userManager->saveUser($user);
        return $this->json([], $status = 200);
    }
}
