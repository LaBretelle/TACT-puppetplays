<?php

namespace App\Service;

use App\Entity\Project;
use Symfony\Component\Filesystem\Exception\IOExceptionInterface;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\Finder\Finder;
use Symfony\Component\HttpFoundation\File\File;
use Doctrine\ORM\EntityManagerInterface;

class ExportManager
{
    protected $em;
    protected $mediaRepo;
    protected $dirRepo;

    public function __construct(EntityManagerInterface $em)
    {
        $this->em = $em;
        $this->mediaRepo = $repository = $this->em->getRepository('App:Media');
        $this->dirRepo = $repository = $this->em->getRepository('App:Directory');
    }

    public function export(Project $projet)
    {
        $exportDir = '/tmp/'.uniqid();
        $zipName = $exportDir.".zip";
        $fileSystem = new Filesystem();
        $fileSystem->appendToFile($exportDir.'/logs.xml', 'Email sent to user@example.com');
        $fileSystem->appendToFile($exportDir.'/toto.xml', 'Email sent to user@example.com');

        $this->recursiveCreateDirAndFile($projet, null, $exportDir);

        die();

        $zip = new \ZipArchive();
        $finder = new Finder();
        $finder->files()->in($exportDir);
        foreach ($finder as $file) {
            if ($zip->open($zipName, \ZipArchive::CREATE)) {
                $zip->addFile($file->getRealpath(), basename($file->getRealpath()));
                $zip->close();
            }
        }
        $fileSystem->remove($exportDir);
        new File($zipName);

        return $zipName;
    }


    private function recursiveCreateDirAndFile($project, $parent, $path)
    {
        $dirs = $this->dirRepo->findBy([
          "project" => $project,
          "parent" => $parent
        ]);

        foreach ($dirs as $dir) {
            $dirName = $dir->getName();
            $this->recursiveCreateDirAndFile($project, $dir, $path.'/'.$dirName);
        }

        $medias = $this->mediaRepo->findBy([
          "project" => $project,
          "parent" => $parent
        ]);
        // créer le média au path courant
    }
}
