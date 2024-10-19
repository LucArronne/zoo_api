<?php

namespace App\Controller;

use App\Entity\Comment;
use App\Repository\CommentRepository;
use App\Repository\ServiceRepository;
use App\Utils\FileUploader;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Component\Serializer\SerializerInterface;
use Symfony\Component\Validator\Exception\ValidationFailedException;
use Symfony\Component\Validator\Validator\ValidatorInterface;

#[Route(path: '/home')]
class HomeController extends AbstractController
{
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

    #[Route('/services', name: 'services', methods: ['GET'])]
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
}
