<?php

namespace App\Service;

use App\Entity\IntentoQuizz;
use App\Entity\Logro;
use App\Entity\Usuario;
use App\Entity\UsuarioLogro;
use App\Entity\EntregaTarea;
use App\Entity\UsuarioCurso;
use App\Entity\RespuestaQuizz;
use App\Entity\Notificacion;
use Doctrine\ORM\EntityManagerInterface;

class LogroService
{
    public function __construct(
        private readonly EntityManagerInterface $entityManager,
        private readonly NotificacionService $notificacionService
    ) {}

    public function verificarLogrosQuiz(IntentoQuizz $intento): void
    {
        $usuario = $intento->getIdUsuario();
        $calificacion = floatval($intento->getCalificacion());
        
        // Verificar primer quiz completado
        $this->verificarPrimerQuizCompletado($usuario);
        
        // Verificar logro de 50% o más
        if ($calificacion >= 5.0) {
            $this->otorgarLogro($usuario, 'Lanza Proyectiles');
        }
        
        // Verificar quiz perfecto y quiz con solo 1 fallo
        $respuestasIncorrectas = $this->entityManager->getRepository(RespuestaQuizz::class)
            ->count([
                'idIntento' => $intento,
                'esCorrecta' => false
            ]);
        
        if ($respuestasIncorrectas === 1) {
            $this->otorgarLogro($usuario, 'Ultra Instinto Sin Dominar');
        } elseif ($respuestasIncorrectas === 0) {
            $this->otorgarLogro($usuario, 'Ultra Instinto Dominado');
            
            // Verificar 3 quizzes perfectos seguidos
            $ultimosIntentos = $this->entityManager->getRepository(IntentoQuizz::class)
                ->findBy(
                    ['idUsuario' => $usuario],
                    ['fechaFin' => 'DESC'],
                    3
                );
            
            $todossonPerfectos = true;
            foreach ($ultimosIntentos as $intentoPrevio) {
                $respuestasIncorrectasPrevias = $this->entityManager->getRepository(RespuestaQuizz::class)
                    ->count([
                        'idIntento' => $intentoPrevio,
                        'esCorrecta' => false
                    ]);
                if ($respuestasIncorrectasPrevias > 0) {
                    $todossonPerfectos = false;
                    break;
                }
            }
            
            if ($todossonPerfectos && count($ultimosIntentos) === 3) {
                $this->otorgarLogro($usuario, 'Six eyes unlocked');
            }
        }
    }

    public function verificarLogrosTarea(EntregaTarea $entrega): void
    {
        $usuario = $entrega->getUsuarioCurso()->getIdUsuario();
        
        // Verificar primera tarea
        $this->verificarPrimeraTarea($usuario);
        
        // Verificar 5 tareas completadas
        $qb = $this->entityManager->createQueryBuilder();
        $qb->select('COUNT(et)')
           ->from(EntregaTarea::class, 'et')
           ->innerJoin('et.usuarioCurso', 'uc')
           ->where('uc.idUsuario = :usuario')
           ->andWhere('et.estado IN (:estados)')
           ->setParameter('usuario', $usuario)
           ->setParameter('estados', [EntregaTarea::ESTADO_ENTREGADO, EntregaTarea::ESTADO_CALIFICADO]);
        
        $tareasCompletadas = $qb->getQuery()->getSingleScalarResult();
        
        if ($tareasCompletadas >= 5) {
            $this->otorgarLogro($usuario, 'Maestro Constructor');
        }
        
        // Verificar entrega antes de fecha límite
        if ($entrega->getFechaEntrega() < $entrega->getIdTarea()->getFechaLimite()) {
            $this->otorgarLogro($usuario, 'Crono-Maestro');
        }
    }

    public function verificarLogrosCurso(UsuarioCurso $usuarioCurso): void
    {
        $porcentaje = floatval($usuarioCurso->getPorcentajeCompletado());
        if ($porcentaje >= 100) {
            // Verificar nivel basado en cursos completados
            $cursosCompletados = $this->entityManager->getRepository(UsuarioCurso::class)
                ->count([
                    'idUsuario' => $usuarioCurso->getIdUsuario(),
                    'porcentajeCompletado' => '100.00'
                ]);
            
            if ($cursosCompletados >= 8) {
                $this->otorgarLogro($usuarioCurso->getIdUsuario(), 'Ascensión Divina');
            } elseif ($cursosCompletados >= 7) {
                $this->otorgarLogro($usuarioCurso->getIdUsuario(), 'Nivel 10 – Sabio Místico');
            } elseif ($cursosCompletados >= 6) {
                $this->otorgarLogro($usuarioCurso->getIdUsuario(), 'Nivel 5 – Guerrero');
            } elseif ($cursosCompletados >= 2) {
                $this->otorgarLogro($usuarioCurso->getIdUsuario(), 'Nivel 1 – Aventurero');
            }
        }
    }

    private function verificarPrimerQuizCompletado(Usuario $usuario): void
    {
        $intentosCompletados = $this->entityManager->getRepository(IntentoQuizz::class)
            ->count(['idUsuario' => $usuario, 'completado' => true]);
        
        if ($intentosCompletados === 1) {
            $this->otorgarLogro($usuario, 'Novato Aprendiz');
        }
    }

    private function verificarPrimeraTarea(Usuario $usuario): void
    {
        $qb = $this->entityManager->createQueryBuilder();
        $qb->select('COUNT(et)')
           ->from(EntregaTarea::class, 'et')
           ->innerJoin('et.usuarioCurso', 'uc')
           ->where('uc.idUsuario = :usuario')
           ->andWhere('et.estado IN (:estados)')
           ->setParameter('usuario', $usuario)
           ->setParameter('estados', [EntregaTarea::ESTADO_ENTREGADO, EntregaTarea::ESTADO_CALIFICADO]);
        
        $tareasCompletadas = $qb->getQuery()->getSingleScalarResult();
        
        if ($tareasCompletadas === 1) {
            $this->otorgarLogro($usuario, 'Entrenamiento Básico');
        }
    }

    private function otorgarLogro(Usuario $usuario, string $tituloLogro): void
    {
        // Verificar si el logro ya fue otorgado
        $logro = $this->entityManager->getRepository(Logro::class)
            ->findOneBy(['titulo' => $tituloLogro]);
        
        if (!$logro) {
            return;
        }
        
        $usuarioLogro = $this->entityManager->getRepository(UsuarioLogro::class)
            ->findOneBy([
                'idUsuario' => $usuario,
                'idLogro' => $logro
            ]);
        
        if (!$usuarioLogro) {
            $usuarioLogro = new UsuarioLogro();
            $usuarioLogro->setIdUsuario($usuario);
            $usuarioLogro->setIdLogro($logro);
            $usuarioLogro->setFechaObtencion(new \DateTime());
            
            $this->entityManager->persist($usuarioLogro);
            $this->entityManager->flush();

            // Crear notificación del logro
            $this->notificacionService->crearNotificacion(
                $usuario,
                Notificacion::TIPO_LOGRO,
                '¡Nuevo logro desbloqueado!',
                sprintf('Has desbloqueado el logro "%s" - %s', $logro->getTitulo(), $logro->getMotivo()),
                '/dashboard'  // URL donde el usuario puede ver sus logros
            );
        }
    }
}