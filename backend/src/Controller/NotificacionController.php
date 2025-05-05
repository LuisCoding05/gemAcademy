<?php

namespace App\Controller;

use App\Entity\Notificacion;
use App\Service\NotificacionService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;

#[Route('/api/notificaciones')]
class NotificacionController extends AbstractController
{
    private $notificacionService;
    private $entityManager;

    public function __construct(
        NotificacionService $notificacionService,
        EntityManagerInterface $entityManager
    ) {
        $this->notificacionService = $notificacionService;
        $this->entityManager = $entityManager;
    }

    #[Route('', name: 'get_notificaciones', methods: ['GET'])]
    public function getNotificaciones(Request $request): JsonResponse
    {
        $usuario = $this->getUser();
        $page = $request->query->getInt('page', 1);
        $limit = $request->query->getInt('limit', 10);
        
        $notificaciones = $this->entityManager->getRepository(Notificacion::class)
            ->findByUsuario($usuario, $page, $limit);

        $totalNotifications = $this->entityManager->getRepository(Notificacion::class)
            ->getTotalNotificationCount($usuario);

        $totalPages = ceil($totalNotifications / $limit);

        $data = array_map(function($notificacion) {
            return [
                'id' => $notificacion->getId(),
                'tipo' => $notificacion->getTipo(),
                'titulo' => $notificacion->getTitulo(),
                'contenido' => $notificacion->getContenido(),
                'leida' => $notificacion->isLeida(),
                'url' => $notificacion->getUrl(),
                'fechaCreacion' => $notificacion->getFechaCreacion()->format('Y-m-d H:i:s'),
                'fechaLectura' => $notificacion->getFechaLectura() ? 
                    $notificacion->getFechaLectura()->format('Y-m-d H:i:s') : null,
            ];
        }, $notificaciones);

        return $this->json([
            'notificaciones' => $data,
            'noLeidas' => $this->entityManager->getRepository(Notificacion::class)
                ->countUnreadByUsuario($usuario),
            'paginacion' => [
                'paginaActual' => $page,
                'totalPaginas' => $totalPages,
                'totalNotificaciones' => $totalNotifications,
                'porPagina' => $limit
            ]
        ]);
    }

    #[Route('/{id}/leer', name: 'marcar_notificacion_leida', methods: ['PUT'])]
    public function marcarComoLeida(Notificacion $notificacion): JsonResponse
    {
        $this->denyAccessUnlessGranted('ROLE_USER');
        
        if ($notificacion->getUsuario() !== $this->getUser()) {
            return $this->json(['message' => 'No tienes permiso para acceder a esta notificación'], 403);
        }

        $this->notificacionService->marcarComoLeida($notificacion);

        return $this->json(['message' => 'Notificación marcada como leída']);
    }

    #[Route('/leer-todas', name: 'marcar_todas_leidas', methods: ['PUT'])]
    public function marcarTodasComoLeidas(): JsonResponse
    {
        $this->denyAccessUnlessGranted('ROLE_USER');
        
        $this->notificacionService->marcarTodasComoLeidas($this->getUser());

        return $this->json(['message' => 'Todas las notificaciones han sido marcadas como leídas']);
    }
}