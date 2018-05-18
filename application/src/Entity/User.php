<?php


namespace App\Entity;

use App\Entity\UserProjectStatus;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;
use Symfony\Component\Security\Core\User\AdvancedUserInterface;

/**
 * @ORM\Table(name="app_user")
 * @ORM\Entity(repositoryClass="App\Repository\UserRepository")
 * @UniqueEntity("email")
 * @UniqueEntity("username")
 */
class User implements AdvancedUserInterface, \Serializable
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
     * @ORM\OneToMany(targetEntity="App\Entity\Transcription", mappedBy="user")
     */
    private $transcriptions;

    /**
     * @ORM\Column(type="json_array")
     */
    private $roles;

    /**
     * @ORM\Column(type="boolean")
     */
    private $active;

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

    public function __construct()
    {
        $this->active = false;
        $this->publicMail = true;
        $this->projectStatus = new ArrayCollection();
        $this->transcriptions = new ArrayCollection();
        $this->projectStatus = new ArrayCollection();
        $this->createdAt = new \DateTime();
        $this->updatedAt = new \DateTime();
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

    public function getActive(): ?bool
    {
        return $this->active;
    }

    public function setActive(bool $active): self
    {
        $this->active = $active;

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

    public function setPasswordRequestedAt(\DateTimeInterface $passwordRequestedAt): self
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
          $this->createdAt,
          $this->updatedAt,
      ) = unserialize($serialized, ['allowed_classes' => false]);
    }

    public function isEnabled()
    {
        return $this->active;
    }

    public function isAccountNonExpired()
    {
        return true;
    }

    public function isAccountNonLocked()
    {
        return true;
    }

    public function isCredentialsNonExpired()
    {
        return true;
    }

    // only to satisfy interface
    public function getSalt()
    {
        return null;
    }

    // only to satisfy interface
    public function eraseCredentials()
    {
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
            $transcription->setUser($this);
        }

        return $this;
    }

    public function removeTranscription(Transcription $transcription): self
    {
        if ($this->transcriptions->contains($transcription)) {
            $this->transcriptions->removeElement($transcription);
            // set the owning side to null (unless already changed)
            if ($transcription->getUser() === $this) {
                $transcription->setUser(null);
            }
        }

        return $this;
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
}
