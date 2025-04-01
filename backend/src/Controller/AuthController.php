<?php

namespace App\Controller;

use App\Entity\Usuario;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Lexik\Bundle\JWTAuthenticationBundle\Services\JWTTokenManagerInterface;
use Symfony\Component\Security\Http\Authentication\AuthenticationUtils;
use Psr\Log\LoggerInterface;
use App\Entity\Log;

class AuthController extends AbstractController
{
    public function __construct(
        private readonly EntityManagerInterface $entityManager,
        private readonly UserPasswordHasherInterface $passwordHasher,
        private readonly LoggerInterface $logger
    ) {}

    #[Route('/api/login', name: 'api_login', methods: ['POST'])]
    public function login(Request $request, JWTTokenManagerInterface $JWTManager): JsonResponse
    {
        $data = json_decode($request->getContent(), true);
        $this->logger->info('Datos recibidos:', $data);
        
        if (!isset($data['email']) || !isset($data['password'])) {
            $this->logger->error('Faltan campos requeridos', $data);
            return $this->json([
                'message' => 'Email y contraseña son requeridos'
            ], 400);
        }

        $user = $this->entityManager->getRepository(Usuario::class)->findOneBy(['email' => $data['email']]);
        $this->logger->info('Usuario encontrado:', ['user' => $user ? $user->getId() : null]);

        if (!$user || !$this->passwordHasher->isPasswordValid($user, $data['password'])) {
            $this->logger->error('Credenciales inválidas');
            return $this->json([
                'message' => 'Credenciales inválidas'
            ], 401);
        }

        // Registrar el log de inicio de sesión
        $log = new Log();
        $log->setUsuario($user);
        $this->entityManager->persist($log);
        $this->entityManager->flush();

        $token = $JWTManager->create($user);
        $this->logger->info('Token generado correctamente');

        return $this->json([
            'token' => $token,
            'user' => [
                'id' => $user->getId(),
                'email' => $user->getEmail(),
                'nombre' => $user->getNombre(),
                'apellido' => $user->getApellido(),
                'roles' => $user->getRoles()
            ]
        ]);
    }

    #[Route('/api/register', name: 'api_register', methods: ['POST'])]
    public function register(Request $request): JsonResponse
    {
        $data = json_decode($request->getContent(), true);

        if (!isset($data['email']) || !isset($data['password']) || !isset($data['nombre']) || !isset($data['apellido'])) {
            return $this->json([
                'message' => 'Todos los campos son requeridos'
            ], 400);
        }

        $existingUser = $this->entityManager->getRepository(Usuario::class)->findOneBy(['email' => $data['email']]);

        if ($existingUser) {
            return $this->json([
                'message' => 'El email ya está registrado'
            ], 400);
        }

        $user = new Usuario();
        $user->setEmail($data['email']);
        $user->setNombre($data['nombre']);
        $user->setApellido($data['apellido']);
        
        $hashedPassword = $this->passwordHasher->hashPassword($user, $data['password']);
        $user->setPassword($hashedPassword);

        $this->entityManager->persist($user);
        $this->entityManager->flush();

        return $this->json([
            'message' => 'Usuario registrado exitosamente',
            'user' => [
                'id' => $user->getId(),
                'email' => $user->getEmail(),
                'nombre' => $user->getNombre(),
                'apellido' => $user->getApellido()
            ]
        ], 201);
    }
} 