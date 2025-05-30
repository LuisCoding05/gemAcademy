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
    public function __construct(
        private readonly EntityManagerInterface $entityManager,
        private readonly LogroService $logroService
    ) {}

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

        $this->entityManager->flush();

        // Calcular el promedio y el porcentaje de progreso
        $this->calcularPorcentaje($usuarioCurso);
        $this->calcularPromedio($usuarioCurso);
    }

    public function calcularPorcentaje(UsuarioCurso $usuarioCurso): string
    {
        $curso = $usuarioCurso->getIdCurso();
        $totalItems = $curso->getTotalItems();

        // Si el curso no tiene items, el porcentaje es 0
        if ($totalItems === 0) {
            $nuevoProgreso = '0.00';
            $usuarioCurso->setPorcentajeCompletado($nuevoProgreso);
            $usuarioCurso->setUltimaActualizacion(new \DateTime());
            $this->entityManager->flush();
            return $nuevoProgreso;
        }

        // Calcular items completados
        $itemsCompletados = 
            $usuarioCurso->getMaterialesCompletados() + 
            $usuarioCurso->getTareasCompletadas() + 
            $usuarioCurso->getQuizzesCompletados();

        // Calcular el porcentaje con 2 decimales
        $nuevoProgreso = number_format(($itemsCompletados / $totalItems) * 100, 2, '.', '');
        
        // Actualizar el porcentaje y la fecha
        $usuarioCurso->setPorcentajeCompletado($nuevoProgreso);
        $usuarioCurso->setUltimaActualizacion(new \DateTime());
        
        // Verificar logros si el porcentaje cambió
        $this->logroService->verificarLogrosCurso($usuarioCurso);
        
        $this->entityManager->flush();
        
        return $nuevoProgreso;
    }

    public function calcularPromedio(UsuarioCurso $usuarioCurso): void
    {
        $curso = $usuarioCurso->getIdCurso();
        $sumaCalificaciones = 0;
        $totalItems = 0;

        // Calcular promedio de tareas entregadas y calificadas
        $entregasTareas = $this->entityManager->getRepository(EntregaTarea::class)
            ->findBy(['usuarioCurso' => $usuarioCurso]);

        foreach ($entregasTareas as $entrega) {
            if ($entrega->isCalificado() && $entrega->getCalificacion() !== null) {
                $sumaCalificaciones += floatval($entrega->getCalificacion());
                $totalItems++;
            }
        }

        // Calcular promedio de quizzes (solo último intento)
        foreach ($curso->getQuizzs() as $quizz) {
            $ultimoIntento = $this->entityManager->getRepository(IntentoQuizz::class)
                ->findOneBy(
                    ['idQuizz' => $quizz, 'idUsuario' => $usuarioCurso->getIdUsuario()],
                    ['fechaFin' => 'DESC']
                );

            if ($ultimoIntento && $ultimoIntento->getCalificacion() !== null) {
                $sumaCalificaciones += floatval($ultimoIntento->getCalificacion());
                $totalItems++;
            }
        }

        // Calcular y guardar el promedio
        if ($totalItems > 0) {
            $promedio = number_format($sumaCalificaciones / $totalItems, 2);
            $usuarioCurso->setPromedio($promedio);
            $usuarioCurso->setUltimaActualizacion(new \DateTime());
            $this->entityManager->flush();
        }
    }
}