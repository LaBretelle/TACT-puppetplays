<?php


namespace App\Entity;

use App\Entity\UserProjectStatus;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;
use Symfony\Component\Security\Core\User\UserInterface;

/**
 * @ORM\Table(name="app_user")
 * @ORM\Entity(repositoryClass="App\Repository\UserRepository")
 * @UniqueEntity("email", message="user_unique_email")
 * @UniqueEntity("username", message="user_unique_username")
 */
class User implements UserInterface, \Serializable
{
    /**
     * @ORM\Id()
     * @ORM\GeneratedValue()
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @ORM\Column(type="string", length=255)
     */
    private $firstname;

    /**
     * @ORM\Column(type="string", length=255)
     */
    private $lastname;

    /**
     * @ORM\Column(type="string", length=25, unique=true)
     */
    private $username;

    /**
     * @ORM\Column(type="string", length=64)
     */
    private $password;

    /**
     * @var string
     */
    private $plainPassword;

    /**
     * @ORM\Column(type="string", length=255, unique=true)
     */
    private $email;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private $image;

    /**
     * @ORM\Column(type="text", nullable=true)
     */
    private $description;

    /**
     * @ORM\Column(type="boolean")
     */
    private $publicMail;

    /**
     * @ORM\OneToMany(targetEntity="App\Entity\UserProjectStatus", mappedBy="user")
     */
    private $projectStatus;

    /**
     * @ORM\Column(type="json_array")
     */
    private $roles;

    /**
     * @ORM\Column(type="boolean")
     */
    private $active;

    /**
     * @ORM\Column(type="boolean")
     */
    private $anonymous;

    /**
     * Random string sent to the user email address in order to verify it.
     *
     * @var string|null
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    protected $confirmationToken;

    /**
     * @var \DateTime|null
     * @ORM\Column(type="datetime", nullable=true)
     */
    protected $passwordRequestedAt;

    /**
     * @ORM\Column(type="datetime")
     */
    private $createdAt;

    /**
     * @ORM\Column(type="datetime")
     */
    private $updatedAt;

    /**
     * @ORM\OneToMany(targetEntity="App\Entity\TranscriptionLog", mappedBy="user")
     */
    private $transcriptionLogs;

    /**
     * @ORM\OneToMany(targetEntity="App\Entity\ReviewRequest", mappedBy="user", orphanRemoval=true)
     */
    private $reviewRequests;

    /**
     * @ORM\OneToMany(targetEntity="App\Entity\Review", mappedBy="user", orphanRemoval=true)
     */
    private $reviews;

    /**
     * @ORM\OneToMany(targetEntity="App\Entity\Comment", mappedBy="user", orphanRemoval=true)
     */
    private $comments;

    /**
     * @ORM\Column(type="boolean")
     */
    private $firstTranscript;

    /**
     * @ORM\ManyToMany(targetEntity="App\Entity\Transcription", mappedBy="subscribersUsers")
     */
    private $subscribedTranscriptions;

    /**
     * @ORM\Column(type="datetime", nullable=true)
     */
    private $lastAccess = null;


    public function __construct()
    {
        $this->active = false;
        $this->publicMail = true;
        $this->firstTranscript = true;
        $this->anonymous = false;
        $this->projectStatus = new ArrayCollection();
        $this->createdAt = new \DateTime();
        $this->updatedAt = new \DateTime();
        $this->transcriptionLogs = new ArrayCollection();
        $this->reviewRequests = new ArrayCollection();
        $this->reviews = new ArrayCollection();
        $this->comments = new ArrayCollection();
        $this->subscribedTranscriptions = new ArrayCollection();
    }

    public function getId() :int
    {
        return $this->id;
    }

    public function getFirstname(): ?string
    {
        return $this->firstname;
    }

    public function setFirstname(string $firstname): self
    {
        $this->firstname = $firstname;

        return $this;
    }

    public function getLastname(): ?string
    {
        return $this->lastname;
    }

    public function setLastname(string $lastname): self
    {
        $this->lastname = $lastname;

        return $this;
    }

    public function getFullname(): string
    {
        return $this->lastname . ' ' . $this->firstname;
    }

    public function getUsername(): ?string
    {
        return $this->username;
    }

    public function setUsername(string $username): self
    {
        $this->username = $username;

        return $this;
    }

    public function getPassword(): ?string
    {
        return $this->password;
    }

    public function setPassword(string $password): self
    {
        $this->password = $password;

        return $this;
    }

    public function getPlainPassword(): ?string
    {
        return $this->plainPassword;
    }

    public function setPlainPassword(string $plainPassword): self
    {
        $this->plainPassword = $plainPassword;

        return $this;
    }

    public function getEmail(): ?string
    {
        return $this->email;
    }

    public function setEmail(string $email): self
    {
        $this->email = $email;

        return $this;
    }

    public function getImage(): ?string
    {
        return $this->image;
    }

    public function setImage(?string $image): self
    {
        $this->image = $image;

        return $this;
    }

    public function getDescription(): ?string
    {
        return $this->description;
    }

    public function setDescription(?string $description): self
    {
        $this->description = $description;

        return $this;
    }

    public function getPublicMail(): ?bool
    {
        return $this->publicMail;
    }

    public function setPublicMail(bool $publicMail): self
    {
        $this->publicMail = $publicMail;

        return $this;
    }

    public function getRoles()
    {
        return $this->roles;
    }

    public function setRoles($roles): self
    {
        $this->roles = $roles;

        return $this;
    }

    public function isActive(): ?bool
    {
        return $this->active;
    }

    public function setActive(bool $active): self
    {
        $this->active = $active;

        return $this;
    }

    public function isAnonymous(): ?bool
    {
        return $this->anonymous;
    }

    public function setAnonymous(bool $anonymous): self
    {
        $this->anonymous = $anonymous;
        $this->eraseCredentials();

        return $this;
    }

    public function getConfirmationToken(): ?string
    {
        return $this->confirmationToken;
    }

    public function setConfirmationToken(?string $confirmationToken): self
    {
        $this->confirmationToken = $confirmationToken;

        return $this;
    }

    public function getPasswordRequestedAt(): ?\DateTimeInterface
    {
        return $this->passwordRequestedAt;
    }

    public function setPasswordRequestedAt(\DateTimeInterface $passwordRequestedAt = null): self
    {
        $this->passwordRequestedAt = $passwordRequestedAt;

        return $this;
    }

    public function getCreatedAt(): ?\DateTimeInterface
    {
        return $this->createdAt;
    }

    public function setCreatedAt(\DateTimeInterface $createdAt): self
    {
        $this->createdAt = $createdAt;

        return $this;
    }

    public function getUpdatedAt(): ?\DateTimeInterface
    {
        return $this->updatedAt;
    }

    public function setUpdatedAt(\DateTimeInterface $updatedAt): self
    {
        $this->updatedAt = $updatedAt;

        return $this;
    }

    /** @see \Serializable::serialize() */
    public function serialize()
    {
        return serialize(array(
          $this->id,
          $this->firstname,
          $this->lastname,
          $this->username,
          $this->password,
          $this->email,
          $this->publicMail,
          $this->active,
          $this->anonymous,
          $this->createdAt,
          $this->updatedAt,
      ));
    }

    /** @see \Serializable::unserialize() */
    public function unserialize($serialized)
    {
        list(
          $this->id,
          $this->firstname,
          $this->lastname,
          $this->username,
          $this->password,
          $this->email,
          $this->publicMail,
          $this->active,
          $this->anonymous,
          $this->createdAt,
          $this->updatedAt,
      ) = unserialize($serialized, ['allowed_classes' => false]);
    }

    public function isEnabled()
    {
        return $this->active;
    }


    // only to satisfy interface
    public function getSalt()
    {
        return null;
    }

    // only to satisfy interface
    public function eraseCredentials()
    {
        $this->roles = [];
    }

    /**
     * @return Collection|UserProjectStatus[]
     */
    public function getProjectStatus(): Collection
    {
        return $this->projectStatus;
    }

    public function addProjectStatus(UserProjectStatus $projectStatus): self
    {
        if (!$this->projectStatus->contains($projectStatus)) {
            $this->projectStatus[] = $projectStatus;
            $projectStatus->setUser($this);
        }

        return $this;
    }

    public function removeProjectStatus(UserProjectStatus $projectStatus): self
    {
        if ($this->projectStatus->contains($projectStatus)) {
            $this->projectStatus->removeElement($projectStatus);
            // set the owning side to null (unless already changed)
            if ($projectStatus->getUser() === $this) {
                $projectStatus->setUser(null);
            }
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
            $transcriptionLog->setUser($this);
        }

        return $this;
    }

    public function removeTranscriptionLog(TranscriptionLog $transcriptionLog): self
    {
        if ($this->transcriptionLogs->contains($transcriptionLog)) {
            $this->transcriptionLogs->removeElement($transcriptionLog);
            // set the owning side to null (unless already changed)
            if ($transcriptionLog->getUser() === $this) {
                $transcriptionLog->setUser(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection|ReviewRequest[]
     */
    public function getReviewRequests(): Collection
    {
        return $this->reviewRequests;
    }

    public function addReviewRequest(ReviewRequest $reviewRequest): self
    {
        if (!$this->reviewRequests->contains($reviewRequest)) {
            $this->reviewRequests[] = $reviewRequest;
            $reviewRequest->setUser($this);
        }

        return $this;
    }

    public function removeReviewRequest(ReviewRequest $reviewRequest): self
    {
        if ($this->reviewRequests->contains($reviewRequest)) {
            $this->reviewRequests->removeElement($reviewRequest);
            // set the owning side to null (unless already changed)
            if ($reviewRequest->getUser() === $this) {
                $reviewRequest->setUser(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection|Review[]
     */
    public function getReviews(): Collection
    {
        return $this->reviews;
    }

    public function addReview(Review $review): self
    {
        if (!$this->reviews->contains($review)) {
            $this->reviews[] = $review;
            $review->setUser($this);
        }

        return $this;
    }

    public function removeReview(Review $review): self
    {
        if ($this->reviews->contains($review)) {
            $this->reviews->removeElement($review);
            // set the owning side to null (unless already changed)
            if ($review->getUser() === $this) {
                $review->setUser(null);
            }
        }

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
            $comment->setUser($this);
        }

        return $this;
    }

    public function removeComment(Comment $comment): self
    {
        if ($this->comments->contains($comment)) {
            $this->comments->removeElement($comment);
            // set the owning side to null (unless already changed)
            if ($comment->getUser() === $this) {
                $comment->setUser(null);
            }
        }

        return $this;
    }

    public function getFirstTranscript(): ?bool
    {
        return $this->firstTranscript;
    }

    public function setFirstTranscript(bool $firstTranscript): self
    {
        $this->firstTranscript = $firstTranscript;

        return $this;
    }

    /**
     * @return Collection|Transcription[]
     */
    public function getSubscribedTranscriptions(): Collection
    {
        return $this->subscribedTranscriptions;
    }

    public function addSubscribedTranscription(Transcription $subscribedTranscription): self
    {
        if (!$this->subscribedTranscriptions->contains($subscribedTranscription)) {
            $this->subscribedTranscriptions[] = $subscribedTranscription;
            $subscribedTranscription->addSubscribersUser($this);
        }

        return $this;
    }

    public function removeSubscribedTranscription(Transcription $subscribedTranscription): self
    {
        if ($this->subscribedTranscriptions->contains($subscribedTranscription)) {
            $this->subscribedTranscriptions->removeElement($subscribedTranscription);
            $subscribedTranscription->removeSubscribersUser($this);
        }

        return $this;
    }

    public function getLastAccess(): ?\DateTimeInterface
    {
        return $this->lastAccess;
    }

    public function setLastAccess(\DateTimeInterface $lastAccess): self
    {
        $this->lastAccess = $lastAccess;

        return $this;
    }
}
