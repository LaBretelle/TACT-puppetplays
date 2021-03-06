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
     * @ORM\OneToOne(targetEntity="App\Entity\Media", mappedBy="transcription")
     */
    private $media;

    /**
     * @ORM\OneToMany(targetEntity="App\Entity\TranscriptionLog", mappedBy="transcription", cascade={"persist", "remove"})
     */
    private $transcriptionLogs;

    /**
     * @ORM\OneToOne(targetEntity="App\Entity\ReviewRequest", mappedBy="transcription", cascade={"persist", "remove"})
     */
    private $reviewRequest;

    /**
     * @ORM\Column(type="boolean", nullable=true)
     */
    private $isValid;

    /**
     * @ORM\OneToMany(targetEntity="App\Entity\Comment", mappedBy="transcription", orphanRemoval=true)
     * @ORM\OrderBy({"createdAt" = "DESC"})
     */
    private $comments;

    /**
     * @ORM\ManyToMany(targetEntity="App\Entity\User", inversedBy="subscribedTranscriptions")
     * @ORM\JoinTable(name="transcriptions_subscribers")
     */
    private $subscribersUsers;

    public function __construct()
    {
        $this->transcriptionLogs = new ArrayCollection();
        $this->isValid = false;
        $this->comments = new ArrayCollection();
        $this->subscribersUsers = new ArrayCollection();
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

    public function getReviewRequest(): ?ReviewRequest
    {
        return $this->reviewRequest;
    }

    public function setReviewRequest(ReviewRequest $reviewRequest): self
    {
        $this->reviewRequest = $reviewRequest;

        // set the owning side of the relation if necessary
        if ($this !== $reviewRequest->getTranscription()) {
            $reviewRequest->setTranscription($this);
        }

        return $this;
    }

    public function getIsValid(): ?bool
    {
        return $this->isValid;
    }

    public function setIsValid(?bool $isValid): self
    {
        $this->isValid = $isValid;

        return $this;
    }

    /**
     * @return Collection|Comment[]
     */
    public function getComments(): Collection
    {
        return $this->comments;
    }

    public function addComment(Comment $comment): self
    {
        if (!$this->comments->contains($comment)) {
            $this->comments[] = $comment;
            $comment->setTranscription($this);
        }

        return $this;
    }

    public function removeComment(Comment $comment): self
    {
        if ($this->comments->contains($comment)) {
            $this->comments->removeElement($comment);
            // set the owning side to null (unless already changed)
            if ($comment->getTranscription() === $this) {
                $comment->setTranscription(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection|User[]
     */
    public function getSubscribersUsers(): Collection
    {
        return $this->subscribersUsers;
    }

    public function addSubscribersUser(User $subscribersUser): self
    {
        if (!$this->subscribersUsers->contains($subscribersUser)) {
            $this->subscribersUsers[] = $subscribersUser;
        }

        return $this;
    }

    public function removeSubscribersUser(User $subscribersUser): self
    {
        if ($this->subscribersUsers->contains($subscribersUser)) {
            $this->subscribersUsers->removeElement($subscribersUser);
        }

        return $this;
    }
}
