<?php

namespace App\Controller\Admin;

use App\Entity\Service;
use App\Repository\ServiceRepository;
use App\Utils\FileUploader;
use Doctrine\ORM\EntityManagerInterface;
use InvalidArgumentException;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\File\Exception\FileException;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Component\Serializer\SerializerInterface;
use Symfony\Component\Validator\Exception\ValidationFailedException;
use Symfony\Component\Validator\Validator\ValidatorInterface;

class ServiceController extends AbstractController
{
    #[Route('/public/services', name: 'services', methods: ['GET'])]
    public function getServices(
        ServiceRepository $serviceRepository,
        SerializerInterface $serializer,
        FileUploader $uploader,
        UrlGeneratorInterface $urlGenerator,
    ): JsonResponse {
        $serviceList = $serviceRepository->findAll();

        $services = [];

        foreach ($serviceList as $service) {
            $services[] = [
                'id' => $service->getId(),
                'title' => $service->getName(),
                'description' => $service->getDescription(),
                'image' => $service->getImage() === null
                    ? null
                    : $uploader->getFilePublicUrl($urlGenerator, $service->getImage()),
            ];
        }

        $jsonServiceList = $serializer->serialize($services, 'json');

        return new JsonResponse(
            $jsonServiceList,
            Response::HTTP_OK,
            [],
            true,
        );
    }

    #[Route('/admin/services', name: 'createService', methods: ['POST'])]
    public function createService(
        Request $request,
        EntityManagerInterface $em,
        FileUploader $uploader,
        SerializerInterface $serializer,
        ValidatorInterface $validator,
        UrlGeneratorInterface $urlGenerator,

    ): JsonResponse {

        $service = new Service();
        $service->setName($request->get('name'));
        $service->setDescription($request->get('description'));

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
            $imageUrl = $uploader->getFilePublicUrl($urlGenerator, $service->getImage());
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
