<?php

namespace App\Controller;

use App\Entity\TranscriptionLog;
use App\Service\AppEnums;
use App\Service\PermissionManager;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;

/**
 * @Route("/transcriptionlog", name="transcription_log_")
 */
class TranscriptionLogController extends AbstractController
{
    protected $em;
    protected $permissionManager;

    public function __construct(EntityManagerInterface $em, PermissionManager $permissionManager)
    {
        $this->em = $em;
        $this->permissionManager = $permissionManager;
    }

    /**
     * @Route("/{id}", name="locked_update",options={"expose"=true}, methods="POST")
     */
    public function updateLockedLog(TranscriptionLog $log)
    {
        $project = $log->getTranscription()->getMedia()->getProject();
        if (false === $this->permissionManager->isAuthorizedOnProject($project, AppEnums::ACTION_TRANSCRIBE)) {
            return $this->json([], $status = 403);
        }
        $log->setCreatedAt(new \DateTime());
        $this->em->persist($log);
        $this->em->flush();
        return $this->json(['success'], $status = 200);
    }
}
