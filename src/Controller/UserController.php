<?php

namespace App\Controller;

use App\Entity\User;
use App\Services\MailerProvider;
use Doctrine\ORM\EntityManagerInterface;
use Psr\Log\LoggerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;
use Symfony\Component\Serializer\SerializerInterface;

#[Route('/api/user', methods: ['GET'])]
final class UserController extends AbstractController
{
    private LoggerInterface $logger;
    private EntityManagerInterface $entityManager;
    private MailerProvider $mailerProvider;
    private UserPasswordHasherInterface $passwordHasher;
    private SerializerInterface $serializer;

    public function __construct(
        LoggerInterface $logger, EntityManagerInterface $entityManager, MailerProvider $mailerProvider,
        UserPasswordHasherInterface $passwordHasher, SerializerInterface $serializer)
    {
        $this->entityManager = $entityManager;
        $this->mailerProvider = $mailerProvider;
        $this->logger = $logger;
        $this->passwordHasher = $passwordHasher;
        $this->serializer = $serializer;
    }

    #[Route('/me', methods: ['GET'])]
    #[IsGranted('ROLE_ADMIN')]
    public function me(): JsonResponse
    {
        try {
            $user = $this->getUser();
            if (!$user) {
                return $this->json(['message' => 'User not found'], Response::HTTP_NOT_FOUND);
            }
            $dataUser = $this->serializer->normalize($user, 'json', ['groups' => 'user']);
            return new JsonResponse(['success' => true, 'user' => $dataUser], Response::HTTP_OK);
        } catch(\Throwable $e) {
            $this->logger->error('Erreur de la récupération de l\'utilisateur connecté', ['error' => $e->getMessage()]);
            return new JsonResponse(['error' => $e->getMessage()], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    #[Route('/email-existing', methods: ['POST'])]
    public function existing(Request $request): JsonResponse
    {
        try {
            $data = json_decode($request->getContent(), true, 512, JSON_THROW_ON_ERROR);
            $email = $data['email'] ?? null;

            if (!$email) {
                return new JsonResponse(['error' => 'user no exists'], Response::HTTP_NOT_FOUND);
            }

            $emailExists = $this->entityManager->getRepository(User::class)->findOneBy(['email' => $email]);
            if ($emailExists) {
                return new JsonResponse(['exists' => true, 'message' => 'Email already exists'], Response::HTTP_OK);
            } else {
                return new JsonResponse(['exists' => false, 'message' => 'Email does not exist'], Response::HTTP_NO_CONTENT);
            }
        } catch(\Throwable $e) {
            $this->logger->error('Error email already exists', ['error' => $e->getMessage()]);
            return new JsonResponse(['error' => $e->getMessage()], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    #[Route('/request-password', methods: ['POST'])]
    public function request(Request $request): JsonResponse
    {
        try {
            $data = json_decode($request->getContent(), true, 512, JSON_THROW_ON_ERROR);
            $email = $data['email'] ?? null;

            if (!$email) {
                return new JsonResponse(['message' => 'Email manquant'], Response::HTTP_BAD_REQUEST);
            }

            $user = $this->entityManager->getRepository(User::class)->findOneBy(['email' => $email]);
            if (!$user) {
                return new JsonResponse(['message' => 'Utilisateur non trouvé'], Response::HTTP_NO_CONTENT);
            }

            $token = bin2hex(random_bytes(32));
            $user->setResetToken($token);
            $user->setResetTokenExpiresAt(new \DateTime('+1 hour'));

            $this->entityManager->flush();

            $url = $this->getParameter('frontend_url') . '/reset-password/' . $token;
            $body = $this->render('emails/reset-password.html.twig', [
                'url' => $url,
            ])->getContent();

            $this->mailerProvider->sendEmail($user->getEmail(), 'Réinitialisation de votre mot de passe', $body);

            return new JsonResponse([
                'success' => true,
                'message' => 'Un mail de réinitialisation a été envoyé à votre adresse e-mail.'
            ], Response::HTTP_CREATED);
        } catch (\Throwable $e) {
            $this->logger->error('Erreur lors de la demande de réinitialisation de mot de passe', ['error' => $e->getMessage(),]);
            return new JsonResponse(['error' => 'Une erreur est survenue : ' . $e->getMessage(),], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    #[Route('/reset-password/{token}', methods: ['POST'])]
    public function reset(string $token, Request $request,): JsonResponse
    {
        try {
            $data = json_decode($request->getContent(), true, 512, JSON_THROW_ON_ERROR);
            $newPassword = $data['password'] ?? null;

            if (empty($token)) {
                return new JsonResponse(['message' => 'Token introuvable'], Response::HTTP_BAD_REQUEST);
            }

            if (empty($newPassword)) {
                return new JsonResponse(['message' => 'Mot de passe introuvable'], Response::HTTP_BAD_REQUEST);
            }

            $user = $this->entityManager->getRepository(User::class)->findOneBy(['resetToken' => $token]);
            if (!$user) {
                return new JsonResponse(['message' => 'Utilisateur introuvable pour ce token.'], Response::HTTP_NOT_FOUND);
            }

            if ($user->getResetTokenExpiresAt() < new \DateTime()) {
                return new JsonResponse(['message' => 'Le lien de réinitialisation a expiré.'], Response::HTTP_GONE);
            }

            $user->setPassword($this->passwordHasher->hashPassword($user, $newPassword));
            $user->setResetToken(null);
            $user->setResetTokenExpiresAt(null);

            $this->entityManager->flush();
            return new JsonResponse(['success' => true, 'Mot de passe modifié avec succès'], Response::HTTP_CREATED);
        } catch (\Throwable $e) {
            $this->logger->error('Erreur lors de la demande de réinitialisation de mot de passe', ['error' => $e->getMessage(),]);
            return new JsonResponse(['error' => 'Une erreur est survenue : ' . $e->getMessage(),], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }
}
