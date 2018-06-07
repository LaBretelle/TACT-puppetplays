<?php

namespace App\Service;

use App\Entity\Media;
use App\Entity\Project;
use App\Entity\User;
use App\Service\AppEnums;
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

    public function canHandleProjectMedia(Project $project, User $user)
    {
        if ($this->authChecker->isGranted('ROLE_ADMIN')) {
            return true;
        } else {
            $userStatuses = $project->getUserStatuses();
            $upsRepository = $this->em->getRepository('App:UserProjectStatus');
            $usRepository = $this->em->getRepository('App:UserStatus');
            $managerStatus = $usRepository->findOneByName(AppEnums::USER_STATUS_MANAGER_NAME);
            $userPojectStatus = $upsRepository->findOneBy(['user' => $user, 'status' => $managerStatus]);
            return null !== $userPojectStatus;
        }
    }

    public function addProjectMedia(Project $project, array $files)
    {
        $basePath = $this->params->get('project_file_dir');
        $uploadPath = $basePath.DIRECTORY_SEPARATOR.$project->getId();
        if (!is_dir($basePath)) {
            mkdir($basePath);
        }

        if (!is_dir($uploadPath)) {
            mkdir($uploadPath);
        }

        foreach ($files as $file) {
            /** @var Symfony\Component\HttpFoundation\File\UploadedFile $file */
            $media = new Media();
            // given a jpg guessExtension will result in a jpeg...
            $extension = $file->guessExtension();
            $name = explode('.', $file->getClientOriginalName())[0];
            $media->setUrl(md5(uniqid()).'.'.$extension);
            $media->setName($name);

            $file->move($uploadPath, $media->getUrl());
            $project->addMedia($media);
            $this->em->persist($project);
        }
        $this->em->flush();
        return $project;
    }

    public function removeProjectMedia(Media $media)
    {
        $project = $media->getProject();
        $basePath = $this->params->get('project_file_dir');
        $filePath = $basePath.DIRECTORY_SEPARATOR.$project->getId().DIRECTORY_SEPARATOR.$media->getUrl();
        if (file_exists($filePath)) {
            unlink($filePath);
            $this->em->remove($media);
        }
        $this->em->flush();
    }
}
