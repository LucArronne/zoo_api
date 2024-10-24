<?php

namespace App\Controller\Admin;

use App\Entity\Service;
use App\Utils\FileUploader;
use Doctrine\ORM\EntityManagerInterface;
use InvalidArgumentException;
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
class ServiceController extends AbstractController
{

    #[Route('/services', name: 'createService', methods: ['POST'])]
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
