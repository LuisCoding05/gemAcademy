<?php

namespace App\Controller;

use App\Entity\Curso;
use App\Entity\IntentoQuizz;
use App\Entity\Notificacion;
use App\Entity\Quizz;
use App\Entity\RespuestaQuizz;
use App\Entity\Usuario;
use App\Entity\UsuarioCurso;
use App\Entity\OpcionPregunta;
use App\Entity\PreguntaQuizz;
use App\Service\CursoInscripcionService;
use App\Service\NotificacionService;
use App\Service\LogroService;
use App\Service\NivelService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[IsGranted('ROLE_USER')]
final class QuizController extends AbstractController
{
    public function __construct(
        private readonly EntityManagerInterface $entityManager,
        private readonly CursoInscripcionService $cursoInscripcionService,
        private readonly NotificacionService $notificacionService,
        private readonly LogroService $logroService,
        private readonly NivelService $nivelService
    ) {}

    #[Route('/api/item/{id}/quiz/{quizId}', name: 'app_quiz_detail', methods: ['GET'])]
    public function quizDetail($id, $quizId): JsonResponse
    {
        // Obtener el curso
        $curso = $this->entityManager->getRepository(Curso::class)->find($id);
        if (!$curso) {
            return $this->json([
                'message' => 'Curso no encontrado'
            ], 404);
        }

        // Obtener el quiz
        $quiz = $this->entityManager->getRepository(Quizz::class)->find($quizId);
        if (!$quiz || $quiz->getIdCurso()->getId() != $id) {
            return $this->json([
                'message' => 'Quiz no encontrado'
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
                'message' => 'No tienes acceso a este quiz'
            ], 403);
        }

        // Obtener los intentos si existen
        $intentos = [];
        if ($usuario) {
            $intentos = $this->entityManager->getRepository(IntentoQuizz::class)
                ->findBy([
                    'idQuizz' => $quiz,
                    'idUsuario' => $usuario
                ], ['fechaInicio' => 'DESC']);
        }

        // Formatear los intentos
        $intentosData = [];
        foreach ($intentos as $intento) {
            $intentosData[] = [
                "id" => $intento->getId(),
                "fechaInicio" => $intento->getFechaInicio() ? $intento->getFechaInicio()->format('Y/m/d H:i:s') : "No comenzado",
                "fechaFin" => $intento->getFechaFin() ? $intento->getFechaFin()->format('Y/m/d H:i:s') : "No finalizado",
                "puntuacionTotal" => $intento->getPuntuacionTotal(),
                "completado" => $intento->isCompletado(),
                "calificacion" => $intento->getCalificacion()
            ];
        }

        // Formatear la respuesta
        $quizData = [
            "id" => $quiz->getId(),
            "titulo" => $quiz->getTitulo(),
            "descripcion" => $quiz->getDescripcion(),
            "fechaPublicacion" => $quiz->getFechaPublicacion()->format('Y/m/d H:i:s'),
            "fechaLimite" => $quiz->getFechaLimite()->format('Y/m/d H:i:s'),
            "puntos" => $quiz->getPuntosTotales(),
            "tiempoLimite" => $quiz->getTiempoLimite(),
            "intentosPermitidos" => $quiz->getIntentosPermitidos(),
            "intentos" => $intentosData
        ];

        return $this->json($quizData);
    }

    #[Route('/api/item/{id}/quiz/{quizId}/start', name: 'app_quiz_start', methods: ['POST'])]
    public function startQuiz($id, $quizId): JsonResponse
    {
        // Obtener el curso
        $curso = $this->entityManager->getRepository(Curso::class)->find($id);
        if (!$curso) {
            return $this->json([
                'message' => 'Curso no encontrado'
            ], 404);
        }

        // Obtener el quiz
        $quiz = $this->entityManager->getRepository(Quizz::class)->find($quizId);
        if (!$quiz || $quiz->getIdCurso()->getId() != $id) {
            return $this->json([
                'message' => 'Quiz no encontrado'
            ], 404);
        }

        // Verificar si el usuario está inscrito
        $user = $this->getUser();
        $usuario = $this->entityManager->getRepository(Usuario::class)
            ->findOneBy(['email' => $user->getUserIdentifier()]);
        
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

        // Verificar si tiene intentos disponibles
        $intentos = $this->entityManager->getRepository(IntentoQuizz::class)
            ->findBy([
                'idQuizz' => $quiz,
                'idUsuario' => $usuario,
                'completado' => true
            ]);

        if ($quiz->getIntentosPermitidos() !== 0 && count($intentos) >= $quiz->getIntentosPermitidos()) {
            return $this->json([
                'message' => 'Has alcanzado el número máximo de intentos permitidos'
            ], 400);
        }

        // Verificar si tiene un intento sin completar
        $intentoActivo = $this->entityManager->getRepository(IntentoQuizz::class)
            ->findOneBy([
                'idQuizz' => $quiz,
                'idUsuario' => $usuario,
                'completado' => false
            ]);

        if ($intentoActivo) {
            return $this->json([
                'message' => 'Ya tienes un intento sin completar'
            ], 400);
        }

        // Crear nuevo intento
        try {
            $intento = new IntentoQuizz();
            $intento->setIdQuizz($quiz);
            $intento->setIdUsuario($usuario);
            $intento->setFechaInicio(new \DateTime());
            $intento->setCompletado(false);
            
            $this->entityManager->persist($intento);
            $this->entityManager->flush();

            return $this->json([
                'id' => $intento->getId(),
                'fechaInicio' => $intento->getFechaInicio()->format('Y/m/d H:i:s'),
                'tiempoLimite' => $quiz->getTiempoLimite()
            ]);

        } catch (\Exception $e) {
            return $this->json([
                'message' => 'Error al crear el intento',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    #[Route('/api/item/{id}/quiz/{quizId}/preguntas/{intentoId}', name: 'app_quiz_preguntas', methods: ['GET'])]
    public function getQuizPreguntas($id, $quizId, $intentoId): JsonResponse
    {
        // Obtener el quiz y validar acceso
        $quiz = $this->entityManager->getRepository(Quizz::class)->find($quizId);
        if (!$quiz || $quiz->getIdCurso()->getId() != $id) {
            return $this->json([
                'message' => 'Quiz no encontrado'
            ], 404);
        }

        // Verificar el intento
        $intento = $this->entityManager->getRepository(IntentoQuizz::class)->find($intentoId);
        if (!$intento || $intento->getIdQuizz()->getId() !== $quiz->getId()) {
            return $this->json([
                'message' => 'Intento no válido'
            ], 404);
        }

        // Verificar que el intento pertenece al usuario actual
        $user = $this->getUser();
        $usuario = $this->entityManager->getRepository(Usuario::class)
            ->findOneBy(['email' => $user->getUserIdentifier()]);

        if ($intento->getIdUsuario()->getId() !== $usuario->getId()) {
            return $this->json([
                'message' => 'No tienes acceso a este intento'
            ], 403);
        }

        // Obtener preguntas y opciones
        $preguntas = $quiz->getPreguntaQuizzs();
        $preguntasData = [];

        foreach ($preguntas as $pregunta) {
            $opciones = [];
            foreach ($pregunta->getOpcionPreguntas() as $opcion) {
                $opciones[] = [
                    'id' => $opcion->getId(),
                    'texto' => $opcion->getTexto()
                ];
            }

            $preguntasData[] = [
                'id' => $pregunta->getId(),
                'pregunta' => $pregunta->getPregunta(),
                'puntos' => $pregunta->getPuntos(),
                'orden' => $pregunta->getOrden(),
                'opciones' => $opciones
            ];
        }

        // Ordenar preguntas por orden si está definido
        usort($preguntasData, function($a, $b) {
            if ($a['orden'] === null && $b['orden'] === null) return 0;
            if ($a['orden'] === null) return 1;
            if ($b['orden'] === null) return -1;
            return $a['orden'] - $b['orden'];
        });

        return $this->json($preguntasData);
    }

    #[Route('/api/item/{id}/quiz/{quizId}/submit/{intentoId}', name: 'app_quiz_submit', methods: ['POST'])]
    public function submitQuiz($id, $quizId, $intentoId, Request $request): JsonResponse
    {
        // Obtener el quiz y validar acceso
        $quiz = $this->entityManager->getRepository(Quizz::class)->find($quizId);
        if (!$quiz || $quiz->getIdCurso()->getId() != $id) {
            return $this->json([
                'message' => 'Quiz no encontrado'
            ], 404);
        }

        // Verificar el intento
        $intento = $this->entityManager->getRepository(IntentoQuizz::class)->find($intentoId);
        if (!$intento || $intento->getIdQuizz()->getId() !== $quiz->getId()) {
            return $this->json([
                'message' => 'Intento no válido'
            ], 404);
        }

        // Verificar que el intento pertenece al usuario actual
        $user = $this->getUser();
        $usuario = $this->entityManager->getRepository(Usuario::class)
            ->findOneBy(['email' => $user->getUserIdentifier()]);

        if ($intento->getIdUsuario()->getId() !== $usuario->getId()) {
            return $this->json([
                'message' => 'No tienes acceso a este intento'
            ], 403);
        }

        // Procesar respuestas
        $data = json_decode($request->getContent(), true);
        $respuestas = $data['respuestas'] ?? [];

        try {
            // Calcular puntuación total y máxima posible
            $puntuacionMaxima = 0;
            $puntuacionObtenida = 0;
            
            // Obtener todas las preguntas del quiz
            $preguntas = $quiz->getPreguntaQuizzs();
            foreach ($preguntas as $pregunta) {
                $puntuacionMaxima += $pregunta->getPuntos();
            }

            // Procesar respuestas y calcular puntuación
            foreach ($respuestas as $preguntaId => $opcionId) {
                $pregunta = $this->entityManager->getRepository(PreguntaQuizz::class)->find($preguntaId);
                $opcion = $this->entityManager->getRepository(OpcionPregunta::class)->find($opcionId);

                if (!$pregunta || !$opcion || $opcion->getIdPregunta()->getId() !== $pregunta->getId()) {
                    continue;
                }

                $respuesta = new RespuestaQuizz();
                $respuesta->setIdIntento($intento);
                $respuesta->setIdPregunta($pregunta);
                $respuesta->setRespuesta($opcion->getTexto());
                $respuesta->setEsCorrecta($opcion->isEsCorrecta());
                
                if ($opcion->isEsCorrecta()) {
                    $respuesta->setPuntosObtenidos($pregunta->getPuntos());
                    $puntuacionObtenida += $pregunta->getPuntos();
                } else {
                    $respuesta->setPuntosObtenidos(0);
                }

                $this->entityManager->persist($respuesta);
                $intento->addRespuestaQuizz($respuesta);
            }

            // Calcular nota sobre 10 con 2 decimales
            $notaFinal = ($puntuacionMaxima > 0) ? round(($puntuacionObtenida / $puntuacionMaxima) * 10, 2) : 0;
            
            // Actualizar el intento
            $intento->setFechaFin(new \DateTime());
            $intento->setCompletado(true);
            $intento->setPuntuacionTotal($puntuacionObtenida);
            $intento->setCalificacion(strval($notaFinal));

            // Otorgar puntos al usuario basado en la puntuación obtenida
            $this->nivelService->agregarPuntos($intento->getIdUsuario(), $puntuacionObtenida);

            // Actualizar estadísticas del usuario en el curso
            $usuarioCurso = $this->entityManager->getRepository(UsuarioCurso::class)
                ->findOneBy([
                    'idUsuario' => $usuario,
                    'idCurso' => $quiz->getIdCurso()
                ]);

            if ($usuarioCurso) {
                // Verificar si es el primer intento completado de este quiz
                $intentosAnteriores = $this->entityManager->getRepository(IntentoQuizz::class)
                    ->findBy([
                        'idQuizz' => $quiz,
                        'idUsuario' => $usuario,
                        'completado' => true
                    ]);

                if (count($intentosAnteriores) === 0) {
                    $quizzesCompletados = $usuarioCurso->getQuizzesCompletados() ?? 0;
                    $usuarioCurso->setQuizzesCompletados($quizzesCompletados + 1);

                    // Actualizar el porcentaje completado
                    $totalItems = $quiz->getIdCurso()->getTotalItems();
                    $itemsCompletados = $usuarioCurso->getMaterialesCompletados() + 
                                    $usuarioCurso->getTareasCompletadas() + 
                                    $usuarioCurso->getQuizzesCompletados();
                    
                    $porcentajeCompletado = ($totalItems > 0) ? 
                        round(($itemsCompletados / $totalItems) * 100, 2) : 0;
                    
                    $usuarioCurso->setPorcentajeCompletado(strval($porcentajeCompletado));
                }
                
                $usuarioCurso->setUltimaActualizacion(new \DateTime());
            }

            // Flush antes de verificar logros para asegurar que las respuestas están guardadas
            $this->entityManager->flush();

            if ($usuarioCurso) {
                $this->handleQuizCompletion($intento);
            }

            return $this->json([
                'message' => 'Quiz enviado correctamente',
                'puntuacionTotal' => $puntuacionObtenida,
                'puntuacionMaxima' => $puntuacionMaxima,
                'calificacion' => $notaFinal
            ]);

        } catch (\Exception $e) {
            return $this->json([
                'message' => 'Error al enviar el quiz',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    #[Route('/api/item/{id}/quiz/{quizId}/check-abandoned/{intentoId}', name: 'app_quiz_check_abandoned', methods: ['POST'])]
    public function checkAbandonedAttempt($id, $quizId, $intentoId): JsonResponse
    {
        // Obtener el quiz y validar acceso
        $quiz = $this->entityManager->getRepository(Quizz::class)->find($quizId);
        if (!$quiz || $quiz->getIdCurso()->getId() != $id) {
            return $this->json([
                'message' => 'Quiz no encontrado'
            ], 404);
        }

        // Verificar el intento
        $intento = $this->entityManager->getRepository(IntentoQuizz::class)->find($intentoId);
        if (!$intento || $intento->getIdQuizz()->getId() !== $quiz->getId()) {
            return $this->json([
                'message' => 'Intento no válido'
            ], 404);
        }

        // Verificar si el intento está abandonado
        $fechaInicio = $intento->getFechaInicio();
        $ahora = new \DateTime();
        $diferencia = $ahora->diff($fechaInicio);
        $minutosTranscurridos = ($diferencia->days * 24 * 60) + ($diferencia->h * 60) + $diferencia->i;

        if ($minutosTranscurridos >= $quiz->getTiempoLimite() && !$intento->isCompletado()) {
            // Marcar el intento como completado
            $intento->setCompletado(true);
            $intento->setFechaFin($ahora);
            $intento->setPuntuacionTotal(0); // Si se abandona, la puntuación es 0
            
            $this->entityManager->flush();

            return $this->json([
                'message' => 'Intento finalizado por tiempo excedido',
                'abandoned' => true
            ]);
        }

        return $this->json([
            'message' => 'Intento válido',
            'abandoned' => false,
            'tiempoRestante' => ($quiz->getTiempoLimite() * 60) - ($minutosTranscurridos * 60)
        ]);
    }

    #[Route('/api/item/{id}/quiz/{quizId}/results/{intentoId}', name: 'app_quiz_results', methods: ['GET'])]
    public function getQuizResults($id, $quizId, $intentoId): JsonResponse
    {
        // Obtener el quiz y validar acceso
        $quiz = $this->entityManager->getRepository(Quizz::class)->find($quizId);
        if (!$quiz || $quiz->getIdCurso()->getId() != $id) {
            return $this->json([
                'message' => 'Quiz no encontrado'
            ], 404);
        }

        // Verificar el intento
        $intento = $this->entityManager->getRepository(IntentoQuizz::class)->find($intentoId);
        if (!$intento || $intento->getIdQuizz()->getId() !== $quiz->getId()) {
            return $this->json([
                'message' => 'Intento no válido'
            ], 404);
        }

        // Verificar que el intento esté completado
        if (!$intento->isCompletado()) {
            return $this->json([
                'message' => 'El intento no ha sido completado aún'
            ], 400);
        }

        // Verificar que el usuario tenga acceso a este intento
        $user = $this->getUser();
        $usuario = $this->entityManager->getRepository(Usuario::class)
            ->findOneBy(['email' => $user->getUserIdentifier()]);

        // Permitir acceso al estudiante que hizo el intento o al profesor del curso
        $isOwner = $intento->getIdUsuario()->getId() === $usuario->getId();
        $isProfesor = $quiz->getIdCurso()->getProfesor()->getId() === $usuario->getId();

        if (!$isOwner && !$isProfesor) {
            return $this->json([
                'message' => 'No tienes acceso a estos resultados'
            ], 403);
        }

        // Obtener todas las respuestas del intento
        $respuestas = $this->entityManager->getRepository(RespuestaQuizz::class)
            ->findBy(['idIntento' => $intento]);

        // Crear un mapa de respuestas por pregunta
        $respuestasPorPregunta = [];
        foreach ($respuestas as $respuesta) {
            $respuestasPorPregunta[$respuesta->getIdPregunta()->getId()] = $respuesta;
        }

        // Obtener todas las preguntas del quiz con sus opciones
        $preguntas = $quiz->getPreguntaQuizzs();
        $resultados = [];

        foreach ($preguntas as $pregunta) {
            $respuestaUsuario = $respuestasPorPregunta[$pregunta->getId()] ?? null;
            
            // Obtener todas las opciones de la pregunta
            $opciones = [];
            $opcionCorrecta = null;
            $opcionSeleccionada = null;

            foreach ($pregunta->getOpcionPreguntas() as $opcion) {
                $opcionData = [
                    'id' => $opcion->getId(),
                    'texto' => $opcion->getTexto(),
                    'esCorrecta' => $opcion->isEsCorrecta(),
                    'retroalimentacion' => $opcion->getRetroalimentacion()
                ];

                $opciones[] = $opcionData;

                if ($opcion->isEsCorrecta()) {
                    $opcionCorrecta = $opcionData;
                }

                // Identificar la opción seleccionada por el usuario
                if ($respuestaUsuario && $respuestaUsuario->getRespuesta() === $opcion->getTexto()) {
                    $opcionSeleccionada = $opcionData;
                }
            }

            $resultados[] = [
                'pregunta' => [
                    'id' => $pregunta->getId(),
                    'texto' => $pregunta->getPregunta(),
                    'puntos' => $pregunta->getPuntos(),
                    'orden' => $pregunta->getOrden()
                ],
                'opciones' => $opciones,
                'respuestaUsuario' => $opcionSeleccionada,
                'respuestaCorrecta' => $opcionCorrecta,
                'esCorrecta' => $respuestaUsuario ? $respuestaUsuario->isEsCorrecta() : false,
                'puntosObtenidos' => $respuestaUsuario ? $respuestaUsuario->getPuntosObtenidos() : 0,
                'retroalimentacion' => $opcionSeleccionada ? $opcionSeleccionada['retroalimentacion'] : null
            ];
        }

        // Ordenar por orden de pregunta
        usort($resultados, function($a, $b) {
            if ($a['pregunta']['orden'] === null && $b['pregunta']['orden'] === null) return 0;
            if ($a['pregunta']['orden'] === null) return 1;
            if ($b['pregunta']['orden'] === null) return -1;
            return $a['pregunta']['orden'] - $b['pregunta']['orden'];
        });

        // Calcular estadísticas generales
        $totalPreguntas = count($resultados);
        $preguntasCorrectas = array_reduce($resultados, function($count, $resultado) {
            return $count + ($resultado['esCorrecta'] ? 1 : 0);
        }, 0);

        return $this->json([
            'intento' => [
                'id' => $intento->getId(),
                'fechaInicio' => $intento->getFechaInicio()->format('Y-m-d H:i:s'),
                'fechaFin' => $intento->getFechaFin()->format('Y-m-d H:i:s'),
                'puntuacionTotal' => $intento->getPuntuacionTotal(),
                'calificacion' => $intento->getCalificacion(),
                'completado' => $intento->isCompletado()
            ],
            'quiz' => [
                'id' => $quiz->getId(),
                'titulo' => $quiz->getTitulo(),
                'descripcion' => $quiz->getDescripcion(),
                'puntosTotales' => $quiz->getPuntosTotales()
            ],
            'estadisticas' => [
                'totalPreguntas' => $totalPreguntas,
                'preguntasCorrectas' => $preguntasCorrectas,
                'preguntasIncorrectas' => $totalPreguntas - $preguntasCorrectas,
                'porcentajeAcierto' => $totalPreguntas > 0 ? round(($preguntasCorrectas / $totalPreguntas) * 100, 2) : 0
            ],
            'resultados' => $resultados
        ]);
    }

    // After a quiz attempt is completed and graded
    private function handleQuizCompletion(IntentoQuizz $intento): void
    {
        $usuarioCurso = $this->entityManager->getRepository(UsuarioCurso::class)
            ->findOneBy([
                'idUsuario' => $intento->getIdUsuario(),
                'idCurso' => $intento->getIdQuizz()->getIdCurso()
            ]);

        if ($usuarioCurso) {
            // Verificar si es el primer intento completado de este quiz específico
            $intentosAnteriores = $this->entityManager->getRepository(IntentoQuizz::class)
                ->findBy([
                    'idQuizz' => $intento->getIdQuizz(),
                    'idUsuario' => $intento->getIdUsuario(),
                    'completado' => true
                ]);

            // Si solo hay un intento (el actual) para este quiz, incrementar el contador
            if (count($intentosAnteriores) === 1 && $intentosAnteriores[0]->getId() === $intento->getId()) {
                $quizzesCompletados = $usuarioCurso->getQuizzesCompletados();
                $usuarioCurso->setQuizzesCompletados($quizzesCompletados + 1);
                
                // Actualizar el porcentaje usando el servicio centralizado
                $this->cursoInscripcionService->calcularPorcentaje($usuarioCurso);
            }

            // Calcular el nuevo promedio
            $this->cursoInscripcionService->calcularPromedio($usuarioCurso);
            
            // Verificar logros
            $this->logroService->verificarLogrosQuiz($intento);
            $this->logroService->verificarLogrosCurso($usuarioCurso);
            
            // Notificar al profesor
            $this->notificacionService->crearNotificacion(
                $intento->getIdQuizz()->getIdCurso()->getProfesor(),
                Notificacion::TIPO_TAREA,
                'Quiz completado por estudiante',
                sprintf(
                    'El estudiante %s ha completado el quiz "%s" con una calificación de %s/10',
                    $intento->getIdUsuario()->getNombre() . ' ' . $intento->getIdUsuario()->getApellido(),
                    $intento->getIdQuizz()->getTitulo(),
                    $intento->getCalificacion()
                ),
                sprintf('/cursos/%d/quiz/%d', 
                    $intento->getIdQuizz()->getIdCurso()->getId(),
                    $intento->getIdQuizz()->getId()
                )
            );
        }
    }

}