<?php

namespace App\Service;

use App\Entity\Review;
use App\Entity\ReviewRequest;
use App\Entity\Transcription;
use App\Service\FlashManager;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Security\Core\Security;

class ReviewRequestManager
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
        $request = new ReviewRequest();
        $request->setUser($user);
        $request->setTranscription($transcription);

        $this->em->persist($request);
        $this->em->flush();

        return $request;
    }
}
