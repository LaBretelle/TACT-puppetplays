<?php

namespace App\Controller;

use App\Entity\User;
use App\Form\UserType;
use App\Service\MailManager;
use App\Service\UserManager;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Authentication\AuthenticationUtils;

class UserController extends Controller
{
    private $userManager;
    private $mailManager;

    public function __construct(UserManager $userManager, MailManager $mailManager)
    {
        $this->userManager = $userManager;
        $this->mailManager = $mailManager;
    }

    /**
     * @Route("/register", name="register")
     */
    public function register(Request $request)
    {
        $user = new User();
        $form = $this->createForm(UserType::class, $user);
        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            /** @var Symfony\Component\HttpFoundation\File\UploadedFile $file */
            $image = $form->get('image')->getData();
            $this->userManager->createUserFromForm($user, $image);
            $this->mailManager->sendConfirmationMail($user);
            $this->addFlash(
              'notice',
              'Your account was created but not activated. You\'ll receive an email to activate your account.'
            );
            return $this->redirectToRoute('home');
        }

        return $this->render(
            'user/register.html.twig',
            array('form' => $form->createView())
        );
    }

    /**
     * @Route("/login", name="login")
     */
    public function login(Request $request, AuthenticationUtils $authenticationUtils)
    {
        // get the login error if there is one
        $error = $authenticationUtils->getLastAuthenticationError();

        // last username entered by the user
        $lastUsername = $authenticationUtils->getLastUsername();

        return $this->render('user/login.html.twig', [
         'last_username' => $lastUsername,
         'auth_error'    => $error,
        ]);
    }

    /**
     * [recoverPassword description]
     * @return [type] [description]
     */
    public function recoverPassword()
    {
    }

    /**
     * [confirmRegistration description]
     * @Route("/confirm/{token}", name="user_register_confirm", requirements={"token"=".+"})
     */
    public function confirmRegistration(string $token)
    {
        $repository = $this->getDoctrine()->getRepository(User::class);
        $em = $this->getDoctrine()->getManager();
        $user = $repository->findOneBy(['confirmationToken' => $token]);
        if ($user) {
            $user->setActive(true);
            $user->setConfirmationToken(null);
            $em->persist($user);
            $em->flush();
            $this->addFlash(
            'notice',
            'Your account is activated.'
          );
            return $this->redirectToRoute('home');
        }

        $this->addFlash(
          'danger',
          'A problem occured while trying to activate your account.'
        );
        return $this->redirectToRoute('home');
    }
}
