<?php

namespace App\Controller;

use App\Entity\Comment;
use App\Repository\CommentRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;
use Symfony\Component\Serializer\SerializerInterface;

class CommentController extends AbstractController
{
    #[Route('/comments', name: 'comments', methods: ['GET'])]
    #[IsGranted('ROLE_EMPLOYEE', message: 'Access denied')]
    public function getAllComments(
        CommentRepository $commentRepository,
        SerializerInterface $serializer
    ): JsonResponse {

        $commentList = $commentRepository->findAll();

        $jsonList = $serializer->serialize($commentList, 'json');

        return new JsonResponse(
            $jsonList,
            Response::HTTP_OK,
            [],
            true
        );
    }

    #[Route('/comments/{id}', name: 'updateCommentStatus', methods: ['PUT'])]
    #[IsGranted('ROLE_EMPLOYEE', message: 'Access denied')]
    public function updateCommentStatus(
        Comment $comment,
        SerializerInterface $serializer,
        EntityManagerInterface $em
    ): JsonResponse {

        $comment->setVisible(!$comment->isVisible());
        $em->persist($comment);
        $em->flush();

        $jsonComment = $serializer->serialize($comment, 'json');

        return new JsonResponse(
            $jsonComment,
            Response::HTTP_OK,
            [],
            true
        );
    }


    #[Route('/comments/{id}', name: 'deleteComment', methods: ['DELETE'])]
    #[IsGranted('ROLE_EMPLOYEE', message: 'Access denied')]
    public function deleteComment(
        Comment $comment,
        EntityManagerInterface $em
    ): JsonResponse {
        $em->remove($comment);
        $em->flush();

        return new JsonResponse(
            null,
            Response::HTTP_NO_CONTENT
        );
    }
}
