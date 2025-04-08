<?php

namespace App\Controller;

use App\Entity\Imagen;
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
use App\Service\EmailService;

class AuthController extends AbstractController
{
    public function __construct(
        private readonly EntityManagerInterface $entityManager,
        private readonly UserPasswordHasherInterface $passwordHasher,
        private readonly LoggerInterface $logger,
        private readonly EmailService $emailService
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

        if ($user && (!$user->isVerificado())) {
            $this->logger->error('Usuario no verificado');
            return $this->json([
                'message' => 'Usuario no verificado. Por favor, verifica tu cuenta con el código enviado a tu correo electrónico.'
            ], 401);
        }

        if ($user && ($user->isBanned())) {
            $this->logger->error('Usuario no verificado');
            return $this->json([
                'message' => 'No tienes acceso.'
            ], 401);
        }

        if (!$user || !$this->passwordHasher->isPasswordValid($user, $data['password'])) {
            $this->logger->error('Credenciales inválidas');
            return $this->json([
                'message' => 'Credenciales inválidas'
            ], 401);
        }

        $user->setUltimaConexion(new \DateTime());
        $user->setTokenVerificacion(null);
        // Registrar el log de inicio de sesión
        $log = new Log();
        $log->setUsuario($user);
        $this->entityManager->persist($log);
        $this->entityManager->flush();

        $token = $JWTManager->create($user);

        // Verificar el token generado
    try {
        $decodedToken = $JWTManager->parse($token);
        $this->logger->info('Token decodificado:', [
            'payload' => $decodedToken
        ]);
    } catch (\Exception $e) {
        $this->logger->error('Error al verificar token:', [
            'error' => $e->getMessage()
        ]);
    }
    // Verificar que el token sea válido
    if (!$token) {
        $this->logger->error('Error al generar el token');
        return $this->json([
            'message' => 'Error al generar el token de autenticación'
        ], 500);
    }

        $this->logger->info('Token generado correctamente');

        return $this->json([
            'token' => $token,
            'user' => [
                'id' => $user->getId(),
                'email' => $user->getEmail(),
                'nombre' => $user->getNombre(),
                'apellido' => $user->getApellido(),
                'roles' => $user->getRoles(),
                'verificado' => $user->isVerificado(),
                'imagen' => [
                    'url' => $user->getImagen() ? $user->getImagen()->getUrl() : './images/pfpgemacademy/default.webp'
                ]
            ]
        ]);
    }

    #[Route('/api/register', name: 'api_register', methods: ['POST'])]
    public function register(Request $request): JsonResponse
    {
        try {
            $data = json_decode($request->getContent(), true);
            $this->logger->info('Datos de registro recibidos:', ['data' => $data]);

            // Validar campos requeridos
            if (!isset($data['email']) || !isset($data['password']) || !isset($data['nombre']) || !isset($data['apellido'])) {
                return $this->json([
                    'message' => 'Todos los campos son requeridos'
                ], 400);
            }

            // Validar formato de email
            if (!filter_var($data['email'], FILTER_VALIDATE_EMAIL)) {
                return $this->json([
                    'message' => 'El formato del correo electrónico no es válido'
                ], 400);
            }

            // Validar longitud de la contraseña
            if (strlen($data['password']) < 6) {
                return $this->json([
                    'message' => 'La contraseña debe tener al menos 6 caracteres'
                ], 400);
            }

            // Verificar si el email ya existe
            $existingUser = $this->entityManager->getRepository(Usuario::class)->findOneBy(['email' => $data['email']]);
            if ($existingUser) {
                return $this->json([
                    'message' => 'El email ya está registrado'
                ], 400);
            }

            // Verificar si el username ya existe (si se proporciona)
            if (isset($data['username']) && $data['username']) {
                $existingUsername = $this->entityManager->getRepository(Usuario::class)->findOneBy(['username' => $data['username']]);
                if ($existingUsername) {
                    return $this->json([
                        'message' => 'El nombre de usuario ya está en uso'
                    ], 400);
                }
            }

            $user = new Usuario();
            $user->setEmail($data['email']);
            $user->setNombre($data['nombre']);
            $user->setApellido($data['apellido'] ?? "");
            $user->setApellido2($data['apellido2'] ?? null);
            $user->setUsername($data['username'] ?? $data['email']);
            // Asignamos la imagen por defecto
            $imagen = $this->entityManager->getRepository(Imagen::class)->findOneBy(['url' => './images/pfpgemacademy/default.webp']);
            $user->setImagen($imagen);

            $user->setBan(false);
            $user->setRoles(["ROLE_USER"]);
            
            $hashedPassword = $this->passwordHasher->hashPassword($user, $data['password']);
            $user->setPassword($hashedPassword);

            // Generar token de verificación
            $verificationCode = bin2hex(random_bytes(16));
            $user->setTokenVerificacion($verificationCode);
            $user->setVerificado(false);

            $this->entityManager->persist($user);
            $this->entityManager->flush();

            $this->emailService->sendVerificationEmail($user);

            $this->logger->info('Usuario registrado exitosamente', [
                'userId' => $user->getId(),
                'email' => $user->getEmail()
            ]);

            return $this->json([
                'message' => 'Usuario registrado exitosamente. Por favor, verifica tu cuenta con el código enviado a tu correo electrónico.',
                'user' => [
                    'id' => $user->getId(),
                    'email' => $user->getEmail(),
                    'nombre' => $user->getNombre(),
                    'apellido' => $user->getApellido()
                ]
            ], 201);

        } catch (\Exception $e) {
            $this->logger->error('Error en el registro de usuario', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            return $this->json([
                'message' => 'Error interno del servidor. Por favor, intenta nuevamente más tarde.'
            ], 500);
        }
    }

    #[Route('/api/verify', name: 'api_verify', methods: ['POST'])]
    public function verifyAccount(Request $request): JsonResponse
    {
        $data = json_decode($request->getContent(), true);
        $this->logger->info('Datos de verificación recibidos:', $data);

        if (!isset($data['email']) || !isset($data['verificationCode'])) {
            return $this->json([
                'message' => 'Email y código de verificación son requeridos'
            ], 400);
        }

        $user = $this->entityManager->getRepository(Usuario::class)->findOneBy(['email' => $data['email']]);

        if (!$user) {
            return $this->json([
                'message' => 'Usuario no encontrado'
            ], 404);
        }

        if ($user->getTokenVerificacion() !== $data['verificationCode']) {
            return $this->json([
                'message' => 'Código de verificación inválido'
            ], 400);
        }

        $user->setVerificado(true);
        $user->setTokenVerificacion(null);
        $this->entityManager->flush();

        $this->logger->info('Usuario verificado exitosamente', [
            'userId' => $user->getId(),
            'email' => $user->getEmail()
        ]);

        return $this->json([
            'message' => 'Cuenta verificada exitosamente',
            'user' => [
                'id' => $user->getId(),
                'email' => $user->getEmail(),
                'verificado' => true
            ]
        ]);
    }

    #[Route('/api/reset-password', name: 'api_reset_password', methods: ['POST'])]
    public function resetPassword(Request $request): JsonResponse
    {
        $data = json_decode($request->getContent(), true);

        if (!isset($data['email']) || !isset($data['verificationCode']) || !isset($data['newPassword'])) {
            return $this->json([
                'message' => 'Email, código de verificación y nueva contraseña son requeridos'
            ], 400);
        }

        $user = $this->entityManager->getRepository(Usuario::class)->findOneBy(['email' => $data['email']]);

        if (!$user) {
            return $this->json([
                'message' => 'Usuario no encontrado'
            ], 404);
        }

        if ($user->getTokenVerificacion() !== $data['verificationCode']) {
            return $this->json([
                'message' => 'Código de verificación inválido'
            ], 400);
        }

        $hashedPassword = $this->passwordHasher->hashPassword($user, $data['newPassword']);
        $user->setPassword($hashedPassword);
        $user->setTokenVerificacion(null);
        $this->entityManager->flush();

        return $this->json([
            'message' => 'Contraseña actualizada exitosamente'
        ]);
    }

    #[Route('/api/send-verification', name: 'api_send_verification', methods: ['POST'])]
    public function sendVerificationCode(Request $request): JsonResponse
    {
        $data = json_decode($request->getContent(), true);

        if (!isset($data['email'])) {
            return $this->json([
                'message' => 'Email es requerido'
            ], 400);
        }

        $user = $this->entityManager->getRepository(Usuario::class)->findOneBy(['email' => $data['email']]);

        if (!$user) {
            return $this->json([
                'message' => 'Usuario no encontrado'
            ], 404);
        }

        // Generar código de verificación
        $verificationCode = bin2hex(random_bytes(16));
        $user->setTokenVerificacion($verificationCode);
        $this->entityManager->flush();

        // Aquí deberías implementar el envío real del correo electrónico
        $this->emailService->sendVerificationEmail($user);
        // Por ahora solo simulamos el envío
        $this->logger->info('Código de verificación generado', [
            'email' => $user->getEmail(),
            'code' => $verificationCode
        ]);

        return $this->json([
            'message' => 'Código de verificación enviado'
        ]);
    }

    #[Route('/api/request-password-reset', name: 'api_request_password_reset', methods: ['POST'])]
    public function requestPasswordReset(Request $request): JsonResponse
    {
        $data = json_decode($request->getContent(), true);

        if (!isset($data['email'])) {
            return $this->json([
                'message' => 'El email es requerido'
            ], 400);
        }

        $user = $this->entityManager->getRepository(Usuario::class)->findOneBy(['email' => $data['email']]);

        if (!$user) {
            // Por seguridad, siempre devolvemos el mismo mensaje aunque el email no exista
            return $this->json([
                'message' => 'Si el email existe en nuestra base de datos, recibirás un código de restablecimiento'
            ]);
        }

        // Generar código de verificación
        $verificationCode = bin2hex(random_bytes(16));
        $user->setTokenVerificacion($verificationCode);
        $this->entityManager->flush();

        // Enviar email de restablecimiento
        $this->emailService->sendPasswordResetEmail($user);

        return $this->json([
            'message' => 'Si el email existe en nuestra base de datos, recibirás un código de restablecimiento'
        ]);
    }
} 