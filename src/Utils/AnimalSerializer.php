<?php

namespace App\Utils;

use App\Dto\AnimalDto;
use App\Entity\Animal;

class AnimalSerializer
{
    private ImageToUrlSerializer $imageToUrlSerializer;

    public function __construct(ImageToUrlSerializer $imageToUrlSerializer)
    {
        $this->imageToUrlSerializer = $imageToUrlSerializer;
    }

    public function serialize(Animal $animal): AnimalDto
    {
        return new AnimalDto(
            $animal->getId(),
            $animal->getName(),
            $animal->getRace(),
            $this->imageToUrlSerializer->serializeArray($animal->getImages()->toArray()),
        );
    }
    public function serializeArray(array $animals): array
    {
        return array_map(function (Animal $animal) {
            return $this->serialize($animal);
        }, $animals);
    }
}
