<?php

namespace App\Controller;

use App\Entity\Usuario;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[Route('/api')]
class DashboardController extends AbstractController
{
    #[Route('/dashboard', name: 'api_dashboard', methods: ['GET'])]
    #[IsGranted('ROLE_USER')]
    public function index(): JsonResponse
    {
        /** @var Usuario $user */
        $user = $this->getUser();

        return $this->json([
            'message' => 'Bienvenido a tu dashboard',
            'user' => [
                'id' => $user->getId(),
                'email' => $user->getEmail(),
                'nombre' => $user->getNombre(),
                'apellido' => $user->getApellido(),
                'roles' => $user->getRoles()
            ]
        ]);
    }
} 