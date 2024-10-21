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

        $service = $serializer->deserialize($request->get("data"), Service::class, 'json');

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

        $jsonService = $serializer->serialize($service, 'json');

        return new JsonResponse(
            $jsonService,
            Response::HTTP_CREATED,
            [],
            true,
        );
    }
}
