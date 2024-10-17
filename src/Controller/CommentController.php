<?php

namespace App\Controller;

use App\Entity\Comment;
use App\Repository\CommentRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Serializer\SerializerInterface;

#[Route("/api")]
class CommentController extends AbstractController
{
    #[Route('/comments', name: 'comments', methods: ['GET'])]
    public function getAllComments(CommentRepository $commentRepository, SerializerInterface $serializer): JsonResponse
    {
        $commentList = $commentRepository->findAll();

        $jsonList = $serializer->serialize($commentList, 'json');

        return new JsonResponse($jsonList, Response::HTTP_OK, [], true);
    }

    #[Route('/approuved-comments', name: 'approuved-comments', methods: ['GET'])]
    public function getApprouvedComments(CommentRepository $commentRepository, SerializerInterface $serializer): JsonResponse
    {
        $validComments = $commentRepository->findValidComments();

        $jsonList = $serializer->serialize($validComments, 'json');

        return new JsonResponse($jsonList, Response::HTTP_OK, [], true);

    }

    #[Route('/comments', name: 'createComment', methods: ['POST'])]
    public function createComment(Request $request, SerializerInterface $serializer, EntityManagerInterface $em): JsonResponse
    {
        $comment = $serializer->deserialize($request->getContent(), Comment::class, 'json');
        $em->persist($comment);
        $em->flush();

        $jsonComment = $serializer->serialize($comment, 'json');

        return new JsonResponse($jsonComment, Response::HTTP_CREATED, [], true);
    }


    #[Route('/update-comment-status/{id}', name: 'updateCommentStatus', methods: ['PUT'])]
    public function updateCommentStatus(Comment $comment, SerializerInterface $serializer, EntityManagerInterface $em): JsonResponse
    {
        $comment->setVisible(!$comment->isVisible());
        $em->persist($comment);
        $em->flush();

        $jsonComment = $serializer->serialize($comment, 'json');

        return new JsonResponse($jsonComment, Response::HTTP_OK, [], true);
    }


    #[Route('/comments/{id}', name: 'deleteComment', methods: ['DELETE'])]
    public function deleteComment(Comment $comment, EntityManagerInterface $em): JsonResponse
    {
        $em->remove($comment);
        $em->flush();

        return new JsonResponse(null, Response::HTTP_NO_CONTENT);
    }

}


