<?php

namespace App\Controller;

use App\Entity\Curso;
use App\Entity\EntregaTarea;
use App\Entity\Fichero;
use App\Entity\Tarea;
use App\Entity\Usuario;
use App\Entity\UsuarioCurso;
use App\Service\FileService;
use Doctrine\ORM\EntityManagerInterface;
use Exception;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[IsGranted('ROLE_USER')]
final class TareaController extends AbstractController
{
    public function __construct(
        private readonly EntityManagerInterface $entityManager,
        private readonly FileService $fileService
    ) {}

    #[Route('/api/item/{id}/tarea/{tareaId}', name: 'app_tarea_detail', methods: ['GET'])]
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

        // Obtener la entrega si existe y el usuario es estudiante
        $entrega = null;
        if ($isEstudiante) {
            $entrega = $this->entityManager->getRepository(EntregaTarea::class)
                ->findOneBy([
                    'usuarioCurso' => $usuarioCurso,
                    'idTarea' => $tarea
                ]);
        }

        $tareaData = [
            "id" => $tarea->getId(),
            "titulo" => $tarea->getTitulo(),
            "descripcion" => $tarea->getDescripcion(),
            "fechaPublicacion" => $tarea->getFechaPublicacion()->format('Y/m/d H:i:s'),
            "fechaLimite" => $tarea->getFechaLimite()->format('Y/m/d H:i:s'),
            "puntosMaximos" => $tarea->getPuntosMaximos(),
            "esObligatoria" => $tarea->isEsObligatoria(),
            "fichero" => $tarea->getFichero() ? [
                "id" => $tarea->getFichero()->getId(),
                "url" => $tarea->getFichero()->getRuta(),
                "nombreOriginal" => $tarea->getFichero()->getNombreOriginal()
            ] : null,
            "userRole" => $isProfesor ? 'profesor' : ($isEstudiante ? 'estudiante' : null),
            "entrega" => $entrega ? [
                "id" => $entrega->getId(),
                "fechaEntrega" => $entrega->getFechaEntrega() ? $entrega->getFechaEntrega()->format('Y/m/d H:i:s') : null,
                "calificacion" => $entrega->getCalificacion(),
                "puntosObtenidos" => $entrega->getPuntosObtenidos(),
                "comentarioProfesor" => $entrega->getComentarioProfesor(),
                "estado" => $entrega->getEstado(),
                "comentarioEstudiante" => $entrega->getComentarioEstudiante(),
                "archivo" => $entrega->getFichero() ? [
                    "id" => $entrega->getFichero()->getId(),
                    "url" => $entrega->getFichero()->getRuta(),
                    "nombreOriginal" => $entrega->getFichero()->getNombreOriginal()
                ] : null,
                "isCalificado" => $entrega->isCalificado()
            ] : null
        ];

        return $this->json($tareaData);
    }

    #[Route('/api/item/{id}/tarea/{tareaId}/entrega', name: 'app_tarea_entrega', methods: ['POST'])]
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
                    if (!$entrega) {
                        $entrega = new EntregaTarea();
                        $entrega->setIdTarea($tarea);
                        $entrega->setUsuarioCurso($usuarioCurso);
                        $this->entityManager->persist($entrega);
                    }
                    $entrega->setComentarioEstudiante($comentario);
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
                    }

                    // Establecer el comentario si se proporciona
                    if ($comentario !== null) {
                        $entrega->setComentarioEstudiante($comentario);
                    }

                    // Manejar archivo
                    if ($ficheroId) {
                        $fichero = $this->entityManager->getRepository(Fichero::class)->find($ficheroId);
                        if ($fichero) {
                            // Si ya hay un fichero anterior, eliminarlo
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
                    if (!$entrega) {
                        return $this->json([
                            'message' => 'No existe una entrega para actualizar'
                        ], 404);
                    }

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

                            $fichero->setEntregaTarea($entrega);
                            $entrega->setFichero($fichero);
                        }
                    }
                    $entrega->setFechaEntrega(new \DateTime());
                    if ($tarea->getFechaLimite() < new \DateTime()) {
                        $entrega->setEstado(EntregaTarea::ESTADO_ATRASADO);
                    }
                    break;

                case 'borrar':
                    if (!$entrega) {
                        return $this->json([
                            'message' => 'No existe una entrega para borrar'
                        ], 404);
                    }

                    if ($entrega->isCalificado()) {
                        return $this->json([
                            'message' => 'No se puede borrar una entrega calificada'
                        ], 400);
                    }

                    // Eliminar archivo asociado
                    if ($entrega->getFichero()) {
                        $oldFichero = $entrega->getFichero();
                        $this->fileService->deleteFile($oldFichero->getRuta());
                        $oldFichero->setEntregaTarea(null);
                        $entrega->setFichero(null);
                        $this->entityManager->remove($oldFichero);
                    }

                    if ($entrega->getEstado() !== EntregaTarea::ESTADO_PENDIENTE) {
                        // Actualizar estadísticas del usuario en el curso
                        $tareasCompletadasActual = $usuarioCurso->getTareasCompletadas();
                        $usuarioCurso->setTareasCompletadas(max(0, $tareasCompletadasActual - 1));
                    }

                    $entrega->setEstado(EntregaTarea::ESTADO_PENDIENTE);
                    $entrega->setFechaEntrega(null);
                    $entrega->setPuntosObtenidos(null);
                    $entrega->setComentarioProfesor(null);
                    $entrega->setComentarioEstudiante(null);
                    break;

                case 'solicitarRevision':
                    if (!$entrega || !in_array($entrega->getEstado(), [EntregaTarea::ESTADO_ENTREGADO, EntregaTarea::ESTADO_ATRASADO])) {
                        return $this->json([
                            'message' => 'No se puede solicitar revisión de esta entrega'
                        ], 400);
                    }
                    $entrega->setEstado(EntregaTarea::ESTADO_REVISION_SOLICITADA);
                    break;

                default:
                    return $this->json([
                        'message' => 'Acción no válida'
                    ], 400);
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
                        'nombreOriginal' => $entrega->getFichero()->getNombreOriginal()
                    ] : null,
                    'puntosObtenidos' => $entrega->getPuntosObtenidos(),
                    'calificacion' => $entrega->getCalificacion(),
                    'comentarioProfesor' => $entrega->getComentarioProfesor(),
                    'isCalificado' => $entrega->isCalificado()
                ]
            ]);

        } catch (Exception $e) {
            return $this->json([
                'message' => 'Error al actualizar la entrega',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    #[Route('/api/item/{id}/tarea/create', name: 'app_tarea_create', methods: ['POST'])]
    public function createTarea(Request $request, $id): JsonResponse
    {
        // Obtener el curso
        $curso = $this->entityManager->getRepository(Curso::class)->find($id);
        if (!$curso) {
            return $this->json(['message' => 'Curso no encontrado'], 404);
        }

        // Verificar que el usuario es el profesor del curso
        $user = $this->getUser();
        $usuario = $this->entityManager->getRepository(Usuario::class)
            ->findOneBy(['email' => $user->getUserIdentifier()]);

        if ($curso->getProfesor()->getId() !== $usuario->getId()) {
            return $this->json(['message' => 'No tienes permisos para crear tareas en este curso'], 403);
        }

        try {
            $content = $request->getContent();
            $requestData = json_decode($content, true);
            
            if (!$requestData || !isset($requestData['data'])) {
                return $this->json(['message' => 'No se recibieron datos válidos'], 400);
            }

            $data = json_decode($requestData['data'], true);
            if ($data === null) {
                return $this->json([
                    'message' => 'Error al decodificar los datos JSON',
                    'content' => $content,
                    'requestData' => $requestData
                ], 400);
            }
            
            // Validar datos requeridos
            if (!isset($data['titulo']) || !isset($data['descripcion']) || !isset($data['fechaLimite'])) {
                return $this->json([
                    'message' => 'El título, descripción y fecha límite son requeridos',
                    'data' => $data
                ], 400);
            }

            $tarea = new Tarea();
            $tarea->setTitulo($data['titulo']);
            $tarea->setDescripcion($data['descripcion']);
            $tarea->setIdCurso($curso);
            $tarea->setFechaPublicacion(new \DateTime());
            $tarea->setFechaLimite(new \DateTime($data['fechaLimite']));
            $tarea->setPuntosMaximos($data['puntosMaximos'] ?? 100);
            $tarea->setEsObligatoria($data['esObligatoria'] ?? true);

            // Procesar ficheroId si existe
            if (isset($data['ficheroId'])) {
                $fichero = $this->entityManager->getRepository(Fichero::class)->find($data['ficheroId']);
                if ($fichero) {
                    $tarea->setFichero($fichero);
                }
            }

            $this->entityManager->persist($tarea);
            $this->entityManager->flush();

            return $this->json([
                'message' => 'Tarea creada exitosamente',
                'tarea' => [
                    'id' => $tarea->getId(),
                    'titulo' => $tarea->getTitulo(),
                    'descripcion' => $tarea->getDescripcion(),
                    'fechaPublicacion' => $tarea->getFechaPublicacion()->format('Y/m/d H:i:s'),
                    'fechaLimite' => $tarea->getFechaLimite()->format('Y/m/d H:i:s'),
                    'puntosMaximos' => $tarea->getPuntosMaximos(),
                    'esObligatoria' => $tarea->isEsObligatoria(),
                    'fichero' => $tarea->getFichero() ? [
                        'id' => $tarea->getFichero()->getId(),
                        'nombreOriginal' => $tarea->getFichero()->getNombreOriginal(),
                        'url' => $tarea->getFichero()->getRuta()
                    ] : null
                ]
            ], 201);

        } catch (Exception $e) {
            return $this->json([
                'message' => 'Error al crear la tarea: ' . $e->getMessage(),
                'data' => $data ?? null
            ], 500);
        }
    }

    #[Route('/api/item/{id}/tarea/{tareaId}/edit', name: 'app_tarea_edit', methods: ['POST'])]
    public function editTarea(Request $request, $id, $tareaId): JsonResponse
    {
        // Obtener la tarea y verificar que existe
        $tarea = $this->entityManager->getRepository(Tarea::class)->find($tareaId);
        if (!$tarea || $tarea->getIdCurso()->getId() != $id) {
            return $this->json(['message' => 'Tarea no encontrada'], 404);
        }

        // Verificar que el usuario es el profesor del curso
        $user = $this->getUser();
        $usuario = $this->entityManager->getRepository(Usuario::class)
            ->findOneBy(['email' => $user->getUserIdentifier()]);

        if ($tarea->getIdCurso()->getProfesor()->getId() !== $usuario->getId()) {
            return $this->json(['message' => 'No tienes permisos para editar esta tarea'], 403);
        }

        try {
            $data = json_decode($request->request->get('data'), true);
            
            if (isset($data['titulo'])) {
                $tarea->setTitulo($data['titulo']);
            }
            if (isset($data['descripcion'])) {
                $tarea->setDescripcion($data['descripcion']);
            }
            if (isset($data['fechaLimite'])) {
                $tarea->setFechaLimite(new \DateTime($data['fechaLimite']));
            }
            if (isset($data['puntosMaximos'])) {
                $tarea->setPuntosMaximos($data['puntosMaximos']);
            }
            if (isset($data['esObligatoria'])) {
                $tarea->setEsObligatoria($data['esObligatoria']);
            }

            // Procesar ficheroId si existe
            if (isset($data['ficheroId'])) {
                $fichero = $this->entityManager->getRepository(Fichero::class)->find($data['ficheroId']);
                if ($fichero) {
                    // Si hay un archivo anterior, eliminarlo
                    $ficheroAnterior = $tarea->getFichero();
                    if ($ficheroAnterior) {
                        // Eliminar el archivo físico
                        $this->fileService->deleteFile($ficheroAnterior->getRuta());
                        $this->entityManager->remove($ficheroAnterior);
                    }
                    
                    $tarea->setFichero($fichero);
                }
            }

            $this->entityManager->flush();

            return $this->json([
                'message' => 'Tarea actualizada exitosamente',
                'tarea' => [
                    'id' => $tarea->getId(),
                    'titulo' => $tarea->getTitulo(),
                    'descripcion' => $tarea->getDescripcion(),
                    'fechaPublicacion' => $tarea->getFechaPublicacion()->format('Y/m/d H:i:s'),
                    'fechaLimite' => $tarea->getFechaLimite()->format('Y/m/d H:i:s'),
                    'puntosMaximos' => $tarea->getPuntosMaximos(),
                    'esObligatoria' => $tarea->isEsObligatoria(),
                    'fichero' => $tarea->getFichero() ? [
                        'id' => $tarea->getFichero()->getId(),
                        'nombreOriginal' => $tarea->getFichero()->getNombreOriginal(),
                        'url' => $tarea->getFichero()->getRuta()
                    ] : null
                ]
            ]);

        } catch (Exception $e) {
            return $this->json([
                'message' => 'Error al actualizar la tarea',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    #[Route('/api/item/{id}/tarea/{tareaId}/delete', name: 'app_tarea_delete', methods: ['DELETE'])]
    public function deleteTarea($id, $tareaId): JsonResponse
    {
        // Obtener la tarea
        $tarea = $this->entityManager->getRepository(Tarea::class)->find($tareaId);
        if (!$tarea || $tarea->getIdCurso()->getId() != $id) {
            return $this->json(['message' => 'Tarea no encontrada'], 404);
        }

        // Verificar que el usuario es el profesor del curso
        $user = $this->getUser();
        $usuario = $this->entityManager->getRepository(Usuario::class)
            ->findOneBy(['email' => $user->getUserIdentifier()]);

        if ($tarea->getIdCurso()->getProfesor()->getId() !== $usuario->getId()) {
            return $this->json(['message' => 'No tienes permisos para eliminar esta tarea'], 403);
        }

        try {
            // Obtener todos los usuarios del curso para actualizar estadísticas
            $usuariosCurso = $this->entityManager->getRepository(UsuarioCurso::class)
                ->findBy(['idCurso' => $tarea->getIdCurso()]);

            foreach ($usuariosCurso as $usuarioCurso) {
                // Buscar si el usuario tiene una entrega para esta tarea
                $entrega = $this->entityManager->getRepository(EntregaTarea::class)
                    ->findOneBy([
                        'usuarioCurso' => $usuarioCurso,
                        'idTarea' => $tarea
                    ]);

                if ($entrega) {
                    // Si hay una entrega, actualizar estadísticas
                    if ($entrega->getEstado() !== 'pendiente') {
                        $tareasCompletadas = $usuarioCurso->getTareasCompletadas();
                        if ($tareasCompletadas > 0) {
                            $usuarioCurso->setTareasCompletadas($tareasCompletadas - 1);
                        }
                    }

                    // Eliminar el archivo de la entrega si existe
                    if ($entrega->getFichero()) {
                        $ficheroEntrega = $entrega->getFichero();
                        $this->fileService->deleteFile($ficheroEntrega->getRuta());
                        $this->entityManager->remove($ficheroEntrega);
                    }

                    // Eliminar la entrega
                    $this->entityManager->remove($entrega);
                }

                // Recalcular porcentaje completado
                $totalItems = $tarea->getIdCurso()->getTotalItems() - 1; // Restamos 1 por la tarea que se eliminará
                $itemsCompletados = $usuarioCurso->getMaterialesCompletados() + 
                                  $usuarioCurso->getTareasCompletadas() + 
                                  $usuarioCurso->getQuizzesCompletados();
                
                $porcentajeCompletado = ($totalItems > 0) ? 
                    round(($itemsCompletados / $totalItems) * 100, 2) : 0;
                
                $usuarioCurso->setPorcentajeCompletado(strval($porcentajeCompletado));
                $usuarioCurso->setUltimaActualizacion(new \DateTime());
            }

            // Eliminar el archivo de la tarea si existe
            if ($tarea->getFichero()) {
                $fichero = $tarea->getFichero();
                $this->fileService->deleteFile($fichero->getRuta());
                $this->entityManager->remove($fichero);
            }

            // Eliminar la tarea
            $this->entityManager->remove($tarea);
            $this->entityManager->flush();

            return $this->json([
                'message' => 'Tarea eliminada exitosamente'
            ]);

        } catch (Exception $e) {
            return $this->json([
                'message' => 'Error al eliminar la tarea',
                'error' => $e->getMessage()
            ], 500);
        }
    }
}