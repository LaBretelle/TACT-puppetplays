<?php

namespace App\Controller;

use App\Entity\User;
use App\Form\UserResetPasswordType;
use App\Form\UserType;
use App\Form\UserTypeFull;
use App\Service\FlashManager;
use App\Service\MailManager;
use App\Service\PermissionManager;
use App\Service\UserManager;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\File\File;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\ResponseHeaderBag;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Core\Authorization\AuthorizationCheckerInterface;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;
use Symfony\Component\Security\Http\Authentication\AuthenticationUtils;
use Symfony\Component\Translation\TranslatorInterface;

/** @Route("/user", name="user_") */
class UserController extends AbstractController
{
    private $userManager;
    private $mailManager;
    private $translator;
    private $flashManager;

    public function __construct(
        UserManager $userManager,
        MailManager $mailManager,
        TranslatorInterface $translator,
        FlashManager $flashManager
    ) {
        $this->userManager = $userManager;
        $this->mailManager = $mailManager;
        $this->translator = $translator;
        $this->flashManager = $flashManager;
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
            $this->userManager->createUserFromForm($user);
            $this->mailManager->sendConfirmationMail($user);
            $this->flashManager->add('notice', 'user_account_created');

            return $this->redirectToRoute('home');
        }

        return $this->render(
            'user/register.html.twig',
            array('form' => $form->createView())
        );
    }

    /**
     * Edit a user account. Should be reachable by admin or owner of the account.
     *
     * @Route("/edit/{id}", name="edit")
     */
    public function edit(User $user, Request $request, AuthorizationCheckerInterface $authChecker)
    {
        // if not authenticated redirect to login page
        $this->denyAccessUnlessGranted('IS_AUTHENTICATED_FULLY', null, $this->translator->trans('access_denied', [], 'messages'));

        $connectedUser = $this->getUser();
        if ($connectedUser->getId() !== $user->getId() && false === $authChecker->isGranted('ROLE_ADMIN')) {
            throw new AccessDeniedException($this->translator->trans('access_denied', [], 'messages'));
        }

        $form = $this->createForm(UserTypeFull::class, $user);
        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $image = $form->get('image')->getData();
            $previous_image = $request->get('previous_image');

            $this->userManager->updateUser($user, $image, $previous_image);
            $this->flashManager->add('notice', 'user_account_updated');

            return $this->redirectToRoute('home');
        }

        return $this->render(
            'user/account.html.twig',
            ['form' => $form->createView(), 'user' => $user]
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
     * @Route("/profile/{id}", name="profile")
     */
    public function display(User $user, AuthorizationCheckerInterface $authChecker)
    {
        $this->denyAccessUnlessGranted('IS_AUTHENTICATED_FULLY', null, $this->translator->trans('access_denied', [], 'messages'));

        $connectedUser = $this->getUser();
        if ($connectedUser->getId() !== $user->getId() && false === $authChecker->isGranted('ROLE_ADMIN')) {
            throw new AccessDeniedException($this->translator->trans('access_denied', [], 'messages'));
        }

        return $this->render('user/display.html.twig', [
         'user' => $user,
        ]);
    }

    /**
     *
     * @Route("/password-lost", name="lost_password_form")
     */
    public function lostPassword(Request $request)
    {
        $userData = $request->get('user_data');
        if ($userData) {
            // check if user exist and that he has not requested a password less than two hours ago
            if ($user = $this->userManager->userCanRenewPassword($userData)) {
                $token = base64_encode(random_bytes(10));
                $user->setConfirmationToken($token);
                $user->setPasswordRequestedAt(new \DateTime('now'));
                $em = $this->getDoctrine()->getManager();
                $em->persist($user);
                $em->flush();
                $this->mailManager->sendRecoverPasswordMail($user);
                $mailparts = explode('@', $user->getEmail());
                $obfuscatedMail = substr_replace($mailparts[0], '*', 3).'@'.$mailparts[1];
                $this->flashManager->add('notice', 'user_renew_password_mail_sent', ['%usermail%' => $obfuscatedMail]);
            } else {
                $this->flashManager->add('error', 'user_renew_password_error');
            }
        }

        return $this->render('user/recover.html.twig');
    }

    /**
     *
     * @Route("/reset/{token?}", name="reset_password", requirements={"token"=".+"})
     */
    public function resetPassword(Request $request, string $token = null)
    {
        $repository = $this->getDoctrine()->getRepository(User::class);
        if (null === $token) {
            $token = $request->get('token');
            if (!$token) {
                $this->flashManager->add('error', 'user_renew_password_error');

                return $this->redirectToRoute('home');
            }
        }

        $user = $repository->findOneBy(['confirmationToken' => $token]);

        if (!$user) {
            $this->flashManager->add('error', 'user_renew_password_error');

            return $this->redirectToRoute('home');
        }
        $form = $this->createForm(UserResetPasswordType::class, $user);
        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            if ($this->userManager->resetPassword($user)) {
                $this->flashManager->add('notice', 'user_renew_password_success');
            } else {
                $this->flashManager->add('error', 'user_renew_password_error');
            }

            return $this->redirectToRoute('home');
        }

        return $this->render(
            'user/reset.password.html.twig',
            array('form' => $form->createView(), 'token' => $token)
        );
    }

    /**
     *
     * @Route("/activate/{token}", name="activate_account", requirements={"token"=".+"})
     */
    public function activateAccount(string $token)
    {
        $repository = $this->getDoctrine()->getRepository(User::class);
        $em = $this->getDoctrine()->getManager();
        $user = $repository->findOneBy(['confirmationToken' => $token]);
        if ($user) {
            $user->setActive(true);
            $user->setConfirmationToken(null);
            $em->persist($user);
            $em->flush();
            $this->flashManager->add('notice', 'user_account_activated');

            return $this->redirectToRoute('home');
        }

        $this->flashManager->add('error', 'user_account_activated_error');

        return $this->redirectToRoute('home');
    }

    /**
     *  @Route("/export/{id}", name="export", methods="POST")
     * @return Response
     */
    public function exportUser(User $user, AuthorizationCheckerInterface $authChecker)
    {
        $this->denyAccessUnlessGranted('IS_AUTHENTICATED_FULLY', null, $this->translator->trans('access_denied', [], 'messages'));

        $connectedUser = $this->getUser();
        if ($connectedUser->getId() !== $user->getId() && false === $authChecker->isGranted('ROLE_ADMIN')) {
            throw new AccessDeniedException($this->translator->trans('access_denied', [], 'messages'));
        }

        $userData = $this->userManager->exportUserData($user);
        $response = new Response(json_encode($userData));
        $disposition = $response->headers->makeDisposition(
            ResponseHeaderBag::DISPOSITION_ATTACHMENT,
            'user-'.$user->getFirstName().'-'.$user->getLastName().'-export.json'
        );
        $response->headers->set('Content-Disposition', $disposition);
        $response->headers->set('Content-Type', 'application/json');

        return $response;
    }

    /**
     *  @Route("/tutorialViewed", name="tutorial_viewed", methods="POST", options={"expose"=true})
     * @return Response
     */
    public function tutorialViewed()
    {
        $this->denyAccessUnlessGranted('IS_AUTHENTICATED_FULLY', null, $this->translator->trans('access_denied', [], 'messages'));

        $em = $this->getDoctrine()->getManager();
        $connectedUser = $this->getUser();
        $connectedUser->setFirstTranscript(false);
        $em->persist($connectedUser);
        $em->flush();

        return $this->json([], $status = 200);
    }
}
