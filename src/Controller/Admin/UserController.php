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
    public function create(
        Request $request,
        EntityManagerInterface $em,
        RoleRepository $roleRepository,
        SerializerInterface $serializer,
        UserPasswordHasherInterface $passwordHasher,
        ValidatorInterface $validator
    ): JsonResponse {

        $user = $serializer->deserialize($request->getContent(), User::class, 'json');

        $roleValue = strtoupper($request->toArray()['role'] ?? '');
        $role = $roleRepository->findOneByValue($roleValue);
        $user->setRole($role);

        $violations = $validator->validate($user);
        if ($violations->count() > 0) {
            throw new ValidationFailedException($user, $violations);
        }

        if ($roleValue === 'ROLE_ADMIN') {
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

        $user->setPassword($passwordHasher->hashPassword($user, $user->getPassword()));

        $em->persist($user);
        $em->flush();

        /// TODO send email after account creation

        $jsonResult = $serializer->serialize($user, 'json', ['groups' => 'getUsers']);

        return new JsonResponse($jsonResult, Response::HTTP_CREATED, [], true);
    }
}
