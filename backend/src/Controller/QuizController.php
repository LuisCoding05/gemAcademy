<?php

namespace App\Controller;

use App\Entity\Curso;
use App\Entity\IntentoQuizz;
use App\Entity\Quizz;
use App\Entity\RespuestaQuizz;
use App\Entity\Usuario;
use App\Entity\UsuarioCurso;
use App\Entity\OpcionPregunta;
use App\Entity\PreguntaQuizz;
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
        private readonly EntityManagerInterface $entityManager
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
            }

            // Calcular nota sobre 10 con 2 decimales
            $notaFinal = ($puntuacionMaxima > 0) ? round(($puntuacionObtenida / $puntuacionMaxima) * 10, 2) : 0;
            
            // Actualizar el intento
            $intento->setFechaFin(new \DateTime());
            $intento->setCompletado(true);
            $intento->setPuntuacionTotal($puntuacionObtenida);
            $intento->setCalificacion(strval($notaFinal)); // Convertir a string ya que el campo es decimal en la BD

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

            $this->entityManager->flush();

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
}