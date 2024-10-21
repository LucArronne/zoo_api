<?php

namespace App\Controller;

use App\Entity\Comment;
use App\Repository\AnimalRepository;
use App\Repository\CommentRepository;
use App\Repository\HabitatRepository;
use App\Repository\ServiceRepository;
use App\Utils\FileUploader;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Serializer\SerializerInterface;
use Symfony\Component\Validator\Exception\ValidationFailedException;
use Symfony\Component\Validator\Validator\ValidatorInterface;

#[Route(path: '/home')]
class HomeController extends AbstractController
{

    #[Route(path: '/habitats', name: 'habitats', methods: ['GET'])]
    public function getHabitas(
        HabitatRepository $habitatRepository,
        FileUploader $uploader,
        SerializerInterface $serializer,
    ): JsonResponse {

        $habitatList = $habitatRepository->findAll();

        $result = [];
        foreach ($habitatList as $habitat) {
            $images = [];
            foreach ($habitat->getImages() as $image) {
                $images[] = [
                    'id' => $image->getId(),
                    'path' => $uploader->getFilePublicUrl($image->getPath())
                ];
            }
            $animals = [];
            foreach ($habitat->getAnimals() as $animal) {
                $animals[] = [
                    'id' => $animal->getId(),
                    'name' => $animal->getName(),
                    'race' => $animal->getRace(),
                    'images' => $animal->getImages()->map(function ($image) use ($uploader) {
                        return [
                            'id' => $image->getId(),
                            'path' => $uploader->getFilePublicUrl($image->getPath()),
                        ];
                    }),
                ];
            }
            $result[] = [
                'id' => $habitat->getId(),
                'name' => $habitat->getName(),
                'descrption' => $habitat->getDescription(),
                'images' => $images,
                'animals' => $animals,
            ];
        }

        return new JsonResponse(
            $serializer->serialize($result, 'json'),
            Response::HTTP_OK,
            [],
            true,
        );
    }

    #[Route('/services', name: 'services', methods: ['GET'])]
    public function getServices(
        ServiceRepository $serviceRepository,
        SerializerInterface $serializer,
        FileUploader $uploader,
    ): JsonResponse {

        $serviceList = $serviceRepository->findAll();

        $result = [];

        foreach ($serviceList as $service) {
            $result[] = [
                'id' => $service->getId(),
                'name' => $service->getName(),
                'description' => $service->getDescription(),
                'image' => $service->getImage() === null
                    ? null
                    : $uploader->getFilePublicUrl($service->getImage()),
            ];
        }

        return new JsonResponse(
            $serializer->serialize($result, 'json'),
            Response::HTTP_OK,
            [],
            true,
        );
    }

    #[Route('/animals', name: 'animals', methods: ['GET'])]
    public function getAnimals(
        AnimalRepository $animalRepository,
        SerializerInterface $serializer,
        FileUploader $uploader,
    ): JsonResponse {
        $animalList = $animalRepository->findAll();

        $result = [];

        foreach ($animalList as $animal) {
            $result[] = [
                'id' => $animal->getId(),
                'name' => $animal->getName(),
                'race' => $animal->getRace(),
                'images' => $animal->getImages()->map(function ($image) use ($uploader) {
                    return [
                        'id' => $image->getId(),
                        'path' => $uploader->getFilePublicUrl($image->getPath()),
                    ];
                }),
            ];
        }

        return new JsonResponse(
            $serializer->serialize($result, 'json'),
            Response::HTTP_OK,
            [],
            true,
        );
    }

    #[Route('/comments', name: 'approuved-comments', methods: ['GET'])]
    public function getApprouvedComments(CommentRepository $commentRepository, SerializerInterface $serializer): JsonResponse
    {
        $validComments = $commentRepository->findValidComments();

        $jsonList = $serializer->serialize($validComments, 'json');

        return new JsonResponse($jsonList, Response::HTTP_OK, [], true);
    }

    #[Route('/comments', name: 'createComment', methods: ['POST'])]
    public function createComment(
        Request $request,
        SerializerInterface $serializer,
        EntityManagerInterface $em,
        ValidatorInterface $validator,
    ): JsonResponse {
        $comment = $serializer->deserialize($request->getContent(), Comment::class, 'json');

        $violations = $validator->validate($comment);

        if ($violations->count() > 0) {
            throw new ValidationFailedException($comment, $violations);
        }

        $comment->setVisible(false);
        $em->persist($comment);
        $em->flush();

        $jsonComment = $serializer->serialize($comment, 'json');

        return new JsonResponse($jsonComment, Response::HTTP_CREATED, [], true);
    }
}
