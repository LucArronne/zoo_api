<?php

namespace App\Controller;

use App\Entity\Animal;
use App\Entity\AnimalFood;
use App\Repository\AnimalFoodRepository;
use App\Repository\AnimalRepository;
use Doctrine\ORM\EntityManagerInterface;
use Nelmio\ApiDocBundle\Annotation\Model;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;
use Symfony\Component\Serializer\SerializerInterface;
use Symfony\Component\Validator\Exception\ValidationFailedException;
use Symfony\Component\Validator\Validator\ValidatorInterface;
use OpenApi\Attributes as OA;


#[IsGranted('ROLE_EMPLOYEE', message: 'Access denied')]
#[OA\Tag(name: 'Employee')]
class AnimalFoodController extends AbstractController
{
    #[Route('/foods/animal/{animalId}', name: 'addAnimalFood', methods: ['POST'])]
    #[OA\Post(
        summary: 'Add an animal food consommation',
        description: 'Use to add a food consommation for a specific animal',
        parameters: [
            new OA\Parameter(
                name: "animalId",
                in: "path",
                required: true,
                description: "The Id of the animal",
                schema: new OA\Schema(type: "integer")
            )
        ],
        requestBody: new OA\RequestBody(
            required: true,
            description: 'Food data in json format',
            content: new OA\JsonContent(
                type: 'object',
                properties: [
                    new OA\Property(
                        property: "name",
                        type: "string",
                        example: "Food 1"
                    ),
                    new OA\Property(
                        property: "quantity",
                        type: "number",
                        format: "float",
                        example: 250,
                    ),
                    new OA\Property(
                        property: "date",
                        type: "string",
                        format: "date-time",
                    ),
                ],
            ),
        ),
        responses: [
            new OA\Response(
                response: Response::HTTP_CREATED,
                description: "Food added successfully.",
                content: new OA\JsonContent(ref: new Model(type: AnimalFood::class, groups: ["getFoods"])),
            ),
            new OA\Response(
                response: Response::HTTP_BAD_REQUEST,
                description: "Invalid input data or validation error.",
            ),
            new OA\Response(
                response: Response::HTTP_NOT_FOUND,
                description: "Animal not found",
            )
        ]

    )]
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
    #[OA\Get(
        summary: 'Get the foods of an animal',
        parameters: [
            new OA\Parameter(
                name: "animalId",
                in: "path",
                required: true,
                description: "The Id of the animal",
                schema: new OA\Schema(type: "integer")
            )
        ],
        responses: [
            new OA\Response(
                response: Response::HTTP_OK,
                description: 'Returns the list of animal\'s foods',
                content: new OA\JsonContent(
                    type: 'array',
                    items: new OA\Items(ref: new Model(type: AnimalFood::class, groups: ['getFoods']))
                )
            ),
            new OA\Response(
                response: Response::HTTP_NOT_FOUND,
                description: "Animal not found",
            ),
        ]
    )]
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
