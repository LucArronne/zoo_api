<?php

namespace App\Controller;

use App\Entity\Comment;
use App\Entity\Service;
use App\Repository\AnimalImageRepository;
use App\Repository\AnimalRepository;
use App\Repository\CommentRepository;
use App\Repository\HabitatImageRepository;
use App\Repository\HabitatRepository;
use App\Repository\ServiceRepository;
use App\Utils\AnimalSerializer;
use App\Utils\FileUploader;
use App\Utils\HabitatSerializer;
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
    #[Route(path: '/images', name: 'images', methods: ['GET'])]
    public function getImages(
        HabitatImageRepository $habitatImageRepository,
        AnimalImageRepository $animalImageRepository,
        SerializerInterface $serializer,
        FileUploader $uploader,
    ): JsonResponse {

        $result = array_map(function ($image) use ($uploader) {
            return $uploader->getFilePublicUrl($image["path"]);
        }, [...$animalImageRepository->findRandomImages(), ...$habitatImageRepository->findRandomImages()]);


        return new JsonResponse(
            $serializer->serialize($result, 'json'),
            Response::HTTP_OK,
            [],
            true,
        );
    }

    #[Route(path: '/habitats', name: 'habitats', methods: ['GET'])]
    public function getHabitas(
        HabitatRepository $habitatRepository,
        SerializerInterface $serializer,
        HabitatSerializer $habitatSerializer
    ): JsonResponse {

        $result = $habitatSerializer->serializeArray($habitatRepository->findAll());

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
        AnimalSerializer $animalSerializer,
    ): JsonResponse {

        $result = $animalSerializer->serializeArray($animalRepository->findAll());

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

        $result = array_map(function (Service $value) use ($uploader): Service {
            $value->setImage($uploader->getFilePublicUrl($value->getImage()));
            return $value;
        }, $serviceRepository->findAll());

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
        $jsonList = $serializer->serialize($commentRepository->findValidComments(), 'json');

        return new JsonResponse(
            $jsonList,
            Response::HTTP_OK,
            [],
            true
        );
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

        return new JsonResponse(
            $serializer->serialize($comment, 'json'),
            Response::HTTP_CREATED,
            [],
            true
        );
    }
}
