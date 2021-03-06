<?php

namespace App\Entity;

use App\Entity\Project;
use App\Entity\User;
use App\Entity\UserStatus;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Table(name="app_project_user_status")
 * @ORM\Entity(repositoryClass="App\Repository\UserProjectStatusRepository")
 */
class UserProjectStatus
{
    /**
     * @ORM\Id()
     * @ORM\GeneratedValue()
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\User", inversedBy="projectStatus")
     * @ORM\JoinColumn(nullable=false)
     */
    private $user;

    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\Project", inversedBy="userStatuses")
     * @ORM\JoinColumn(nullable=false)
     */
    private $project;

    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\UserStatus")
     * @ORM\JoinColumn(nullable=false)
     */
    private $status;

    /**
     * @ORM\Column(type="boolean")
     */
    private $enabled = 1;

    /**
     * @ORM\Column(type="boolean", nullable=true)
     */
    private $subscribe;

    public function getId(): ?int
    {
        return $this->id;
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

    public function getProject(): ?Project
    {
        return $this->project;
    }

    public function setProject(?Project $project): self
    {
        $this->project = $project;

        return $this;
    }

    public function getStatus(): ?UserStatus
    {
        return $this->status;
    }

    public function setStatus(?UserStatus $status): self
    {
        $this->status = $status;

        return $this;
    }

    public function getEnabled(): ?bool
    {
        return $this->enabled;
    }

    public function setEnabled(bool $enabled): self
    {
        $this->enabled = $enabled;

        return $this;
    }

    public function getSubscribe(): ?bool
    {
        return $this->subscribe;
    }

    public function setSubscribe(?bool $subscribe): self
    {
        $this->subscribe = $subscribe;

        return $this;
    }
}
