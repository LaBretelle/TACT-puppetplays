<?php


namespace App\Entity;

use App\Entity\Project\RegisteredUser;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Security\Core\User\AdvancedUserInterface;

/**
 * @ORM\Table(name="app_user")
 * @ORM\Entity(repositoryClass="App\Repository\UserRepository")
 */
class User implements AdvancedUserInterface, \Serializable
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
     * @ORM\Column(type="string", length=254, unique=true)
     */
    private $email;

    /**
     * @ORM\Column(name="is_active", type="boolean")
     */
    private $isActive;

    /** @ORM\Column(type="json")
     *
     */
    private $roles = [];

    /**
     * @ORM\OneToMany(targetEntity="App\Entity\Project\RegisteredUser", mappedBy="user")
     */
    private $registeredProjects;

    /**
     * @ORM\OneToMany(targetEntity="App\Entity\Transcription", mappedBy="user")
     */
    private $transcriptions;

    public function __construct()
    {
        $this->isActive = true;
        $this->registeredProjects = new ArrayCollection();
        $this->transcriptions = new ArrayCollection();
    }

    public function getUsername()
    {
        return $this->username;
    }

    public function setUsername(string $username)
    {
        $this->username = $username;
    }

    public function getEmail()
    {
        return $this->email;
    }

    public function setEmail(string $email)
    {
        $this->email = $email;
    }

    public function setPassword(string $password)
    {
        $this->password = $password;
    }

    public function getPassword()
    {
        return $this->password;
    }

    public function getPlainPassword()
    {
        return $this->plainPassword;
    }

    public function setPlainPassword(string $plainPassword)
    {
        $this->plainPassword = $plainPassword;
    }


    public function getRoles(): array
    {
        //return array_unique(array_merge(['ROLE_USER'], $this->roles));
        return array_unique($this->roles);
    }

    public function setRoles(array $roles)
    {
        $this->roles = $roles;
    }

    public function resetRoles()
    {
        $this->roles = [];
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

    /** @see \Serializable::serialize() */
    public function serialize()
    {
        return serialize(array(
          $this->id,
          $this->username,
          $this->password,
          $this->email,
          $this->isActive,
      ));
    }

    /** @see \Serializable::unserialize() */
    public function unserialize($serialized)
    {
        list(
          $this->id,
          $this->username,
          $this->password,
          $this->email,
          $this->isActive,
      ) = unserialize($serialized, ['allowed_classes' => false]);
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

    public function isEnabled()
    {
        return $this->isActive;
    }

    /**
     * @return Collection|RegisteredUser[]
     */
    public function getRegisteredProjects(): Collection
    {
        return $this->registeredProjects;
    }

    public function addRegisteredProject(RegisteredUser $registeredProject): self
    {
        if (!$this->registeredProjects->contains($registeredProject)) {
            $this->registeredProjects[] = $registeredProject;
            $registeredProject->setUser($this);
        }

        return $this;
    }

    public function removeRegisteredProject(RegisteredUser $registeredProject): self
    {
        if ($this->registeredProjects->contains($registeredProject)) {
            $this->registeredProjects->removeElement($registeredProject);
            // set the owning side to null (unless already changed)
            if ($registeredProject->getUser() === $this) {
                $registeredProject->setUser(null);
            }
        }

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
}
