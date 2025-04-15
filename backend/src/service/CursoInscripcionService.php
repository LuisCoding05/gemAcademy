<?php

namespace App\Service;

use App\Entity\Curso;
use App\Entity\EntregaTarea;
use App\Entity\IntentoQuizz;
use App\Entity\MaterialCompletado;
use App\Entity\Tarea;
use App\Entity\Usuario;
use App\Entity\UsuarioCurso;
use App\Entity\Quizz;
use Doctrine\ORM\EntityManagerInterface;

class CursoInscripcionService
{
    private $entityManager;

    public function __construct(EntityManagerInterface $entityManager)
    {
        $this->entityManager = $entityManager;
    }

    /**
     * Inscribe a un usuario en un curso y crea los registros iniciales
     */
    public function inscribirUsuarioEnCurso(Usuario $usuario, Curso $curso): UsuarioCurso
    {
        // Verificar si el usuario ya está inscrito
        $usuarioCurso = $this->entityManager->getRepository(UsuarioCurso::class)
            ->findOneBy([
                'idUsuario' => $usuario,
                'idCurso' => $curso
            ]);

        if ($usuarioCurso) {
            return $usuarioCurso;
        }

        // Crear nueva inscripción
        $usuarioCurso = new UsuarioCurso();
        $usuarioCurso->setIdUsuario($usuario);
        $usuarioCurso->setIdCurso($curso);
        $usuarioCurso->setMaterialesCompletados(0);
        $usuarioCurso->setTareasCompletadas(0);
        $usuarioCurso->setQuizzesCompletados(0);
        $usuarioCurso->setPorcentajeCompletado('0.00');
        $usuarioCurso->setUltimaActualizacion(new \DateTime());

        $this->entityManager->persist($usuarioCurso);
        $this->entityManager->flush();

        // Crear registros iniciales para materiales, tareas y quizzes
        $this->crearRegistrosIniciales($usuarioCurso, $curso);

        return $usuarioCurso;
    }

    /**
     * Crea los registros iniciales para un usuario en un curso
     */
    public function crearRegistrosIniciales(UsuarioCurso $usuarioCurso, Curso $curso): void
    {
        // Crear registros para tareas
        $tareas = $curso->getTareas();
        foreach ($tareas as $tarea) {
            $entregaTarea = new EntregaTarea();
            $entregaTarea->setIdTarea($tarea);
            $entregaTarea->setUsuarioCurso($usuarioCurso);
            $entregaTarea->setEstado(EntregaTarea::ESTADO_PENDIENTE);
            // Los demás campos se dejan como null hasta que el usuario entregue la tarea
            
            $this->entityManager->persist($entregaTarea);
        }

        // Crear registros para quizzes
        $quizzes = $curso->getQuizzs();
        foreach ($quizzes as $quiz) {
            $intentoQuizz = new IntentoQuizz();
            $intentoQuizz->setIdQuizz($quiz);
            $intentoQuizz->setIdUsuario($usuarioCurso->getIdUsuario());
            $intentoQuizz->setPuntuacionTotal(0);
            $intentoQuizz->setCompletado(false);
            
            $this->entityManager->persist($intentoQuizz);
        }

        $this->entityManager->flush();
    }
} 