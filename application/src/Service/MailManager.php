<?php

namespace App\Service;

use App\Entity\Media;
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
        $confirmationUrl = $this->router->generate(
          'user_activate_account',
          ['token' => $user->getConfirmationToken()],
          UrlGeneratorInterface::ABSOLUTE_URL
        );

        $subject = $this->translator->trans('email_registration_confirm_subject', [], 'emails');

        $body =   $this->templating->render(
              'emails/registration.html.twig',
              ['user' => $user, 'url' => $confirmationUrl]
          );

        $this->send($user->getEmail(), $subject, $body);

        return;
    }

    public function sendRecoverPasswordMail(User $user)
    {
        $url = $this->router->generate(
          'user_reset_password',
          ['token' => $user->getConfirmationToken()],
          UrlGeneratorInterface::ABSOLUTE_URL
        );

        $subject = $this->translator->trans('email_renew_password_subject', [], 'emails');

        $body = $this->templating->render(
            'emails/reset.html.twig',
            ['user' => $user, 'url' => $url]
        );

        $this->send($user->getEmail(), $subject, $body);

        return;
    }

    public function sendValidationOrUnvalidationMail(User $user, Media $media, bool $valid, string $comment = null)
    {
        $subject = $this->translator->trans('email_transcription_validted_unvalidated_subject', [], 'emails');

        $url = $this->router->generate(
          'media_transcription_edit',
          ['id' => $media->getId()],
          UrlGeneratorInterface::ABSOLUTE_URL
        );

        $body = $this->templating->render(
            'emails/transcription-validated-unvalidated.html.twig',
            ['user' => $user, 'url' => $url, 'valid' => $valid, 'comment' => $comment]
        );

        $this->send($user->getEmail(), $subject, $body);

        return;
    }

    private function send($to, $subject, $body)
    {
        $message = (new \Swift_Message($subject))
          ->setContentType('text/html')
          ->setFrom($this->params->get('platform_email'))
          ->setTo($to)
          ->setBody($body);

        $this->mailer->send($message);

        return;
    }
}
