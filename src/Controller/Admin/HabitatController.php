<?php

namespace App\Controller\Admin;

use App\Dto\HabitatDto;
use App\Entity\Habitat;
use App\Entity\HabitatImage;
use App\Entity\Image;
use App\Repository\HabitatImageRepository;
use App\Utils\FileUploader;
use App\Utils\HabitatMapper;
use App\Utils\ImageMapper;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\EntityManagerInterface;
use InvalidArgumentException;
use Nelmio\ApiDocBundle\Annotation\Model;
use OpenApi\Attributes as OA;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;
use Symfony\Component\HttpFoundation\File\Exception\FileException;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Serializer\Normalizer\AbstractNormalizer;
use Symfony\Component\Serializer\SerializerInterface;
use Symfony\Component\Validator\Exception\ValidationFailedException;
use Symfony\Component\Validator\Validator\ValidatorInterface;

#[Route(path: '/admin')]
#[OA\Tag(name: 'Admin')]
class HabitatController extends AbstractController
{
    #[Route('/habitats/images', name: 'habitatImages', methods: ['GET'])]
    #[OA\Get(
        summary: 'Get the list of habitats images'
    )]
    #[OA\Response(
        response: Response::HTTP_OK,
        description: 'Returns the list of habitats images',
        content: new OA\JsonContent(
            type: 'array',
            items: new OA\Items(ref: new Model(type: Image::class))
        )
    )]
    public function getHabitatImages(
        HabitatImageRepository $habitatImageRepository,
        SerializerInterface $serializer,
        ImageMapper $imageToUrlSerializer,
    ): JsonResponse {

        $result = $serializer->serialize(
            $imageToUrlSerializer->convertToUrlArray($habitatImageRepository->findAll()),
            'json',
        );

        return new JsonResponse(
            $result,
            Response::HTTP_OK,
            [],
            true
        );
    }

    #[Route('/habitats', name: 'createHabitat', methods: ['POST'])]
    #[OA\Post(
        summary: 'Create a new habitat',
        description: 'Create a new animal record with optinal images',
        requestBody: new OA\RequestBody(
            description: 'Habitat data object',
            required: true,
            content: new OA\MediaType(
                mediaType: 'multipart/form-data',
                schema: new OA\Schema(
                    type: 'object',
                    properties: [
                        new OA\Property(
                            property: 'data',
                            type: 'object',
                            required: ["name", "description"],
                            description: 'Habitat data in json format (required)',
                            properties: [
                                new OA\Property(property: "name", type: "string", example: "Habitat 1"),
                                new OA\Property(property: "description", type: "string", example: "Description 1"),
                                new OA\Property(
                                    property: "images",
                                    type: "array",
                                    items: new OA\Items(ref: new Model(type: Image::class)),
                                ),
                            ]
                        ),
                        new OA\Property(
                            property: "files[]",
                            type: "array",
                            items: new OA\Items(type: "string", format: "binary"),
                            description: "Image files for the habitat. Allowed formats: jpg, jpeg, png. (optional)"
                        ),
                    ]
                )

            )
        ),
        responses: [
            new OA\Response(
                response: Response::HTTP_CREATED,
                description: "Habitat created successfully.",
                content: new OA\JsonContent(ref: new Model(type: HabitatDto::class))
            ),
            new OA\Response(
                response: Response::HTTP_BAD_REQUEST,
                description: "Invalid input data or validation error.",
            )
        ]

    )]
    public function createHabitat(
        Request $request,
        EntityManagerInterface $em,
        HabitatImageRepository $habitatImageRepository,
        FileUploader $uploader,
        SerializerInterface $serializer,
        ValidatorInterface $validator,
        HabitatMapper $habitatSerializer,
    ): JsonResponse {

        if (!$request->get('data')) {
            return new JsonResponse(
                $serializer->serialize(
                    [
                        "status" => Response::HTTP_BAD_REQUEST,
                        "message" => "Argument validation failed",
                        'error' => "Add habitat json object as 'data' key"
                    ],
                    'json'
                ),
                Response::HTTP_BAD_REQUEST,
                [],
                true,
            );
        }

        $habitat = $serializer->deserialize(
            $request->get("data"),
            Habitat::class,
            'json'
        );

        foreach ($habitat->getImages() as $image) {
            $habitat->removeImage($image);
            $habitatImage = $habitatImageRepository->find($image->getId() ?? -1);
            if ($habitatImage) {
                $habitat->addImage($habitatImage);
            }
        }

        $violations = $validator->validate($habitat);

        if ($violations->count() > 0) {
            throw new ValidationFailedException($habitat, $violations);
        }

        $imageFiles = $request->files->get("files");

        if ($imageFiles) {

            $allowedExtensions = ['jpg', 'jpeg', 'png'];

            foreach ($imageFiles as $image) {
                try {
                    $imageFileName = $uploader->upload($image, $allowedExtensions);
                    $habitatImage = new HabitatImage();
                    $habitatImage->setPath($imageFileName);
                    $habitat->addImage($habitatImage);
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

        $em->persist($habitat);
        $em->flush();

        $result = $serializer->serialize(
            $habitatSerializer->convertToDto($habitat),
            'json'
        );

        return new JsonResponse(
            $result,
            Response::HTTP_CREATED,
            [],
            true,
        );
    }

    #[Route('/habitats/{id}', name: 'updateHabitat', methods: ['POST'])]
    #[OA\Post(
        summary: 'Update a habitat',
        description: 'Update a animal with new data or images',
        parameters: [
            new OA\Parameter(
                name: "id",
                in: "path",
                required: true,
                description: "The ID of the habitat",
                schema: new OA\Schema(type: "integer")
            )
        ],
        requestBody: new OA\RequestBody(
            description: 'Habitat data object',
            content: new OA\MediaType(
                mediaType: 'multipart/form-data',
                schema: new OA\Schema(
                    type: 'object',
                    properties: [
                        new OA\Property(
                            property: 'data',
                            type: 'object',
                            required: ["name", "description"],
                            description: 'Habitat data in json format',
                            properties: [
                                new OA\Property(property: "name", type: "string", example: "Habitat 2"),
                                new OA\Property(property: "description", type: "string", example: "Description 2"),
                                new OA\Property(
                                    property: "images",
                                    type: "array",
                                    items: new OA\Items(ref: new Model(type: Image::class)),
                                ),
                            ]
                        ),
                        new OA\Property(
                            property: "files[]",
                            type: "array",
                            items: new OA\Items(type: "string", format: "binary"),
                            description: "Image files for the habitat. Allowed formats: jpg, jpeg, png"
                        ),
                    ]
                )

            )
        ),
        responses: [
            new OA\Response(
                response: Response::HTTP_CREATED,
                description: "Habitat updated successfully.",
                content: new OA\JsonContent(ref: new Model(type: HabitatDto::class))
            ),
            new OA\Response(
                response: Response::HTTP_BAD_REQUEST,
                description: "Invalid input data or validation error.",
            ),
            new OA\Response(
                response: Response::HTTP_NOT_FOUND,
                description: "Habitat not found",
            )
        ]

    )]
    public function updateHabitat(
        Request $request,
        Habitat $currenthabitat,
        EntityManagerInterface $em,
        HabitatImageRepository $habitatImageRepository,
        FileUploader $uploader,
        SerializerInterface $serializer,
        ValidatorInterface $validator,
        HabitatMapper $habitatSerializer,
    ): JsonResponse {

        $updatedHabitat = $currenthabitat;

        if ($request->get('data')) {
            $updatedHabitat = $serializer->deserialize(
                $request->get("data"),
                Habitat::class,
                'json',
                [
                    AbstractNormalizer::OBJECT_TO_POPULATE => $currenthabitat
                ]
            );
            foreach ($updatedHabitat->getImages() as $image) {
                $updatedHabitat->removeImage($image);
                $habitatImage = $habitatImageRepository->find($image->getId() ?? -1);
                if ($habitatImage) {
                    $updatedHabitat->addImage($habitatImage);
                }
            }

            $violations = $validator->validate($updatedHabitat);

            if ($violations->count() > 0) {
                throw new ValidationFailedException($updatedHabitat, $violations);
            }
        }

        if ($request->files->get("files")) {

            $allowedExtensions = ['jpg', 'jpeg', 'png'];

            foreach ($request->files->get("files") as $image) {
                try {
                    $imageFileName = $uploader->upload($image, $allowedExtensions);
                    $habitatImage = new HabitatImage();
                    $habitatImage->setPath($imageFileName);
                    $updatedHabitat->addImage($habitatImage);
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

        $em->persist($updatedHabitat);
        $em->flush();

        $result = $serializer->serialize(
            $habitatSerializer->convertToDto($updatedHabitat),
            'json'
        );

        return new JsonResponse(
            $result,
            Response::HTTP_CREATED,
            [],
            true,
        );
    }

    #[Route(path: '/habitats/{id}', name: 'deleteHabitat', methods: ['DELETE'])]
    #[OA\Delete(
        summary: "Delete a habitat",
        description: "Remove a habitat record",
        parameters: [
            new OA\Parameter(
                name: "id",
                in: "path",
                required: true,
                description: "The ID of the habitat",
                schema: new OA\Schema(type: "integer")
            )
        ],
        responses: [
            new OA\Response(
                response: Response::HTTP_NO_CONTENT,
                description: "Habitat deleted successfully."
            ),
            new OA\Response(
                response: Response::HTTP_NOT_FOUND,
                description: "Habitat not found."
            )
        ]
    )]
    public function deleteHabitat(
        Habitat $habitat,
        EntityManagerInterface $em,
        ParameterBagInterface $params,
    ): JsonResponse {
        foreach ($habitat->getImages() as $image) {
            if ($image->getHabitats()->count() == 1) {
                $filePath = str_contains($image->getPath(), "http")
                    ? $params->get('uploads_directory') . '/' . basename($image->getPath())
                    : $params->get('uploads_directory') . '/' . $image->getPath();
                if (file_exists($filePath)) {
                    unlink($filePath);
                }
                $em->remove($image);
            }
        }
        $em->remove($habitat);
        $em->flush();
        return new JsonResponse(
            null,
            Response::HTTP_NO_CONTENT
        );
    }
}
