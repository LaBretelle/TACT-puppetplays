<?php

namespace App\Controller\Admin;

use App\Entity\User;
use App\Service\MailManager;
use App\Service\UserManager;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Translation\TranslatorInterface;

/** @Route("/admin/user", name="admin_user_") */
class UserAdminController extends Controller
{
    private $userManager;
    private $mailManager;
    private $translator;

    public function __construct(UserManager $userManager, MailManager $mailManager, TranslatorInterface $translator)
    {
        $this->userManager = $userManager;
        $this->mailManager = $mailManager;
        $this->translator = $translator;
    }

    /**
     * @Route("/", name="list")
     * @Method("GET")
     */
    public function listUsers()
    {
        $repository = $this->getDoctrine()->getRepository(User::class);
        $users = $repository->findAll();
        return $this->render(
            'admin/user/list.html.twig',
            ['users' => $users]
        );
    }


    /**
     *
     * @Route("/activate/{id}", name="activate_account")
     * @Method("POST")
     * @ParamConverter("user", class="App:User")
     */
    public function activateAccount(Request $request, User $user)
    {
        $user->setActive(true);
        return new JsonResponse(['status' => 200, 'message' => 'success']);
    }
}
