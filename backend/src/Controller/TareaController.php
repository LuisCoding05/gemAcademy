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