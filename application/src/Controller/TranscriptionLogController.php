<?php

namespace App\Controller;

use App\Entity\TranscriptionLog;
use App\Service\AppEnums;
use App\Service\PermissionManager;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;

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
        if (false === $this->permissionManager->isAuthorizedOnProject($project, AppEnums::ACTION_TRANSCRIBE)) {
            return $this->json([], $status = 403);
        }
        $log->setCreatedAt(new \DateTime());
        $this->em->persist($log);
        $this->em->flush();
        return $this->json(['success'], $status = 200);
    }
}
