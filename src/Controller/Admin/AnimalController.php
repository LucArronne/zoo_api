<?php

namespace App\Controller\Admin;

use App\Entity\Animal;
use App\Entity\AnimalImage;
use App\Repository\HabitatRepository;
use App\Repository\RaceRepository;
use App\Utils\AnimalSerializer;
use App\Utils\FileUploader;
use Doctrine\ORM\EntityManagerInterface;
use InvalidArgumentException;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\File\Exception\FileException;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Serializer\Normalizer\AbstractNormalizer;
use Symfony\Component\Serializer\SerializerInterface;
use Symfony\Component\Validator\Exception\ValidationFailedException;
use Symfony\Component\Validator\Validator\ValidatorInterface;

#[Route(path: '/admin')]
class AnimalController extends AbstractController
{
    #[Route('/animals', name: 'createAnimal', methods: ['POST'])]
    public function createAnimal(
        Request $request,
        EntityManagerInterface $em,
        HabitatRepository $habitatRepository,
        RaceRepository $raceRepository,
        FileUploader $uploader,
        SerializerInterface $serializer,
        ValidatorInterface $validator,
        AnimalSerializer $animalSerializer,
    ): JsonResponse {

        if (!$request->get('data')) {
            return new JsonResponse(
                $serializer->serialize(
                    [
                        "status" => Response::HTTP_BAD_REQUEST,
                        "message" => "Argument validation failed",
                        'error' => "Add animal json object 'data' as key"
                    ],
                    'json'
                ),
                Response::HTTP_BAD_REQUEST,
                [],
                true,
            );
        }
        $data = json_decode($request->get('data'), true);

        $animal = new Animal();
        $animal->setName($data['name']);

        $race = $data['race'] ?? -1;
        $race = $raceRepository->find($race);
        $animal->setRace($race);

        $habitat = $data['habitat'] ?? -1;
        $habitat = $habitatRepository->find($habitat);
        $animal->setHabitat($habitat);

        $violations = $validator->validate($animal);

        if ($violations->count() > 0) {
            throw new ValidationFailedException($animal, $violations);
        }

        if ($request->files->get("images")) {

            $allowedExtensions = ['jpg', 'jpeg', 'png'];

            foreach ($request->files->get("images") as $image) {
                try {
                    $imageFileName = $uploader->upload(
                        $image,
                        $allowedExtensions
                    );
                    $animalImage = new AnimalImage();
                    $animalImage->setPath($imageFileName);
                    $animal->addImage($animalImage);
                } catch (InvalidArgumentException $e) {
                    return new JsonResponse(
                        $serializer->serialize(
                            [
                                "status" => Response::HTTP_BAD_REQUEST,
                                "message" => "File validation failed",
                                'error' => 'Invalid file type, only ' . join(",", $allowedExtensions)
                                    . ' are allowed.'
                            ],
                            'json'
                        ),
                        Response::HTTP_BAD_REQUEST,
                        [],
                        true,
                    );
                } catch (FileException $e) {
                    return new JsonResponse(
                        $serializer->serialize(
                            [
                                "status" => Response::HTTP_INTERNAL_SERVER_ERROR,
                                "message" => 'Upload failed',
                                "error" => $e->getMessage(),
                            ],
                            'json'
                        ),
                        Response::HTTP_INTERNAL_SERVER_ERROR,
                        [],
                        true,
                    );
                }
            }
        }

        $em->persist($animal);
        $em->flush();

        return new JsonResponse(
            $serializer->serialize(
                $animalSerializer->serialize($animal),
                'json'
            ),
            Response::HTTP_CREATED,
            [],
            true,
        );
    }

    #[Route('/animals/{id}', name: 'updateAnimal', methods: ['POST'])]
    public function updateAnimal(
        Request $request,
        Animal $currentAnimal,
        EntityManagerInterface $em,
        HabitatRepository $habitatRepository,
        RaceRepository $raceRepository,
        FileUploader $uploader,
        SerializerInterface $serializer,
        ValidatorInterface $validator,
        AnimalSerializer $animalSerializer,
    ): JsonResponse {

        $updatedAnimal = $currentAnimal;

        if ($request->get('data')) {
            $data = json_decode($request->get('data'), true);

            if (array_key_exists('name', $data)) {
                $updatedAnimal->setName($data['name']);
            }
            if (array_key_exists('race', $data)) {
                $race = $raceRepository->find($data['race']);
                $updatedAnimal->setRace($race);
            }
            if (array_key_exists('habitat',  $data)) {
                $habitat = $habitatRepository->find($data['habitat']);
                $updatedAnimal->setHabitat($habitat);
            }

            $violations = $validator->validate($updatedAnimal);

            if ($violations->count() > 0) {
                throw new ValidationFailedException($updatedAnimal, $violations);
            }
        }

        if ($request->files->get("images")) {

            $allowedExtensions = ['jpg', 'jpeg', 'png'];

            foreach ($request->files->get("images") as $image) {
                try {
                    $imageFileName = $uploader->upload(
                        $image,
                        $allowedExtensions
                    );
                    $animalImage = new AnimalImage();
                    $animalImage->setPath($imageFileName);
                    $updatedAnimal->addImage($animalImage);
                } catch (InvalidArgumentException $e) {
                    return new JsonResponse(
                        $serializer->serialize(
                            [
                                "status" => Response::HTTP_BAD_REQUEST,
                                "message" => "File validation failed",
                                'error' => 'Invalid file type, only ' . join(",", $allowedExtensions)
                                    . ' are allowed.'
                            ],
                            'json'
                        ),
                        Response::HTTP_BAD_REQUEST,
                        [],
                        true,
                    );
                } catch (FileException $e) {
                    return new JsonResponse(
                        $serializer->serialize(
                            [
                                "status" => Response::HTTP_INTERNAL_SERVER_ERROR,
                                "message" => 'Upload failed',
                                "error" => $e->getMessage(),
                            ],
                            'json'
                        ),
                        Response::HTTP_INTERNAL_SERVER_ERROR,
                        [],
                        true,
                    );
                }
            }
        }

        $em->persist($updatedAnimal);
        $em->flush();

        return new JsonResponse(
            $serializer->serialize(
                $animalSerializer->serialize($updatedAnimal),
                'json'
            ),
            Response::HTTP_OK,
            [],
            true,
        );
    }

    #[Route(path: '/animals/{id}', name: 'deleteAnimal', methods: ['DELETE'])]
    public function deleteAnimal(
        Animal $animal,
        EntityManagerInterface $em
    ): JsonResponse {
        $em->remove($animal);
        $em->flush();
        return new JsonResponse(
            null,
            Response::HTTP_NO_CONTENT
        );
    }
}
