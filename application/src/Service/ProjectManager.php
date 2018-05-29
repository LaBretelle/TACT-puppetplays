<?php

namespace App\Service;

use App\Entity\Project;
use App\Entity\Media;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;
use Symfony\Component\Security\Core\Authorization\AuthorizationCheckerInterface;

class ProjectManager
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

    public function createFromForm($project)
    {
        $project->setCreatedAt(new \DateTime);

        $this->em->persist($project);
        $this->em->flush();

        return $project;
    }

    public function editFromForm($project, $originalStatuses)
    {
        $project->setUpdatedAt(new \DateTime);

        if ($this->authChecker->isGranted('ROLE_ADMIN')) {
            foreach ($originalStatuses as $status) {
                if (!$project->getUserStatuses()->contains($status)) {
                    $project->removeUserStatus($status);
                    $this->em->remove($status);
                }
            }
        }

        $this->em->persist($project);
        $this->em->flush();

        return $project;
    }

    public function addProjectMedia(Project $project, array $files)
    {
        $basePath = $this->params->get('project_file_dir');
        $uploadPath = $basePath.DIRECTORY_SEPARATOR.$project->getName();
        if (!is_dir($basePath)) {
            mkdir($basePath);
        }


        if (!is_dir($uploadPath)) {
            mkdir($uploadPath);
        }
        foreach ($files as $file) {
            $media = new Media();
            $media->setUrl(md5(uniqid()).'.'.$file->guessExtension());

            // project dir
            /** @var Symfony\Component\HttpFoundation\File\UploadedFile $file */
            $file->move($uploadPath, $media->getUrl());
            $project->addMedia($media);
            $this->em->persist($media);
        }
        $this->em->flush();
        return $project;
    }

    public function removeProjectMedia(Media $media)
    {
        $project = $media->getProject();
        $basePath = $this->params->get('project_file_dir');
        $filePath = $basePath.DIRECTORY_SEPARATOR.$project->getName().DIRECTORY_SEPARATOR.$media->getUrl();
        if (file_exists($filePath)) {
            unlink($filePath);
            $this->em->remove($media);
        }
        $this->em->flush();
    }
}
