<?php

namespace App\Document;

use Doctrine\ODM\MongoDB\Mapping\Annotations as MongoDB;
use Symfony\Component\Serializer\Annotation\Groups;

#[MongoDB\Document(collection: "animal_visits")]
#[MongoDB\Index(keys: ['animalId' => 'asc'], options: ['unique' => true])]
class AnimalVisit
{
    #[MongoDB\Id]
    private ?string $id = null;

    #[MongoDB\Field(type: 'int')]
    #[Groups(['getVisitors'])]
    private ?int $animalId = null;

    #[MongoDB\Field(type: 'string')]
    #[Groups(['getVisitors'])]
    private ?string $animalName = null;

    #[MongoDB\Field(type: 'int')]
    #[Groups(['getVisitors'])]
    private int $visitCount = 0;

    public function getId(): ?string
    {
        return $this->id;
    }

    public function getAnimalId(): ?int
    {
        return $this->animalId;
    }

    public function setAnimalId(int $animalId): self
    {
        $this->animalId = $animalId;

        return $this;
    }

    public function getAnimalName(): ?string
    {
        return $this->animalName;
    }

    public function setAnimalName(string $animalName): self
    {
        $this->animalName = $animalName;

        return $this;
    }

    public function getVisitCount(): int
    {
        return $this->visitCount;
    }

    public function setVisitCount(int $visitCount): self
    {
        $this->visitCount = $visitCount;

        return $this;
    }

    public function incrementVisitCount(): self
    {
        $this->visitCount++;

        return $this;
    }
}
