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
use App\Entity\Fichero;
use App\Service\FileService;
use Doctrine\ORM\EntityManagerInterface;
use Proxies\__CG__\App\Entity\IntentoQuizz;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[IsGranted('ROLE_USER')]
final class ItemController extends AbstractController
{

    public function __construct(
        private readonly EntityManagerInterface $entityManager,
        private readonly FileService $fileService
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
            "contenido" => $material->getFichero() ? $material->getFichero()->getRuta() : null,
            "fechaPublicacion" => $material->getFechaPublicacion()->format('Y/m/d H:i:s'),
            "url" => $material->getFichero() ? $material->getFichero()->getRuta() : null,
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
                "archivo" => $entrega->getFichero() ? [
                    "id" => $entrega->getFichero()->getId(),
                    "url" => $entrega->getFichero()->getRuta(),
                    "nombreOriginal" => $entrega->getFichero()->getNombreOriginal()
                ] : null,
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

    #[Route('/api/item/{id}/tarea/{tareaId}/entrega', name: 'app_item_tarea_entrega', methods: ['POST'])]
    public function actualizarEntregaTarea($id, $tareaId, Request $request): JsonResponse
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
        $usuario = $this->entityManager->getRepository(Usuario::class)->findOneBy(['email' => $user->getUserIdentifier()]);
        if (!$usuario) {
            return $this->json([
                'message' => 'Usuario no encontrado'
            ], 404);
        }

        // Verificar si está inscrito como estudiante
        $usuarioCurso = $this->entityManager->getRepository(UsuarioCurso::class)
            ->findOneBy([
                'idUsuario' => $usuario,
                'idCurso' => $curso
            ]);
        
        if (!$usuarioCurso) {
            return $this->json([
                'message' => 'No estás inscrito en este curso'
            ], 403);
        }

        $data = json_decode($request->getContent(), true);
        $accion = $data['accion'] ?? null;
        $comentario = $data['comentario'] ?? null;
        $ficheroId = $data['ficheroId'] ?? null;

        // Obtener o crear la entrega
        $entrega = $this->entityManager->getRepository(EntregaTarea::class)
            ->findOneBy([
                'idTarea' => $tarea,
                'usuarioCurso' => $usuarioCurso
            ]);
        
        try {
            switch ($accion) {
                case 'actualizarComentario':
                    if ($entrega && !$entrega->isCalificado()) {
                        $entrega->setComentarioEstudiante($comentario);
                    } else {
                        return $this->json([
                            'message' => 'No se puede actualizar el comentario'
                        ], 400);
                    }
                    break;

                case 'entregar':
                    if (!$entrega) {
                        $entrega = new EntregaTarea();
                        $entrega->setIdTarea($tarea);
                        $entrega->setUsuarioCurso($usuarioCurso);
                        $this->entityManager->persist($entrega);

                        // Actualizar estadísticas del usuario en el curso
                        $tareasCompletadasActual = $usuarioCurso->getTareasCompletadas() ?? 0;
                        $usuarioCurso->setTareasCompletadas($tareasCompletadasActual + 1);
                    } else if ($entrega->getEstado() === EntregaTarea::ESTADO_PENDIENTE) {
                        // Si la tarea estaba pendiente y ahora se está entregando, incrementar contador
                        $tareasCompletadasActual = $usuarioCurso->getTareasCompletadas() ?? 0;
                        $usuarioCurso->setTareasCompletadas($tareasCompletadasActual + 1);
                    }

                    // Establecer el comentario si se proporciona
                    if ($comentario !== null) {
                        $entrega->setComentarioEstudiante($comentario);
                    }

                    // Manejar archivo
                    if ($ficheroId) {
                        $fichero = $this->entityManager->getRepository(Fichero::class)->find($ficheroId);
                        if ($fichero) {
                            // Si ya hay un fichero anterior, desvincularlo correctamente y eliminar el archivo
                            if ($entrega->getFichero()) {
                                $oldFichero = $entrega->getFichero();
                                $this->fileService->deleteFile($oldFichero->getRuta());
                                $oldFichero->setEntregaTarea(null);
                                $entrega->setFichero(null);
                                $this->entityManager->remove($oldFichero);
                            }

                            // Asegurarnos de que el fichero no esté ya asociado a otra entrega
                            if ($fichero->getEntregaTarea()) {
                                $oldEntrega = $fichero->getEntregaTarea();
                                $oldEntrega->setFichero(null);
                                $fichero->setEntregaTarea(null);
                            }

                            // Establecer la relación bidireccional
                            $fichero->setEntregaTarea($entrega);
                            $entrega->setFichero($fichero);
                        }
                    }

                    $entrega->setFechaEntrega(new \DateTime());
                    $entrega->setEstado($tarea->getFechaLimite() < new \DateTime() ? 
                        EntregaTarea::ESTADO_ATRASADO : 
                        EntregaTarea::ESTADO_ENTREGADO
                    );
                    break;

                case 'actualizarArchivo':
                    if ($entrega && !$entrega->isCalificado()) {
                        if ($ficheroId) {
                            $fichero = $this->entityManager->getRepository(Fichero::class)->find($ficheroId);
                            if ($fichero) {
                                // Eliminar archivo anterior si existe
                                if ($entrega->getFichero()) {
                                    $oldFichero = $entrega->getFichero();
                                    $this->fileService->deleteFile($oldFichero->getRuta());
                                    $oldFichero->setEntregaTarea(null);
                                    $entrega->setFichero(null);
                                    $this->entityManager->remove($oldFichero);
                                }
                                // Asegurarnos de que el fichero no esté ya asociado a otra entrega
                                if ($fichero->getEntregaTarea()) {
                                    $oldEntrega = $fichero->getEntregaTarea();
                                    $oldEntrega->setFichero(null);
                                    $fichero->setEntregaTarea(null);
                                }
                                // Establecer la relación bidireccional
                                $fichero->setEntregaTarea($entrega);
                                $entrega->setFichero($fichero);
                            }
                        }
                        $entrega->setFechaEntrega(new \DateTime());
                        if ($tarea->getFechaLimite() < new \DateTime()) {
                            $entrega->setEstado(EntregaTarea::ESTADO_ATRASADO);
                        }
                    } else {
                        return $this->json([
                            'message' => 'No se puede actualizar el archivo de una entrega calificada'
                        ], 400);
                    }
                    break;

                case 'borrar':
                    if ($entrega && !$entrega->isCalificado()) {
                        // Eliminar archivo asociado
                        if ($entrega->getFichero()) {
                            $oldFichero = $entrega->getFichero();
                            $this->fileService->deleteFile($oldFichero->getRuta());
                            $oldFichero->setEntregaTarea(null);
                            $entrega->setFichero(null);
                            $this->entityManager->remove($oldFichero);
                        }
                        $entrega->setEstado(EntregaTarea::ESTADO_PENDIENTE);
                        $entrega->setFechaEntrega(null);
                        $entrega->setFichero(null);
                        $entrega->setPuntosObtenidos(null);
                        $entrega->setComentarioProfesor(null);
                        $entrega->setComentarioEstudiante(null);
                        
                        // Actualizar estadísticas del usuario en el curso
                        $tareasCompletadasActual = $usuarioCurso->getTareasCompletadas();
                        $usuarioCurso->setTareasCompletadas(max(0, $tareasCompletadasActual - 1));
                    } else {
                        return $this->json([
                            'message' => 'No se puede borrar una entrega calificada'
                        ], 400);
                    }
                    break;

                case 'solicitarRevision':
                    if ($entrega && ($entrega->getEstado() === EntregaTarea::ESTADO_ENTREGADO || $entrega->getEstado() === EntregaTarea::ESTADO_ATRASADO)) {
                        // Si está atrasado, mantener el estado atrasado
                        $entrega->setEstado(EntregaTarea::ESTADO_REVISION_SOLICITADA);
                    } else {
                        return $this->json([
                            'message' => 'No se puede solicitar revisión de esta entrega'
                        ], 400);
                    }
                    break;
            }

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
            
            return $this->json([
                'message' => 'Entrega actualizada correctamente',
                'entrega' => [
                    'id' => $entrega->getId(),
                    'estado' => $entrega->getEstado(),
                    'fechaEntrega' => $entrega->getFechaEntrega() ? $entrega->getFechaEntrega()->format('Y/m/d H:i:s') : null,
                    'comentarioEstudiante' => $entrega->getComentarioEstudiante(),
                    'archivo' => $entrega->getFichero() ? [
                        'id' => $entrega->getFichero()->getId(),
                        'url' => $entrega->getFichero()->getRuta(),
                        'nombre' => $entrega->getFichero()->getNombreOriginal()
                    ] : null,
                    'puntosObtenidos' => $entrega->getPuntosObtenidos(),
                    'calificacion' => $entrega->getCalificacion(),
                    'comentarioProfesor' => $entrega->getComentarioProfesor()
                ]
            ]);

        } catch (\Exception $e) {
            return $this->json([
                'message' => 'Error al actualizar la entrega',
                'error' => $e->getMessage()
            ], 500);
        }
    }

}
