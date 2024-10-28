<?php

namespace  App\Dto;

use App\Entity\Image;
use Nelmio\ApiDocBundle\Annotation\Model;
use OpenApi\Attributes as OA;

class HabitatDto
{
    private int $id;
    private string  $name;
    private string $description;

    #[OA\Property(
        type: "array",
        items: new OA\Items(ref: new Model(type: AnimalDto::class)),
    )]
    private array $animals;

    #[OA\Property(
        type: "array",
        items: new OA\Items(ref: new Model(type: Image::class)),
    )]
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
