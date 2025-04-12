<?php

namespace App\Controller;

use App\Entity\Foro;
use App\Entity\MensajeForo;
use App\Repository\ForoRepository;
use App\Repository\MensajeForoRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\Response;

class ForoController extends AbstractController
{
    private $entityManager;
    private $foroRepository;
    private $mensajeForoRepository;

    public function __construct(
        EntityManagerInterface $entityManager,
        ForoRepository $foroRepository,
        MensajeForoRepository $mensajeForoRepository
    ) {
        $this->entityManager = $entityManager;
        $this->foroRepository = $foroRepository;
        $this->mensajeForoRepository = $mensajeForoRepository;
    }

    #[Route('/api/foro/{id}/mensajes', name: 'obtener_mensajes_foro', methods: ['GET'])]
    public function obtenerMensajes(int $id, MensajeForoRepository $mensajeForoRepository): JsonResponse
    {
        $mensajes = $mensajeForoRepository->findBy(
            ['idForo' => $id, 'mensajePadre' => null],
            ['fechaPublicacion' => 'DESC']
        );

        $mensajesFormateados = array_map(
            fn(MensajeForo $mensaje) => $this->formatearMensaje($mensaje),
            $mensajes
        );

        return new JsonResponse($mensajesFormateados);
    }

    #[Route('/api/foro/mensaje/{id}/respuestas', name: 'obtener_respuestas_mensaje', methods: ['GET'])]
    public function obtenerRespuestas(int $id, MensajeForoRepository $mensajeForoRepository): JsonResponse
    {
        $mensaje = $mensajeForoRepository->find($id);
        
        if (!$mensaje) {
            return new JsonResponse(['error' => 'Mensaje no encontrado'], Response::HTTP_NOT_FOUND);
        }

        $respuestas = $mensaje->getMensajeHijo()->toArray();
        usort($respuestas, fn($a, $b) => $a->getFechaPublicacion() <=> $b->getFechaPublicacion());

        $respuestasFormateadas = array_map(
            fn(MensajeForo $respuesta) => $this->formatearMensaje($respuesta),
            $respuestas
        );

        return new JsonResponse($respuestasFormateadas);
    }

    private function formatearMensaje(MensajeForo $mensaje): array
    {
        return [
            'id' => $mensaje->getId(),
            'contenido' => $mensaje->getContenido(),
            'fechaPublicacion' => $mensaje->getFechaPublicacion()->format('Y/m/d H:i:s'),
            'usuario' => [
                'id' => $mensaje->getIdUsuario()->getId(),
                'nombre' => $mensaje->getIdUsuario()->getNombre() . " " . $mensaje->getIdUsuario()->getApellido(),
                'email' => $mensaje->getIdUsuario()->getEmail()
            ],
            'tieneRespuestas' => !$mensaje->getMensajeHijo()->isEmpty()
        ];
    }

    #[Route('/api/foro/{id}/mensaje', name: 'app_foro_mensaje_crear', methods: ['POST'])]
    public function crearMensaje(Request $request, int $id): JsonResponse
    {
        // Obtener el usuario actual
        $usuario = $this->getUser();
        if (!$usuario) {
            return $this->json([
                'message' => 'Debes iniciar sesión para enviar mensajes'
            ], 401);
        }

        // Obtener el foro
        $foro = $this->foroRepository->find($id);
        if (!$foro) {
            return $this->json([
                'message' => 'Foro no encontrado'
            ], 404);
        }

        // Verificar si el usuario está inscrito en el curso
        $curso = $foro->getCurso();
        $usuarioCursos = $curso->getUsuarioCursos();
        $isEnrolled = false;
        foreach ($usuarioCursos as $usuarioCurso) {
            if ($usuarioCurso->getIdUsuario() === $usuario) {
                $isEnrolled = true;
                break;
            }
        }

        // Si no está inscrito y no es el profesor, no puede enviar mensajes
        if (!$isEnrolled && $curso->getProfesor() !== $usuario) {
            return $this->json([
                'message' => 'Debes estar inscrito en el curso para enviar mensajes'
            ], 403);
        }

        // Obtener datos del request
        $data = json_decode($request->getContent(), true);
        $contenido = $data['contenido'] ?? null;
        $mensajePadreId = $data['mensajePadreId'] ?? null;

        if (!$contenido) {
            return $this->json([
                'message' => 'El contenido del mensaje es requerido'
            ], 400);
        }

        // Crear nuevo mensaje
        $mensaje = new MensajeForo();
        $mensaje->setContenido($contenido);
        $mensaje->setFechaPublicacion(new \DateTime());
        $mensaje->setIdForo($foro);
        $mensaje->setIdUsuario($usuario);

        // Si es una respuesta, establecer el mensaje padre
        if ($mensajePadreId) {
            $mensajePadre = $this->mensajeForoRepository->find($mensajePadreId);
            if ($mensajePadre) {
                $mensaje->setIdMensajePadre($mensajePadre);
            }
        }

        $this->entityManager->persist($mensaje);
        $this->entityManager->flush();

        return $this->json([
            'message' => 'Mensaje enviado correctamente',
            'id' => $mensaje->getId()
        ]);
    }
} 