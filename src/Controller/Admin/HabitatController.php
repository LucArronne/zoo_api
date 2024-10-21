<?php

namespace App\Controller\Admin;

use App\Entity\Habitat;
use App\Entity\HabitatImage;
use App\Utils\FileUploader;
use App\Utils\HabitatSerializer;
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
class HabitatController extends AbstractController
{
    #[Route('/habitats', name: 'createHabitat', methods: ['POST'])]
    public function createHabitat(
        Request $request,
        EntityManagerInterface $em,
        FileUploader $uploader,
        SerializerInterface $serializer,
        ValidatorInterface $validator,
        HabitatSerializer $habitatSerializer,
    ): JsonResponse {

        $habitat = new Habitat();
        $habitat->setName($request->get('name'));
        $habitat->setDescription($request->get('description'));

        $violations = $validator->validate($habitat);

        if ($violations->count() > 0) {
            throw new ValidationFailedException($habitat, $violations);
        }

        $habitatImages = $request->files->get("files");

        if ($habitatImages) {

            $allowedExtensions = ['jpg', 'jpeg', 'png'];

            foreach ($habitatImages as $image) {
                try {
                    $imageFileName = $uploader->upload($image, $allowedExtensions);
                    $habitatImage = new HabitatImage();
                    $habitatImage->setPath($imageFileName);
                    $habitat->addImage($habitatImage);
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

        $em->persist($habitat);
        $em->flush();

        $result = $serializer->serialize($habitatSerializer->serialize($habitat), 'json');

        return new JsonResponse(
            $result,
            Response::HTTP_CREATED,
            [],
            true,
        );
    }
}
