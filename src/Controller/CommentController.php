<?php

namespace App\Controller;

use App\Entity\Comment;
use App\Repository\CommentRepository;
use Doctrine\ORM\EntityManagerInterface;
use Nelmio\ApiDocBundle\Annotation\Model;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;
use Symfony\Component\Serializer\SerializerInterface;
use OpenApi\Attributes as OA;

#[IsGranted('ROLE_EMPLOYEE', message: 'Access denied')]
#[OA\Tag(name: 'Employee')]
class CommentController extends AbstractController
{
    #[Route('/comments', name: 'comments', methods: ['GET'])]
    #[OA\Get(
        summary: 'Get the list of comments',
        description: 'Get the list of visitor\'s comments',
    )]
    #[OA\Response(
        response: Response::HTTP_OK,
        description: 'Returns the list of comments',
        content: new OA\JsonContent(
            type: 'array',
            items: new OA\Items(ref: new Model(type: Comment::class))
        )
    )]
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
    #[OA\Put(
        summary: 'Update a comment status',
        description: 'Use to validate or invalidate a comment',
        parameters: [
            new OA\Parameter(
                name: "id",
                in: "path",
                required: true,
                description: "The ID of the comment",
                schema: new OA\Schema(type: "integer")
            )
        ],
        responses: [
            new OA\Response(
                response: Response::HTTP_OK,
                description: "Comment updated successfully.",
                content: new OA\JsonContent(ref: new Model(type: Comment::class)),
            ),
            new OA\Response(
                response: Response::HTTP_NOT_FOUND,
                description: "Commment not found",
            ),
        ]

    )]
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
    #[OA\Delete(
        summary: 'Delete comment',
        parameters: [
            new OA\Parameter(
                name: "id",
                in: "path",
                required: true,
                description: "The ID of the comment",
                schema: new OA\Schema(type: "integer")
            )
        ],
        responses: [
            new OA\Response(
                response: Response::HTTP_NO_CONTENT,
                description: "Comment deleted successfully.",
            ),
            new OA\Response(
                response: Response::HTTP_NOT_FOUND,
                description: "Commment not found",
            ),
        ]

    )]
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
