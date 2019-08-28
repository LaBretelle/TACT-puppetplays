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
 * @ORM\Entity(repositoryClass="App\Repository\ProjectRepository")
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
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private $catchPhrase;

    /**
     * @ORM\Column(type="text", nullable=true)
     */
    private $description;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private $image;

    /**
     * @ORM\Column(type="datetime")
     */
    private $createdAt;

    /**
     * @ORM\Column(type="datetime", nullable=true)
     */
    private $updatedAt;

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
     * @ORM\OneToMany(targetEntity="App\Entity\UserProjectStatus", mappedBy="project", cascade={"persist", "remove"})
     */
    private $userStatuses;

    /**
     * @ORM\OneToMany(targetEntity="App\Entity\Media", mappedBy="project", cascade={"persist", "remove"})
     * @ORM\OrderBy({"name" = "ASC"})
     */
    private $medias;

    /**
     * @ORM\OneToMany(targetEntity="App\Entity\Directory", mappedBy="project", cascade={"persist", "remove"})
     * @ORM\OrderBy({"name" = "ASC"})
     */
    private $dirs;

    /**
     * @ORM\Column(type="boolean")
     */
    private $public = 1;

    /**
     * @ORM\Column(type="integer")
     */
    private $nbValidation;

    /**
     * @ORM\Column(type="text", nullable=true)
     */
    private $css;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private $projectHelpLink;



    /**
     * @ORM\Column(type="boolean")
     */
    private $archived = false;

    public function __construct()
    {
        $this->financers = new ArrayCollection();
        $this->userStatuses = new ArrayCollection();
        $this->medias = new ArrayCollection();
        $this->dirs = new ArrayCollection();
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
    public function getUserStatuses(): Collection
    {
        return $this->userStatuses;
    }

    public function addUserStatus(UserProjectStatus $userStatus): self
    {
        if (!$this->userStatuses->contains($userStatus)) {
            $this->userStatuses[] = $userStatus;
            $userStatus->setProject($this);
        }

        return $this;
    }

    public function removeUserStatus(UserProjectStatus $userStatus): self
    {
        if ($this->userStatuses->contains($userStatus)) {
            $this->userStatuses->removeElement($userStatus);
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


    /**
     * @return Collection|Directory[]
     */
    public function getDirs(): Collection
    {
        return $this->dirs;
    }

    public function addDir(Directory $directory): self
    {
        if (!$this->dirs->contains($directory)) {
            $this->dirs[] = $directory;
            $directory->setProject($this);
        }

        return $this;
    }

    public function removeDir(Directory $directory): self
    {
        if ($this->dirs->contains($directory)) {
            $this->dirs->removeElement($directory);
            // set the owning side to null (unless already changed)
            if ($directory->getProject() === $this) {
                $directory->setProject(null);
            }
        }

        return $this;
    }

    public function getUpdatedAt(): ?\DateTimeInterface
    {
        return $this->updatedAt;
    }

    public function setUpdatedAt(?\DateTimeInterface $updatedAt): self
    {
        $this->updatedAt = $updatedAt;

        return $this;
    }

    public function getPublic(): ?bool
    {
        return $this->public;
    }

    public function setPublic(bool $public): self
    {
        $this->public = $public;

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

    public function getNbValidation(): ?int
    {
        return $this->nbValidation;
    }

    public function setNbValidation(int $nbValidation): self
    {
        $this->nbValidation = $nbValidation;

        return $this;
    }

    public function getCss(): ?string
    {
        return $this->css;
    }

    public function setCss(?string $css): self
    {
        $this->css = $css;

        return $this;
    }

    public function getProjectHelpLink(): ?string
    {
        return $this->projectHelpLink;
    }

    public function setProjectHelpLink(?string $projectHelpLink): self
    {
        $this->projectHelpLink = $projectHelpLink;

        return $this;
    }

    public function getCatchPhrase(): ?string
    {
        return $this->catchPhrase;
    }

    public function setCatchPhrase(?string $catchPhrase): self
    {
        $this->catchPhrase = $catchPhrase;

        return $this;
    }

    public function getArchived(): ?bool
    {
        return $this->archived;
    }

    public function setArchived(bool $archived): self
    {
        $this->archived = $archived;

        return $this;
    }
}
