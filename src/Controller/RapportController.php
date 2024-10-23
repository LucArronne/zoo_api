<?php

namespace App\Controller;

use App\Entity\AnimalRapport;
use App\Repository\AnimalRapportRepository;
use App\Repository\AnimalRepository;
use App\Repository\UserRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;
use Symfony\Component\Serializer\SerializerInterface;
use Symfony\Component\Validator\Exception\ValidationFailedException;
use Symfony\Component\Validator\Validator\ValidatorInterface;

class RapportController extends AbstractController
{
    #[Route('/rapports', name: 'createRapport', methods: ['POST'])]
    #[IsGranted('ROLE_VETERNARY', message: 'Access denied')]
    public function createRapport(
        Request $request,
        EntityManagerInterface $em,
        SerializerInterface $serializer,
        UserRepository $userRepository,
        AnimalRepository $animalRepository,
        ValidatorInterface $validator,
    ): JsonResponse {

        $rapport = $serializer->deserialize(
            $request->getContent(),
            AnimalRapport::class,
            'json'
        );

        $rapport->setUser($this->getUser());

        $animal = $animalRepository->find($request->toArray()['animal'] ?? '');
        $rapport->setAnimal($animal);

        $violations = $validator->validate($rapport);

        if ($violations->count() > 0) {
            throw new ValidationFailedException($rapport, $violations);
        }

        $em->persist($rapport);
        $em->flush();

        return new JsonResponse(
            $serializer->serialize($rapport, 'json', ['groups' => 'getRapports']),
            Response::HTTP_CREATED,
            [],
            true,
        );
    }

    #[Route('/rapports', name: 'rapports', methods: ['GET'])]
    #[IsGranted('ROLE_VETERNARY', message: 'Access denied')]
    public function getRapports(
        SerializerInterface $serializer,
        AnimalRapportRepository $animalRapportRepository,
    ): JsonResponse {

        $result = $serializer->serialize(
            $animalRapportRepository->findAll(),
            'json',
            ['groups' => 'getRapports']
        );

        return new JsonResponse(
            $result,
            Response::HTTP_CREATED,
            [],
            true,
        );
    }
}
