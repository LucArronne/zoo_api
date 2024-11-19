<?php

namespace App\Controller;

use App\Dto\AnimalDto;
use App\Dto\HabitatDto;
use App\Entity\Comment;
use App\Entity\Email as EntityEmail;
use App\Entity\Service;
use App\Repository\AnimalImageRepository;
use App\Repository\AnimalRepository;
use App\Repository\CommentRepository;
use App\Repository\HabitatImageRepository;
use App\Repository\HabitatRepository;
use App\Repository\ServiceRepository;
use App\Utils\AnimalMapper;
use App\Utils\FileUploader;
use App\Utils\HabitatMapper;
use Doctrine\ORM\EntityManagerInterface;
use Nelmio\ApiDocBundle\Annotation\Model;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Serializer\SerializerInterface;
use Symfony\Component\Validator\Exception\ValidationFailedException;
use Symfony\Component\Validator\Validator\ValidatorInterface;
use OpenApi\Attributes as OA;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Mime\Email;

#[Route(path: '/home')]
#[OA\Tag(name: 'Visitor')]
class HomeController extends AbstractController
{
    #[Route(path: '/random-images', name: 'randomImages', methods: ['GET'])]
    #[OA\Get(
        summary: 'Random images',
        description: 'Get a random images of habitats and animals in the zoo',
        security: [],
        responses: [
            new OA\Response(
                response: Response::HTTP_OK,
                description: 'Return a random or empty list path',
                content: new OA\JsonContent(
                    type: 'array',
                    items: new OA\Items(
                        type: 'string'
                    ),
                )
            )
        ]
    )]
    public function getImages(
        HabitatImageRepository $habitatImageRepository,
        AnimalImageRepository $animalImageRepository,
        SerializerInterface $serializer,
        FileUploader $uploader,
    ): JsonResponse {

        $result = array_map(function ($image) use ($uploader) {
            return $uploader->getFilePublicUrl($image["path"]);
        }, [...$animalImageRepository->findRandomImages(), ...$habitatImageRepository->findRandomImages()]);


        return new JsonResponse(
            $serializer->serialize($result, 'json'),
            Response::HTTP_OK,
            [],
            true,
        );
    }

    #[Route(path: '/habitats', name: 'habitats', methods: ['GET'])]
    #[OA\Get(
        summary: 'Get the habitat list',
        security: [],
        responses: [
            new OA\Response(
                response: Response::HTTP_OK,
                description: 'Return the habitat list',
                content: new OA\JsonContent(
                    type: 'array',
                    items: new OA\Items(
                        ref: new Model(type: HabitatDto::class),
                    ),
                )
            )
        ]
    )]
    public function getHabitas(
        HabitatRepository $habitatRepository,
        SerializerInterface $serializer,
        HabitatMapper $habitatSerializer
    ): JsonResponse {

        $result = $habitatSerializer->convertToDtoArray($habitatRepository->findAll());

        return new JsonResponse(
            $serializer->serialize($result, 'json'),
            Response::HTTP_OK,
            [],
            true,
        );
    }

    #[Route('/animals', name: 'animals', methods: ['GET'])]
    #[OA\Get(
        summary: 'Get the animal list',
        security: [],
        responses: [
            new OA\Response(
                response: Response::HTTP_OK,
                description: 'Return the animal list',
                content: new OA\JsonContent(
                    type: 'array',
                    items: new OA\Items(
                        ref: new Model(type: AnimalDto::class),
                    ),
                )
            )
        ]
    )]
    public function getAnimals(
        AnimalRepository $animalRepository,
        SerializerInterface $serializer,
        AnimalMapper $animalSerializer,
    ): JsonResponse {

        $result = $animalSerializer->convertToDtoArray($animalRepository->findAll());

        return new JsonResponse(
            $serializer->serialize($result, 'json'),
            Response::HTTP_OK,
            [],
            true,
        );
    }


    #[Route('/services', name: 'services', methods: ['GET'])]
    #[OA\Get(
        summary: 'Get the service list',
        security: [],
        responses: [
            new OA\Response(
                response: Response::HTTP_OK,
                description: 'Return the service list',
                content: new OA\JsonContent(
                    type: 'array',
                    items: new OA\Items(
                        ref: new Model(type: Service::class),
                    ),
                )
            )
        ]
    )]
    public function getServices(
        ServiceRepository $serviceRepository,
        SerializerInterface $serializer,
        FileUploader $uploader,
    ): JsonResponse {

        $result = array_map(function (Service $value) use ($uploader): Service {
            if ($value->getImage()) {
                $value->setImage($uploader->getFilePublicUrl($value->getImage()));
            }
            return $value;
        }, $serviceRepository->findAll());

        return new JsonResponse(
            $serializer->serialize($result, 'json'),
            Response::HTTP_OK,
            [],
            true,
        );
    }

    #[Route('/comments', name: 'approuved-comments', methods: ['GET'])]
    #[OA\Get(
        summary: 'Get the approuved comment list',
        security: [],
        responses: [
            new OA\Response(
                response: Response::HTTP_OK,
                description: 'Return the approuved comment list',
                content: new OA\JsonContent(
                    type: 'array',
                    items: new OA\Items(
                        ref: new Model(type: Comment::class),
                    ),
                )
            )
        ]
    )]
    public function getApprouvedComments(CommentRepository $commentRepository, SerializerInterface $serializer): JsonResponse
    {
        $jsonList = $serializer->serialize($commentRepository->findValidComments(), 'json');

        return new JsonResponse(
            $jsonList,
            Response::HTTP_OK,
            [],
            true
        );
    }

    #[Route('/comments', name: 'createComment', methods: ['POST'])]
    #[OA\Post(
        summary: 'Add a new comment',
        security: [],
        requestBody: new OA\RequestBody(
            required: true,
            description: 'Comment data in json format ',
            content: new OA\JsonContent(
                type: 'object',
                properties: [
                    new OA\Property(
                        property: "pseudo",
                        type: "string",
                        example: "John Doe"
                    ),
                    new OA\Property(
                        property: "text",
                        type: "text",
                        example: "Cool website"
                    ),
                ],
            ),
        ),
        responses: [
            new OA\Response(
                response: Response::HTTP_CREATED,
                description: "Comment created successfully.",
                content: new OA\JsonContent(ref: new Model(type: Comment::class)),
            ),
            new OA\Response(
                response: Response::HTTP_BAD_REQUEST,
                description: "Invalid input data or validation error.",
            )
        ]

    )]
    public function createComment(
        Request $request,
        SerializerInterface $serializer,
        EntityManagerInterface $em,
        ValidatorInterface $validator,
    ): JsonResponse {
        $comment = $serializer->deserialize($request->getContent(), Comment::class, 'json');

        $violations = $validator->validate($comment);

        if ($violations->count() > 0) {
            throw new ValidationFailedException($comment, $violations);
        }

        $comment->setVisible(false);
        $em->persist($comment);
        $em->flush();

        return new JsonResponse(
            $serializer->serialize($comment, 'json'),
            Response::HTTP_CREATED,
            [],
            true
        );
    }

    #[Route('/send-email', name: 'sendEmail', methods: ['POST'])]
    #[OA\Post(
        summary: 'Send email',
        security: [],
        requestBody: new OA\RequestBody(
            required: true,
            description: 'Email data in json format ',
            content: new OA\JsonContent(
                type: 'object',
                properties: [
                    new OA\Property(
                        property: "adress",
                        type: "email",
                        example: "demo@example.com"
                    ),
                    new OA\Property(
                        property: "subject",
                        type: "string",
                        example: "subject"
                    ),
                    new OA\Property(
                        property: "text",
                        type: "string",
                        example: "text"
                    ),
                    new OA\Property(
                        property: "name",
                        type: "string",
                        example: "name"
                    ),
                ],
            ),
        ),
        responses: [
            new OA\Response(
                response: Response::HTTP_CREATED,
                description: "Email sent successfully.",
            ),
            new OA\Response(
                response: Response::HTTP_BAD_REQUEST,
                description: "Invalid input data or validation error.",
            )
        ]

    )]
    public function sendEmail(
        Request $request,
        EntityManagerInterface $em,
        MailerInterface $mailer,
        SerializerInterface $serializer,
        ValidatorInterface $validator,
    ): Response {
        $entityEmail = $serializer->deserialize(
            $request->getContent(),
            EntityEmail::class,
            'json'
        );

        $violations = $validator->validate($entityEmail);

        if ($violations->count() > 0) {
            throw new ValidationFailedException($entityEmail, $violations);
        }

        $email = (new Email())
            ->from('tsiorymauyz@gmail.com')
            ->to('tsiorymauyz@gmail.com', 'hajarjh@yahoo.fr')
            ->subject($entityEmail->getSubject())
            ->html('<div>
            <p>Message de <b>' . ucwords($entityEmail->getName() ?? 'anonyme') . '</b> par ' . $entityEmail->getAdress() . '</p>
            <p>' . $entityEmail->getText() . '</p></div>');

        $mailer->send($email);

        $em->persist($entityEmail);
        $em->flush();

        return new JsonResponse(
            "Email sent successfully",
            Response::HTTP_CREATED,
            [],
            false
        );
    }
}
