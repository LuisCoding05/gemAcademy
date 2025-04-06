<?php

namespace App\Controller;

use App\Entity\Log;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

final class TestController extends AbstractController
{
    public function __construct(private readonly EntityManagerInterface $entityManager){
        
    }
    #[Route('/api/logs', name: 'app_logs')]
    #[IsGranted('ROLE_ADMIN')]
    public function index(): JsonResponse
    {
        $logs = $this->entityManager->getRepository(Log::class)->findAll();
        $logsArray = array_map(function(Log $log) {
            return [
                'fecha' => $log->getFecha()->format('d/m/Y H:i:s'),
                'usuario' => [
                    'nombre' => $log->getUsuario()->getNombre() . " " . $log->getUsuario()->getApellido(),
                    'email' => $log->getUsuario()->getEmail()
                ]

            ];
        }, $logs);
    
        return $this->json([
            "logs" => $logsArray
        ]);
    }
}
