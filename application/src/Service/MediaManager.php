<?php

namespace App\Service;

use App\Entity\Directory;
use App\Entity\IiifServer;
use App\Entity\Media;
use App\Entity\Project;
use App\Entity\Transcription;
use App\Entity\TranscriptionLog;
use App\Service\AppEnums;
use App\Service\TranscriptionManager;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\DomCrawler\Crawler;
use Symfony\Component\HttpFoundation\File\File;
use Symfony\Component\Security\Core\Security;

class MediaManager
{
    protected $em;
    protected $security;
    protected $transcriptionManager;

    public function __construct(EntityManagerInterface $em, Security $security, TranscriptionManager $transcriptionManager)
    {
        $this->em = $em;
        $this->security = $security;
        $this->transcriptionManager = $transcriptionManager;
    }


    public function createMediaFromIIIF(string $identifier, string $fileClientName, Project $project, Directory $parent = null, IiifServer $server)
    {
        $name = explode('.', $fileClientName)[0];
        $media = new Media();
        $media->setUrl($identifier);
        $media->setName($name);
        $media->setParent($parent);
        $media->setProject($project);
        $media->setIiifServer($server);
        $project->addMedia($media);
        $media = $this->initMediaTranscription($media);

        $this->em->persist($project);
        $this->em->persist($media);
        $this->em->flush();

        return $media;
    }

    public function createMediaFromFile(File $file, string $fileClientName, Project $project, Directory $parent = null)
    {
        $name = explode('.', $fileClientName)[0];
        // $extension = $file->guessExtension();
        $extension = pathinfo($fileClientName, PATHINFO_EXTENSION);
        $media = new Media();
        $media->setUrl(md5(uniqid()).'.'.$extension);
        $media->setName($name);
        $media->setParent($parent);
        $media->setProject($project);
        $project->addMedia($media);
        $media = $this->initMediaTranscription($media);

        $this->em->persist($project);
        $this->em->persist($media);
        $this->em->flush();

        return $media;
    }

    public function createMediaFromNothing(string $fileClientName, Project $project, string $content, Directory $parent = null)
    {
        $name = explode('.', $fileClientName)[0];
        $media = new Media();
        $media->setUrl(null);
        $media->setName($name);
        $media->setParent($parent);
        $media->setProject($project);
        $project->addMedia($media);
        $media = $this->initMediaTranscription($media, $content);

        $this->em->persist($project);
        $this->em->persist($media);
        $this->em->flush();

        return $media;
    }

    public function initMediaTranscription(Media $media, $content = '')
    {
        $transcription = new Transcription();
        $transcription->setContent($content);
        $this->transcriptionManager->addLog($transcription, AppEnums::TRANSCRIPTION_LOG_CREATED);
        $media->setTranscription($transcription);

        return $media;
    }

    public function setMediaTranscription(Media $media, string $content)
    {
        $transcription = $media->getTranscription();
        $oldContent = $transcription->getContent();

        if ($oldContent !== $content) {
            $transcription->setContent($content);
            $this->transcriptionManager->addLog($transcription, AppEnums::TRANSCRIPTION_LOG_UPDATED);
            $this->em->persist($transcription);
            $this->em->flush();
        }

        return;
    }

    public function save(Media $media)
    {
        $this->em->persist($media);
        $this->em->flush();

        return $media;
    }

    public function getIIIFInfos($absolutePath)
    {
        $xml = file_get_contents($absolutePath);
        preg_match('/<([^<:]+:)?identifier>([^<]+)<\/([^<:]+:)?identifier>/', $xml, $matches);
        $id = $matches[2];

        return $id;
    }
}
