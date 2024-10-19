<?php

namespace App\Entity;

use App\Repository\AnimalRapportRepository;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: AnimalRapportRepository::class)]
class AnimalRapport
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(type: Types::TEXT)]
    private ?string $state = null;

    #[ORM\Column(length: 255)]
    private ?string $food = null;

    #[ORM\Column]
    private ?float $quantity = null;

    #[ORM\Column(type: Types::DATE_IMMUTABLE)]
    private ?\DateTimeImmutable $date = null;

    #[ORM\Column(type: Types::TEXT, nullable: true)]
    private ?string $details = null;

    #[ORM\ManyToOne]
    #[ORM\JoinColumn(nullable: false)]
    private ?User $user = null;

    #[ORM\ManyToOne]
    #[ORM\JoinColumn(nullable: false)]
    private ?Animal $animal = null;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getState(): ?string
    {
        return $this->state;
    }

    public function setState(string $state): static
    {
        $this->state = $state;

        return $this;
    }

    public function getFood(): ?string
    {
        return $this->food;
    }

    public function setFood(string $food): static
    {
        $this->food = $food;

        return $this;
    }

    public function getQuantity(): ?float
    {
        return $this->quantity;
    }

    public function setQuantity(float $quantity): static
    {
        $this->quantity = $quantity;

        return $this;
    }

    public function getDate(): ?\DateTimeImmutable
    {
        return $this->date;
    }

    public function setDate(\DateTimeImmutable $date): static
    {
        $this->date = $date;

        return $this;
    }

    public function getDetails(): ?string
    {
        return $this->details;
    }

    public function setDetails(?string $details): static
    {
        $this->details = $details;

        return $this;
    }

    public function getUser(): ?User
    {
        return $this->user;
    }

    public function setUser(?User $user): static
    {
        $this->user = $user;

        return $this;
    }

    public function getAnimal(): ?Animal
    {
        return $this->animal;
    }

    public function setAnimal(?Animal $animal): static
    {
        $this->animal = $animal;

        return $this;
    }
}
