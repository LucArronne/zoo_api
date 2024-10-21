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

        $animal = new Animal();
        $animal->setName($request->get('name'));


        $raceId = $request->get('race') ?? '';
        $race = $raceRepository->find($raceId);
        $animal->setRace($race);

        $habitatId = $request->get('habitat') ?? '';
        $habitat = $habitatRepository->find($habitatId);
        $animal->setHabitat($habitat);

        $violations = $validator->validate($animal);

        if ($violations->count() > 0) {
            throw new ValidationFailedException($$animal, $violations);
        }

        $animalImages = $request->files->get("files");

        if ($animalImages) {

            $allowedExtensions = ['jpg', 'jpeg', 'png'];

            foreach ($animalImages as $image) {
                try {
                    $imageFileName = $uploader->upload($image, $allowedExtensions);
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
                                "message" => $e->getMessage(),
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

        $result = $serializer->serialize($animalSerializer->serialize($animal), 'json');

        return new JsonResponse(
            $result,
            Response::HTTP_CREATED,
            [],
            true,
        );
    }
}
