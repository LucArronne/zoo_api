<?php

namespace  App\Dto;

class HabitatDto
{
    private int $id;
    private string  $name;
    private string $description;
    private array $animals;
    private array $images;

    public function __construct(int $id, string $name, string $description, array $animals, array $images)
    {
        $this->id = $id;
        $this->name = $name;
        $this->description = $description;
        $this->animals = $animals;
        $this->images = $images;
    }

    public function getId(): int
    {
        return $this->id;
    }

    public function getName(): string
    {
        return $this->name;
    }
    public function getDescription(): string
    {
        return $this->description;
    }
    public function getAnimals(): array
    {
        return $this->animals;
    }
    public function getImages(): array
    {
        return $this->images;
    }
}
