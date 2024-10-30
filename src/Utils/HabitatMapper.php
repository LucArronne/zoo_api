<?php

namespace App\Utils;

use App\Dto\HabitatDto;
use App\Entity\Habitat;

class HabitatMapper
{
    private ImageMapper $imageMapper;
    private AnimalMapper $animalMapper;

    public function __construct(ImageMapper $imageMapper, AnimalMapper $animalMapper)
    {
        $this->imageMapper = $imageMapper;
        $this->animalMapper = $animalMapper;
    }

    public function convertToDto(Habitat $habitat): HabitatDto
    {
        return new HabitatDto(
            $habitat->getId(),
            $habitat->getName(),
            $habitat->getDescription(),
            $this->animalMapper->convertToDtoArray($habitat->getAnimals()->toArray()),
            $this->imageMapper->convertToUrlArray($habitat->getImages()->toArray())
        );
    }
    public function convertToDtoArray(array $habitats): array
    {
        return array_map(function (Habitat $habitat) {
            return $this->convertToDto($habitat);
        }, $habitats);
    }
}
