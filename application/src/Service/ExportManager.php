<?php

namespace App\Service;

use App\Entity\Media;
use App\Entity\Project;
use App\Service\FileManager;
use App\Service\TranscriptionManager;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Filesystem\Exception\IOExceptionInterface;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\Finder\Finder;
use Symfony\Component\HttpFoundation\File\File;
use Symfony\Component\Serializer\Encoder\CsvEncoder;
use Symfony\Component\Serializer\Normalizer\ObjectNormalizer;
use Symfony\Component\Serializer\Serializer;
use Symfony\Component\Translation\TranslatorInterface;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

class ExportManager
{
    protected $em;
    protected $mediaRepo;
    protected $dirRepo;
    protected $fileManager;
    protected $translator;
    protected $tm;
    protected $router;

    public function __construct(EntityManagerInterface $em, FileManager $fileManager, TranslatorInterface $translator, TranscriptionManager $tm, UrlGeneratorInterface $router)
    {
        $this->em = $em;
        $this->tm = $tm;
        $this->mediaRepo = $repository = $this->em->getRepository('App:Media');
        $this->dirRepo = $repository = $this->em->getRepository('App:Directory');
        $this->fileManager = $fileManager;
        $this->translator = $translator;
        $this->router = $router;
    }

    public function export(Project $project, $params)
    {
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
            $this->exportProjectInfo($fileSystem, $exportDir, $project, $projectPath);
        }

        // Handle project users
        if ($params["usersList"]) {
            $this->exportUsersList($fileSystem, $exportDir, $project);
        }

        // Handle project transcriptions
        if ($params["transcriptionsList"]) {
            $this->exportTranscriptionsList($fileSystem, $exportDir, $project);
        }

        // Zip everything
        $this->recursiveZipData($exportDir, $zipName);

        // delete tmp dir.
        $fileSystem->remove($exportDir);
        new File($zipName);

        return $zipName;
    }

    private function exportTranscriptionsList($fileSystem, $exportDir, Project $project)
    {
        $file = $exportDir.DIRECTORY_SEPARATOR."transcriptions.csv";
        $medias = $project->getMedias();
        $dataArray = [];

        $dataArray[] = [
          "id",
          "media",
          "transcription",
          "status",
          "url"
        ];

        foreach ($medias as $media) {
            $transcription = $media->getTranscription();
            $fullPath = $this->getFullPath($media);
            $ext = pathinfo($media->getUrl(), PATHINFO_EXTENSION);
            $dataArray[] = [
              $transcription->getId(),
              $fullPath.".".$ext,
              $fullPath.".xml",
              $this->tm->getStatus($transcription),
              $this->router->generate('media_transcription_display', ['id' => $media->getId()], UrlGeneratorInterface::ABSOLUTE_URL)
            ];
        }

        $csv = $this->arrayToCsv($dataArray);
        $fileSystem->appendToFile($file, $csv);
    }

    private function exportUsersList($fileSystem, $exportDir, Project $project)
    {
        $file = $exportDir.DIRECTORY_SEPARATOR."users.csv";
        $statuses = $project->getUserStatuses();
        $dataArray = [];
        $dataArray[] = [
          "id",
          "username",
          "mail",
          "status",
          "status_validated"
        ];
        foreach ($statuses as $status) {
            $user = $status->getUser();
            $userStatus = $status->getStatus();
            $dataArray[] = [
                $user->getId(),
                $user->getUsername(),
                $user->getEmail(),
                $this->translator->trans($userStatus->getName()),
                $status->getEnabled() ? "1" : "0"
            ];
        }

        $csv = $this->arrayToCsv($dataArray);
        $fileSystem->appendToFile($file, $csv);
    }

    private function exportProjectInfo($fileSystem, $exportDir, Project $project, $projectPath)
    {
        $rootDir = $exportDir.DIRECTORY_SEPARATOR."INFOS".DIRECTORY_SEPARATOR;

        $fileSystem->appendToFile($rootDir."description.txt", $project->getDescription());
        $fileSystem->appendToFile($rootDir."catchphrase.txt", $project->getCatchPhrase());

        if ($css = $project->getCss()) {
            $fileSystem->appendToFile($rootDir."style.css", $css);
        }

        if ($help = $project->getProjectHelpLink()) {
            $fileSystem->appendToFile($rootDir."helpLink.txt", $help);
        }

        $csv = $projectPath.DIRECTORY_SEPARATOR."schema.csv";
        if ($fileSystem->exists($csv)) {
            $fileSystem->copy($csv, $rootDir."schema.csv");
        }

        $json = $projectPath.DIRECTORY_SEPARATOR."tei-schema.json";
        if ($fileSystem->exists($json)) {
            $fileSystem->copy($json, $rootDir."tei-schema.json");
        }

        if ($imageName = $project->getImage()) {
            $image = $projectPath.DIRECTORY_SEPARATOR.$imageName;
            if ($fileSystem->exists($image)) {
                $fileSystem->copy($image, $rootDir.$imageName);
            }
        }
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

            if ($params["medias"] && $media->getUrl()) {
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
                            if (is_dir($file) === true && $file !== "/tmp") {
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

    public function generateXML(Media $media)
    {
        $project = $media->getProject();
        $transcription = $media->getTranscription()->getContent();
        $xslFile = $this->fileManager->getProjectPath($project).DIRECTORY_SEPARATOR."export.xsl";

        if (file_exists($xslFile)) {
            $xsl = new \DOMDocument();
            $xsl->load($xslFile);

            $xslt = new \XSLTProcessor();
            $xslt->importStylesheet($xsl);

            $xml = new \DOMDocument();
            $xml->loadXML("<body>".$transcription."</body>");
            $xml->xinclude(LIBXML_NOWARNING);

            return $xslt->transformToXML($xml);
        }

        return $transcription;
    }

    private function arrayToCsv($array)
    {
        $serializer = new Serializer([new ObjectNormalizer()], [new CsvEncoder()]);
        $csv = $serializer->encode($array, 'csv');

        return $csv;
    }

    private function getFullPath(Media $media)
    {
        $parent = $media->getParent();
        $path = $media->getName();

        while ($parent) {
            $path = $parent->getName().DIRECTORY_SEPARATOR.$path;
            $parent = $parent->getParent();
        }

        return $path;
    }
}
