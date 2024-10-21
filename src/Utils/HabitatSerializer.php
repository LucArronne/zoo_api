<?php

namespace App\Utils;

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

    public function serialize(Habitat $habitat): array
    {
        return [
            "id" => $habitat->getId(),
            "name" => $habitat->getName(),
            "description" => $habitat->getDescription(),
            "images" => $this->imageToUrlSerializer->serializeArray($habitat->getImages()->toArray()),
            "animals" => $this->animalSerializer->serializeArray($habitat->getAnimals()->toArray()),
        ];
    }
    public function serializeArray(array $habitats): array
    {
        return array_map(function (Habitat $habitat) {
            return $this->serialize($habitat);
        }, $habitats);
    }
}
