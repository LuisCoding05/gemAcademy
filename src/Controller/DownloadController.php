<?php

namespace App\Controller;

use App\Entity\Fichero;
use App\Service\FileService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\BinaryFileResponse;
use Symfony\Component\HttpFoundation\ResponseHeaderBag;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[IsGranted('ROLE_USER')]
final class DownloadController extends AbstractController
{
    public function __construct(
        private readonly EntityManagerInterface $entityManager,
        private readonly FileService $fileService
    ) {}

    #[Route('/api/download/{id}', name: 'app_download_file', methods: ['GET'])]
    public function downloadFile(int $id): BinaryFileResponse
    {
        // Obtener el fichero
        $fichero = $this->entityManager->getRepository(Fichero::class)->find($id);
        
        if (!$fichero) {
            throw $this->createNotFoundException('Archivo no encontrado');
        }

        // Obtener la ruta completa del archivo
        $filePath = $this->getParameter('kernel.project_dir') . '/public' . $fichero->getRuta();
        
        if (!file_exists($filePath)) {
            throw $this->createNotFoundException('Archivo no encontrado en el sistema');
        }

        $response = new BinaryFileResponse($filePath);
        
        // Forzar la descarga con el nombre original del archivo
        $response->setContentDisposition(
            ResponseHeaderBag::DISPOSITION_ATTACHMENT,
            $fichero->getNombreOriginal()
        );

        return $response;
    }
}