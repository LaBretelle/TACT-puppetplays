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

        if (!$review = $this->em->getRepository(Review::class)->findOneBy(['user' => $user, 'request' => $request])) {
            $review = new Review();
            $review->setUser($user);
            $review->setRequest($request);
        }

        return $review;
    }

    public function save(Review $review)
    {
        $this->em->persist($review);
        $this->em->flush();

        $text = $review->getIsValid() ? 'transcription_validated' : 'transcription_unvalidated';
        $this->fm->add('notice', $text);

        return $review;
    }



    public function countReview(Transcription $transcription, $valid = true)
    {
        $count = 0;
        if ($request = $transcription->getReviewRequest()) {
            // TODO > faire une joli requête pour éviter de boucler
            $reviews = $request->getReviews();
            foreach ($reviews as $review) {
                if ($review->getIsValid() === $valid) {
                    $count++;
                }
            }
        }

        return $count;
    }
}
