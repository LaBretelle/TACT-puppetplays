<?php

namespace App\Service;

use App\Entity\Media;
use App\Entity\Project;
use App\Entity\Transcription;
use App\Entity\User;
use App\Entity\UserProjectStatus;
use Doctrine\ORM\EntityManagerInterface;
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
    private $em;

    public function __construct(ParameterBagInterface $params, \Swift_Mailer $mailer, UrlGeneratorInterface $router, \Twig_Environment $templating, TranslatorInterface $translator, EntityManagerInterface $em)
    {
        $this->params = $params;
        $this->mailer = $mailer;
        $this->router = $router;
        $this->templating = $templating;
        $this->translator = $translator;
        $this->em = $em;
    }

    public function sendReviewRequest(Project $project, Transcription $transcription)
    {
        $users = $this->em->getRepository(User::class)->getSubscribedReviewersByProject($project);
        if ($users) {
            $media = $transcription->getMedia();
            $projectName = $project->getName();
            $mediaName = $media->getName();

            $mails = [];
            foreach ($users as $user) {
                $mails[] = $user->getEmail();
            }

            $subject = $this->translator->trans('new_review_request', ["%projectName%" => $project->getName()]);
            $url = $this->router->generate('media_transcription_review', ['id' => $media->getId()], UrlGeneratorInterface::ABSOLUTE_URL);

            $body = $this->templating->render(
            'emails/new-reviewrequest.html.twig',
            ['projectName' => $projectName, 'mediaName' => $mediaName, 'url' => $url]
          );

            $this->send($mails, $subject, $body, true);
        }
        return;
    }

    public function sendConfirmationMail(User $user)
    {
        $confirmationUrl = $this->router->generate(
          'user_activate_account',
          ['token' => $user->getConfirmationToken()],
          UrlGeneratorInterface::ABSOLUTE_URL
        );

        $subject = $this->translator->trans('email_registration_confirm_subject', [], 'emails');

        $body = $this->templating->render(
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

    public function sendValidationMail(User $user, Media $media, bool $valid, string $comment = null)
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

    private function send($to, $subject, $body, $hidden = false)
    {
        $message = (new \Swift_Message($subject))
          ->setContentType('text/html')
          ->setFrom($this->params->get('platform_email'))
          ->setBody($body);

        if ($hidden) {
            $message->setBcc($to);
        } else {
            $message->setTo($to);
        }

        $this->mailer->send($message);

        return;
    }
}
