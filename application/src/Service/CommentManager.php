<?php

namespace App\Service;

use App\Entity\Comment;
use App\Entity\Transcription;
use App\Service\FlashManager;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Security\Core\Security;

class CommentManager
{
    protected $em;
    protected $fm;
    protected $security;

    public function __construct(
      EntityManagerInterface $em,
      FlashManager $fm,
      Security $security
    ) {
        $this->em = $em;
        $this->fm = $fm;
        $this->security = $security;
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
        $this->em->persist($comment);
        $this->em->flush();

        $this->fm->add('notice', 'comment_created');

        return;
    }
}
