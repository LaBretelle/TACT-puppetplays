<?php

namespace App\EventListener;

use App\Service\UserManager;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Event\ExceptionEvent;
use Symfony\Component\HttpKernel\Event\RequestEvent;
use Symfony\Component\HttpKernel\Exception\HttpExceptionInterface;

class EverythingListener
{
    protected $um;
    protected $em;

    public function __construct(UserManager $um, EntityManagerInterface $em)
    {
        $this->um = $um;
        $this->em = $em;
    }

    public function onKernelRequest(RequestEvent $event)
    {
        if ($event->isMasterRequest()) {
            $user =  $this->um->getCurrentUser();
            if ($user) {
                $currentDate = new \DateTime();
                $previousDate = $user->getLastAccess();

                if (!$previousDate || $previousDate->diff($currentDate)->d > 0) {
                    $user->setLastAccess($currentDate);
                    $this->em->persist($user);
                    $this->em->flush();
                }
            }

            return;
        }
    }
}
