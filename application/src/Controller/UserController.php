<?php

namespace App\Controller;

use App\Entity\User;
use App\Form\UserResetPasswordType;
use App\Form\UserType;
use App\Form\UserTypeFull;
use App\Service\MailManager;
use App\Service\UserManager;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\File\File;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Core\Authorization\AuthorizationCheckerInterface;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;
use Symfony\Component\Security\Http\Authentication\AuthenticationUtils;
use Symfony\Component\Translation\TranslatorInterface;

/** @Route("/user", name="user_") */
class UserController extends Controller
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
            $this->addFlash(
              'notice',
              $this->translator->trans('user_account_created', [], 'messages')
            );
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
     * @ParamConverter("user", class="App:User")
     */
    public function edit(Request $request, User $user, AuthorizationCheckerInterface $authChecker)
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
            /** @var Symfony\Component\HttpFoundation\File\UploadedFile $file */
            $image = $form->get('image')->getData();
            $previous_image = $request->get('previous_image');

            $this->userManager->updateUser($user, $image, $previous_image);
            $this->addFlash(
              'success',
              $this->translator->trans('user_account_updated', [], 'messages')
            );
            return $this->redirectToRoute('home');
        }

        return $this->render(
            'user/account.html.twig',
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
     *
     * @Route("/password-lost", name="lost_password_form")
     */
    public function lostPassword(Request $request)
    {
        if ($request->get('user_data')) {
            // check if user exist and that he has not requested a password less than two hours ago
            if ($user = $this->userManager->userCanRenewPassword($request->get('user_data'))) {
                $token = base64_encode(random_bytes(10));
                $user->setConfirmationToken($token);
                $user->setPasswordRequestedAt(new \DateTime('now'));
                $em = $this->getDoctrine()->getManager();
                $em->persist($user);
                $em->flush();
                $this->mailManager->sendRecoverPasswordMail($user);
                $mailparts = explode('@', $user->getEmail());
                $obfuscatedMail = substr_replace($mailparts[0], '*', 3).'@'.$mailparts[1];
                $this->addFlash(
                  'success',
                  $this->translator->trans('user_renew_password_mail_sent', ['%usermail%' => $obfuscatedMail], 'messages')
                );
            } else {
                $this->addFlash(
                'danger',
                $this->translator->trans('user_renew_password_error', [], 'messages')
              );
            }

            return $this->redirectToRoute('home');
        }
        return $this->render(
          'user/recover.html.twig'
        );
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
                $this->addFlash(
                  'danger',
                  $this->translator->trans('user_renew_password_error', [], 'messages')
                );
                return $this->redirectToRoute('home');
            }
        }

        $user = $repository->findOneBy(['confirmationToken' => $token]);

        if (!$user) {
            $this->addFlash(
              'danger',
              $this->translator->trans('user_renew_password_error', [], 'messages')
            );
            return $this->redirectToRoute('home');
        }
        $form = $this->createForm(UserResetPasswordType::class, $user);
        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            if ($this->userManager->resetPassword($user)) {
                $this->addFlash(
                'success',
                $this->translator->trans('user_renew_password_success', [], 'messages')
              );
            } else {
                $this->addFlash(
                'danger',
                $this->translator->trans('user_renew_password_error', [], 'messages')
              );
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
            $this->addFlash(
              'success',
              $this->translator->trans('user_account_activated', [], 'messages')
            );
            return $this->redirectToRoute('home');
        }

        $this->addFlash(
          'danger',
          $this->translator->trans('user_account_activated_error', [], 'messages')
        );
        return $this->redirectToRoute('home');
    }
}
