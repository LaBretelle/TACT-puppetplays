<?php

namespace App\Service;

use App\Entity\Directory;
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
use Symfony\Component\HttpFoundation\File\File;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\Filesystem\Exception\IOExceptionInterface;

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

    public function editFromForm($project)
    {
        $project->setUpdatedAt(new \DateTime);

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

    public function initMediaProcessing(Project $project, string $uploadPath, Directory $parent = null)
    {
        $projectPath = $this->fileManager->getProjectPath($project);
        $this->recursiveBrowse($project, $projectPath, $uploadPath, $parent);
        $this->deleteTempFolders($uploadPath);
    }

    public function deleteTempFolders(string $path)
    {
        $iterator = new \RecursiveIteratorIterator(new \RecursiveDirectoryIterator($path, \FilesystemIterator::SKIP_DOTS), \RecursiveIteratorIterator::CHILD_FIRST);
        foreach ($iterator as $filename => $fileInfo) {
            if ($fileInfo->isDir()) {
                rmdir($filename);
            } else {
                unlink($filename);
            }
        }
    }

    public function recursiveBrowse(Project $project, string $projectPath, string $uploadPath, Directory $parent = null)
    {
        $cdir = scandir($uploadPath);

        foreach ($cdir as $value) {
            if (!in_array($value, array(".",".."))) {
                $absolutePath = $uploadPath . DIRECTORY_SEPARATOR . $value;
                if (is_dir($absolutePath)) {
                    $newDirectory = $this->dirManager->create($project, $value, $parent);
                    $this->recursiveBrowse($project, $projectPath, $absolutePath, $newDirectory);
                } else {
                    $file = new File($absolutePath);
                    $media = $this->mediaManager->createMediaFromFile($file, $value, $project, $parent);
                    $file->move($projectPath, $media->getUrl());
                }
            }
        }
    }

    //addProjectMedia($project, $media, $isZip, $parent);
    public function addProjectMedia(Project $project, $files, bool $isZip, Directory $parent = null)
    {
        // base path for all projects media
        $basePath = $this->fileManager->getBaseProjectPath();
        // project path for media
        $projectMediaPath = $this->fileManager->getProjectPath($project);

        if (!is_dir($basePath)) {
            mkdir($basePath);
        }

        if (!is_dir($projectMediaPath)) {
            mkdir($projectMediaPath);
        }

        $uploadPath = $projectMediaPath . DIRECTORY_SEPARATOR . 'tmp';
        mkdir($uploadPath);

        if ($isZip) {
            $zipName = $files->getClientOriginalName();
            $files->move($uploadPath, $zipName);
            $zip = new \ZipArchive;
            $success = $zip->open($uploadPath . DIRECTORY_SEPARATOR . $zipName);
            if ($success === true) {
                $zip->extractTo($uploadPath);
                $zip->close();
            }

            unlink($uploadPath . DIRECTORY_SEPARATOR . $zipName);
        } else {
            foreach ($files as $file) {
                $file->move($uploadPath, $file->getClientOriginalName());
            }
        }

        $this->initMediaProcessing($project, $uploadPath, $parent);
        rmdir($uploadPath);
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
