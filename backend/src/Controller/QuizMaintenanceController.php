<?php

namespace App\Controller;

use App\Entity\Curso;
use App\Entity\IntentoQuizz;
use App\Entity\Notificacion;
use App\Entity\OpcionPregunta;
use App\Entity\PreguntaQuizz;
use App\Entity\Quizz;
use App\Entity\Usuario;
use App\Entity\UsuarioCurso;
use App\Service\NotificacionService;
use DateTime;
use Doctrine\ORM\EntityManagerInterface;
use Exception;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[IsGranted('ROLE_USER')]
final class QuizMaintenanceController extends AbstractController
{
    public function __construct(
        private readonly EntityManagerInterface $entityManager,
        private readonly NotificacionService $notificacionService
    ) {}
    #[Route('/quiz/maintenance', name: 'app_quiz_maintenance')]
    public function index(): JsonResponse
    {
        return $this->json([
            'message' => 'Welcome to your new controller!',
            'path' => 'src/Controller/QuizMaintenanceController.php',
        ]);
    }

    private function actualizarPuntosTotalesQuiz(Quizz $quiz): void
    {
        $puntosTotales = 0;
        foreach ($quiz->getPreguntaQuizzs() as $pregunta) {
            $puntosTotales += $pregunta->getPuntos();
        }
        $quiz->setPuntosTotales($puntosTotales);
    }

    #[Route('/api/item/{id}/quiz/create', name: 'app_quiz_create', methods: ['POST'])]
    public function createQuiz($id, Request $request): JsonResponse
    {
        // Obtener el curso
        $curso = $this->entityManager->getRepository(Curso::class)->find($id);
        if (!$curso) {
            return $this->json([
                'message' => 'Curso no encontrado'
            ], 404);
        }

        // Verificar si el usuario es el profesor del curso
        $user = $this->getUser();
        $usuario = $this->entityManager->getRepository(Usuario::class)
            ->findOneBy(['email' => $user->getUserIdentifier()]);

        if ($curso->getProfesor()->getId() !== $usuario->getId()) {
            return $this->json([
                'message' => 'Solo el profesor del curso puede crear quizzes'
            ], 403);
        }

        try {
            $data = json_decode($request->getContent(), true);

            $quiz = new Quizz();
            $quiz->setTitulo($data['titulo']);
            $quiz->setDescripcion($data['descripcion'] ?? null);
            $quiz->setFechaPublicacion(new DateTime());
            $quiz->setFechaLimite(new DateTime($data['fechaLimite']));
            $quiz->setTiempoLimite($data['tiempoLimite'] ?? 30);
            $quiz->setPuntosTotales(0); // Inicialmente 0, se actualizará al añadir preguntas
            $quiz->setIntentosPermitidos($data['intentosPermitidos'] ?? 1);
            $quiz->setIdCurso($curso);

            $this->entityManager->persist($quiz);

            // Notificar a todos los estudiantes sobre el nuevo quiz
            $usuariosCurso = $this->entityManager->getRepository(UsuarioCurso::class)
                ->findBy(['idCurso' => $curso]);

            foreach ($usuariosCurso as $usuarioCurso) {
                $this->notificacionService->crearNotificacion(
                    $usuarioCurso->getIdUsuario(),
                    Notificacion::TIPO_TAREA,
                    'Nuevo quiz disponible',
                    sprintf(
                        'Se ha publicado un nuevo quiz: "%s". Fecha límite: %s',
                        $quiz->getTitulo(),
                        $quiz->getFechaLimite()->format('d/m/Y H:i')
                    ),
                    sprintf('/cursos/%d/quiz/%d', $id, $quiz->getId())
                );
            }

            $this->entityManager->flush();

            return $this->json([
                'message' => 'Quiz creado correctamente',
                'id' => $quiz->getId()
            ], 201);

        } catch (Exception $e) {
            return $this->json([
                'message' => 'Error al crear el quiz',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    #[Route('/api/item/{id}/quiz/{quizId}', name: 'app_quiz_update', methods: ['PUT'])]
    public function updateQuiz($id, $quizId, Request $request): JsonResponse
    {
        // Obtener el quiz y validar acceso
        $quiz = $this->entityManager->getRepository(Quizz::class)->find($quizId);
        if (!$quiz || $quiz->getIdCurso()->getId() != $id) {
            return $this->json([
                'message' => 'Quiz no encontrado'
            ], 404);
        }

        // Verificar si el usuario es el profesor del curso
        $user = $this->getUser();
        $usuario = $this->entityManager->getRepository(Usuario::class)
            ->findOneBy(['email' => $user->getUserIdentifier()]);

        if ($quiz->getIdCurso()->getProfesor()->getId() !== $usuario->getId()) {
            return $this->json([
                'message' => 'Solo el profesor del curso puede editar quizzes'
            ], 403);
        }

        try {
            $data = json_decode($request->getContent(), true);

            if (isset($data['titulo'])) {
                $quiz->setTitulo($data['titulo']);
            }
            if (isset($data['descripcion'])) {
                $quiz->setDescripcion($data['descripcion']);
            }
            if (isset($data['fechaLimite'])) {
                $quiz->setFechaLimite(new DateTime($data['fechaLimite']));
            }
            if (isset($data['tiempoLimite'])) {
                $quiz->setTiempoLimite($data['tiempoLimite']);
            }
            if (isset($data['intentosPermitidos'])) {
                $quiz->setIntentosPermitidos($data['intentosPermitidos']);
            }

            // Actualizar puntos totales basado en las preguntas existentes
            $this->actualizarPuntosTotalesQuiz($quiz);

            $this->entityManager->flush();

            return $this->json([
                'message' => 'Quiz actualizado correctamente',
                'puntosTotales' => $quiz->getPuntosTotales() // Devolver los puntos actualizados
            ]);

        } catch (Exception $e) {
            return $this->json([
                'message' => 'Error al actualizar el quiz',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    #[Route('/api/item/{id}/quiz/{quizId}', name: 'app_quiz_delete', methods: ['DELETE'])]
    public function deleteQuiz($id, $quizId): JsonResponse
    {
        // Obtener el quiz y validar acceso
        $quiz = $this->entityManager->getRepository(Quizz::class)->find($quizId);
        if (!$quiz || $quiz->getIdCurso()->getId() != $id) {
            return $this->json([
                'message' => 'Quiz no encontrado'
            ], 404);
        }

        // Verificar si el usuario es el profesor del curso
        $user = $this->getUser();
        $usuario = $this->entityManager->getRepository(Usuario::class)
            ->findOneBy(['email' => $user->getUserIdentifier()]);

        if ($quiz->getIdCurso()->getProfesor()->getId() !== $usuario->getId()) {
            return $this->json([
                'message' => 'Solo el profesor del curso puede eliminar quizzes'
            ], 403);
        }

        try {
            // Obtener todos los usuarios que han completado este quiz
            $intentosCompletados = $this->entityManager->getRepository(IntentoQuizz::class)
                ->findBy([
                    'idQuizz' => $quiz,
                    'completado' => true
                ]);

            // Crear un conjunto de usuarios únicos que completaron el quiz
            $usuariosCompletados = [];
            foreach ($intentosCompletados as $intento) {
                $usuarioId = $intento->getIdUsuario()->getId();
                if (!isset($usuariosCompletados[$usuarioId])) {
                    $usuariosCompletados[$usuarioId] = $intento->getIdUsuario();
                }
            }

            // Actualizar las estadísticas de cada usuario
            foreach ($usuariosCompletados as $usuario) {
                $usuarioCurso = $this->entityManager->getRepository(UsuarioCurso::class)
                    ->findOneBy([
                        'idUsuario' => $usuario,
                        'idCurso' => $quiz->getIdCurso()
                    ]);

                if ($usuarioCurso) {
                    // Decrementar quizzes completados
                    $quizzesCompletados = $usuarioCurso->getQuizzesCompletados();
                    $usuarioCurso->setQuizzesCompletados(max(0, $quizzesCompletados - 1));

                    // Recalcular porcentaje completado
                    $totalItems = $quiz->getIdCurso()->getTotalItems();
                    $itemsCompletados = $usuarioCurso->getMaterialesCompletados() + 
                                    $usuarioCurso->getTareasCompletadas() + 
                                    $usuarioCurso->getQuizzesCompletados();
                    
                    $porcentajeCompletado = ($totalItems > 0) ? 
                        round(($itemsCompletados / $totalItems) * 100, 2) : 0;
                    
                    $usuarioCurso->setPorcentajeCompletado(strval($porcentajeCompletado));
                    $usuarioCurso->setUltimaActualizacion(new DateTime());
                }
            }

            // Eliminar el quiz (esto eliminará en cascada los intentos, preguntas y opciones)
            $this->entityManager->remove($quiz);
            $this->entityManager->flush();

            return $this->json([
                'message' => 'Quiz eliminado correctamente'
            ]);

        } catch (Exception $e) {
            return $this->json([
                'message' => 'Error al eliminar el quiz',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    #[Route('/api/item/{id}/quiz/{quizId}/preguntas', name: 'app_quiz_pregunta_create', methods: ['POST'])]
    public function createPregunta($id, $quizId, Request $request): JsonResponse
    {
        // Obtener el quiz y validar acceso
        $quiz = $this->entityManager->getRepository(Quizz::class)->find($quizId);
        if (!$quiz || $quiz->getIdCurso()->getId() != $id) {
            return $this->json([
                'message' => 'Quiz no encontrado'
            ], 404);
        }

        // Verificar si el usuario es el profesor del curso
        $user = $this->getUser();
        $usuario = $this->entityManager->getRepository(Usuario::class)
            ->findOneBy(['email' => $user->getUserIdentifier()]);

        if ($quiz->getIdCurso()->getProfesor()->getId() !== $usuario->getId()) {
            return $this->json([
                'message' => 'Solo el profesor del curso puede añadir preguntas'
            ], 403);
        }

        try {
            $data = json_decode($request->getContent(), true);

            $pregunta = new PreguntaQuizz();
            $pregunta->setPregunta($data['pregunta']);
            $pregunta->setPuntos($data['puntos'] ?? 10);
            $pregunta->setOrden($data['orden'] ?? null);
            $pregunta->setIdQuizz($quiz);

            // Validar y crear opciones
            if (!isset($data['opciones']) || !is_array($data['opciones']) || count($data['opciones']) < 2) {
                return $this->json([
                    'message' => 'Debe proporcionar al menos 2 opciones'
                ], 400);
            }

            // Validar que hay al menos una opción correcta
            $tieneOpcionCorrecta = false;
            foreach ($data['opciones'] as $opcionData) {
                if (isset($opcionData['esCorrecta']) && $opcionData['esCorrecta']) {
                    $tieneOpcionCorrecta = true;
                    break;
                }
            }

            if (!$tieneOpcionCorrecta) {
                return $this->json([
                    'message' => 'Debe haber al menos una opción correcta'
                ], 400);
            }

            $this->entityManager->persist($pregunta);

            // Crear opciones
            foreach ($data['opciones'] as $opcionData) {
                $opcion = new OpcionPregunta();
                $opcion->setTexto($opcionData['texto']);
                $opcion->setEsCorrecta($opcionData['esCorrecta'] ?? false);
                $opcion->setRetroalimentacion($opcionData['retroalimentacion'] ?? null);
                $opcion->setIdPregunta($pregunta);
                
                $this->entityManager->persist($opcion);
            }

            // Actualizar puntos totales del quiz
            $this->actualizarPuntosTotalesQuiz($quiz);
            
            $this->entityManager->flush();

            return $this->json([
                'message' => 'Pregunta creada correctamente',
                'id' => $pregunta->getId()
            ], 201);

        } catch (Exception $e) {
            return $this->json([
                'message' => 'Error al crear la pregunta',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    #[Route('/api/item/{id}/quiz/{quizId}/preguntas/{preguntaId}', name: 'app_quiz_pregunta_update', methods: ['PUT'])]
    public function updatePregunta($id, $quizId, $preguntaId, Request $request): JsonResponse
    {
        // Obtener la pregunta y validar acceso
        $pregunta = $this->entityManager->getRepository(PreguntaQuizz::class)->find($preguntaId);
        if (!$pregunta || $pregunta->getIdQuizz()->getId() != $quizId) {
            return $this->json([
                'message' => 'Pregunta no encontrada'
            ], 404);
        }

        // Verificar si el usuario es el profesor del curso
        $user = $this->getUser();
        $usuario = $this->entityManager->getRepository(Usuario::class)
            ->findOneBy(['email' => $user->getUserIdentifier()]);

        if ($pregunta->getIdQuizz()->getIdCurso()->getProfesor()->getId() !== $usuario->getId()) {
            return $this->json([
                'message' => 'Solo el profesor del curso puede editar preguntas'
            ], 403);
        }

        try {
            $data = json_decode($request->getContent(), true);

            if (isset($data['pregunta'])) {
                $pregunta->setPregunta($data['pregunta']);
            }
            if (isset($data['puntos'])) {
                $pregunta->setPuntos($data['puntos']);
            }
            if (isset($data['orden'])) {
                $pregunta->setOrden($data['orden']);
            }

            // Actualizar opciones si se proporcionan
            if (isset($data['opciones']) && is_array($data['opciones'])) {
                if (count($data['opciones']) < 2) {
                    return $this->json([
                        'message' => 'Debe proporcionar al menos 2 opciones'
                    ], 400);
                }

                // Validar que hay al menos una opción correcta
                $tieneOpcionCorrecta = false;
                foreach ($data['opciones'] as $opcionData) {
                    if (isset($opcionData['esCorrecta']) && $opcionData['esCorrecta']) {
                        $tieneOpcionCorrecta = true;
                        break;
                    }
                }

                if (!$tieneOpcionCorrecta) {
                    return $this->json([
                        'message' => 'Debe haber al menos una opción correcta'
                    ], 400);
                }

                // Eliminar opciones existentes
                foreach ($pregunta->getOpcionPreguntas() as $opcion) {
                    $this->entityManager->remove($opcion);
                }

                // Crear nuevas opciones
                foreach ($data['opciones'] as $opcionData) {
                    $opcion = new OpcionPregunta();
                    $opcion->setTexto($opcionData['texto']);
                    $opcion->setEsCorrecta($opcionData['esCorrecta'] ?? false);
                    $opcion->setRetroalimentacion($opcionData['retroalimentacion'] ?? null);
                    $opcion->setIdPregunta($pregunta);
                    
                    $this->entityManager->persist($opcion);
                }
            }

            // Actualizar puntos totales del quiz
            $this->actualizarPuntosTotalesQuiz($pregunta->getIdQuizz());

            $this->entityManager->flush();

            return $this->json([
                'message' => 'Pregunta actualizada correctamente'
            ]);

        } catch (Exception $e) {
            return $this->json([
                'message' => 'Error al actualizar la pregunta',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    #[Route('/api/item/{id}/quiz/{quizId}/preguntas/{preguntaId}', name: 'app_quiz_pregunta_delete', methods: ['DELETE'])]
    public function deletePregunta($id, $quizId, $preguntaId): JsonResponse
    {
        // Obtener la pregunta y validar acceso
        $pregunta = $this->entityManager->getRepository(PreguntaQuizz::class)->find($preguntaId);
        if (!$pregunta || $pregunta->getIdQuizz()->getId() != $quizId) {
            return $this->json([
                'message' => 'Pregunta no encontrada'
            ], 404);
        }

        // Verificar si el usuario es el profesor del curso
        $user = $this->getUser();
        $usuario = $this->entityManager->getRepository(Usuario::class)
            ->findOneBy(['email' => $user->getUserIdentifier()]);

        if ($pregunta->getIdQuizz()->getIdCurso()->getProfesor()->getId() !== $usuario->getId()) {
            return $this->json([
                'message' => 'Solo el profesor del curso puede eliminar preguntas'
            ], 403);
        }

        try {
            $quiz = $pregunta->getIdQuizz();
            $this->entityManager->remove($pregunta);
            
            // Actualizar puntos totales del quiz después de eliminar la pregunta
            $this->actualizarPuntosTotalesQuiz($quiz);
            
            $this->entityManager->flush();

            return $this->json([
                'message' => 'Pregunta eliminada correctamente'
            ]);

        } catch (Exception $e) {
            return $this->json([
                'message' => 'Error al eliminar la pregunta',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    #[Route('/api/item/{id}/quiz/{quizId}/edit', name: 'app_quiz_edit_detail', methods: ['GET'])]
    public function getQuizDetail($id, $quizId): JsonResponse
    {
        // Obtener el quiz y validar acceso
        $quiz = $this->entityManager->getRepository(Quizz::class)->find($quizId);
        if (!$quiz || $quiz->getIdCurso()->getId() != $id) {
            return $this->json([
                'message' => 'Quiz no encontrado'
            ], 404);
        }

        // Verificar si el usuario es el profesor del curso
        $user = $this->getUser();
        $usuario = $this->entityManager->getRepository(Usuario::class)
            ->findOneBy(['email' => $user->getUserIdentifier()]);

        if ($quiz->getIdCurso()->getProfesor()->getId() !== $usuario->getId()) {
            return $this->json([
                'message' => 'Solo el profesor del curso puede editar quizzes'
            ], 403);
        }

        // Formatear datos del quiz incluyendo preguntas y opciones
        $quizData = [
            'id' => $quiz->getId(),
            'titulo' => $quiz->getTitulo(),
            'descripcion' => $quiz->getDescripcion(),
            'fechaPublicacion' => $quiz->getFechaPublicacion()->format('Y-m-d\TH:i'),
            'fechaLimite' => $quiz->getFechaLimite()->format('Y-m-d\TH:i'),
            'tiempoLimite' => $quiz->getTiempoLimite(),
            'puntosTotales' => $quiz->getPuntosTotales(),
            'intentosPermitidos' => $quiz->getIntentosPermitidos(),
            'preguntas' => []
        ];

        // Añadir preguntas y opciones
        foreach ($quiz->getPreguntaQuizzs() as $pregunta) {
            $preguntaData = [
                'id' => $pregunta->getId(),
                'pregunta' => $pregunta->getPregunta(),
                'puntos' => $pregunta->getPuntos(),
                'orden' => $pregunta->getOrden(),
                'opciones' => []
            ];

            foreach ($pregunta->getOpcionPreguntas() as $opcion) {
                $preguntaData['opciones'][] = [
                    'id' => $opcion->getId(),
                    'texto' => $opcion->getTexto(),
                    'esCorrecta' => $opcion->isEsCorrecta(),
                    'retroalimentacion' => $opcion->getRetroalimentacion()
                ];
            }

            $quizData['preguntas'][] = $preguntaData;
        }

        return $this->json($quizData);
    }
}
