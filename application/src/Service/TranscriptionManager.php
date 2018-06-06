<?php

namespace App\Service;

use App\Entity\Transcription;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;
use Symfony\Component\Security\Core\Authorization\AuthorizationCheckerInterface;

class TranscriptionManager
{
    protected $em;
    protected $authChecker;
    protected $params;

    public function __construct(EntityManagerInterface $em, AuthorizationCheckerInterface $authChecker, ParameterBagInterface $params)
    {
        $this->em = $em;
        $this->authChecker = $authChecker;
        $this->params = $params;
    }
}
