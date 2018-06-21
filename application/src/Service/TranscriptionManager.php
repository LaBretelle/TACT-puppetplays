<?php

namespace App\Service;

use App\Entity\Transcription;
use App\Entity\TranscriptionLog;
use App\Service\AppEnums;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;

use Symfony\Component\Security\Core\Security;

class TranscriptionManager
{
    protected $em;
    protected $security;
    protected $params;

    public function __construct(EntityManagerInterface $em, Security $security, ParameterBagInterface $params)
    {
        $this->em = $em;
        $this->security = $security;
        $this->params = $params;
    }

    public function addLog(Transcription $transcription, string $name)
    {
        $currentUser = $this->security->getUser();
        $log = new TranscriptionLog();
        $log->setUser($this->security->getUser());
        $log->setName($name);

        $transcription->addTranscriptionLog($log);

        return $transcription;
    }
}
