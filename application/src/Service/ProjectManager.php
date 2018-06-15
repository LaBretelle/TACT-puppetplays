<?php

namespace App\Service;

use App\Entity\Media;
use App\Entity\Project;
use App\Entity\User;
use App\Service\AppEnums;
use App\Service\DirectoryManager;
use App\Service\FileManager;
use App\Service\MediaManager;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\Security\Core\Authorization\AuthorizationCheckerInterface;

class ProjectManager
{
    protected $em;
    protected $authChecker;
    protected $mediaManager;
    protected $fileManager;
    protected $dirManager;

    public function __construct(
      EntityManagerInterface $em,
      MediaManager $mediaManager,
      AuthorizationCheckerInterface $authChecker,
      FileManager $fileManager,
      DirectoryManager $dirManager
    ) {
        $this->em = $em;
        $this->authChecker = $authChecker;
        $this->mediaManager = $mediaManager;
        $this->fileManager = $fileManager;
        $this->dirManager = $dirManager;
    }

    public function createFromForm($project)
    {
        $project->setCreatedAt(new \DateTime);
        $repository = $this->em->getRepository('App:ProjectStatus');
        $pStatus = $repository->findOneByName(AppEnums::PROJECT_STATUS_NEW_NAME);
        $project->setStatus($pStatus);
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

    public function handleImage(Project $project, UploadedFile $file = null, string $previous_image = null)
    {
        if ($file) {
            $fileName = '__background.'.$file->guessExtension();
            $filePath = $this->fileManager->getProjectPath($project);
            $file->move($filePath, $fileName);
            $project->setImage($fileName);
        } elseif ($previous_image) {
            $project->setImage($previous_image);
        }

        $this->em->persist($project);
        $this->em->flush();
    }

    public function delete($project)
    {
        $project->setUpdatedAt(new \DateTime);
        $project->setDeleted(true);
        $this->em->persist($project);
        $this->em->flush();

        return $project;
    }

    public function removeImage(Project $project)
    {
        $image = $project->getImage();
        $path = $this->fileManager->getProjectPath($project).$image;
        $this->fileManager->delete($path);
        $project->setImage(null);

        $this->em->persist($project);
        $this->em->flush();

        return;
    }

    public function unzip($zipFile)
    {
        $projectPath = $this->fileManager->getProjectPath($project);

        // unzip
      // récursivement copier les images dans le dossier du projet,, à plat
      // créer un objet Directory par répertoire et s'en servir comme parent des images et des autres sous-répartoires directs.
      // à la racine, l'attribut parent des dossiers et fichiers est NULL
    }

    public function recursiveBrowse($dir, $parent)
    {
        $result = array();

        $cdir = scandir($dir);
        foreach ($cdir as $key => $value) {
            if (!in_array($value, array(".",".."))) {
                $path = $dir . DIRECTORY_SEPARATOR . $value;
                if (is_dir($path)) {
                    $newDirectory = $this->dirManager->create($value, $parent);
                    recursiveBrowse($path, $newDirectory);
                } else {
                    // createMedia with current parent
                    // copier le fichier dans le project path dir
                }
            }
        }

        return $result;
    }

    public function addProjectMedia(Project $project, array $files)
    {
        $basePath = $this->fileManager->getBaseProjectPath();
        $uploadPath = $this->fileManager->getProjectPath($project);

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

            $media = $this->mediaManager->initMediaTranscription($media);
            $project->addMedia($media);
            $this->em->persist($project);
        }
        $this->em->flush();

        return $project;
    }

    public function removeProjectMedia(Media $media)
    {
        $project = $media->getProject();

        $filePath = $this->fileManager->getProjectPath($project).$media->getUrl();
        $this->fileManager->delete($filePath);

        $this->em->remove($media);
        $this->em->flush();

        return;
    }

    public function getProjectManagerUser(Project $project)
    {
        $statuses = $project->getUserStatuses();
        foreach ($statuses as $userProjectStatus) {
            /* @var UserProjectStatus $userProjectStatus */
            if ($userProjectStatus->getStatus()->getName() === AppEnums::USER_STATUS_MANAGER_NAME) {
                return $userProjectStatus->getUser();
            }
        }
    }
}
