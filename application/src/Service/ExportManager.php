<?php

namespace App\Service;

use App\Entity\Project;
use App\Entity\Media;
use App\Service\FileManager;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Filesystem\Exception\IOExceptionInterface;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\Finder\Finder;
use Symfony\Component\HttpFoundation\File\File;

class ExportManager
{
    protected $em;
    protected $mediaRepo;
    protected $dirRepo;
    protected $fileManager;

    public function __construct(EntityManagerInterface $em, FileManager $fileManager)
    {
        $this->em = $em;
        $this->mediaRepo = $repository = $this->em->getRepository('App:Media');
        $this->dirRepo = $repository = $this->em->getRepository('App:Directory');
        $this->fileManager = $fileManager;
    }

    public function export(Project $project, $params)
    {
        // todo > supprimer le dossier tmp dans le zip de sortie.
        $exportDir = '/tmp/project'.$project->getId().'-'.date("Ymd").'-'.uniqid();
        $zipName = $exportDir.".zip";
        $fileSystem = new Filesystem();
        $projectPath = $this->fileManager->getProjectPath($project);

        // Create "root" dir
        $fileSystem->mkdir($exportDir);

        // Handle medias && transcriptions
        if ($params["medias"] || $params["transcriptions"]) {
            $mediaPath = $exportDir.DIRECTORY_SEPARATOR."MEDIAS";
            $transcriptionPath = $exportDir.DIRECTORY_SEPARATOR."TRANSCRIPTIONS";
            $this->recursiveCreateDirAndFile($project, null, $mediaPath, $transcriptionPath, $fileSystem, $projectPath, $params);
        }

        // Handle project datas
        if ($params["infos"]) {
            $fileSystem->mkdir($exportDir.DIRECTORY_SEPARATOR."INFOS");
        }

        // Handle project users
        if ($params["usersList"]) {
            $this->exportUsesList($fileSystem, $exportDir, $project);
        }

        // Handle project users
        if ($params["transcriptionsList"]) {
            $fileSystem->mkdir($exportDir.DIRECTORY_SEPARATOR."TRANSCRIPTIONSLIST");
        }

        // Zip everything
        $this->recursiveZipData($exportDir, $zipName);

        $fileSystem->remove($exportDir);
        new File($zipName);

        return $zipName;
    }

    private function exportUsesList($fileSystem, $exportDir, Project $project)
    {
        $file = $exportDir.DIRECTORY_SEPARATOR."users.txt";
        $statuses = $project->getUserStatuses();
        foreach ($statuses as $status) {
            $user = $status->getUser();
            $fileSystem->appendToFile($file, $user->getUsername());
        }


        //$fileSystem->appendToFile('logs.txt', 'Email sent to user@example.com');
    }

    private function recursiveCreateDirAndFile(Project $project, $parent, $mediaPath, $transcriptionPath, $fileSystem, $projectPath, $params)
    {
        $dirs = $this->dirRepo->findBy([
          "project" => $project,
          "parent" => $parent
        ]);

        foreach ($dirs as $dir) {
            $dirName = DIRECTORY_SEPARATOR.$dir->getName();
            $this->recursiveCreateDirAndFile($project, $dir, $mediaPath.$dirName, $transcriptionPath.$dirName, $fileSystem, $projectPath, $params);
        }

        $medias = $this->mediaRepo->findBy([
          "project" => $project,
          "parent" => $parent
        ]);

        foreach ($medias as $media) {
            $fullMediaFilePath = $mediaPath.DIRECTORY_SEPARATOR.$media->getName();
            $fullTranscriptionFilePath = $transcriptionPath.DIRECTORY_SEPARATOR.$media->getName();

            if ($params["transcriptions"]) {
                $fileSystem->appendToFile($fullTranscriptionFilePath.'.xml', $this->generateXML($media));
            }

            if ($params["medias"]) {
                $filePath = $projectPath.DIRECTORY_SEPARATOR.$media->getUrl();
                $ext = pathinfo($filePath, PATHINFO_EXTENSION);
                $fileSystem->copy($filePath, $fullMediaFilePath.'.'.$ext);
            }
        }
    }


    private function recursiveZipData($source, $destination)
    {
        if (extension_loaded('zip') === true) {
            if (file_exists($source) === true) {
                $zip = new \ZipArchive();
                if ($zip->open($destination, \ZIPARCHIVE::CREATE) === true) {
                    $source = realpath($source);
                    if (is_dir($source) === true) {
                        $files = new \RecursiveIteratorIterator(new \RecursiveDirectoryIterator($source), \RecursiveIteratorIterator::SELF_FIRST);
                        foreach ($files as $file) {
                            $file = realpath($file);
                            if (is_dir($file) === true) {
                                $zip->addEmptyDir(str_replace($source.DIRECTORY_SEPARATOR, '', $file .DIRECTORY_SEPARATOR));
                            } elseif (is_file($file) === true) {
                                $zip->addFromString(str_replace($source .DIRECTORY_SEPARATOR, '', $file), file_get_contents($file));
                            }
                        }
                    } elseif (is_file($source) === true) {
                        $zip->addFromString(basename($source), file_get_contents($source));
                    }
                }
                return $zip->close();
            }
        }
        return false;
    }

    private function generateXML(Media $media)
    {
        $xml = "<xml>".$media->getTranscription()->getContent()."</xml>";

        return $xml;
    }
}
