<?php

namespace App\Utils;

use App\Dto\HabitatDto;
use App\Entity\Habitat;

class HabitatSerializer
{
    private ImageToUrlSerializer $imageToUrlSerializer;
    private AnimalSerializer $animalSerializer;

    public function __construct(ImageToUrlSerializer $imageToUrlSerializer, AnimalSerializer $animalSerializer)
    {
        $this->imageToUrlSerializer = $imageToUrlSerializer;
        $this->animalSerializer = $animalSerializer;
    }

    public function serialize(Habitat $habitat): HabitatDto
    {
        return new HabitatDto(
            $habitat->getId(),
            $habitat->getName(),
            $habitat->getDescription(),
            $this->animalSerializer->serializeArray($habitat->getAnimals()->toArray()),
            $this->imageToUrlSerializer->serializeArray($habitat->getImages()->toArray())
        );
    }
    public function serializeArray(array $habitats): array
    {
        return array_map(function (Habitat $habitat) {
            return $this->serialize($habitat);
        }, $habitats);
    }
}
