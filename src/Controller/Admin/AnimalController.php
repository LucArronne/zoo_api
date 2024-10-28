<?php

namespace App\Controller\Admin;

use App\Dto\AnimalDto;
use App\Entity\Animal;
use App\Entity\AnimalImage;
use App\Repository\HabitatRepository;
use App\Repository\RaceRepository;
use App\Utils\AnimalSerializer;
use App\Utils\FileUploader;
use Doctrine\ORM\EntityManagerInterface;
use InvalidArgumentException;
use Nelmio\ApiDocBundle\Annotation\Model;
use OpenApi\Attributes as OA;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\File\Exception\FileException;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Serializer\SerializerInterface;
use Symfony\Component\Validator\Exception\ValidationFailedException;
use Symfony\Component\Validator\Validator\ValidatorInterface;

#[Route(path: '/admin')]
#[OA\Tag(name: 'admin')]
class AnimalController extends AbstractController
{
    /**
     * Create a new animal  
     */
    #[Route('/animals', name: 'createAnimal', methods: ['POST'])]
    #[OA\Post(
        summary: 'Create a new animal',
        description: 'Create a new animal record with optional images',
        requestBody: new OA\RequestBody(
            description: 'Animal data object',
            required: true,
            content: new OA\MediaType(
                mediaType: 'multipart/form-data',
                schema: new OA\Schema(
                    type: 'object',
                    properties: [
                        new OA\Property(
                            property: 'data',
                            type: 'string',
                            required: ["name", "race", "habitat"],
                            description: 'Animal data in json format (required)',
                            example: '{"name": "Lion", "race": 1, "habitat": 2}'
                        ),
                        new OA\Property(
                            property: "images",
                            type: "array",
                            items: new OA\Items(type: "string", format: "binary"),
                            description: "Image files for the animal. Allowed formats: jpg, jpeg, png. (optional)"
                        ),
                    ]
                )

            )
        ),
        responses: [
            new OA\Response(
                response: Response::HTTP_CREATED,
                description: "Animal created successfully.",
                content: new OA\JsonContent(ref: new Model(type: AnimalDto::class))
            ),
            new OA\Response(
                response: Response::HTTP_BAD_REQUEST,
                description: "Invalid input data or validation error.",
            )
        ]

    )]
    public function createAnimal(
        Request $request,
        EntityManagerInterface $em,
        HabitatRepository $habitatRepository,
        RaceRepository $raceRepository,
        FileUploader $uploader,
        SerializerInterface $serializer,
        ValidatorInterface $validator,
        AnimalSerializer $animalSerializer,
    ): JsonResponse {

        if (!$request->get('data')) {
            return new JsonResponse(
                $serializer->serialize(
                    [
                        "status" => Response::HTTP_BAD_REQUEST,
                        "message" => "Argument validation failed",
                        'error' => "Add animal data as json object"
                    ],
                    'json'
                ),
                Response::HTTP_BAD_REQUEST,
                [],
                true,
            );
        }
        $data = json_decode($request->get('data'), true);

        $animal = new Animal();
        $animal->setName($data['name']);

        $race = $data['race'] ?? -1;
        $race = $raceRepository->find($race);
        $animal->setRace($race);

        $habitat = $data['habitat'] ?? -1;
        $habitat = $habitatRepository->find($habitat);
        $animal->setHabitat($habitat);

        $violations = $validator->validate($animal);

        if ($violations->count() > 0) {
            throw new ValidationFailedException($animal, $violations);
        }

        if ($request->files->get("images")) {

            $allowedExtensions = ['jpg', 'jpeg', 'png'];

            foreach ($request->files->get("images") as $image) {
                try {
                    $imageFileName = $uploader->upload(
                        $image,
                        $allowedExtensions
                    );
                    $animalImage = new AnimalImage();
                    $animalImage->setPath($imageFileName);
                    $animal->addImage($animalImage);
                } catch (InvalidArgumentException $e) {
                    return new JsonResponse(
                        $serializer->serialize(
                            [
                                "status" => Response::HTTP_BAD_REQUEST,
                                "message" => "File validation failed",
                                'error' => 'Invalid file type, only ' . join(",", $allowedExtensions)
                                    . ' are allowed.'
                            ],
                            'json'
                        ),
                        Response::HTTP_BAD_REQUEST,
                        [],
                        true,
                    );
                } catch (FileException $e) {
                    return new JsonResponse(
                        $serializer->serialize(
                            [
                                "status" => Response::HTTP_INTERNAL_SERVER_ERROR,
                                "message" => 'Upload failed',
                                "error" => $e->getMessage(),
                            ],
                            'json'
                        ),
                        Response::HTTP_INTERNAL_SERVER_ERROR,
                        [],
                        true,
                    );
                }
            }
        }

        $em->persist($animal);
        $em->flush();

        return new JsonResponse(
            $serializer->serialize(
                $animalSerializer->serialize($animal),
                'json'
            ),
            Response::HTTP_CREATED,
            [],
            true,
        );
    }

    #[Route('/animals/{id}', name: 'updateAnimal', methods: ['POST'])]
    #[OA\Post(
        summary: 'Update an animal',
        description: 'Update an animal with new data or images',
        requestBody: new OA\RequestBody(
            description: 'Animal data object',
            content: new OA\MediaType(
                mediaType: 'multipart/form-data',
                schema: new OA\Schema(
                    type: 'object',
                    properties: [
                        new OA\Property(
                            property: 'data',
                            type: 'string',
                            required: ["name", "race", "habitat"],
                            description: 'Animal data in json format',
                            example: '{"name": "Lion", "race": 1, "habitat": 2}'
                        ),
                        new OA\Property(
                            property: "images",
                            type: "array",
                            items: new OA\Items(type: "string", format: "binary"),
                            description: "Image files for the animal. Allowed formats: jpg, jpeg, png."
                        ),
                    ]
                )

            )
        ),
        responses: [
            new OA\Response(
                response: Response::HTTP_OK,
                description: "Animal updated successfully.",
                content: new OA\JsonContent(ref: new Model(type: AnimalDto::class))
            ),
            new OA\Response(
                response: Response::HTTP_BAD_REQUEST,
                description: "Invalid input data or validation error.",
            ),
            new OA\Response(
                response: Response::HTTP_NOT_FOUND,
                description: "Animal not found."
            )
        ]

    )]
    public function updateAnimal(
        Request $request,
        Animal $currentAnimal,
        EntityManagerInterface $em,
        HabitatRepository $habitatRepository,
        RaceRepository $raceRepository,
        FileUploader $uploader,
        SerializerInterface $serializer,
        ValidatorInterface $validator,
        AnimalSerializer $animalSerializer,
    ): JsonResponse {

        $updatedAnimal = $currentAnimal;

        if ($request->get('data')) {
            $data = json_decode($request->get('data'), true);

            if (array_key_exists('name', $data)) {
                $updatedAnimal->setName($data['name']);
            }
            if (array_key_exists('race', $data)) {
                $race = $raceRepository->find($data['race']);
                $updatedAnimal->setRace($race);
            }
            if (array_key_exists('habitat',  $data)) {
                $habitat = $habitatRepository->find($data['habitat']);
                $updatedAnimal->setHabitat($habitat);
            }

            $violations = $validator->validate($updatedAnimal);

            if ($violations->count() > 0) {
                throw new ValidationFailedException($updatedAnimal, $violations);
            }
        }

        if ($request->files->get("images")) {

            $allowedExtensions = ['jpg', 'jpeg', 'png'];

            foreach ($request->files->get("images") as $image) {
                try {
                    $imageFileName = $uploader->upload(
                        $image,
                        $allowedExtensions
                    );
                    $animalImage = new AnimalImage();
                    $animalImage->setPath($imageFileName);
                    $updatedAnimal->addImage($animalImage);
                } catch (InvalidArgumentException $e) {
                    return new JsonResponse(
                        $serializer->serialize(
                            [
                                "status" => Response::HTTP_BAD_REQUEST,
                                "message" => "File validation failed",
                                'error' => 'Invalid file type, only ' . join(",", $allowedExtensions)
                                    . ' are allowed.'
                            ],
                            'json'
                        ),
                        Response::HTTP_BAD_REQUEST,
                        [],
                        true,
                    );
                } catch (FileException $e) {
                    return new JsonResponse(
                        $serializer->serialize(
                            [
                                "status" => Response::HTTP_INTERNAL_SERVER_ERROR,
                                "message" => 'Upload failed',
                                "error" => $e->getMessage(),
                            ],
                            'json'
                        ),
                        Response::HTTP_INTERNAL_SERVER_ERROR,
                        [],
                        true,
                    );
                }
            }
        }

        $em->persist($updatedAnimal);
        $em->flush();

        return new JsonResponse(
            $serializer->serialize(
                $animalSerializer->serialize($updatedAnimal),
                'json'
            ),
            Response::HTTP_OK,
            [],
            true,
        );
    }

    #[Route(path: '/animals/{id}', name: 'deleteAnimal', methods: ['DELETE'])]
    #[OA\Delete(
        summary: "Delete an animal",
        description: "Remove an animal record",
        responses: [
            new OA\Response(
                response: Response::HTTP_NO_CONTENT,
                description: "Animal deleted successfully."
            ),
            new OA\Response(
                response: Response::HTTP_NOT_FOUND,
                description: "Animal not found."
            )
        ]
    )]
    public function deleteAnimal(
        Animal $animal,
        EntityManagerInterface $em
    ): JsonResponse {
        $em->remove($animal);
        $em->flush();
        return new JsonResponse(
            null,
            Response::HTTP_NO_CONTENT
        );
    }
}
