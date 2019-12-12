<?php

namespace App\Controller\Admin;

use App\Entity\TranscriptionLog;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;

/** @Route("/admin/log", name="admin_log_") */
class LogController extends AbstractController
{
    /**
     * @Route("/display", name="display")
     */
    public function displayLogs(Request $request)
    {
        $logs = $this->getDoctrine()->getRepository(TranscriptionLog::class)->findAlmostAll();

        return $this->render(
            'admin/logs/display.html.twig',
            ['logs' => $logs]
        );
    }
}
