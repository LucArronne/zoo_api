<?php

namespace App\Controller;

use App\Entity\Animal;
use App\Entity\AnimalFood;
use App\Repository\AnimalFoodRepository;
use App\Repository\AnimalRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;
use Symfony\Component\Serializer\SerializerInterface;
use Symfony\Component\Validator\Exception\ValidationFailedException;
use Symfony\Component\Validator\Validator\ValidatorInterface;

class AnimalFoodController extends AbstractController
{
    #[Route('/foods/animal/{animalId}', name: 'addAnimalFood', methods: ['POST'])]
    #[IsGranted('ROLE_EMPLOYEE', message: 'Access denied')]
    public function addAnimalFood(
        Request $request,
        int $animalId,
        EntityManagerInterface $em,
        SerializerInterface $serializer,
        AnimalRepository $animalRepository,
        ValidatorInterface $validator,

    ): JsonResponse {
        $animalFood = $serializer->deserialize(
            $request->getContent(),
            AnimalFood::class,
            'json'
        );

        $animal = $animalRepository->find($animalId);
        $animalFood->setAnimal($animal);
        $animalFood->setUser($this->getUser());


        $violations = $validator->validate($animalFood);
        if ($violations->count() > 0) {
            throw new ValidationFailedException($animalFood, $violations);
        }

        $em->persist($animalFood);
        $em->flush();

        return new JsonResponse(
            $serializer->serialize(
                $animalFood,
                'json',
                ['groups' => 'getFoods']

            ),
            Response::HTTP_CREATED,
            [],
            true,
        );
    }

    #[Route('/foods/animal/{animalId}', name: 'getAnimalFoods', methods: ['GET'])]
    #[IsGranted('ROLE_EMPLOYEE', message: 'Access denied')]
    public function getAnimalFoods(
        int $animalId,
        AnimalFoodRepository $animalFoodRepository,
        SerializerInterface $serializer,
    ): JsonResponse {

        $result = $animalFoodRepository->findAnimalFoods($animalId);

        return new JsonResponse(
            $serializer->serialize(
                $result,
                'json',
                ['groups' => 'getFoods']
            ),
            Response::HTTP_OK,
            [],
            true,
        );
    }
}
