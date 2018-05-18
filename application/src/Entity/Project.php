<?php

namespace App\Entity;

use App\Entity\Financer;
use App\Entity\UserProjectStatus;
use App\Entity\ProjectStatus;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;

/**
 * @ORM\Table(name="app_project")
 * @ORM\Entity
 * @UniqueEntity("name")
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
     * @ORM\Column(type="string", length=255, unique=true)
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
     * @ORM\ManyToOne(targetEntity="App\Entity\ProjectStatus", inversedBy="projects")
     */
    private $status;

    /**
     * @ORM\ManyToMany(targetEntity="App\Entity\Financer", inversedBy="projects")
     */
    private $financers;

    /**
     * @ORM\OneToMany(targetEntity="App\Entity\UserProjectStatus", mappedBy="project")
     */
    private $userStatus;

    /**
     * @ORM\OneToMany(targetEntity="App\Entity\Media", mappedBy="project")
     */
    private $medias;

    public function __construct()
    {
        $this->financers = new ArrayCollection();
        $this->userStatus = new ArrayCollection();
        $this->medias = new ArrayCollection();
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

    public function getStatus(): ?ProjectStatus
    {
        return $this->status;
    }

    public function setStatus(?ProjectStatus $status): self
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
     * @return Collection|UserProjectStatus[]
     */
    public function getUserStatus(): Collection
    {
        return $this->userStatus;
    }

    public function addUserStatus(UserProjectStatus $userStatus): self
    {
        if (!$this->userStatus->contains($userStatus)) {
            $this->userStatus[] = $userStatus;
            $userStatus->setProject($this);
        }

        return $this;
    }

    public function removeUserStatus(UserProjectStatus $userStatus): self
    {
        if ($this->userStatus->contains($userStatus)) {
            $this->userStatus->removeElement($userStatus);
            // set the owning side to null (unless already changed)
            if ($userStatus->getProject() === $this) {
                $userStatus->setProject(null);
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
