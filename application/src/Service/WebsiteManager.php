<?php

namespace App\Service;

use App\Entity\Website;
use App\Service\AppEnums;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;
use Symfony\Component\Security\Core\Authorization\AuthorizationCheckerInterface;
use Symfony\Component\Security\Core\Security;

class WebsiteManager
{
    protected $em;
    protected $authChecker;
    protected $params;
    protected $security;

    public function __construct(EntityManagerInterface $em, AuthorizationCheckerInterface $authChecker, ParameterBagInterface $params, Security $security)
    {
        $this->em = $em;
        $this->authChecker = $authChecker;
        $this->params = $params;
        $this->security = $security;
    }

    public function updateWebsiteProperties(Website $website)
    {
        $this->em->persist($website);
        $this->em->flush();
    }

    public function getWebsiteParameters()
    {
        $repository = $this->em->getRepository(Website::class);
        return $repository->getWebsiteParameters();
    }
}
