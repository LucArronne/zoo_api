<?php

namespace App\Utils;

use App\Dto\AnimalDto;
use App\Entity\Animal;

class AnimalMapper
{
    private ImageMapper $imageMapper;

    public function __construct(ImageMapper $imageMapper)
    {
        $this->imageMapper = $imageMapper;
    }

    public function convertToDto(Animal $animal): AnimalDto
    {
        return new AnimalDto(
            $animal->getId(),
            $animal->getName(),
            $animal->getRace(),
            $this->imageMapper->convertToUrlArray($animal->getImages()->toArray()),
        );
    }
    public function convertToDtoArray(array $animals): array
    {
        return array_map(function (Animal $animal) {
            return $this->convertToDto($animal);
        }, $animals);
    }
}
