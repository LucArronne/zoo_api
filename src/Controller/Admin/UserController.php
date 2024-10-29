<?php

namespace App\Controller\Admin;

use App\Entity\User;
use App\Repository\RoleRepository;
use App\Repository\UserRepository;
use Doctrine\ORM\EntityManagerInterface;
use Nelmio\ApiDocBundle\Annotation\Model;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Serializer\Normalizer\AbstractNormalizer;
use Symfony\Component\Serializer\SerializerInterface;
use Symfony\Component\Validator\Exception\ValidationFailedException;
use Symfony\Component\Validator\Validator\ValidatorInterface;
use OpenApi\Attributes as OA;

#[Route(path: '/admin')]
#[OA\Tag(name: 'Admin')]
class UserController extends AbstractController
{
    #[Route('/users', name: 'users', methods: ['GET'])]
    #[OA\Get(
        summary: 'Get the list of users'
    )]
    #[OA\Response(
        response: Response::HTTP_OK,
        description: 'Returns the list of all not admin users',
        content: new OA\JsonContent(
            type: 'array',
            items: new OA\Items(ref: new Model(type: User::class, groups: ['getUsers']))
        )
    )]
    public function getUsers(
        UserRepository $userRepository,
        SerializerInterface $serializer
    ): JsonResponse {

        $result = $serializer->serialize(
            $userRepository->findAllNotAdminUsers(),
            'json',
            ['groups' => 'getUsers']
        );

        return new JsonResponse(
            $result,
            Response::HTTP_OK,
            [],
            true
        );
    }

    #[Route('/users', name: 'createUser', methods: ['POST'])]
    #[OA\Post(
        summary: 'Create new user',
        description: 'Create a new user record with role',
        requestBody: new OA\RequestBody(
            required: true,
            description: 'User data in json format. User role, either ROLE_EMPLOYEE or ROLE_VETERNARY',
            content: new OA\JsonContent(
                type: 'object',
                properties: [
                    new OA\Property(
                        property: "email",
                        type: "string",
                        example: "test@zoo.org"
                    ),
                    new OA\Property(
                        property: "password",
                        type: "string",
                        example: "password"
                    ),
                    new OA\Property(
                        property: "name",
                        type: "string",
                        example: "Test name"
                    ),
                    new OA\Property(
                        property: "role",
                        type: "string",
                        enum: ["ROLE_EMPLOYEE", "ROLE_VETERNARY"],
                    )
                ],
            ),
        ),
        responses: [
            new OA\Response(
                response: Response::HTTP_CREATED,
                description: "User created successfully.",
                content: new OA\JsonContent(ref: new Model(type: User::class, groups: ["getUsers"])),
            ),
            new OA\Response(
                response: Response::HTTP_BAD_REQUEST,
                description: "Invalid input data or validation error.",
            )
        ]

    )]
    public function createUser(
        Request $request,
        EntityManagerInterface $em,
        RoleRepository $roleRepository,
        SerializerInterface $serializer,
        UserPasswordHasherInterface $passwordHasher,
        ValidatorInterface $validator
    ): JsonResponse {

        $user = $serializer->deserialize(
            $request->getContent(),
            User::class,
            'json'
        );

        $role = strtoupper($request->toArray()['role'] ?? '');
        if ($role === 'ROLE_ADMIN') {
            return new JsonResponse(
                $serializer->serialize(
                    [
                        "status" => Response::HTTP_BAD_REQUEST,
                        "message" => "Role validation failed",
                        'errors' => "Invalid role use ROLE_EMPLOYEE or ROLE_VETERNARY",
                    ],
                    'json',
                ),
                Response::HTTP_BAD_REQUEST,
                [],
                true
            );
        }
        $role = $roleRepository->findOneBy(['value' => $role]);
        $user->setRole($role);

        $violations = $validator->validate($user);
        if ($violations->count() > 0) {
            throw new ValidationFailedException($user, $violations);
        }

        $user->setPassword($passwordHasher->hashPassword($user, $user->getPassword()));

        $em->persist($user);
        $em->flush();

        /// TODO send email after account creation

        return new JsonResponse(
            $serializer->serialize($user, 'json', ['groups' => 'getUsers']),
            Response::HTTP_CREATED,
            [],
            true
        );
    }

    #[Route('/users/{id}', name: 'updateUser', methods: ['PUT'])]
    #[OA\Put(
        summary: 'Update a user',
        parameters: [
            new OA\Parameter(
                name: "id",
                in: "path",
                required: true,
                description: "The ID of the user",
                schema: new OA\Schema(type: "integer")
            )
        ],
        requestBody: new OA\RequestBody(
            description: 'User data in json format. User role, either ROLE_EMPLOYEE or ROLE_VETERNARY',
            content: new OA\JsonContent(
                type: 'object',
                properties: [
                    new OA\Property(
                        property: "email",
                        type: "string",
                        example: "test@zoo.org"
                    ),
                    new OA\Property(
                        property: "password",
                        type: "string",
                        example: "password"
                    ),
                    new OA\Property(
                        property: "name",
                        type: "string",
                        example: "Test name"
                    ),
                    new OA\Property(
                        property: "role",
                        type: "string",
                        enum: ["ROLE_EMPLOYEE", "ROLE_VETERNARY"],
                    )
                ],
            ),
        ),
        responses: [
            new OA\Response(
                response: Response::HTTP_OK,
                description: "User created successfully.",
                content: new OA\JsonContent(ref: new Model(type: User::class, groups: ["getUsers"])),
            ),
            new OA\Response(
                response: Response::HTTP_BAD_REQUEST,
                description: "Invalid input data or validation error.",
            ),
            new OA\Response(
                response: Response::HTTP_NOT_FOUND,
                description: "User not found",
            ),
        ]

    )]
    public function updateUser(
        Request $request,
        User $currentUser,
        EntityManagerInterface $em,
        RoleRepository $roleRepository,
        UserPasswordHasherInterface $passwordHasher,
        SerializerInterface $serializer,
        ValidatorInterface $validator
    ): JsonResponse {

        $updatedUser = $serializer->deserialize(
            $request->getContent(),
            User::class,
            'json',
            [
                AbstractNormalizer::OBJECT_TO_POPULATE => $currentUser
            ]
        );

        if (array_key_exists('password', $request->toArray())) {
            $updatedUser->setPassword(
                $passwordHasher->hashPassword(
                    $updatedUser,
                    $request->toArray()['password']
                )
            );
        }

        if (array_key_exists('role', $request->toArray())) {
            $role = strtoupper($request->toArray()['role']);
            if ($role === 'ROLE_ADMIN') {
                return new JsonResponse(
                    $serializer->serialize(
                        [
                            "status" => Response::HTTP_BAD_REQUEST,
                            "message" => "Role validation failed",
                            'errors' => "Invalid role use ROLE_EMPLOYEE or ROLE_VETERNARY",
                        ],
                        'json',
                    ),
                    Response::HTTP_BAD_REQUEST,
                    [],
                    true
                );
            }
            $role = $roleRepository->findOneBy(['value' => $role]);
            $updatedUser->setRole($role);
        }

        $violations = $validator->validate($updatedUser);
        if ($violations->count() > 0) {
            throw new ValidationFailedException($updatedUser, $violations);
        }

        $em->persist($updatedUser);
        $em->flush();

        return new JsonResponse(
            $serializer->serialize(
                $updatedUser,
                'json',
                ['groups' => 'getUsers']
            ),
            Response::HTTP_OK,
            [],
            true
        );
    }

    #[Route('/users/{id}', name: 'deleteUser', methods: ['DELETE'])]
    #[OA\Delete(
        summary: 'Delete a user',
        parameters: [
            new OA\Parameter(
                name: "id",
                in: "path",
                required: true,
                description: "The ID of the user",
                schema: new OA\Schema(type: "integer")
            )
        ],
        responses: [
            new OA\Response(
                response: Response::HTTP_NO_CONTENT,
                description: "User deleted successfully.",
            ),
            new OA\Response(
                response: Response::HTTP_NOT_FOUND,
                description: "User not found",
            ),
        ]

    )]
    public function deleteComment(
        User $user,
        EntityManagerInterface $em
    ): JsonResponse {
        $em->remove($user);
        $em->flush();

        return new JsonResponse(
            null,
            Response::HTTP_NO_CONTENT
        );
    }
}
