<?php

namespace App\Controller;

use App\Entity\TranscriptionLog;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Doctrine\ORM\EntityManagerInterface;

/**
 * @Route("/transcriptionlog", name="transcription_log_")
 */
class TranscriptionLogController extends Controller
{
    protected $em;

    public function __construct(EntityManagerInterface $em)
    {
        $this->em = $em;
    }

    /**
     * @Route("/{id}", name="locked_update",options={"expose"=true}, methods="POST")
     */
    public function updateLockedLog(TranscriptionLog $log)
    {
        $log->setCreatedAt(new \DateTime());
        $this->em->persist($log);
        $this->em->flush();
        return $this->json(['success'], $status = 200);
    }
}
