<?php

namespace App\Controller\Admin;

use App\Entity\Testamonial;
use Doctrine\ORM\EntityManagerInterface;
use Psr\Log\LoggerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;
use Symfony\Component\Serializer\SerializerInterface;

#[Route('/api/testimonial')]
#[IsGranted('ROLE_ADMIN')]
class TestimonialAdminController extends AbstractController
{
    private EntityManagerInterface $entityManager;
    private LoggerInterface $logger;

    public function __construct(EntityManagerInterface $entityManager, LoggerInterface $logger)
    {
        $this->entityManager = $entityManager;
        $this->logger = $logger;
    }

    #[Route('/list', methods: ['GET'])]
    public function list(Request $request, SerializerInterface $serializer): JsonResponse
    {
        try {
            $offset = $request->query->get('offset');
            $limit = $request->query->get('limit');

            $testimonial = $this->entityManager->getRepository(Testamonial::class)->findAllOrdered($limit, $offset);
            $dataTestimonial = $serializer->normalize($testimonial, 'json', ['groups' => ['testimonial']]);
            return new JsonResponse($dataTestimonial, Response::HTTP_OK);
        } catch(\Throwable $e) {
            $this->logger->error('Erreur de la récupération des avis utilisateurs', ['error' => $e->getMessage()]);
            return new JsonResponse(['error' => $e->getMessage()], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    #[Route('/search', methods: ['GET'])]
    public function search(Request $request, SerializerInterface $serializer): JsonResponse
    {
        try {
            $search = $request->query->get('search');

            $testimonial = $this->entityManager->getRepository(Testamonial::class)->findAllSearch($search);
            $dataTestimonial = $serializer->normalize($testimonial, 'json', ['groups' => ['testimonial']]);

            return new JsonResponse($dataTestimonial, Response::HTTP_OK);
        } catch(\Throwable $e) {
            $this->logger->error('Erreur de la récupération des avis utilisateurs', ['error' => $e->getMessage()]);
            return new JsonResponse(['error' => $e->getMessage()], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    #[Route('/{id}', methods: ['GET'])]
    public function id(int $id, SerializerInterface $serializer): JsonResponse
    {
        try {
            $testimonial = $this->entityManager->getRepository(Testamonial::class)->find($id);

            $testimonial->setIsRead(true);
            $this->entityManager->persist($testimonial);
            $this->entityManager->flush();

            $dataTestimonial = $serializer->normalize($testimonial, 'json', ['groups' => ['testimonial']]);
            return new JsonResponse($dataTestimonial, Response::HTTP_OK);
        } catch(\Throwable $e) {
            $this->logger->error('Erreur de la récupération des avis utilisateurs', ['error' => $e->getMessage()]);
            return new JsonResponse(['error' => $e->getMessage()], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    #[Route('/{id}/toggle', name: 'toggle', methods: ['GET'])]
    public function toggle(Testamonial $testimonial): Response
    {
        try {
            $testimonial->setIsPublished(!$testimonial->isPublished());

            $this->entityManager->persist($testimonial);
            $this->entityManager->flush();

            return new JsonResponse([
                'success' => true,
                'isPublished' => $testimonial->isPublished(),
            ]);
        } catch(\Throwable $e) {
            return new JsonResponse(['error' => $e->getMessage()], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }
}
