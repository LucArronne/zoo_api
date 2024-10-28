<?php

namespace App\Controller\Admin;

use App\Entity\Service;
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
use Symfony\Component\Serializer\Normalizer\AbstractNormalizer;
use Symfony\Component\Serializer\SerializerInterface;
use Symfony\Component\Validator\Exception\ValidationFailedException;
use Symfony\Component\Validator\Validator\ValidatorInterface;

#[Route(path: '/admin')]
#[OA\Tag(name: 'admin')]
class ServiceController extends AbstractController
{

    #[Route('/services', name: 'createService', methods: ['POST'])]
    #[OA\Post(
        summary: 'Create a new service',
        description: 'Create a new service record with optinal image',
        requestBody: new OA\RequestBody(
            description: 'Service data object',
            required: true,
            content: new OA\MediaType(
                mediaType: 'multipart/form-data',
                schema: new OA\Schema(
                    type: 'object',
                    properties: [
                        new OA\Property(
                            property: 'data',
                            type: 'string',
                            required: ["name", "description"],
                            description: 'Service data in json format (required)',
                            example: '{"name": "Guide", "description": "Guide service"}'
                        ),
                        new OA\Property(
                            property: "image",
                            type: "string",
                            format: "binary",
                            description: "Image file for the service. Allowed formats: jpg, jpeg, png (optional)"
                        ),
                    ]
                )

            )
        ),
        responses: [
            new OA\Response(
                response: Response::HTTP_CREATED,
                description: "Service created successfully.",
                content: new OA\JsonContent(ref: new Model(type: Service::class))
            ),
            new OA\Response(
                response: Response::HTTP_BAD_REQUEST,
                description: "Invalid input data or validation error.",
            )
        ]

    )]
    public function createService(
        Request $request,
        EntityManagerInterface $em,
        FileUploader $uploader,
        SerializerInterface $serializer,
        ValidatorInterface $validator,

    ): JsonResponse {

        if (!$request->get('data')) {
            return new JsonResponse(
                $serializer->serialize(
                    [
                        "status" => Response::HTTP_BAD_REQUEST,
                        "message" => "Argument validation failed",
                        'error' => "Add service json object as 'data' key"
                    ],
                    'json'
                ),
                Response::HTTP_BAD_REQUEST,
                [],
                true,
            );
        }
        $service = $serializer->deserialize(
            $request->get("data"),
            Service::class,
            'json'
        );

        $violations = $validator->validate($service);

        if ($violations->count() > 0) {
            throw new ValidationFailedException($service, $violations);
        }

        $serviceImage = $request->files->get("image");

        if ($serviceImage) {

            $allowedExtensions = ['jpg', 'jpeg', 'png'];
            try {
                $imageFileName = $uploader->upload($serviceImage, $allowedExtensions);
                $service->setImage($imageFileName);
            } catch (InvalidArgumentException $e) {

                return new JsonResponse(
                    $serializer->serialize(
                        [
                            "status" => Response::HTTP_BAD_REQUEST,
                            "message" => "File validation failed",
                            'error' => 'Invalid file type, only ' . join(",", $allowedExtensions) . ' are allowed.'
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
                            "message" => $e->getMessage(),
                        ],
                        'json'
                    ),
                    Response::HTTP_INTERNAL_SERVER_ERROR,
                    [],
                    true,
                );
            }
        }

        $em->persist($service);
        $em->flush();

        if ($service->getImage()) {
            $imageUrl = $uploader->getFilePublicUrl($service->getImage());
            $service->setImage($imageUrl);
        }

        return new JsonResponse(
            $serializer->serialize($service, 'json'),
            Response::HTTP_CREATED,
            [],
            true,
        );
    }

    #[Route('/services/{id}', name: 'updateService', methods: ['POST'])]
    #[OA\Post(
        summary: 'Update a service',
        description: 'Update a service record with new data or image',
        requestBody: new OA\RequestBody(
            description: 'Service data object',
            content: new OA\MediaType(
                mediaType: 'multipart/form-data',
                schema: new OA\Schema(
                    type: 'object',
                    properties: [
                        new OA\Property(
                            property: 'data',
                            type: 'string',
                            description: 'Habitat data in json format',
                            example: '{"name": "Guide", "description": "Guide service"}'
                        ),
                        new OA\Property(
                            property: "image",
                            type: "string",
                            format: "binary",
                            description: "Image file for the service. Allowed formats: jpg, jpeg, png"
                        ),
                    ]
                )

            )
        ),
        responses: [
            new OA\Response(
                response: Response::HTTP_OK,
                description: "Service updated successfully.",
                content: new OA\JsonContent(ref: new Model(type: Service::class))
            ),
            new OA\Response(
                response: Response::HTTP_BAD_REQUEST,
                description: "Invalid input data or validation error.",
            ),
            new OA\Response(
                response: Response::HTTP_NOT_FOUND,
                description: "Service not found",
            )
        ]

    )]

    public function updateService(
        Request $request,
        Service $currentService,
        EntityManagerInterface $em,
        FileUploader $uploader,
        SerializerInterface $serializer,
        ValidatorInterface $validator,

    ): JsonResponse {

        $updatedService = $currentService;

        if ($request->get('data')) {
            $updatedService = $serializer->deserialize(
                $request->get("data"),
                Service::class,
                'json',
                [
                    AbstractNormalizer::OBJECT_TO_POPULATE => $currentService
                ]
            );

            $violations = $validator->validate($updatedService);

            if ($violations->count() > 0) {
                throw new ValidationFailedException($updatedService, $violations);
            }
        }

        if ($request->files->get("image")) {

            $allowedExtensions = ['jpg', 'jpeg', 'png'];
            try {
                $imageFileName = $uploader->upload(
                    $request->files->get("image"),
                    $allowedExtensions
                );
                $updatedService->setImage($imageFileName);
            } catch (InvalidArgumentException $e) {

                return new JsonResponse(
                    $serializer->serialize(
                        [
                            "status" => Response::HTTP_BAD_REQUEST,
                            "message" => "File validation failed",
                            'error' => 'Invalid file type, only ' . join(",", $allowedExtensions) . ' are allowed.'
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
                            "message" => $e->getMessage(),
                        ],
                        'json'
                    ),
                    Response::HTTP_INTERNAL_SERVER_ERROR,
                    [],
                    true,
                );
            }
        }

        $em->persist($updatedService);
        $em->flush();

        if ($updatedService->getImage()) {
            $imageUrl = $uploader->getFilePublicUrl($updatedService->getImage());
            $updatedService->setImage($imageUrl);
        }

        return new JsonResponse(
            $serializer->serialize($updatedService, 'json'),
            Response::HTTP_OK,
            [],
            true,
        );
    }

    #[Route(path: '/services/{id}', name: 'deleteService', methods: ['DELETE'])]
    #[OA\Delete(
        summary: "Delete a service",
        description: "Remove a service record",
        responses: [
            new OA\Response(
                response: Response::HTTP_NO_CONTENT,
                description: "Service deleted successfully."
            ),
            new OA\Response(
                response: Response::HTTP_NOT_FOUND,
                description: "Service not found."
            )
        ]
    )]
    public function deleteService(Service $service, EntityManagerInterface $em): JsonResponse
    {
        $em->remove($service);
        $em->flush();
        return new JsonResponse(
            null,
            Response::HTTP_NO_CONTENT
        );
    }
}
