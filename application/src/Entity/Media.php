<?php

namespace App\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass="App\Repository\MediaRepository")
 */
class Media
{
    /**
     * @ORM\Id()
     * @ORM\GeneratedValue()
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\Project", inversedBy="medias")
     * @ORM\JoinColumn(nullable=false)
     */
    private $project;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private $url;

    /**
     * @ORM\OneToOne(targetEntity="App\Entity\Transcription", inversedBy="media", cascade={"persist", "remove"})
     */
    private $transcription;

    /**
     * @ORM\Column(type="string", length=255)
     */
    private $name;

    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\Directory", inversedBy="medias")
     * @ORM\JoinColumn(nullable=true)
     */
    private $parent;

    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\IiifServer", inversedBy="medias")
     */
    private $iiifServer;

    /**
     * @ORM\OneToMany(targetEntity=MetadataMedia::class, mappedBy="media", orphanRemoval=true)
     */
    private $metadatas;

    public function __construct()
    {
        $this->metadatas = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getParent(): ?Directory
    {
        return $this->parent;
    }

    public function setParent(?Directory $parent): self
    {
        $this->parent = $parent;

        return $this;
    }

    public function getProject(): ?Project
    {
        return $this->project;
    }

    public function setProject(?Project $project): self
    {
        $this->project = $project;

        return $this;
    }

    public function getTranscription(): ?Transcription
    {
        return $this->transcription;
    }

    public function setTranscription(?Transcription $transcription): self
    {
        $this->transcription = $transcription;

        return $this;
    }

    public function setUrl(?string $url) : self
    {
        $this->url = $url;
        return $this;
    }

    public function getUrl() : ?string
    {
        return $this->url;
    }

    public function getName(): ?string
    {
        return $this->name;
    }

    public function setName(string $name): self
    {
        $this->name = $name;

        return $this;
    }

    public function getIiifServer(): ?IiifServer
    {
        return $this->iiifServer;
    }

    public function setIiifServer(?IiifServer $iiifServer): self
    {
        $this->iiifServer = $iiifServer;

        return $this;
    }

    /**
     * @return Collection|MetadataMedia[]
     */
    public function getMetadatas(): Collection
    {
        return $this->metadatas;
    }

    public function addMetadata(MetadataMedia $metadata): self
    {
        if (!$this->metadatas->contains($metadata)) {
            $this->metadatas[] = $metadata;
            $metadata->setMedia($this);
        }

        return $this;
    }

    public function removeMetadata(MetadataMedia $metadata): self
    {
        if ($this->metadatas->contains($metadata)) {
            $this->metadatas->removeElement($metadata);
            // set the owning side to null (unless already changed)
            if ($metadata->getMedia() === $this) {
                $metadata->setMedia(null);
            }
        }

        return $this;
    }
}
