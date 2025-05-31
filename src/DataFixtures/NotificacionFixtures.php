<?php

namespace App\DataFixtures;

use App\Entity\Notificacion;
use App\Entity\Usuario;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;
use Doctrine\Persistence\ObjectManager;

class NotificacionFixtures extends Fixture implements DependentFixtureInterface
{
    public function load(ObjectManager $manager): void
    {
        $estudiante = $this->getReference('usuario-estudiante1', Usuario::class);

        // Notificación de nueva tarea
        $notificacionTarea = new Notificacion();
        $notificacionTarea->setUsuario($estudiante)
            ->setTipo(Notificacion::TIPO_TAREA)
            ->setTitulo('Nueva tarea asignada')
            ->setContenido('Se ha publicado una nueva tarea: "Ejercicios básicos de algoritmos". Fecha límite: 11/05/2025 23:59')
            ->setUrl('/curso/1/tarea/1')
            ->setFechaCreacion(new \DateTime('-2 days'));
        $manager->persist($notificacionTarea);

        // Notificación de corrección
        $notificacionCorreccion = new Notificacion();
        $notificacionCorreccion->setUsuario($estudiante)
            ->setTipo(Notificacion::TIPO_CORRECCION)
            ->setTitulo('Tu entrega ha sido calificada')
            ->setContenido('Tu entrega para la tarea "Introducción a la programación" ha sido calificada con 9.5/10')
            ->setUrl('/curso/1/tarea/1')
            ->setFechaCreacion(new \DateTime('-1 day'));
        $manager->persist($notificacionCorreccion);

        // Notificación de logro
        $notificacionLogro = new Notificacion();
        $notificacionLogro->setUsuario($estudiante)
            ->setTipo(Notificacion::TIPO_LOGRO)
            ->setTitulo('¡Nuevo logro desbloqueado!')
            ->setContenido('Has desbloqueado el logro "Novato Aprendiz" por completar tu primer quiz')
            ->setUrl('/dashboard')
            ->setFechaCreacion(new \DateTime('-3 hours'));
        $manager->persist($notificacionLogro);

        // Notificación de mensaje en foro
        $notificacionMensaje = new Notificacion();
        $notificacionMensaje->setUsuario($estudiante)
            ->setTipo(Notificacion::TIPO_MENSAJE)
            ->setTitulo('Nuevo mensaje en el foro')
            ->setContenido('El profesor ha respondido a tu pregunta en el foro del curso')
            ->setUrl('/curso/1/foro')
            ->setFechaCreacion(new \DateTime('-30 minutes'));
        $manager->persist($notificacionMensaje);

        // Notificación de recordatorio
        $notificacionRecordatorio = new Notificacion();
        $notificacionRecordatorio->setUsuario($estudiante)
            ->setTipo(Notificacion::TIPO_RECORDATORIO)
            ->setTitulo('Recordatorio de entrega')
            ->setContenido('La tarea "Proyecto final: Calculadora simple" vence mañana a las 23:59')
            ->setUrl('/curso/1/tarea/2')
            ->setFechaCreacion(new \DateTime('+23 hours'));
        $manager->persist($notificacionRecordatorio);

        // Notificación de nuevo nivel
        $notificacionNivel = new Notificacion();
        $notificacionNivel->setUsuario($estudiante)
            ->setTipo(Notificacion::NUEVO_NIVEL)
            ->setTitulo('¡Has subido de nivel!')
            ->setContenido('Has alcanzado el nivel "Explorador". ¡Sigue así!')
            ->setUrl('/dashboard')
            ->setFechaCreacion(new \DateTime('-1 hour'));
        $manager->persist($notificacionNivel);

        // Notificación leída (para probar el filtrado)
        $notificacionLeida = new Notificacion();
        $notificacionLeida->setUsuario($estudiante)
            ->setTipo(Notificacion::TIPO_TAREA)
            ->setTitulo('Tarea calificada')
            ->setContenido('Una tarea anterior ha sido calificada')
            ->setUrl('/curso/1/tarea/1')
            ->setFechaCreacion(new \DateTime('-5 days'))
            ->setLeida(true);
        $manager->persist($notificacionLeida);

        $manager->flush();
    }

    public function getDependencies(): array
    {
        return [
            UsuarioFixtures::class
        ];
    }
}