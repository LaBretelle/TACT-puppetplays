<?php

namespace App\Service;

use App\Entity\Platform;
use App\Service\AppEnums;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\Security\Core\Authorization\AuthorizationCheckerInterface;
use Symfony\Component\Security\Core\Security;

class PlatformManager
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

    public function updatePlatformProperties(Platform $platform)
    {
        $this->em->persist($platform);
        $this->em->flush();
    }

    public function getPlatformParameters()
    {
        $repository = $this->em->getRepository(Platform::class);
        return $repository->getPlatformParameters();
    }

    public function handleGuide(Platform $platform, UploadedFile $file = null)
    {
        if ($file) {
            $fileName = 'contributor_guide.pdf';
            $filePath = $this->params->get('platform_file_dir');
            $file->move($filePath, $fileName);
        }
    }


    public function handleLogo(Platform $platform, UploadedFile $file = null, string $previous_logo = null)
    {
        if ($file) {
            $fileName = md5(uniqid()).'.'.$file->guessExtension();
            $filePath = $this->params->get('platform_file_dir');
            $file->move($filePath, $fileName);
            $platform->setLogo($fileName);

            if ($previous_logo && file_exists($filePath.DIRECTORY_SEPARATOR.$previous_logo)) {
                unlink($filePath.DIRECTORY_SEPARATOR.$previous_logo);
            }
        } elseif ($previous_logo) {
            $platform->setLogo($previous_logo);
        }

        $this->em->persist($platform);
        $this->em->flush();
    }

    public function deleteLogo(Platform $platform)
    {
        $filePath = $this->params->get('platform_file_dir').DIRECTORY_SEPARATOR.$platform->getLogo();
        unlink($filePath);
        $platform->setLogo(null);
        $this->em->persist($platform);
        $this->em->flush();
    }
}
