<?php

namespace App\Controller;

use App\Entity\Fichero;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;
use Symfony\Component\HttpFoundation\File\UploadedFile;

#[IsGranted('ROLE_USER')]
final class UploadController extends AbstractController
{
    public function __construct(
        private readonly EntityManagerInterface $entityManager
    ) {}

    #[Route('/api/upload', name: 'api_upload', methods: ['POST'])]
    public function uploadFile(Request $request): JsonResponse
    {
        /** @var UploadedFile $uploadedFile */
        $uploadedFile = $request->files->get('file');
        
        if (!$uploadedFile) {
            return $this->json(['error' => 'No se ha subido ningún archivo'], 400);
        }
    
        // Validaciones básicas
        if ($uploadedFile->getSize() > 20 * 1024 * 1024) {
            return $this->json(['error' => 'El archivo excede el límite de 20MB'], 400);
        }
    
        $allowedMimeTypes = [
            // Documentos
            'application/pdf',
            'application/msword',
            'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
            // Imágenes
            'image/jpeg',
            'image/png',
            'image/webp',
            // Archivos web
            'text/html',
            'application/javascript',
            'text/javascript',
            // Archivos comprimidos
            'application/zip',
            'application/x-zip-compressed',
            // Audio/Video
            'video/mp4',
            'video/webm',
            'audio/mpeg',
            'audio/wav'
        ];

        if (!in_array($uploadedFile->getMimeType(), $allowedMimeTypes)) {
            return $this->json(['error' => 'Tipo de archivo no permitido'], 400);
        }
    
        $fichero = new Fichero();
        $fichero->setNombreOriginal($uploadedFile->getClientOriginalName());
        $fichero->setMimeType($uploadedFile->getMimeType());
        $fichero->setTamanio($uploadedFile->getSize());
        $fichero->setFechaSubida(new \DateTime());
        $fichero->setUsuario($this->getUser());
    
        // Generar nombre único y mover el archivo
        $newFilename = uniqid().'.'.$uploadedFile->guessExtension();
        $uploadDir = $this->getParameter('kernel.project_dir').'/public/uploads/cursos';
        $uploadedFile->move($uploadDir, $newFilename);
        
        $fichero->setRuta('/uploads/cursos/'.$newFilename);
    
        $this->entityManager->persist($fichero);
        $this->entityManager->flush();
    
        return $this->json([
            'id' => $fichero->getId(),
            'url' => $fichero->getRuta(),
            'nombreOriginal' => $fichero->getNombreOriginal()
        ], 201);
    }
}
