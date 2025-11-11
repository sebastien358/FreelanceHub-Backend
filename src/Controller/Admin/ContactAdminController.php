<?php

namespace App\Controller\Admin;

use App\Entity\Contact;
use Doctrine\ORM\EntityManagerInterface;
use Psr\Log\LoggerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;
use Symfony\Component\Serializer\SerializerInterface;

#[Route('/api/admin')]
#[IsGranted('ROLE_ADMIN')]
class ContactAdminController extends AbstractController
{
    private EntityManagerInterface $entityManager;
    private LoggerInterface $logger;

    public function __construct(
        EntityManagerInterface $entityManager, LoggerInterface $logger, )
    {
        $this->entityManager = $entityManager;
        $this->logger = $logger;
    }
    #[Route('/contacts', methods: ['GET'])]
    public function list(Request $request, SerializerInterface $serializer): JsonResponse
    {
        try {
            $limit = $request->query->getInt('limit', 4);
            $offset = $request->query->getInt('offset', 0);

            $contacts = $this->entityManager->getRepository(Contact::class)->findAllOrdered($limit, $offset);
            $dataContacts = $serializer->normalize($contacts, 'json', ['groups' => ['contacts']]);

            return new JsonResponse($dataContacts, Response::HTTP_OK);
        } catch (\Throwable $e) {
            $this->logger->error('Erreur récupération des messages : ', ['error' => $e->getMessage()]);
            return new JsonResponse(['error' => $e->getMessage()], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    #[Route('/contact/{id}', methods: ['GET'])]
    public function details(int $id, SerializerInterface $serializer): JsonResponse
    {
        try {
            $contact = $this->entityManager->getRepository(Contact::class)->find($id);

            $contact->setIsRead(true);
            $this->entityManager->persist($contact);
            $this->entityManager->flush();

            $dataContacts = $serializer->normalize($contact, 'json', ['groups' => ['contact']]);
            return new JsonResponse($dataContacts, Response::HTTP_OK);
        } catch (\Throwable $e) {
            $this->logger->error('Erreur récupération des messages : ', ['error' => $e->getMessage()]);
            return new JsonResponse(['error' => $e->getMessage()], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    #[Route('/contact/delete/{id}', methods: ['DELETE'])]
    public function delete(int $id, Request $request): JsonResponse
    {
        try {
            $contact = $this->entityManager->getRepository(Contact::class)->find($id);
            if (!$contact) {
                return new JsonResponse(['error' => 'Contact not found'], Response::HTTP_NOT_FOUND);
            }
            $this->entityManager->remove($contact);
            $this->entityManager->flush();
            return new JsonResponse(['success' => true], Response::HTTP_OK);
        } catch (\Throwable $e) {
            $this->logger->error('Erreur de la suppression d\'un message : ', ['error' => $e->getMessage()]);
            return new JsonResponse(['error' => $e->getMessage()], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    #[Route('/contacts/search', methods: ['GET'])]
    public function search(Request $request, SerializerInterface $serializer): JsonResponse
    {
        try {
            $search = $request->query->get('search');

            $contacts = $this->entityManager->getRepository(Contact::class)->findAllContactSearch($search);
            $dataContacts = $serializer->normalize($contacts, 'json', ['groups' => ['contacts']]);
            return new JsonResponse($dataContacts, Response::HTTP_OK);
        } catch (\Throwable $e) {
            $this->logger->error('Erreur récupération search messages : ', ['error' => $e->getMessage()]);
            return new JsonResponse(['error' => $e->getMessage()], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }
}
