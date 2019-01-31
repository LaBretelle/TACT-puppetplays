<?php

namespace App\Service;

use App\Entity\Project;
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

    public function export(Project $project, $withMedia)
    {
        $withMedia = ($withMedia == 0) ? false : true;
        $exportDir = '/tmp/'.uniqid();
        $zipName = $exportDir.".zip";
        $fileSystem = new Filesystem();
        $projectPath = $this->fileManager->getProjectPath($project);

        $this->recursiveCreateDirAndFile($project, null, $exportDir, $fileSystem, $projectPath, $withMedia);
        $this->recursiveZipData($exportDir, $zipName);

        $fileSystem->remove($exportDir);
        new File($zipName);

        return $zipName;
    }

    private function recursiveCreateDirAndFile($project, $parent, $path, $fileSystem, $projectPath, $withMedia)
    {
        $dirs = $this->dirRepo->findBy([
          "project" => $project,
          "parent" => $parent
        ]);

        foreach ($dirs as $dir) {
            $dirName = $dir->getName();
            $this->recursiveCreateDirAndFile($project, $dir, $path.'/'.$dirName, $fileSystem, $projectPath, $withMedia);
        }

        $medias = $this->mediaRepo->findBy([
          "project" => $project,
          "parent" => $parent
        ]);

        foreach ($medias as $media) {
            $fullFilePath = $path.DIRECTORY_SEPARATOR.$media->getName();
            $fileSystem->appendToFile($fullFilePath.'.xml', $media->getTranscription()->getContent());

            if ($withMedia) {
                $filePath = $projectPath.DIRECTORY_SEPARATOR.$media->getUrl();
                $ext = pathinfo($filePath, PATHINFO_EXTENSION);
                $fileSystem->copy($filePath, $fullFilePath.'.'.$ext);
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
}
