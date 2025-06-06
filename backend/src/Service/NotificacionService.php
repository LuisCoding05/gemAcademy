<?php

namespace App\Service;

use App\Entity\Notificacion;
use App\Entity\Usuario;
use Doctrine\ORM\EntityManagerInterface;

class NotificacionService
{
    private $entityManager;

    public function __construct(EntityManagerInterface $entityManager)
    {
        $this->entityManager = $entityManager;
    }

    public function crearNotificacion(
        Usuario $usuario,
        string $tipo,
        string $titulo,
        string $contenido,
        ?string $url = null
    ): Notificacion {
        $notificacion = new Notificacion();
        $notificacion->setUsuario($usuario);
        $notificacion->setTipo($tipo);
        $notificacion->setTitulo($titulo);
        $notificacion->setContenido($contenido);
        $notificacion->setUrl($url);

        $this->entityManager->persist($notificacion);
        $this->entityManager->flush();

        return $notificacion;
    }

    public function marcarComoLeida(Notificacion $notificacion): void
    {
        $notificacion->setLeida(true);
        $this->entityManager->flush();
    }

    public function marcarTodasComoLeidas(Usuario $usuario): void
    {
        $notificaciones = $this->entityManager->getRepository(Notificacion::class)
            ->findBy(['usuario' => $usuario, 'leida' => false]);

        foreach ($notificaciones as $notificacion) {
            $notificacion->setLeida(true);
        }

        $this->entityManager->flush();
    }
}