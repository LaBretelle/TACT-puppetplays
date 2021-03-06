<?php

namespace App\Service;

use App\Entity\Media;
use App\Entity\Project;
use App\Service\FileManager;
use App\Service\MetadataManager;
use App\Service\TranscriptionManager;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Filesystem\Exception\IOExceptionInterface;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\Finder\Finder;
use Symfony\Component\HttpFoundation\File\File;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Component\Serializer\Encoder\CsvEncoder;
use Symfony\Component\Serializer\Normalizer\ObjectNormalizer;
use Symfony\Component\Serializer\Serializer;
use Symfony\Component\Translation\TranslatorInterface;

class ExportManager
{
    protected $em;
    protected $mediaRepo;
    protected $dirRepo;
    protected $fileManager;
    protected $translator;
    protected $tm;
    protected $router;
    protected $userRepo;
    protected $metadataManager;

    public function __construct(EntityManagerInterface $em, FileManager $fileManager, TranslatorInterface $translator, TranscriptionManager $tm, UrlGeneratorInterface $router, MetadataManager $metadataManager)
    {
        $this->em = $em;
        $this->tm = $tm;
        $this->mediaRepo = $repository = $this->em->getRepository('App:Media');
        $this->dirRepo = $repository = $this->em->getRepository('App:Directory');
        $this->userRepo = $repository = $this->em->getRepository('App:User');
        $this->fileManager = $fileManager;
        $this->translator = $translator;
        $this->router = $router;
        $this->metadataManager = $metadataManager;
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

        if ($project->getMetadatas()) {
            $xmlMetadatas = $this->metadataManager->exportProjectMetadatas($project);
            $fileSystem->appendToFile($rootDir."metadatas.xml", $xmlMetadatas);
        }

        if ($css = $project->getCss()) {
            $fileSystem->appendToFile($rootDir."style.css", $css);
        }

        $csv = $projectPath.DIRECTORY_SEPARATOR."schema.csv";
        if ($fileSystem->exists($csv)) {
            $fileSystem->copy($csv, $rootDir."schema.csv");
        }

        $json = $projectPath.DIRECTORY_SEPARATOR."tei-schema.json";
        if ($fileSystem->exists($json)) {
            $fileSystem->copy($json, $rootDir."tei-schema.json");
        }

        $xsl = $projectPath.DIRECTORY_SEPARATOR."export.xsl";
        if ($fileSystem->exists($xsl)) {
            $fileSystem->copy($xsl, $rootDir."export.xsl");
        }

        if ($projectHelpLink = $project->getProjectHelpLink()) {
            $doc = $projectPath.DIRECTORY_SEPARATOR.$projectHelpLink;
            if ($fileSystem->exists($doc)) {
                $fileSystem->copy($doc, $rootDir.$projectHelpLink);
            }
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
                $withMetadatas = $params["metadatas"];
                $withXsl = $params["xsl"];
                $fileSystem->appendToFile($fullTranscriptionFilePath.'.xml', $this->generateXML($media, $withMetadatas, $withXsl));
            }

            if ($params["medias"] && $media->getUrl()) {
                if ($media->getIiifServer()) {
                    $iiifPath = $fullMediaFilePath .".xml";
                    $fileSystem->appendToFile($iiifPath, "<data><identifier>".$media->getUrl()."</identifier><data/>");
                } else {
                    $filePath = $projectPath.DIRECTORY_SEPARATOR.$media->getUrl();
                    $ext = pathinfo($filePath, PATHINFO_EXTENSION);
                    $fileSystem->copy($filePath, $fullMediaFilePath.'.'.$ext);
                }
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

    public function generateXML(Media $media, $withMetadatas = true, $withXsl = true)
    {
        $project = $media->getProject();
        $transcription = $media->getTranscription()->getContent();
        $xslFile = $this->fileManager->getProjectPath($project).DIRECTORY_SEPARATOR."export.xsl";
        $header = $withMetadatas ? $this->generateHeader($media) : "";
        $header = preg_replace("/<\?xml[^<>]+>\s/", "", $header);

        if (file_exists($xslFile) && $withXsl) {
            $xsl = new \DOMDocument();
            $xsl->load($xslFile);

            $xslt = new \XSLTProcessor();
            $xslt->importStylesheet($xsl);

            $xml = new \DOMDocument();

            $xml->loadXML("<xml>".$header."<body>".$transcription."</body></xml>");
            $xml->xinclude(LIBXML_NOWARNING);

            $output = $xslt->transformToXML($xml);

            return $output;
        }

        $output = $header."\n".$transcription;

        return $output;
    }

    private function generateHeader(Media $media)
    {
        $transcription  = $media->getTranscription();
        $contributors   = $this->tm->getContributors($transcription);
        $status         = $this->translator->trans("transcription_status_".$this->tm->getStatus($transcription));
        $project        = $media->getProject();
        $mediaRealName  = $media->getName();
        if (!$media->getIiifServer() && $media->getUrl()) {
            $mediaParts     = pathinfo($media->getUrl());
            $mediaRealName  = $media->getName() . "." . $mediaParts["extension"];
        }
        $platformUrl    = $this->router->generate('home', [], UrlGeneratorInterface::ABSOLUTE_URL);
        $projectUrl     = $this->router->generate('project_display', ["id" => $project->getId()], UrlGeneratorInterface::ABSOLUTE_URL);
        $platformContributors = ["ELAN","D??marre SHS !"];

        $xml = new \DOMDocument(LIBXML_NOXMLDECL);
        $xmlMetadatas = $xml->createElement("tact_metadatas");

        // PLATFORM
        $xmlPlatform = $xml->createElement("tact_platform");
        $xmlPlatformUrl = $xml->createElement("tact_platform_url", $platformUrl);
        $xmlPlatformContributors = $xml->createElement("tact_platform_contributors");
        foreach ($platformContributors as $platformContributor) {
            $xmlPlatformContributor = $xml->createElement("tact_platform_contributor", $platformContributor);
            $xmlPlatformContributors->appendChild($xmlPlatformContributor);
        }
        $xmlPlatform->appendChild($xmlPlatformUrl);
        $xmlPlatform->appendChild($xmlPlatformContributors);
        $xmlMetadatas->appendChild($xmlPlatform);

        // PROJECT
        $xmlProject = $xml->createElement("tact_project");
        $xmlProjectName = $xml->createElement("tact_project_name", $media->getProject()->getName());
        $xmlProjectUrl = $xml->createElement("tact_project_url", $projectUrl);

        $xmlProjectManagers = $xml->createElement("tact_project_managers");
        $xmlProject->appendChild($xmlProjectName);
        $xmlProject->appendChild($xmlProjectUrl);
        $projectManagers = $this->userRepo->getManagersByProject($project);
        foreach ($projectManagers as $projectManager) {
            $xmlProjectManager = $xml->createElement("tact_project_manager", $projectManager->getUsername());
            $xmlProjectManagers->appendChild($xmlProjectManager);
        }
        $xmlProject->appendChild($xmlProjectManagers);
        $xmlMetadatas->appendChild($xmlProject);

        // MEDIA
        $xmlMedia = $xml->createElement("tact_media");
        $xmlMediaName = $xml->createElement("tact_media_name", $media->getName());
        $xmlMediaUrl = $xml->createElement("tact_media_url", $mediaRealName);
        $xmlMediaStatus = $xml->createElement("tact_media_status", $status);
        $xmlMediaExportDate = $xml->createElement("tact_media_export_date", date('Y-m-d\TH:i:s'));
        $xmlMediaContributors = $xml->createElement("tact_media_contributors");
        foreach ($contributors as $contributor) {
            $userProjectStatus = $this->em->getRepository("App:UserProjectStatus")->findOneBy(["user"=> $contributor, "project" => $project]);
            $status            = ($userProjectStatus && $userProjectStatus->getEnabled()) ? $userProjectStatus : null;
            $statusName        = ($status) ? $status->getStatus()->getName() : "user_status_transcriber_name";

            $xmlMediaContributor = $xml->createElement("tact_media_contributor");
            $xmlMediaContributorName = $xml->createElement("name", $contributor->getUsername());
            $xmlMediaContributorRole = $xml->createElement("role", $this->translator->trans($statusName));
            $xmlMediaContributor->appendChild($xmlMediaContributorName);
            $xmlMediaContributor->appendChild($xmlMediaContributorRole);
            $xmlMediaContributors->appendChild($xmlMediaContributor);
        }
        // MEDIA METADATAS
        $xmlMediaMetadatas = $xml->createElement("tact_media_metadatas");
        $currentMetadatas = $media->getMetadatas();
        foreach ($currentMetadatas as $currentMetadata) {
            $metadataValue = $currentMetadata->getValue();
            $metadataName = $currentMetadata->getMetadata()->getName();
            $xmlMediaMetadata = $xml->createElement("tact_media_metadata");
            $xmlMediaMetadata->setAttribute("name", addslashes($metadataName));
            $xmlMediaMetadata->setAttribute("value", addslashes($metadataValue));
            $xmlMediaMetadatas->appendChild($xmlMediaMetadata);
        }
        $xmlMedia->appendChild($xmlMediaMetadatas);
        // FIN METADATAS
        $xmlMedia->appendChild($xmlMediaContributors);
        $xmlMedia->appendChild($xmlMediaName);
        $xmlMedia->appendChild($xmlMediaExportDate);
        $xmlMedia->appendChild($xmlMediaStatus);
        $xmlMedia->appendChild($xmlMediaUrl);

        $xmlMetadatas->appendChild($xmlMedia);
        $xml->appendChild($xmlMetadatas);

        $xml->preserveWhiteSpace = false;
        $xml->formatOutput = true;

        return html_entity_decode($xml->saveXml(null, LIBXML_NOXMLDECL));
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
