<?php

namespace App\Entity;

use App\Entity\Project\Financer;
use App\Entity\Project\RegisteredUser;
use App\Entity\Project\Status;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Table(name="app_project")
 * @ORM\Entity()
 */
class Project
{
    /**
     * @ORM\Column(type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    private $id;

    /**
     * @ORM\Column(type="string", length=25, unique=true)
     */
    private $name;

    /**
     * @ORM\Column(type="text")
     */
    private $description;

    /**
     * @ORM\Column(type="text")
     */
    private $shortDescription;

    /**
     * @ORM\Column(type="datetime")
     */
    private $createdAt;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private $url;

    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\Project\Status", inversedBy="projects")
     */
    private $status;

    /**
     * @ORM\ManyToMany(targetEntity="App\Entity\Project\Financer", inversedBy="projects")
     */
    private $financers;

    /**
     * @ORM\OneToMany(targetEntity="App\Entity\Project\RegisteredUser", mappedBy="project")
     */
    private $registeredUsers;

    /**
     * @ORM\OneToMany(targetEntity="App\Entity\Media", mappedBy="project")
     */
    private $medias;

    public function __construct()
    {
        $this->financers = new ArrayCollection();
        $this->registeredUsers = new ArrayCollection();
        $this->medias = new ArrayCollection();
    }

    public function getDescription(): ?string
    {
        return $this->description;
    }

    public function setDescription(string $description): self
    {
        $this->description = $description;

        return $this;
    }

    public function getShortDescription(): ?string
    {
        return $this->shortDescription;
    }

    public function setShortDescription(string $shortDescription): self
    {
        $this->shortDescription = $shortDescription;

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

    public function getUrl(): ?string
    {
        return $this->url;
    }

    public function setUrl(?string $url): self
    {
        $this->url = $url;

        return $this;
    }

    public function getId(): ?int
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

    public function getStatus(): ?Status
    {
        return $this->status;
    }

    public function setStatus(?Status $status): self
    {
        $this->status = $status;

        return $this;
    }

    /**
     * @return Collection|Financer[]
     */
    public function getFinancers(): Collection
    {
        return $this->financers;
    }

    public function addFinancer(Financer $financer): self
    {
        if (!$this->financers->contains($financer)) {
            $this->financers[] = $financer;
        }

        return $this;
    }

    public function removeFinancer(Financer $financer): self
    {
        if ($this->financers->contains($financer)) {
            $this->financers->removeElement($financer);
        }

        return $this;
    }

    /**
     * @return Collection|RegisteredUser[]
     */
    public function getRegisteredUsers(): Collection
    {
        return $this->registeredUsers;
    }

    public function addRegisteredUser(RegisteredUser $registeredUser): self
    {
        if (!$this->registeredUsers->contains($registeredUser)) {
            $this->registeredUsers[] = $registeredUser;
            $registeredUser->setProject($this);
        }

        return $this;
    }

    public function removeRegisteredUser(RegisteredUser $registeredUser): self
    {
        if ($this->registeredUsers->contains($registeredUser)) {
            $this->registeredUsers->removeElement($registeredUser);
            // set the owning side to null (unless already changed)
            if ($registeredUser->getProject() === $this) {
                $registeredUser->setProject(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection|Media[]
     */
    public function getMedias(): Collection
    {
        return $this->medias;
    }

    public function addMedia(Media $media): self
    {
        if (!$this->medias->contains($media)) {
            $this->medias[] = $media;
            $media->setProject($this);
        }

        return $this;
    }

    public function removeMedia(Media $media): self
    {
        if ($this->medias->contains($media)) {
            $this->medias->removeElement($media);
            // set the owning side to null (unless already changed)
            if ($media->getProject() === $this) {
                $media->setProject(null);
            }
        }

        return $this;
    }
}
