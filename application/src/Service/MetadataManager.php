<?php

namespace App\Service;

use App\Entity\Media;
use App\Entity\Metadata;
use App\Entity\MetadataMedia;
use App\Entity\Project;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\DomCrawler\Crawler;

class MetadataManager
{
    private $em;

    public function __construct(EntityManagerInterface $em)
    {
        $this->em = $em;
    }

    public function import(Project $project, $fileContent)
    {
        $crawler = new Crawler($fileContent);
        $metadatas = $crawler->filter('tact_project_metadata')->each(function (Crawler $node, $i) {
            return ["name" => $node->attr('name'), "default" => $node->attr('defaultvalue')];
        });

        foreach ($metadatas as $metadata) {
            $name = $metadata["name"];
            $default = $metadata["default"];

            if ($existing = $this->em->getRepository(Metadata::class)->findOneBy(["project" => $project, "name" => $name])) {
                $previousDefault = $existing->getDefaultValue();
                if ($previousDefault != $default) {
                    $mediaMetadatas = $this->em->getRepository(MetadataMedia::class)->findBy(["metadata" => $existing, "value" => $previousDefault]);
                    $existing->setDefaultValue($default);
                    foreach ($mediaMetadatas as $mediaMetadata) {
                        $mediaMetadata->setValue($default);
                        $this->em->persist($mediaMetadata);
                    }
                }
            } else {
                $metadataProject = new Metadata;
                $metadataProject->setName($name);
                $metadataProject->setDefaultValue($default);
                $metadataProject->setProject($project);
                $this->em->persist($metadataProject);

                $this->apply($metadataProject, $project, null, "all");
            }
        }

        $this->em->flush();

        return;
    }

    // on applique la nouvelle valeur que pour ceux qui avaient l'ancienne défaut.
    public function apply(Metadata $metadata, Project $project, $lastDefault, $applyTo)
    {
        $medias = $project->getMedias();
        foreach ($medias as $media) {
            $metadataMedia = $this->em->getRepository(MetadataMedia::class)->findOneBy(["metadata" => $metadata, "media" => $media]);
            if (!$metadataMedia) {
                $this->createMetadataMedia($media, $metadata);
            } else {
                switch ($applyTo) {
                  case 'same':
                    if ($metadataMedia->getValue() == $lastDefault) {
                        $metadataMedia->setValue($metadata->getDefaultValue());
                    }
                    break;
                  case 'empty':
                    if (!$metadataMedia->getValue() || $metadataMedia->getValue() == "") {
                        $metadataMedia->setValue($metadata->getDefaultValue());
                    }
                    break;
                  case 'all':
                      $metadataMedia->setValue($metadata->getDefaultValue());
                    break;
                }
            }
        }
        $this->em->flush();
    }

    public function createMetadataMedia(Media $media, Metadata $metadata)
    {
        $metadataMedia = new MetadataMedia;
        $metadataMedia->setMedia($media);
        $metadataMedia->setMetadata($metadata);
        $metadataMedia->setValue($metadata->getDefaultValue());

        $this->em->persist($metadataMedia);
    }

    public function applyToMedia(Media $media)
    {
        $metadatas = $media->getProject()->getMetadatas();
        foreach ($metadatas as $metadata) {
            $this->createMetadataMedia($media, $metadata);
        }

        return $media;
    }

    public function exportProjectMetadatas(Project $project)
    {
        $metadatas = $project->getMetadatas();
        $xml = new \DOMDocument();
        $xmlMetadatas = $xml->createElement("tact_project_metadatas");
        foreach ($metadatas as $metadata) {
            $xmlMetadata = $xml->createElement("tact_project_metadata");
            $xmlMetadata->setAttribute("name", addslashes($metadata->getName()));
            $xmlMetadata->setAttribute("defaultvalue", addslashes($metadata->getDefaultValue()));
            $xmlMetadatas->appendChild($xmlMetadata);
        }
        $xml->appendChild($xmlMetadatas);
        $xml->preserveWhiteSpace = false;
        $xml->formatOutput = true;

        return html_entity_decode($xml->saveXml(null, LIBXML_NOXMLDECL));
    }
}
