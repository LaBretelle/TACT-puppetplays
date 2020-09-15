<?php

namespace App\Service;

use App\Entity\Directory;
use App\Entity\Media;
use App\Entity\Project;
use App\Entity\User;
use App\Service\AppEnums;
use App\Service\DirectoryManager;
use App\Service\FileManager;
use App\Service\FlashManager;
use App\Service\MediaManager;
use App\Service\ReviewManager;
use App\Service\TranscriptionManager;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\DomCrawler\Crawler;
use Symfony\Component\Filesystem\Exception\IOExceptionInterface;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\HttpFoundation\File\File;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\Security\Core\Authorization\AuthorizationCheckerInterface;

class ProjectManager
{
    protected $em;
    protected $authChecker;
    protected $mediaManager;
    protected $fileManager;
    protected $dirManager;
    protected $reviewManager;
    protected $tm;
    protected $fm;

    public function __construct(
        EntityManagerInterface $em,
        MediaManager $mediaManager,
        AuthorizationCheckerInterface $authChecker,
        FileManager $fileManager,
        DirectoryManager $dirManager,
        ReviewManager $reviewManager,
        TranscriptionManager $tm,
        FlashManager $fm
    ) {
        $this->em = $em;
        $this->authChecker = $authChecker;
        $this->mediaManager = $mediaManager;
        $this->fileManager = $fileManager;
        $this->dirManager = $dirManager;
        $this->reviewManager = $reviewManager;
        $this->tm = $tm;
        $this->fm = $fm;
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

    public function save($project)
    {
        $project->setUpdatedAt(new \DateTime);

        $this->em->persist($project);
        $this->em->flush();

        return $project;
    }

    public function handleXslExport(Project $project, UploadedFile $file = null)
    {
        if ($file) {
            $fileName = "export.xsl";
            $filePath = $this->fileManager->getProjectPath($project);
            $file->move($filePath, $fileName);

            return $filePath;
        }

        return;
    }

    public function handleJsonSchema(Project $project, UploadedFile $file = null)
    {
        if ($file) {
            $fileName = "tei-schema.json";
            $filePath = $this->fileManager->getProjectPath($project);
            $file->move($filePath, $fileName);

            return $filePath;
        }

        return;
    }

    public function handleHelpFile(Project $project, UploadedFile $file = null)
    {
        if ($file) {
            $fileName = $file->getClientOriginalName();
            $filePath = $this->fileManager->getProjectPath($project);
            $file->move($filePath, $fileName);
            $project->setProjectHelpLink($fileName);
        }

        $this->em->persist($project);
        $this->em->flush();
    }


    public function handleImage(Project $project, UploadedFile $file = null, string $previous_image = null)
    {
        if ($file) {
            $fileName = md5(uniqid()).'.'.$file->guessExtension();
            $filePath = $this->fileManager->getProjectPath($project);
            $file->move($filePath, $fileName);

            $imageURL = $filePath.DIRECTORY_SEPARATOR.$fileName;
            list($width, $height) = getimagesize($imageURL);
            $imagick = new \Imagick(realpath($imageURL));
            $imagick->cropImage($width, $width/4, 0, 0);
            $imagick->writeImage($imageURL);

            $project->setImage($fileName);
        } elseif ($previous_image) {
            $project->setImage($previous_image);
        }

        $this->em->persist($project);
        $this->em->flush();
    }

    public function handleReviewLimit(Project $project, $originalReviewLimit)
    {
        $reviewLimit = $project->getnbValidation();
        if ($reviewLimit < $originalReviewLimit) {
            $medias = $project->getMedias();
            foreach ($medias as $media) {
                $this->reviewManager->testForValidation($media->getTranscription(), $project);
            }

            $this->fm->add('notice', 'transcriptions_status_recalculated');
        }

        return;
    }

    public function delete(Project $project)
    {
        $this->removeProjectMedia($project);
        $this->em->remove($project);
        $this->em->flush();

        $this->fm->add('notice', 'project_deleted');

        return;
    }

    public function initIIIFProcessing(Project $project, string $uploadPath, Directory $parent = null, $parameters)
    {
        $projectPath = $this->fileManager->getProjectPath($project);
        $this->recursiveBrowse($project, $projectPath, $uploadPath, "iiif", $parent, $parameters);
        $this->deleteTempFolders($uploadPath);
    }

    public function initMediaProcessing(Project $project, string $uploadPath, Directory $parent = null, $parameters)
    {
        $projectPath = $this->fileManager->getProjectPath($project);
        $thumbnailDir = $projectPath.DIRECTORY_SEPARATOR.'thumbnails';
        if (!is_dir($thumbnailDir)) {
            mkdir($thumbnailDir);
        }
        $this->recursiveBrowse($project, $projectPath, $uploadPath, "media", $parent, $parameters);
        $this->deleteTempFolders($uploadPath);
    }

    public function initXmlProcessing(Project $project, string $uploadPath, Directory $parent = null, $parameters)
    {
        $projectPath = $this->fileManager->getProjectPath($project);
        $this->recursiveBrowse($project, $projectPath, $uploadPath, "xml", $parent, $parameters);
        $this->deleteTempFolders($uploadPath);

        $this->em->flush();
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

    public function recursiveBrowse(Project $project, string $projectPath, string $uploadPath, $uploadType, Directory $parent = null, $parameters)
    {
        $createEmptyMedia = $parameters["createEmptyMedia"];
        $overwrite = $parameters["overwrite"];
        $validTranscript = $parameters["validTranscript"];
        $rootTag = $parameters["rootTag"];
        $updateMedia = $parameters["updateMedia"];
        $dirRepo = $this->em->getRepository('App:Directory');
        $mediaRepo = $this->em->getRepository('App:Media');
        $server = $parameters["server"];

        $cdir = scandir($uploadPath);

        foreach ($cdir as $value) {
            if (!in_array($value, [".",".."])) {
                $absolutePath = $uploadPath . DIRECTORY_SEPARATOR . $value;
                if (is_dir($absolutePath)) {
                    $existingDir = $dirRepo->findOneBy(["name" => $value, "parent" => $parent, "project" => $project]);
                    if (!$existingDir) {
                        $newDirectory = $this->dirManager->create($project, $value, $parent);
                    } else {
                        $newDirectory = $existingDir;
                        $this->fm->add('warning', 'directory_already_existing', ["%dir%" => $absolutePath]);
                    }

                    $this->recursiveBrowse($project, $projectPath, $absolutePath, $uploadType, $newDirectory, $parameters);
                } else {
                    $processedName = explode('.', $value)[0];
                    $existingMedia = $mediaRepo->findOneBy(["name" => $processedName, "parent" => $parent, "project" => $project]);

                    // CAS IIIF
                    if ($uploadType == "iiif") {
                        $server = $parameters["server"];
                        $identifier = $this->mediaManager->getIIIFInfos($absolutePath);
                        if ($identifier) {
                            if (!$existingMedia) {
                                $media = $this->mediaManager->createMediaFromIIIF($identifier, $value, $project, $parent, $server);
                            } else {
                                if ($updateMedia) {
                                    $existingMedia->setUrl($identifier);
                                    $this->em->persist($existingMedia);
                                    $this->em->flush();
                                } else {
                                    $this->fm->add('warning', 'media_already_existing', ["%media%" => $absolutePath]);
                                }
                            }
                        } else {
                            $this->fm->add('warning', 'iiif_identifier_error', ["%media%" => $absolutePath]);
                        }
                    }
                    // CAS MEDIA
                    elseif ($uploadType == "media") {
                        $file = new File($absolutePath);
                        if (!$existingMedia) {
                            $media = $this->mediaManager->createMediaFromFile($file, $value, $project, $parent);
                            $file->move($projectPath, $media->getUrl());

                            $this->generateThumbnail($projectPath, $media->getUrl(), 512);
                        } else {
                            if ($updateMedia) {
                                $url = $existingMedia->getUrl();
                                $file->move($projectPath, $url);
                                $this->generateThumbnail($projectPath, $url, 512);
                            } else {
                                $this->fm->add('warning', 'media_already_existing', ["%media%" => $absolutePath]);
                            }
                        }
                    } elseif ($uploadType == "xml") {
                        // CAS XML
                        $extension = pathinfo($absolutePath, PATHINFO_EXTENSION);
                        $fileContent = file_get_contents($absolutePath);
                        $extensions = ["xml", "txt", "rdf"];
                        $markupExtensions = ["xml", "rdf"];

                        if (in_array($extension, $extensions)) {
                            // Gestion langages balisés
                            if (in_array($extension, $markupExtensions)  && $rootTag != "") {
                                $fileContent = $this->getNodeContent($rootTag, $fileContent);
                            }

                            // gestion pas de média trouvé
                            if (!$existingMedia) {
                                $existingMedia = ($createEmptyMedia)
                                  ? $this->mediaManager->createMediaFromNothing($value, $project, $fileContent, $parent)
                                  : null;
                            }
                            // gestion média déjà existant
                            else {
                                $transcription = $existingMedia->getTranscription();
                                if (!$transcription || $transcription->getContent() == '' || $overwrite) {
                                    $transcription->setContent($fileContent);
                                    $this->em->persist($transcription);
                                }
                            }

                            // validation de la transcription
                            if ($validTranscript && $existingMedia) {
                                $transcription = $existingMedia->getTranscription();
                                $this->tm->validate($transcription, true);
                            }
                        }
                    }
                }
            }
        }
    }

    private function getNodeContent($rootTag, $fileContent)
    {
        $crawler = new Crawler($fileContent);
        $nodes = $crawler->filter($rootTag);

        return ($nodes->count() > 0)
           ? $nodes->html()
           : $fileContent;
    }


    public function addProjectIIIF(Project $project, $files, Directory $parent = null, $parameters)
    {
        $basePath = $this->fileManager->getBaseProjectPath();
        $projectMediaPath = $this->fileManager->getProjectPath($project);

        $uploadPath = $projectMediaPath . DIRECTORY_SEPARATOR . 'tmp';
        if (!is_dir($uploadPath)) {
            mkdir($uploadPath);
        }

        $zipName = $files->getClientOriginalName();
        $files->move($uploadPath, $zipName);
        $zip = new \ZipArchive;
        $success = $zip->open($uploadPath . DIRECTORY_SEPARATOR . $zipName);
        if ($success === true) {
            $zip->extractTo($uploadPath);
            $zip->close();
        }

        unlink($uploadPath . DIRECTORY_SEPARATOR . $zipName);

        $this->initIIIFProcessing($project, $uploadPath, $parent, $parameters);
        rmdir($uploadPath);

        $this->fm->add('notice', 'iiif_added');

        return $project;
    }


    public function addProjectMedia(Project $project, $files, Directory $parent = null, $parameters)
    {
        $isZip = $parameters["isZip"];

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
        if (!is_dir($uploadPath)) {
            mkdir($uploadPath);
        }

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

        $this->initMediaProcessing($project, $uploadPath, $parent, $parameters);
        rmdir($uploadPath);

        $this->fm->add('notice', 'media_added');

        return $project;
    }

    public function addProjectXml(Project $project, $files, Directory $parent = null, $parameters)
    {
        $isZip = $parameters["isZip"];

        $basePath = $this->fileManager->getBaseProjectPath();
        $projectMediaPath = $this->fileManager->getProjectPath($project);

        if (!is_dir($basePath)) {
            mkdir($basePath);
        }

        if (!is_dir($projectMediaPath)) {
            mkdir($projectMediaPath);
        }

        $uploadPath = $projectMediaPath . DIRECTORY_SEPARATOR . 'tmp';
        if (!is_dir($uploadPath)) {
            mkdir($uploadPath);
        }

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

        $this->initXmlProcessing($project, $uploadPath, $parent, $parameters);
        rmdir($uploadPath);

        $this->fm->add('notice', 'xml_added');

        return $project;
    }

    public function removeProjectMediaByIds(array $ids)
    {
        $mediaRepository = $this->em->getRepository(Media::class);

        foreach ($ids as $id) {
            $media = $mediaRepository->find($id);
            $mediaURL = $media->getUrl();
            $project = $media->getProject();
            $project->removeMedia($media);
            $projectPath = $this->fileManager->getProjectPath($project);
            $filePath = $projectPath.DIRECTORY_SEPARATOR.$mediaURL;
            $this->fileManager->delete($filePath);
            $thumbnailPath = $projectPath.DIRECTORY_SEPARATOR.'thumbnails'.DIRECTORY_SEPARATOR.$mediaURL;
            $this->fileManager->delete($thumbnailPath);

            $this->em->remove($media);
            $this->em->persist($project);
        }
        $this->em->flush();

        return;
    }

    public function removeProjectMedia(Project $project)
    {
        $mediaRepository = $this->em->getRepository(Media::class);
        $toDelete = $project->getMedias();

        $projectPath = $this->fileManager->getProjectPath($project);
        foreach ($toDelete as $media) {
            $project->removeMedia($media);
            $this->em->remove($media);
        }

        $this->fileManager->delete($projectPath);

        return;
    }

    public function deleteFolders(Project $project, array $ids)
    {
        $directoryRepository = $this->em->getRepository(Directory::class);
        $projectPath = $this->fileManager->getProjectPath($project);
        foreach ($ids as $id) {
            $dir = $directoryRepository->find($id);
            $this->em->remove($dir);
            $medias = $dir->getMedias();
            foreach ($medias as $media) {
                $filePath = $projectPath.DIRECTORY_SEPARATOR.$media->getUrl();
                $this->fileManager->delete($filePath);
            }
        }
        $this->em->flush();
        $this->fm->add('notice', 'folders_deleted');

        return;
    }

    public function moveProjectMedia(int $target, array $ids)
    {
        $mediaRepository = $this->em->getRepository(Media::class);
        $dirRepository = $this->em->getRepository(Directory::class);
        $movedMedia = [];
        $targetDir = $target === -1 ? null : $dirRepository->find($target);
        foreach ($ids as $id) {
            $media = $mediaRepository->find($id);
            $name = $media->getName();
            $project = $media->getProject();

            $existingMedia = $mediaRepository->findOneBy(["name" => $name, "parent" => $targetDir, "project" => $project]);
            if (!$existingMedia) {
                $media->setParent($targetDir);
                $this->mediaManager->save($media);
                $movedMedia[] = $id;
            }
        }

        return $movedMedia;
    }

    public function moveProjectFolders(int $target, array $ids)
    {
        $mediaRepository = $this->em->getRepository(Media::class);
        $dirRepository = $this->em->getRepository(Directory::class);
        $allMoved = true;
        $targetDir = $target === -1 ? null : $dirRepository->find($target);
        foreach ($ids as $id) {
            $dir = $dirRepository->find($id);
            $name = $dir->getName();
            $project = $dir->getProject();

            $existingDir = $dirRepository->findOneBy(["name" => $name, "parent" => $targetDir, "project" => $project]);
            if (!$existingDir) {
                $dir->setParent($targetDir);
                $this->em->persist($dir);
            } else {
                $allMoved = false;
            }
        }

        if ($allMoved) {
            $this->fm->add('notice', 'folders_moved');
        } else {
            $this->fm->add('warning', 'folders_not_all_moved');
        }

        $this->em->flush();
    }

    public function addFolder(Project $project, int $parentId, string $name)
    {
        $dirRepository = $this->em->getRepository(Directory::class);

        $targetDir = ($parentId === -1) ? null : $dirRepository->find($parentId);
        $dir = $dirRepository->findOneBy(["name" => $name, "parent" => $targetDir, "project" => $project]);

        if (!$dir) {
            $dir = $this->dirManager->create($project, $name, $targetDir);
        } else {
            $this->fm->add('warning', 'directory_already_existing', ["%dir%" => $name]);
        }

        return $dir;
    }

    public function updateFolderName(int $id, string $name)
    {
        $dirRepository = $this->em->getRepository(Directory::class);
        $directory = $dirRepository->find($id);
        $project = $directory->getProject();
        $parent = $directory->getParent();

        $existingDir = $dirRepository->findOneBy(["name" => $name, "parent" => $parent, "project" => $project]);

        if (!$existingDir) {
            $directory->setName($name);
            $this->dirManager->save($directory);
        }

        return ($existingDir) ? false: true;
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

    public function deleteXslt(Project $project)
    {
        $xslPath = $this->fileManager->getProjectPath($project) . DIRECTORY_SEPARATOR . 'export.xsl';
        unlink($xslPath);

        return;
    }

    public function deleteJson(Project $project)
    {
        $jsonPath = $this->fileManager->getProjectPath($project) . DIRECTORY_SEPARATOR . 'tei-schema.json';
        unlink($jsonPath);

        return;
    }

    public function deleteImage(Project $project)
    {
        $imagePath = $this->fileManager->getProjectPath($project) . DIRECTORY_SEPARATOR . $project->getImage();
        unlink($imagePath);

        $project->setImage(null);
        $this->em->persist($project);
        $this->em->flush();

        return;
    }

    public function deleteHelpLink(Project $project)
    {
        $docPath = $this->fileManager->getProjectPath($project) . DIRECTORY_SEPARATOR . $project->getProjectHelpLink();
        unlink($docPath);

        $project->setProjectHelpLink(null);
        $this->em->persist($project);
        $this->em->flush();

        return;
    }

    public function generateThumbnail($path, $fileName, $width)
    {
        $imageURL = $path.DIRECTORY_SEPARATOR.$fileName;
        $imagick = new \Imagick(realpath($imageURL));
        $imagick->adaptiveResizeImage($width, 0);
        $imagick->writeImage($path.DIRECTORY_SEPARATOR.'thumbnails'.DIRECTORY_SEPARATOR.$fileName);

        return;
    }
}
