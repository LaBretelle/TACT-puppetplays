<?php

namespace App\Service;

use App\Entity\Review;
use App\Entity\ReviewRequest;
use App\Entity\Transcription;
use App\Service\FlashManager;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Security\Core\Security;

class ReviewManager
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

    public function create(ReviewRequest $request)
    {
        $user = $this->security->getUser();
        $review = new Review();
        $review->setUser($user);
        $review->setRequest($request);

        return $review;
    }
}
