<?php

namespace App\Controller;

use App\Entity\Curso;
use App\Entity\EntregaTarea;
use App\Entity\Material;
use App\Entity\MaterialCompletado;
use App\Entity\Quizz;
use App\Entity\Tarea;
use App\Entity\Usuario;
use App\Entity\UsuarioCurso;
use Doctrine\ORM\EntityManagerInterface;
use Proxies\__CG__\App\Entity\IntentoQuizz;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[IsGranted('ROLE_USER')]
final class ItemController extends AbstractController
{

    public function __construct(
        private readonly EntityManagerInterface $entityManager
    ) {}

    #[Route('/item', name: 'app_item')]
    public function index(): JsonResponse
    {
        return $this->json([
            'message' => 'Welcome to your new controller!',
            'path' => 'src/Controller/ItemController.php',
        ]);
    }

    #[Route('/api/item/{id}/material/{materialId}', name: 'app_item_material_detail', methods: ['GET'])]
    public function materialDetail($id, $materialId): JsonResponse
    {
        // Obtener el curso
        $curso = $this->entityManager->getRepository(Curso::class)->find($id);
        if (!$curso) {
            return $this->json([
                'message' => 'Curso no encontrado'
            ], 404);
        }

        // Obtener el material
        $material = $this->entityManager->getRepository(Material::class)->find($materialId);
        if (!$material || $material->getIdCurso()->getId() != $id) {
            return $this->json([
                'message' => 'Material no encontrado'
            ], 404);
        }

        // Obtener el usuario actual
        $user = $this->getUser();

        // Verificar si el usuario tiene acceso al curso
        $usuario = $this->entityManager->getRepository(Usuario::class)->findOneBy(['email' => $user->getUserIdentifier()]);
        if (!$usuario) {
            return $this->json([
                'message' => 'Usuario no encontrado'
            ], 404);
        }

        // Verificar si es el profesor
        $isProfesor = $curso->getProfesor()->getId() === $usuario->getId();
        
        // Verificar si está inscrito como estudiante
        $usuarioCurso = $this->entityManager->getRepository(UsuarioCurso::class)
            ->findOneBy([
                'idUsuario' => $usuario,
                'idCurso' => $curso
            ]);
        
        $isEstudiante = $usuarioCurso !== null;

        if (!$isProfesor && !$isEstudiante) {
            return $this->json([
                'message' => 'No tienes acceso a este material'
            ], 403);
        }
        
        // Si es estudiante, marcar el material como completado
        if ($isEstudiante) {
            // Verificar si el material ya está marcado como completado
            $materialCompletado = $this->entityManager->getRepository(MaterialCompletado::class)
                ->findOneBy([
                    'usuarioCurso' => $usuarioCurso,
                    'material' => $material
                ]);

            // Si no está marcado como completado, crearlo
            if (!$materialCompletado) {
                try {
                    $materialCompletado = new MaterialCompletado();
                    $materialCompletado->setUsuarioCurso($usuarioCurso);
                    $materialCompletado->setMaterial($material);
                    $materialCompletado->setFechaCompletado(new \DateTime());
                    
                    $this->entityManager->persist($materialCompletado);
                    
                    // Actualizar el contador de materiales completados
                    $materialesCompletadosActual = $usuarioCurso->getMaterialesCompletados() ?? 0;
                    $usuarioCurso->setMaterialesCompletados($materialesCompletadosActual + 1);
                    
                    // Actualizar el porcentaje completado
                    $totalItems = $curso->getTotalItems();
                    $itemsCompletados = $usuarioCurso->getMaterialesCompletados() + 
                                       $usuarioCurso->getTareasCompletadas() + 
                                       $usuarioCurso->getQuizzesCompletados();
                    
                    $porcentajeCompletado = ($totalItems > 0) ? 
                        round(($itemsCompletados / $totalItems) * 100, 2) : 0;
                    
                    $usuarioCurso->setPorcentajeCompletado(strval($porcentajeCompletado));
                    $usuarioCurso->setUltimaActualizacion(new \DateTime());
                    
                    $this->entityManager->flush();
                } catch (\Exception $e) {
                    return $this->json([
                        'message' => 'Error al marcar el material como completado',
                        'error' => $e->getMessage()
                    ], 500);
                }
            }
        }

        // Formatear la respuesta
        $materialData = [
            "id" => $material->getId(),
            "titulo" => $material->getTitulo(),
            "descripcion" => $material->getDescripcion(),
            "contenido" => $material->getUrl(),
            "fechaPublicacion" => $material->getFechaPublicacion()->format('Y/m/d H:i:s'),
            "url" => $material->getUrl(),
            "completado" => $materialCompletado !== null
        ];

        return $this->json($materialData);
    }

    #[Route('/api/item/{id}/tarea/{tareaId}', name: 'app_item_tarea_detail', methods: ['GET'])]
    public function tareaDetail($id, $tareaId): JsonResponse
    {
        // Obtener el curso
        $curso = $this->entityManager->getRepository(Curso::class)->find($id);
        if (!$curso) {
            return $this->json([
                'message' => 'Curso no encontrado'
            ], 404);
        }

        // Obtener la tarea
        $tarea = $this->entityManager->getRepository(Tarea::class)->find($tareaId);
        if (!$tarea || $tarea->getIdCurso()->getId() != $id) {
            return $this->json([
                'message' => 'Tarea no encontrada'
            ], 404);
        }

        // Obtener el usuario actual
        $user = $this->getUser();

        // Verificar si el usuario tiene acceso al curso
        $usuario = $this->entityManager->getRepository(Usuario::class)->findOneBy(['email' => $user->getUserIdentifier()]);
        if (!$usuario) {
            return $this->json([
                'message' => 'Usuario no encontrado'
            ], 404);
        }

        // Verificar si es el profesor
        $isProfesor = $curso->getProfesor()->getId() === $usuario->getId();
        
        // Verificar si está inscrito como estudiante
        $usuarioCurso = $this->entityManager->getRepository(UsuarioCurso::class)
            ->findOneBy([
                'idUsuario' => $usuario,
                'idCurso' => $curso
            ]);
        
        $isEstudiante = $usuarioCurso !== null;

        if (!$isProfesor && !$isEstudiante) {
            return $this->json([
                'message' => 'No tienes acceso a esta tarea'
            ], 403);
        }

        // Obtener la entrega si existe
        $entrega = null;
        if ($usuarioCurso) {
            $entrega = $this->entityManager->getRepository(EntregaTarea::class)
                ->findOneBy([
                    'idTarea' => $tarea,
                    'usuarioCurso' => $usuarioCurso
                ]);
        }

        // Formatear la respuesta
        $tareaData = [
            "id" => $tarea->getId(),
            "titulo" => $tarea->getTitulo(),
            "descripcion" => $tarea->getDescripcion(),
            "fechaPublicacion" => $tarea->getFechaPublicacion()->format('Y/m/d H:i:s'),
            "fechaLimite" => $tarea->getFechaLimite()->format('Y/m/d H:i:s'),
            "puntos" => $tarea->getPuntosMaximos(),
            "entrega" => $entrega ? [
                "id" => $entrega->getId(),
                "archivoUrl" => $entrega->getArchivoUrl(),
                "fechaEntrega" => $entrega->getFechaEntrega() ? $entrega->getFechaEntrega()->format('Y/m/d H:i:s') : null,
                "calificacion" => $entrega->getCalificacion(),
                "puntosObtenidos" => $entrega->getPuntosObtenidos(),
                "comentarioProfesor" => $entrega->getComentarioProfesor(),
                "estado" => $entrega->getEstado(),
                "comentarioEstudiante" => $entrega->getComentarioEstudiante(),
            ] : null
        ];

        return $this->json($tareaData);
    }

    #[Route('/api/item/{id}/quiz/{quizId}', name: 'app_item_quiz_detail', methods: ['GET'])]
    public function quizDetail($id, $quizId): JsonResponse
    {
        // Obtener el curso
        $curso = $this->entityManager->getRepository(Curso::class)->find($id);
        if (!$curso) {
            return $this->json([
                'message' => 'Curso no encontrado'
            ], 404);
        }

        // Obtener el quiz
        $quiz = $this->entityManager->getRepository(Quizz::class)->find($quizId);
        if (!$quiz || $quiz->getIdCurso()->getId() != $id) {
            return $this->json([
                'message' => 'Quiz no encontrado'
            ], 404);
        }

        // Obtener el usuario actual
        $user = $this->getUser();

        // Verificar si el usuario tiene acceso al curso
        $usuario = $this->entityManager->getRepository(Usuario::class)->findOneBy(['email' => $user->getUserIdentifier()]);
        if (!$usuario) {
            return $this->json([
                'message' => 'Usuario no encontrado'
            ], 404);
        }

        // Verificar si es el profesor
        $isProfesor = $curso->getProfesor()->getId() === $usuario->getId();
        
        // Verificar si está inscrito como estudiante
        $usuarioCurso = $this->entityManager->getRepository(UsuarioCurso::class)
            ->findOneBy([
                'idUsuario' => $usuario,
                'idCurso' => $curso
            ]);
        
        $isEstudiante = $usuarioCurso !== null;

        if (!$isProfesor && !$isEstudiante) {
            return $this->json([
                'message' => 'No tienes acceso a este quiz'
            ], 403);
        }

        // Obtener los intentos si existen
        $intentos = [];
        if ($usuario) {
            $intentos = $this->entityManager->getRepository(IntentoQuizz::class)
                ->findBy([
                    'idQuizz' => $quiz,
                    'idUsuario' => $usuario
                ], ['fechaInicio' => 'DESC']);
        }

        // Formatear los intentos
        $intentosData = [];
        foreach ($intentos as $intento) {
            $intentosData[] = [
                "id" => $intento->getId(),
                "fechaInicio" => $intento->getFechaInicio() ? $intento->getFechaInicio()->format('Y/m/d H:i:s') : "No comenzado",
                "fechaFin" => $intento->getFechaFin() ? $intento->getFechaFin()->format('Y/m/d H:i:s') : "No finalizado",
                "puntuacionTotal" => $intento->getPuntuacionTotal(),
                "completado" => $intento->isCompletado()
            ];
        }

        // Formatear la respuesta
        $quizData = [
            "id" => $quiz->getId(),
            "titulo" => $quiz->getTitulo(),
            "descripcion" => $quiz->getDescripcion(),
            "fechaPublicacion" => $quiz->getFechaPublicacion()->format('Y/m/d H:i:s'),
            "fechaLimite" => $quiz->getFechaLimite()->format('Y/m/d H:i:s'),
            "puntos" => $quiz->getPuntosTotales(),
            "tiempoLimite" => $quiz->getTiempoLimite(),
            "intentos" => $intentosData
        ];

        return $this->json($quizData);
    }

}
