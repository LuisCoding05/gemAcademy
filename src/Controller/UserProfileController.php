<?php

namespace App\Controller;

use App\Entity\Usuario;
use App\Entity\Curso;
use App\Entity\UsuarioLogro;
use App\Entity\UsuarioNivel;
use App\Entity\UsuarioCurso;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Attribute\Route;

class UserProfileController extends AbstractController
{
    public function __construct(
        private readonly EntityManagerInterface $entityManager
    ) {}

    #[Route('/api/profile/{id}', name: 'api_user_profile', methods: ['GET'])]
    public function getUserProfile(int $id): JsonResponse
    {
        $user = $this->entityManager->getRepository(Usuario::class)->find($id);

        if (!$user) {
            return new JsonResponse(['error' => 'Usuario no encontrado'], 404);
        }

        $cursos = [];
        
        // Obtener cursos donde el usuario es profesor
        $cursosDictados = $this->entityManager->getRepository(Curso::class)
            ->findBy(['profesor' => $user]);

        foreach ($cursosDictados as $curso) {
            $cursos[] = [
                'id' => $curso->getId(),
                'titulo' => $curso->getNombre(),
                'descripcion' => $curso->getDescripcion(),
                'userRole' => 'profesor'
            ];
        }

        // Obtener cursos donde el usuario es alumno
        $usuarioCursos = $this->entityManager->getRepository(UsuarioCurso::class)
            ->findBy(['idUsuario' => $user]);

        foreach ($usuarioCursos as $usuarioCurso) {
            $curso = $usuarioCurso->getIdCurso();
            $cursos[] = [
                'id' => $curso->getId(),
                'titulo' => $curso->getNombre(),
                'descripcion' => $curso->getDescripcion(),
                'userRole' => 'estudiante'
            ];
        }

        // Obtener logros del usuario
        $usuarioLogros = $this->entityManager->getRepository(UsuarioLogro::class)
            ->findBy(['idUsuario' => $user]);

        $logros = [];
        foreach ($usuarioLogros as $usuarioLogro) {
            $logro = $usuarioLogro->getIdLogro();
            $logros[] = [
                'id' => $logro->getId(),
                'titulo' => $logro->getTitulo(),
                'motivo' => $logro->getMotivo(),
                'puntos' => $logro->getPuntosOtorgados(),
                'fechaObtencion' => $usuarioLogro->getFechaObtencion()->format('Y-m-d H:i:s'),
                'imagen' => $logro->getImagen() ? [
                    'url' => $logro->getImagen()->getUrl()
                ] : null
            ];
        }

        // Obtener información del nivel actual del usuario
        $usuarioNivel = $this->entityManager->getRepository(UsuarioNivel::class)
            ->findOneBy(['idUsuario' => $user]);

        $nivelActual = null;
        if ($usuarioNivel) {
            $nivel = $usuarioNivel->getIdNivel();
            $nivelActual = [
                'numero' => $nivel->getNumNivel(),
                'nombre' => $nivel->getNombre(),
                'descripcion' => $nivel->getDescripcion()
            ];
        }

        $response = new JsonResponse([
            'user' => [
                'id' => $user->getId(),
                'nombre' => $user->getNombre(),
                'apellido' => $user->getApellido(),
                'apellido2' => $user->getApellido2(),
                'username' => $user->getUsername(),
                'descripcion' => $user->getDescripcion(),
                'imagen' => $user->getImagen() ? [
                    'url' => $user->getImagen()->getUrl()
                ] : null
            ],
            'estadisticas' => [
                'nivel' => $nivelActual
            ],
            'cursos' => $cursos,
            'logros' => $logros
        ]);
        
        $response->headers->set('Content-Type', 'application/json');
        $response->headers->set('Access-Control-Allow-Origin', '*');
        $response->headers->set('Access-Control-Allow-Methods', 'GET');
        $response->headers->set('Access-Control-Allow-Headers', 'Content-Type,Authorization');
        
        return $response;
    }
}