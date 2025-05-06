<?php

namespace App\Service;

use App\Entity\Usuario;
use App\Entity\Nivel;
use App\Entity\Notificacion;
use App\Entity\UsuarioNivel;
use Doctrine\ORM\EntityManagerInterface;

class NivelService
{
    public function __construct(
        private readonly EntityManagerInterface $entityManager,
        private readonly NotificacionService $notificacionService
    ) {}

    public function agregarPuntos(Usuario $usuario, int $puntos): void
    {
        // Obtener el nivel actual del usuario
        $usuarioNivel = $this->entityManager->getRepository(UsuarioNivel::class)
            ->findOneBy(['idUsuario' => $usuario]);

        if (!$usuarioNivel) {
            return;
        }

        // Actualizar puntos
        $puntosActuales = $usuarioNivel->getPuntosActuales() + $puntos;
        $usuarioNivel->setPuntosActuales($puntosActuales);

        // Verificar si alcanza el siguiente nivel
        if ($puntosActuales >= $usuarioNivel->getPuntosSiguienteNivel()) {
            $this->subirNivel($usuarioNivel);
        }

        $this->entityManager->flush();
    }

    private function subirNivel(UsuarioNivel $usuarioNivel): void
    {
        $nivelActual = $usuarioNivel->getIdNivel();
        $siguienteNumNivel = $nivelActual->getNumNivel() + 1;

        // Buscar el siguiente nivel
        $siguienteNivel = $this->entityManager->getRepository(Nivel::class)
            ->findOneBy(['numNivel' => $siguienteNumNivel]);

        if (!$siguienteNivel) {
            return; // Ya está en el nivel máximo
        }

        // Obtener puntos para el siguiente nivel después del que vamos a establecer
        $nivelPosterior = $this->entityManager->getRepository(Nivel::class)
            ->findOneBy(['numNivel' => $siguienteNumNivel + 1]);

        // Actualizar el nivel del usuario
        $usuarioNivel->setIdNivel($siguienteNivel);
        $usuarioNivel->setFechaUltimoNivel(new \DateTime());
        
        // Actualizar puntos para siguiente nivel
        if ($nivelPosterior) {
            $usuarioNivel->setPuntosSiguienteNivel($nivelPosterior->getPuntosRequeridos());
        }

        // Crear notificación de nuevo nivel
        $this->notificacionService->crearNotificacion(
            $usuarioNivel->getIdUsuario(),
            Notificacion::NUEVO_NIVEL,
            '¡Has subido de nivel!',
            sprintf(
                'Has alcanzado el nivel "%s". %s',
                $siguienteNivel->getNombre(),
                $siguienteNivel->getDescripcion()
            ),
            '/dashboard'
        );
    }
}