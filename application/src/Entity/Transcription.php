<?php

namespace App\Entity;

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
     * @ORM\ManyToOne(targetEntity="App\Entity\User", inversedBy="transcriptions")
     * @ORM\JoinColumn(nullable=false)
     */
    private $user;

    /**
     * @ORM\OneToOne(targetEntity="App\Entity\Media", mappedBy="transcription", cascade={"persist", "remove"})
     */
    private $media;

    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\TranscriptionStatus", inversedBy="transcriptions")
     * @ORM\JoinColumn(nullable=false)
     */
    private $status;

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

    public function getUser(): ?User
    {
        return $this->user;
    }

    public function setUser(?User $user): self
    {
        $this->user = $user;

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

    public function getStatus(): ?TranscriptionStatus
    {
        return $this->status;
    }

    public function setStatus(?TranscriptionStatus $status): self
    {
        $this->status = $status;

        return $this;
    }
}
