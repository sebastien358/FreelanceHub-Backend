<?php

namespace App\Controller;

use App\Entity\Picture;
use App\Entity\Testamonial;
use App\Form\TestamonialType;
use App\Services\UploadFile;
use Doctrine\ORM\EntityManagerInterface;
use Psr\Log\LoggerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;


#[Route('/api/testamonial')]
class TestamonialController extends AbstractController
{
    private EntityManagerInterface $entityManager;
    private UploadFile $uploadFile;
    private LoggerInterface $logger;

    public function __construct(
        EntityManagerInterface $entityManager, UploadFile $uploadFile, LoggerInterface $logger)
    {

        $this->entityManager = $entityManager;
        $this->uploadFile = $uploadFile;
        $this->logger = $logger;
    }

    #[Route('/add', methods: ['POST'])]
    public function add(Request $request): JsonResponse
    {
        try {
            $testamonial = new Testamonial();

            $form = $this->createForm(TestamonialType::class, $testamonial);
            $data = $request->request->all();
            $form->submit($data);

            if (!$form->isSubmitted() || !$form->isValid()) {
                $errors = $this->getErrorMessages($form);
                return new JsonResponse(['error' => $errors], Response::HTTP_BAD_REQUEST);
            }

            $image = $request->files->get("filename");

            if ($image) {
                $filename = $this->uploadFile->upload($image);

                $picture = new Picture();
                $picture->setFilename($filename);
                $picture->setTestamonial($testamonial);

                $this->entityManager->persist($picture);
            }

            $this->entityManager->persist($testamonial);
            $this->entityManager->flush();

            return new JsonResponse(['success' => true, 'message' => 'L\'avis a bien été envoyé'], Response::HTTP_CREATED);
        } catch(\Throwable $e) {
            $this->logger->error('Erreur de l\'ajout d\'un avis', ['error' => $e->getMessage()]);
            return $this->json(['error' => $e->getMessage()], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    private function getErrorMessages(FormInterface $form): array
    {
        $errors = [];
        foreach ($form->getErrors() as $key => $error) {
            $errors[] = $error->getMessage();
        }
        foreach ($form->all() as $child) {
            if ($child->isSubmitted() && !$child->isValid()) {
                $errors[$child->getName()] = $this->getErrorMessages($child);
            }
        }
        return $errors;
    }
}
