<?php

namespace App\Controller;

use App\Entity\Usuario;
use App\Entity\UsuarioCurso;
use App\Entity\UsuarioLogro;
use App\Entity\Imagen;
use App\Entity\Curso;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;
use Symfony\Component\Validator\Validator\ValidatorInterface;

class DashboardController extends AbstractController
{
    public function __construct(
        private readonly EntityManagerInterface $entityManager
    ) {}

    #[Route('/api/dashboard', name: 'api_dashboard', methods: ['GET'])]
    #[IsGranted('ROLE_USER')]
    public function index(): JsonResponse
    {
        /** @var Usuario $user */
        $user = $this->getUser();

        $cursos = [];
        $totalCursos = 0;
        $cursosCompletados = 0;
        $puntosTotales = 0;

        // Obtener cursos donde el usuario es profesor
        $cursosDictados = $this->entityManager->getRepository(Curso::class)
            ->findBy(['profesor' => $user]);

        foreach ($cursosDictados as $curso) {
            $cursos[] = [
                'id' => $curso->getId(),
                'titulo' => $curso->getNombre(),
                'descripcion' => $curso->getDescripcion(),
                'porcentajeCompletado' => 100, // El profesor tiene acceso completo
                'materialesCompletados' => count($curso->getMaterials()),
                'materialesTotales' => count($curso->getMaterials()),
                'tareasCompletadas' => count($curso->getTareas()),
                'tareasTotales' => count($curso->getTareas()),
                'quizzesCompletados' => count($curso->getQuizzs()),
                'quizzesTotales' => count($curso->getQuizzs()),
                'fechaInscripcion' => $curso->getFechaCreacion()->format('Y-m-d H:i:s'),
                'ultimaActualizacion' => $curso->getFechaCreacion()->format('Y-m-d H:i:s'),
                'userRole' => 'profesor'
            ];
        }

        // Obtener cursos donde el usuario es estudiante
        $usuarioCursos = $this->entityManager->getRepository(UsuarioCurso::class)
            ->findBy(['idUsuario' => $user]);

        foreach ($usuarioCursos as $usuarioCurso) {
            $curso = $usuarioCurso->getIdCurso();
            $porcentaje = (float)$usuarioCurso->getPorcentajeCompletado();
            
            if ($porcentaje >= 100) {
                $cursosCompletados++;
            }

            $cursos[] = [
                'id' => $curso->getId(),
                'titulo' => $curso->getNombre(),
                'descripcion' => $curso->getDescripcion(),
                'porcentajeCompletado' => $porcentaje,
                'materialesCompletados' => $usuarioCurso->getMaterialesCompletados(),
                'materialesTotales' => $usuarioCurso->getMaterialesTotales(),
                'tareasCompletadas' => $usuarioCurso->getTareasCompletadas(),
                'tareasTotales' => $usuarioCurso->getTareasTotales(),
                'quizzesCompletados' => $usuarioCurso->getQuizzesCompletados(),
                'quizzesTotales' => $usuarioCurso->getQuizzesTotales(),
                'fechaInscripcion' => $usuarioCurso->getFechaInscripcion()->format('Y-m-d H:i:s'),
                'ultimaActualizacion' => $usuarioCurso->getUltimaActualizacion()->format('Y-m-d H:i:s'),
                'userRole' => 'estudiante'
            ];
        }

        $totalCursos = count($usuarioCursos);

        // Obtener logros del usuario
        $usuarioLogros = $this->entityManager->getRepository(UsuarioLogro::class)
            ->findBy(['idUsuario' => $user]);

        $logros = [];
        foreach ($usuarioLogros as $usuarioLogro) {
            $logro = $usuarioLogro->getIdLogro();
            $puntosTotales += $logro->getPuntosOtorgados() ?? 0;

            $logros[] = [
                'id' => $logro->getId(),
                'titulo' => $logro->getTitulo(),
                'motivo' => $logro->getMotivo(),
                'puntos' => $logro->getPuntosOtorgados(),
                'fechaObtencion' => $usuarioLogro->getFechaObtencion()->format('Y-m-d H:i:s'),
                'imagen' => $logro->getImagen() ? [
                    'url' => $logro->getImagen()->getUrl()
                ] : null
            ];
        }

        // Obtener todas las imágenes disponibles
        $imagenes = $this->entityManager->getRepository(Imagen::class)->findAll();
        $imagenesDisponibles = [];
        foreach ($imagenes as $imagen) {
            $imagenesDisponibles[] = [
                'id' => $imagen->getId(),
                'url' => $imagen->getUrl()
            ];
        }

        // Calcular nivel basado en puntos
        $nivel = floor($puntosTotales / 100) + 1;
        $progresoNivel = $puntosTotales % 100;

        $response = new JsonResponse([
            'user' => [
                'id' => $user->getId(),
                'email' => $user->getEmail(),
                'nombre' => $user->getNombre(),
                'apellido' => $user->getApellido(),
                'username' => $user->getUsername(),
                'roles' => $user->getRoles(),
                'imagen' => $user->getImagen() ? [
                    'url' => $user->getImagen()->getUrl()
                ] : null
            ],
            'estadisticas' => [
                'nivel' => $nivel,
                'puntosTotales' => $puntosTotales,
                'progresoNivel' => $progresoNivel,
                'totalCursos' => $totalCursos,
                'cursosCompletados' => $cursosCompletados,
                'porcentajeCompletado' => $totalCursos > 0 ? ($cursosCompletados / $totalCursos) * 100 : 0
            ],
            'cursos' => $cursos,
            'logros' => $logros,
            'imagenesDisponibles' => $imagenesDisponibles
        ]);
        
        $response->headers->set('Content-Type', 'application/json');
        $response->headers->set('Access-Control-Allow-Origin', '*');
        $response->headers->set('Access-Control-Allow-Methods', 'GET,POST,OPTIONS');
        $response->headers->set('Access-Control-Allow-Headers', 'Content-Type,Authorization');
        
        return $response;
    }

    #[Route('/api/dashboard/profile', name: 'api_dashboard_profile_update', methods: ['PUT'])]
    #[IsGranted('ROLE_USER')]
    public function updateProfile(Request $request, ValidatorInterface $validator): JsonResponse
    {
        /** @var Usuario $user */
        $user = $this->getUser();
        
        $data = json_decode($request->getContent(), true);
        
        // Actualizar nombre si se proporciona
        if (isset($data['nombre']) && !empty($data['nombre'])) {
            $user->setNombre($data['nombre']);
        }
        
        // Actualizar apellido si se proporciona
        if (isset($data['apellido']) && !empty($data['apellido'])) {
            $user->setApellido($data['apellido']);
        }

        // Actualizar username si se proporciona
        if (isset($data['username']) && !empty($data['username'])) {
            // Verificar si el username ya existe
            $existingUser = $this->entityManager->getRepository(Usuario::class)
                ->findOneBy(['username' => $data['username']]);
                
            if ($existingUser && $existingUser->getId() !== $user->getId()) {
                return new JsonResponse([
                    'error' => 'El nombre de usuario ya está en uso'
                ], 400);
            }
            
            $user->setUsername($data['username']);
        }
        
        // Actualizar imagen de perfil si se proporciona
        if (isset($data['imagen']) && isset($data['imagen']['url'])) {
            // Buscar si la imagen ya existe
            $imagen = $this->entityManager->getRepository(Imagen::class)
                ->findOneBy(['url' => $data['imagen']['url']]);
            
            // Si no existe, crear una nueva
            if (!$imagen) {
                $imagen = new Imagen();
                $imagen->setUrl($data['imagen']['url']);
                $this->entityManager->persist($imagen);
            }
            
            // Asignar la imagen al usuario
            $user->setImagen($imagen);
        }
        
        // Validar la entidad
        $errors = $validator->validate($user);
        
        if (count($errors) > 0) {
            $errorMessages = [];
            foreach ($errors as $error) {
                $errorMessages[] = $error->getMessage();
            }
            
            return new JsonResponse([
                'error' => 'Error de validación',
                'messages' => $errorMessages
            ], 400);
        }
        
        // Guardar los cambios
        $this->entityManager->flush();
        
        // Devolver el usuario actualizado
        return new JsonResponse([
            'success' => true,
            'user' => [
                'id' => $user->getId(),
                'email' => $user->getEmail(),
                'nombre' => $user->getNombre(),
                'apellido' => $user->getApellido(),
                'username' => $user->getUsername(),
                'roles' => $user->getRoles(),
                'imagen' => $user->getImagen() ? [
                    'url' => $user->getImagen()->getUrl()
                ] : null
            ]
        ]);
    }

    #[Route('/api/dashboard/cleanup-images', name: 'api_dashboard_cleanup_images', methods: ['POST'])]
    #[IsGranted('ROLE_ADMIN')]
    public function cleanupDuplicateImages(): JsonResponse
    {
        // Obtener todas las imágenes
        $imagenes = $this->entityManager->getRepository(Imagen::class)->findAll();
        
        // Crear un array para almacenar las URLs únicas y sus IDs
        $urlMap = [];
        $deletedCount = 0;
        
        foreach ($imagenes as $imagen) {
            $url = $imagen->getUrl();
            
            if (!isset($urlMap[$url])) {
                // Si es la primera vez que vemos esta URL, la guardamos
                $urlMap[$url] = $imagen->getId();
            } else {
                // Si ya existe esta URL, actualizamos las referencias y eliminamos la duplicada
                $originalImageId = $urlMap[$url];
                $originalImage = $this->entityManager->getRepository(Imagen::class)->find($originalImageId);
                
                // Actualizar las referencias de usuarios que usan esta imagen
                $usuarios = $this->entityManager->getRepository(Usuario::class)
                    ->findBy(['imagen' => $imagen]);
                
                foreach ($usuarios as $usuario) {
                    $usuario->setImagen($originalImage);
                }
                
                // Eliminar la imagen duplicada
                $this->entityManager->remove($imagen);
                $deletedCount++;
            }
        }
        
        // Guardar los cambios
        $this->entityManager->flush();
        
        return new JsonResponse([
            'success' => true,
            'message' => "Se han eliminado $deletedCount imágenes duplicadas",
            'deletedCount' => $deletedCount
        ]);
    }
} 