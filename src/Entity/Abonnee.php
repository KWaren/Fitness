<?php

namespace App\Entity;

use App\Repository\AbonneeRepository;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Serializer\Annotation\Groups;
use Symfony\Component\Validator\Constraints as Assert;

#[ORM\Entity(repositoryClass: AbonneeRepository::class)]
class Abonnee
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: 'integer')]
    #[Groups(["getAbonnees", "getAgents"])]
    private $id;

    #[ORM\Column(type: 'string', length: 255)]
    #[Groups(["getAbonnees", "getAgents"])]
    private $Name;

    #[ORM\Column(type: 'string', length: 255)]
    #[Groups(["getAbonnees", "getAgents"])]
    private $surname;

    #[ORM\Column(type: 'integer')]
    #[Groups(["getAbonnees", "getAgents"])]
    private $Num;

    #[ORM\Column(type: 'string', length: 255)]
    #[Groups(["getAbonnees", "getAgents"])]
    private $email;

    #[ORM\ManyToOne(targetEntity: Agent::class, inversedBy: 'abonnees')]
    #[ORM\JoinColumn(nullable: false)]
    #[Groups(["getAbonnees"])]
    private $Agent;

    public function getId(): ?int
    {
        return $this->id;
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
        return $this->surname;
    }

    public function setSurname(string $surname): self
    {
        $this->surname = $surname;

        return $this;
    }

    public function getNum(): ?int
    {
        return $this->Num;
    }

    public function setNum(int $Num): self
    {
        $this->Num = $Num;

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

    public function getAgent(): ?Agent
    {
        return $this->Agent;
    }

    public function setAgent(?Agent $Agent): self
    {
        $this->Agent = $Agent;

        return $this;
    }
}
