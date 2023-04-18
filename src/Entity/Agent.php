<?php

namespace App\Entity;

use App\Repository\AgentRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Security\Core\User\PasswordAuthenticatedUserInterface;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Serializer\Annotation\Groups;
use Symfony\Component\Validator\Constraints as Assert;

#[ORM\Entity(repositoryClass: AgentRepository::class)]
class Agent implements UserInterface, PasswordAuthenticatedUserInterface
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: 'integer')]
    #[Groups(["getAgents"])]
    private $id;

    #[ORM\Column(type: 'string', length: 180, unique: true)]
    #[Groups(["getAgents"])]
    private $email;

    #[ORM\Column(type: 'json')]
    #[Groups(["getAgents"])]
    private $roles = [];

    #[ORM\Column(type: 'string')]
    #[Groups(["getAgents"])]
    private $password;

    #[ORM\Column(type: 'string', length: 255)]
    #[Groups(["getAgents"])]
    private $Name;

    #[ORM\Column(type: 'string', length: 255)]
    #[Groups(["getAgents"])]
    private $Surname;

    #[ORM\OneToMany(mappedBy: 'Agent', targetEntity: Abonnee::class)]
    #[Groups(["getAgents"])]
    private $abonnees;

    public function __construct()
    {
        $this->abonnees = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
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

    /**
     * A visual identifier that represents this user.
     *
     * @see UserInterface
     */
    public function getUserIdentifier(): string
    {
        return (string) $this->email;
    }

    /**
     * @see UserInterface
     */
    public function getRoles(): array
    {
        $roles = $this->roles;
        // guarantee every user at least has ROLE_USER
        $roles[] = 'ROLE_USER';

        return array_unique($roles);
    }

    public function setRoles(array $roles): self
    {
        $this->roles = $roles;

        return $this;
    }

    /**
     * @see PasswordAuthenticatedUserInterface
     */
    public function getPassword(): string
    {
        return $this->password;
    }

    public function setPassword(string $password): self
    {
        $this->password = $password;

        return $this;
    }

    /**
     * @see UserInterface
     */
    public function eraseCredentials()
    {
        // If you store any temporary, sensitive data on the user, clear it here
        // $this->plainPassword = null;
    }

    public function getName(): ?string
    {
        return $this->Name;
    }

    public function setName(string $Name): self
    {
        $this->Name = $Name;

        return $this;
    }

    public function getSurname(): ?string
    {
        return $this->Surname;
    }

    public function setSurname(string $Surname): self
    {
        $this->Surname = $Surname;

        return $this;
    }
    public function getUsername(): string {
        return $this->getUserIdentifier();
    }

    /**
     * @return Collection<int, Abonnee>
     */
    public function getAbonnees(): Collection
    {
        return $this->abonnees;
    }

    public function addAbonnee(Abonnee $abonnee): self
    {
        if (!$this->abonnees->contains($abonnee)) {
            $this->abonnees[] = $abonnee;
            $abonnee->setAgent($this);
        }

        return $this;
    }

    public function removeAbonnee(Abonnee $abonnee): self
    {
        if ($this->abonnees->removeElement($abonnee)) {
            // set the owning side to null (unless already changed)
            if ($abonnee->getAgent() === $this) {
                $abonnee->setAgent(null);
            }
        }

        return $this;
    }
}
