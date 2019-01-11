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

    public function create(ReviewRequest $request, $isValid, $comment)
    {
        // todo > vérifier que l'utilisateur n'a pas déjà review la transcription
        $user = $this->security->getUser();
        $review = new Review();
        $review->setUser($user);
        $review->setIsValid($isValid);
        $review->setComment($comment);
        $review->setRequest($request);
        $this->em->persist($review);
        $this->em->flush();

        $text = $isValid ? 'transcription_validated' : 'transcription_unvalidated';
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
