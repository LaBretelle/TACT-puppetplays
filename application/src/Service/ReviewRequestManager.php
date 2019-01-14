<?php

namespace App\Service;

use App\Entity\Review;
use App\Entity\ReviewRequest;
use App\Entity\Transcription;
use App\Service\FlashManager;
use App\Service\TranscriptionManager;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Security\Core\Security;

class ReviewRequestManager
{
    protected $em;
    protected $fm;
    protected $security;
    protected $tm;

    public function __construct(
      EntityManagerInterface $em,
      FlashManager $fm,
      Security $security,
      TranscriptionManager $tm
    ) {
        $this->em = $em;
        $this->fm = $fm;
        $this->tm = $tm;
        $this->security = $security;
    }

    public function create(Transcription $transcription)
    {
        if (!$request = $transcription->getReviewRequest()) {
            $request = new ReviewRequest();
            $request->setUser($this->security->getUser());
            $request->setTranscription($transcription);
            $this->em->persist($request);

            $this->tm->addLog($transcription, AppEnums::TRANSCRIPTION_LOG_WAITING_FOR_VALIDATION);
            $this->em->persist($transcription);
            $this->em->flush();

            $this->fm->add('notice', 'review_request_created');
        } else {
            $this->fm->add('notice', 'already_pending_review');
        }

        return $request;
    }

    public function delete(ReviewRequest $request)
    {
        $this->em->remove($request);
        $this->em->flush();

        return;
    }
}
