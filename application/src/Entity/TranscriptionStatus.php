<?php

namespace App\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;

/**
 * @ORM\Entity(repositoryClass="App\Repository\TranscriptionStatusRepository")
 *
 * @UniqueEntity("name")
 */
class TranscriptionStatus
{
    /**
     * @ORM\Id()
     * @ORM\GeneratedValue()
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @ORM\Column(type="string", length=255, unique=true)
     */
    private $name;

    /**
     * @ORM\OneToMany(targetEntity="App\Entity\Transcription", mappedBy="status")
     */
    private $transcriptions;

    public function __construct()
    {
        $this->transcriptions = new ArrayCollection();
    }

    public function getId()
    {
        return $this->id;
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

    /**
     * @return Collection|Transcription[]
     */
    public function getTranscriptions(): Collection
    {
        return $this->transcriptions;
    }

    public function addTranscription(Transcription $transcription): self
    {
        if (!$this->transcriptions->contains($transcription)) {
            $this->transcriptions[] = $transcription;
            $transcription->setStatus($this);
        }

        return $this;
    }

    public function removeTranscription(Transcription $transcription): self
    {
        if ($this->transcriptions->contains($transcription)) {
            $this->transcriptions->removeElement($transcription);
            // set the owning side to null (unless already changed)
            if ($transcription->getStatus() === $this) {
                $transcription->setStatus(null);
            }
        }

        return $this;
    }
}
