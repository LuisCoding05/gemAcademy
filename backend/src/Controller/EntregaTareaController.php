<?php

namespace App\Controller;

use App\Entity\Curso;
use App\Entity\EntregaTarea;
use App\Entity\Tarea;
use App\Entity\Usuario;
use App\Entity\UsuarioCurso;
use App\Entity\Notificacion;
use App\Service\CursoInscripcionService;
use App\Service\NotificacionService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[IsGranted('ROLE_USER')]
final class EntregaTareaController extends AbstractController
{
    public function __construct(
        private readonly EntityManagerInterface $entityManager,
        private readonly CursoInscripcionService $cursoInscripcionService,
        private readonly NotificacionService $notificacionService
    ) {}

    #[Route('/api/item/{id}/tarea/{tareaId}/entregas', name: 'app_tarea_entregas', methods: ['GET'])]
    public function obtenerEntregas($id, $tareaId, Request $request): JsonResponse
    {
        // Obtener el curso y verificar acceso
        $curso = $this->entityManager->getRepository(Curso::class)->find($id);
        if (!$curso) {
            return $this->json(['message' => 'Curso no encontrado'], 404);
        }

        // Obtener la tarea
        $tarea = $this->entityManager->getRepository(Tarea::class)->find($tareaId);
        if (!$tarea || $tarea->getIdCurso()->getId() != $id) {
            return $this->json(['message' => 'Tarea no encontrada'], 404);
        }

        // Verificar que el usuario actual es el profesor
        $user = $this->getUser();
        $usuario = $this->entityManager->getRepository(Usuario::class)->findOneBy(['email' => $user->getUserIdentifier()]);
        if (!$usuario || $curso->getProfesor()->getId() !== $usuario->getId()) {
            return $this->json(['message' => 'No tienes permiso para ver las entregas'], 403);
        }

        // Obtener parámetros de búsqueda
        $nombreEstudiante = $request->query->get('nombre');
        $solicitaRevision = $request->query->get('revision') === 'true';

        // Construir query base
        $qb = $this->entityManager->createQueryBuilder();
        $qb->select('et')
           ->from(EntregaTarea::class, 'et')
           ->innerJoin('et.usuarioCurso', 'uc')
           ->innerJoin('uc.idUsuario', 'u')
           ->where('et.idTarea = :tareaId')
           ->setParameter('tareaId', $tareaId);

        // Filtrar por nombre si se especifica
        if ($nombreEstudiante) {
            $qb->andWhere('LOWER(u.nombre) LIKE LOWER(:nombre) OR LOWER(u.apellido) LIKE LOWER(:nombre) OR LOWER(u.username) LIKE LOWER(:nombre)')
               ->setParameter('nombre', '%' . $nombreEstudiante . '%');
        }

        // Filtrar por solicitud de revisión si se especifica
        if ($solicitaRevision) {
            $qb->andWhere('et.estado = :estado')
               ->setParameter('estado', EntregaTarea::ESTADO_REVISION_SOLICITADA);
        }

        // Ejecutar query
        $entregas = $qb->getQuery()->getResult();

        // Formatear respuesta
        $entregasData = [];
        foreach ($entregas as $entrega) {
            $estudiante = $entrega->getUsuarioCurso()->getIdUsuario();
            $entregasData[] = [
                'id' => $entrega->getId(),
                'estudiante' => [
                    'id' => $estudiante->getId(),
                    'nombre' => $estudiante->getNombre(),
                    'apellido' => $estudiante->getApellido(),
                    'username' => $estudiante->getUsername(),
                    'imagen' => $estudiante->getImagen()->getUrl()
                ],
                'fechaEntrega' => $entrega->getFechaEntrega() ? $entrega->getFechaEntrega()->format('Y-m-d H:i:s') : null,
                'estado' => $entrega->getEstado(),
                'calificacion' => $entrega->getCalificacion(),
                'puntosObtenidos' => $entrega->getPuntosObtenidos(),
                'comentarioProfesor' => $entrega->getComentarioProfesor(),
                'comentarioEstudiante' => $entrega->getComentarioEstudiante(),
                'archivo' => $entrega->getFichero() ? [
                    'id' => $entrega->getFichero()->getId(),
                    'nombreOriginal' => $entrega->getFichero()->getNombreOriginal()
                ] : null
            ];
        }

        return $this->json($entregasData);
    }

    #[Route('/api/item/{id}/tarea/{tareaId}/entrega/{entregaId}', name: 'app_tarea_entrega_detalle', methods: ['GET'])]
    public function obtenerEntregaDetalle($id, $tareaId, $entregaId): JsonResponse
    {
        // Obtener el curso y verificar acceso
        $curso = $this->entityManager->getRepository(Curso::class)->find($id);
        if (!$curso) {
            return $this->json(['message' => 'Curso no encontrado'], 404);
        }

        // Verificar que el usuario actual es el profesor
        $user = $this->getUser();
        $usuario = $this->entityManager->getRepository(Usuario::class)->findOneBy(['email' => $user->getUserIdentifier()]);
        if (!$usuario || $curso->getProfesor()->getId() !== $usuario->getId()) {
            return $this->json(['message' => 'No tienes permiso para ver esta entrega'], 403);
        }

        // Obtener la entrega
        $entrega = $this->entityManager->getRepository(EntregaTarea::class)->find($entregaId);
        if (!$entrega || $entrega->getIdTarea()->getId() != $tareaId) {
            return $this->json(['message' => 'Entrega no encontrada'], 404);
        }

        $estudiante = $entrega->getUsuarioCurso()->getIdUsuario();
        return $this->json([
            'id' => $entrega->getId(),
            'estudiante' => [
                'id' => $estudiante->getId(),
                'nombre' => $estudiante->getNombre(),
                'username' => $estudiante->getUsername(),
                'imagen' => $estudiante->getImagen()?->getUrl()
            ],
            'fechaEntrega' => $entrega->getFechaEntrega()?->format('Y-m-d H:i:s'),
            'estado' => $entrega->getEstado(),
            'calificacion' => $entrega->getCalificacion(),
            'puntosObtenidos' => $entrega->getPuntosObtenidos(),
            'comentarioProfesor' => $entrega->getComentarioProfesor(),
            'comentarioEstudiante' => $entrega->getComentarioEstudiante(),
            'archivo' => $entrega->getFichero() ? [
                'id' => $entrega->getFichero()->getId(),
                'nombreOriginal' => $entrega->getFichero()->getNombreOriginal()
            ] : null
        ]);
    }

    #[Route('/api/item/{id}/tarea/{tareaId}/entrega/{entregaId}/calificar', name: 'app_tarea_calificar', methods: ['POST'])]
    public function calificarEntrega($id, $tareaId, $entregaId, Request $request): JsonResponse
    {
        // Verificar curso y permisos
        $curso = $this->entityManager->getRepository(Curso::class)->find($id);
        if (!$curso) {
            return $this->json(['message' => 'Curso no encontrado'], 404);
        }

        $user = $this->getUser();
        $usuario = $this->entityManager->getRepository(Usuario::class)->findOneBy(['email' => $user->getUserIdentifier()]);
        if (!$usuario || $curso->getProfesor()->getId() !== $usuario->getId()) {
            return $this->json(['message' => 'No tienes permiso para calificar entregas'], 403);
        }

        // Obtener la entrega
        $entrega = $this->entityManager->getRepository(EntregaTarea::class)->find($entregaId);
        if (!$entrega || $entrega->getIdTarea()->getId() != $tareaId) {
            return $this->json(['message' => 'Entrega no encontrada'], 404);
        }

        $data = json_decode($request->getContent(), true);
        $calificacion = $data['calificacion'] ?? null;
        $comentario = $data['comentario'] ?? null;

        if ($calificacion === null) {
            return $this->json(['message' => 'La calificación es requerida'], 400);
        }

        try {
            // Validar calificación
            $calificacion = floatval($calificacion);
            if ($calificacion < 0 || $calificacion > 10) {
                return $this->json(['message' => 'La calificación debe estar entre 0 y 10'], 400);
            }

            // Calcular puntos obtenidos
            $puntosMaximos = $entrega->getIdTarea()->getPuntosMaximos();
            $puntosObtenidos = round(($calificacion * $puntosMaximos) / 10);

            $entrega->setCalificacion(number_format($calificacion, 2));
            $entrega->setPuntosObtenidos($puntosObtenidos);
            $entrega->setComentarioProfesor($comentario);
            $entrega->setEstado(EntregaTarea::ESTADO_CALIFICADO);

            $usuarioCurso = $entrega->getUsuarioCurso();
            $this->cursoInscripcionService->calcularPromedio($usuarioCurso);

            // Create notification for the student
            $tarea = $entrega->getIdTarea();
            $this->notificacionService->crearNotificacion(
                $usuarioCurso->getIdUsuario(),
                Notificacion::TIPO_CORRECCION,
                'Tu entrega ha sido calificada',
                sprintf(
                    'Tu entrega para la tarea "%s" ha sido calificada con %s/10', 
                    $tarea->getTitulo(),
                    $calificacion
                ),
                sprintf('/cursos/%d/tarea/%d', $id, $tareaId)
            );

            $this->entityManager->flush();

            return $this->json([
                'message' => 'Entrega calificada con éxito',
                'entrega' => [
                    'id' => $entrega->getId(),
                    'calificacion' => $entrega->getCalificacion(),
                    'puntosObtenidos' => $entrega->getPuntosObtenidos(),
                    'comentarioProfesor' => $entrega->getComentarioProfesor(),
                    'estado' => $entrega->getEstado()
                ]
            ]);
        } catch (\Exception $e) {
            return $this->json(['message' => 'Error al calificar la entrega: ' . $e->getMessage()], 500);
        }
    }
}