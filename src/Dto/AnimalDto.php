<?php

namespace App\Dto;

use App\Entity\Image;
use App\Entity\Race;
use Nelmio\ApiDocBundle\Annotation\Model;
use OpenApi\Attributes as OA;

class AnimalDto
{
    private int $id;
    private string $name;
    private Race $race;

    #[OA\Property(
        type: "array",
        items: new OA\Items(ref: new Model(type: Image::class)),
    )]
    private array $images;

    public function __construct(int $id, string $name, Race $race, array $images)
    {
        $this->id = $id;
        $this->name = $name;
        $this->race = $race;
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

    public function getRace(): Race
    {
        return $this->race;
    }
    public function getImages(): array
    {
        return $this->images;
    }
}
