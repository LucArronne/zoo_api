<?php

namespace App\Controller\Admin;

use App\Entity\User;
use App\Repository\RoleRepository;
use App\Repository\UserRepository;
use Doctrine\ORM\EntityManagerInterface;
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

#[Route(path: '/admin')]
class UserController extends AbstractController
{
    #[Route('/users', name: 'users', methods: ['GET'])]
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
            Response::HTTP_CREATED,
            [],
            true
        );
    }

    #[Route('/users/{id}', name: 'deleteUser', methods: ['DELETE'])]
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
