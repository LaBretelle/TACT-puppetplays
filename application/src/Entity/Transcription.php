<?php

namespace App\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity()
 */
class Transcription
{
    /**
     * @ORM\Id()
     * @ORM\GeneratedValue()
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @ORM\Column(type="text", nullable=true)
     */
    private $content;

    /**
     * @ORM\OneToOne(targetEntity="App\Entity\Media", mappedBy="transcription", cascade={"persist", "remove"})
     */
    private $media;

    /**
     * @ORM\OneToMany(targetEntity="App\Entity\TranscriptionLog", mappedBy="transcription", cascade={"persist", "remove"})
     */
    private $transcriptionLogs;

    public function __construct()
    {
        $this->transcriptionLogs = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getContent(): ?string
    {
        return $this->content;
    }

    public function setContent(?string $content): self
    {
        $this->content = $content;

        return $this;
    }

    public function getMedia(): ?Media
    {
        return $this->media;
    }

    public function setMedia(?Media $media): self
    {
        $this->media = $media;

        // set (or unset) the owning side of the relation if necessary
        $newTranscription = $media === null ? null : $this;
        if ($newTranscription !== $media->getTranscription()) {
            $media->setTranscription($newTranscription);
        }

        return $this;
    }

    /**
     * @return Collection|TranscriptionLog[]
     */
    public function getTranscriptionLogs(): Collection
    {
        return $this->transcriptionLogs;
    }

    public function addTranscriptionLog(TranscriptionLog $transcriptionLog): self
    {
        if (!$this->transcriptionLogs->contains($transcriptionLog)) {
            $this->transcriptionLogs[] = $transcriptionLog;
            $transcriptionLog->setTranscription($this);
        }

        return $this;
    }

    public function removeTranscriptionLog(TranscriptionLog $transcriptionLog): self
    {
        if ($this->transcriptionLogs->contains($transcriptionLog)) {
            $this->transcriptionLogs->removeElement($transcriptionLog);
            // set the owning side to null (unless already changed)
            if ($transcriptionLog->getTranscription() === $this) {
                $transcriptionLog->setTranscription(null);
            }
        }

        return $this;
    }
}
