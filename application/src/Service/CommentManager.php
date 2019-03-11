<?php

namespace App\Service;

use App\Entity\Comment;
use App\Entity\User;
use App\Entity\Transcription;
use App\Service\FlashManager;
use App\Service\MessageManager;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Security\Core\Security;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Component\Translation\TranslatorInterface;

class CommentManager
{
    protected $em;
    protected $fm;
    protected $security;
    protected $mm;
    protected $router;
    protected $translator;

    public function __construct(
        EntityManagerInterface $em,
        FlashManager $fm,
        Security $security,
        MessageManager $mm,
        UrlGeneratorInterface $router,
        TranslatorInterface $translator
    ) {
        $this->em = $em;
        $this->fm = $fm;
        $this->security = $security;
        $this->mm = $mm;
        $this->router = $router;
        $this->translator = $translator;
    }

    public function create(Transcription $transcription)
    {
        $user = $this->security->getUser();

        $comment = new Comment;
        $comment->setUser($user);
        $comment->setTranscription($transcription);

        return $comment;
    }

    public function save(Comment $comment)
    {
        $media = $comment->getTranscription()->getMedia();
        $url = $this->router->generate("media_transcription_edit", ["id" => $media->getId()]);
        $msg = $this->translator->trans("new_comment_transcription", ['%url%' => $url, '%media%' => $media->getName()]);
        $user = $comment->getUser();
        $recipients=[];
        $subscribersUsers = $comment->getTranscription()->getSubscribersUsers();
        foreach ($subscribersUsers as $subscriberUser) {
            if ($subscriberUser != $user) {
                $recipients[]=$subscriberUser;
            }
        }

        $this->mm->create($recipients, $msg, false);

        $this->em->persist($comment);
        $this->em->flush();

        $this->fm->add('notice', 'comment_created');

        return;
    }

    public function delete(Comment $comment)
    {
        $this->em->remove($comment);
        $this->em->flush();

        $this->fm->add('notice', 'comment_deleted');

        return;
    }

    public function subscribe(Transcription $transcription, User $user, $subscribe)
    {
        if ($subscribe) {
            $transcription->addSubscribersUser($user);
        } else {
            $transcription->removeSubscribersUser($user);
        }
        $this->em->persist($transcription);
        $this->em->flush();

        $this->fm->add('notice', $subscribe ? 'subscribe_done' : 'unsubscribe_done');

        return;
    }
}
