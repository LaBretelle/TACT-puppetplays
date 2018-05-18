<?php

namespace App\Service;

use App\Entity\User;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Component\Translation\TranslatorInterface;

class MailManager
{
    private $params;
    private $mailer;
    private $router;
    private $translator;
    private $templating;

    public function __construct(ParameterBagInterface $params, \Swift_Mailer $mailer, UrlGeneratorInterface $router, \Twig_Environment $templating, TranslatorInterface $translator)
    {
        $this->params = $params;
        $this->mailer = $mailer;
        $this->router = $router;
        $this->templating = $templating;
        $this->translator = $translator;
    }

    public function sendConfirmationMail(User $user)
    {
        $confiramtionUrl = $this->router->generate(
          'user_activate_account',
          ['token' => $user->getConfirmationToken()],
          UrlGeneratorInterface::ABSOLUTE_URL
        );

        $subject = $this->translator->trans(
            'email_registration_confirm_subject',
            [],
            'messages',
            'fr_FR'
        );

        $message = (new \Swift_Message($subject))
          ->setFrom($this->params->get('platform_email'))
          ->setTo($user->getEmail())
          ->setBody(
              $this->templating->render(
                  'emails/registration.html.twig',
                  ['user' => $user, 'url' => $confiramtionUrl]
              )
          );
        $this->mailer->send($message);
    }
}
