<?php

namespace App\Controller;

use App\Entity\AnimalRapport;
use App\Repository\AnimalRapportRepository;
use App\Repository\AnimalRepository;
use DateTime;
use Doctrine\ORM\EntityManagerInterface;
use Exception;
use Nelmio\ApiDocBundle\Annotation\Model;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Attribute\MapQueryParameter;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;
use Symfony\Component\Serializer\SerializerInterface;
use Symfony\Component\Validator\Exception\ValidationFailedException;
use Symfony\Component\Validator\Validator\ValidatorInterface;
use OpenApi\Attributes as OA;

#[IsGranted('ROLE_VETERNARY', message: 'Access denied')]
#[OA\Tag(name: 'Veternary')]
class RapportController extends AbstractController
{
    #[Route('/rapports/{animalId}', name: 'createRapport', methods: ['POST'])]
    #[OA\Post(
        summary: 'Add a rapport',
        description: 'Use to add a rapport for a specific animal',
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
            description: 'Rapport data in json format',
            content: new OA\JsonContent(
                type: 'object',
                properties: [
                    new OA\Property(
                        property: "state",
                        type: "string",
                        example: "Animal state"
                    ),
                    new OA\Property(
                        property: "food",
                        type: "string",
                        example: "food suggestion",
                    ),
                    new OA\Property(
                        property: "quantity",
                        type: "number",
                        format: "float",
                        example: 300,
                    ),
                    new OA\Property(
                        property: "date",
                        type: "string",
                        format: "date",
                    ),
                    new OA\Property(
                        property: "details",
                        type: "string",
                        example: "Optional details",
                    ),
                ],
            ),
        ),
        responses: [
            new OA\Response(
                response: Response::HTTP_CREATED,
                description: "Rapport added successfully.",
                content: new OA\JsonContent(ref: new Model(type: AnimalRapport::class, groups: ["getRapports"])),
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
    public function createRapport(
        Request $request,
        int $animalId,
        EntityManagerInterface $em,
        SerializerInterface $serializer,
        AnimalRepository $animalRepository,
        ValidatorInterface $validator,
    ): JsonResponse {

        $rapport = $serializer->deserialize(
            $request->getContent(),
            AnimalRapport::class,
            'json'
        );

        $rapport->setUser($this->getUser());

        $animal = $animalRepository->find($animalId);
        $rapport->setAnimal($animal);

        $violations = $validator->validate($rapport);

        if ($violations->count() > 0) {
            throw new ValidationFailedException($rapport, $violations);
        }

        $em->persist($rapport);
        $em->flush();

        return new JsonResponse(
            $serializer->serialize($rapport, 'json', ['groups' => 'getRapports']),
            Response::HTTP_CREATED,
            [],
            true,
        );
    }

    #[Route('/rapports', name: 'rapports', methods: ['GET'])]
    #[OA\Get(
        summary: 'Get rapports list',
        description: 'Use to get the rapport list. Use animalId or date to filter the list',
        parameters: [
            new OA\Parameter(
                name: "animalId",
                in: "query",
                description: "The Id of the animal (optional)",
                schema: new OA\Schema(type: "integer")
            ),
            new OA\Parameter(
                name: "date",
                in: "query",
                description: "The rapport date (optional)",
                schema: new OA\Schema(type: 'string', format: "date")
            ),
        ],
        responses: [
            new OA\Response(
                response: Response::HTTP_OK,
                description: "Return the list of the rapport",
                content: new OA\JsonContent(
                    type: 'array',
                    items: new OA\Items(ref: new Model(type: AnimalRapport::class, groups: ['getRapports']))
                )
            ),
            new OA\Response(
                response: Response::HTTP_BAD_REQUEST,
                description: "Invalid query error.",
            )
        ]

    )]
    public function getRapports(
        #[MapQueryParameter] ?int $animalId,
        #[MapQueryParameter] ?string $date,
        SerializerInterface $serializer,
        AnimalRapportRepository $animalRapportRepository,
    ): JsonResponse {

        $datetime = null;
        if ($date) {
            try {
                $datetime = new DateTime($date);
            } catch (Exception $e) {
                return new JsonResponse(
                    [
                        "status" => Response::HTTP_BAD_REQUEST,
                        'error' => 'Invalid query',
                        'message' => 'Invalid date format',
                    ],
                    Response::HTTP_BAD_REQUEST,
                    [],
                );
            }
        }
        $result = $animalRapportRepository->findByCriteria(
            $animalId,
            $datetime
        );

        return new JsonResponse(
            $serializer->serialize(
                $result,
                'json',
                ['groups' => 'getRapports']
            ),
            Response::HTTP_OK,
            [],
            true,
        );
    }
}
