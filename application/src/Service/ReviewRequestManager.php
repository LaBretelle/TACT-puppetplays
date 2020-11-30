<?php

namespace App\Service;

use App\Entity\Review;
use App\Entity\ReviewRequest;
use App\Entity\Transcription;
use App\Entity\User;
use App\Service\FlashManager;
use App\Service\MailManager;
use App\Service\TranscriptionManager;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Security\Core\Security;

class ReviewRequestManager
{
    protected $em;
    protected $fm;
    protected $security;
    protected $tm;
    protected $mm;

    public function __construct(
        EntityManagerInterface $em,
        FlashManager $fm,
        MailManager $mm,
        Security $security,
        TranscriptionManager $tm
    ) {
        $this->em = $em;
        $this->mm = $mm;
        $this->fm = $fm;
        $this->tm = $tm;
        $this->security = $security;
    }

    public function create(Transcription $transcription, User $user = null)
    {
        $user = (!$user) ? $this->security->getUser() : $user;
        if (!$request = $transcription->getReviewRequest()) {
            $project = $transcription->getMedia()->getProject();
            $request = new ReviewRequest();
            $request->setUser($user);
            $request->setTranscription($transcription);
            $this->em->persist($request);

            $log = $this->tm->addLog($transcription, AppEnums::TRANSCRIPTION_LOG_WAITING_FOR_VALIDATION, false, $user);
            $this->em->persist($log);
            $this->em->flush();

            $this->fm->add('notice', 'review_request_created');

            $this->mm->sendReviewRequest($project, $transcription);
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
