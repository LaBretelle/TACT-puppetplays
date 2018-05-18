<?php

namespace App\Controller;

use App\Entity\User;
use App\Form\UserType;
use App\Form\UserTypeFull;
use App\Service\MailManager;
use App\Service\UserManager;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Authentication\AuthenticationUtils;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter;
use Symfony\Component\Translation\TranslatorInterface;
use Symfony\Component\HttpFoundation\File\File;
use Symfony\Component\Security\Core\Authorization\AuthorizationCheckerInterface;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;

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
     * @param  Request $request [description]
     * @param  User    $user    [description]
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
     * [recoverPassword description]
     * @return [type] [description]
     */
    public function recoverPassword()
    {
    }

    /**
     * [confirmRegistration description]
     * @Route("/confirm/{token}", name="activate_account", requirements={"token"=".+"})
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
