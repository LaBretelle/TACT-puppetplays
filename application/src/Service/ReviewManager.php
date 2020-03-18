<?php

namespace App\Service;

use App\Entity\Review;
use App\Entity\Project;
use App\Entity\ReviewRequest;
use App\Entity\Transcription;
use App\Service\FlashManager;
use App\Service\MessageManager;
use App\Service\TranscriptionManager;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Component\Security\Core\Security;
use Symfony\Component\Translation\TranslatorInterface;

class ReviewManager
{
    protected $em;
    protected $fm;
    protected $security;
    protected $tm;
    protected $mm;
    protected $router;
    protected $translator;

    public function __construct(
      EntityManagerInterface $em,
      FlashManager $fm,
      Security $security,
      TranscriptionManager $tm,
      MessageManager $mm,
      UrlGeneratorInterface $router,
      TranslatorInterface $translator
    ) {
        $this->em = $em;
        $this->fm = $fm;
        $this->security = $security;
        $this->tm = $tm;
        $this->mm = $mm;
        $this->router = $router;
        $this->translator = $translator;
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
        $request = $review->getRequest();
        $transcription = $request->getTranscription();
        $requestUser = $request->getUser();
        $media = $transcription->getMedia();

        $this->em->persist($review);
        // add log
        $log = $this->tm->addLog($transcription, AppEnums::TRANSCRIPTION_LOG_REREADED);
        $this->em->persist($log);

        // add current user notification
        $this->fm->add('notice', 'review_submitted');

        // send to the requester a message
        $message = $review->getIsValid() ? 'positive_review' : 'negative_review';
        $url = $this->router->generate("media_transcription_display", ["id" => $media->getId()]);
        $message = $this->translator->trans($message, ['%url%' => $url, '%media%' => $media->getName(), '%comment%' => $review->getComment()]);
        $this->mm->create([$requestUser], $message, false);

        $this->em->flush();

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

    public function testForValidation(Transcription $transcription, Project $project)
    {
      // TODO > en un seul parcours
      $nbPositiveReviews = $this->countReview($transcription, true);
      $nbNegativeReviews = $this->countReview($transcription, false);
      if ($nbPositiveReviews >= $project->getNbValidation()) {
          $this->tm->validate($transcription, true);
      } elseif($nbNegativeReviews >= $project->getNbValidation()) {
        $this->tm->validate($transcription, false);
      }

      return;
    }
}
