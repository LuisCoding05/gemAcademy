<?php

namespace App\Controller;

use App\Entity\Usuario;
use App\Entity\UsuarioCurso;
use App\Entity\UsuarioLogro;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[Route('/api')]
class DashboardController extends AbstractController
{
    public function __construct(
        private readonly EntityManagerInterface $entityManager
    ) {}

    #[Route('/dashboard', name: 'api_dashboard', methods: ['GET'])]
    #[IsGranted('ROLE_USER')]
    public function index(): JsonResponse
    {
        /** @var Usuario $user */
        $user = $this->getUser();

        // Obtener cursos del usuario
        $usuarioCursos = $this->entityManager->getRepository(UsuarioCurso::class)
            ->findBy(['idUsuario' => $user]);

        $cursos = [];
        $totalCursos = 0;
        $cursosCompletados = 0;
        $puntosTotales = 0;

        foreach ($usuarioCursos as $usuarioCurso) {
            $curso = $usuarioCurso->getIdCurso();
            $porcentaje = (float)$usuarioCurso->getPorcentajeCompletado();
            
            if ($porcentaje >= 100) {
                $cursosCompletados++;
            }

            $cursos[] = [
                'id' => $curso->getId(),
                'titulo' => $curso->getNombre(),
                'descripcion' => $curso->getDescripcion(),
                'porcentajeCompletado' => $porcentaje,
                'materialesCompletados' => $usuarioCurso->getMaterialesCompletados(),
                'materialesTotales' => $usuarioCurso->getMaterialesTotales(),
                'tareasCompletadas' => $usuarioCurso->getTareasCompletadas(),
                'tareasTotales' => $usuarioCurso->getTareasTotales(),
                'quizzesCompletados' => $usuarioCurso->getQuizzesCompletados(),
                'quizzesTotales' => $usuarioCurso->getQuizzesTotales(),
                'fechaInscripcion' => $usuarioCurso->getFechaInscripcion()->format('Y-m-d H:i:s'),
                'ultimaActualizacion' => $usuarioCurso->getUltimaActualizacion()->format('Y-m-d H:i:s')
            ];
        }

        $totalCursos = count($cursos);

        // Obtener logros del usuario
        $usuarioLogros = $this->entityManager->getRepository(UsuarioLogro::class)
            ->findBy(['idUsuario' => $user]);

        $logros = [];
        foreach ($usuarioLogros as $usuarioLogro) {
            $logro = $usuarioLogro->getIdLogro();
            $puntosTotales += $logro->getPuntosOtorgados() ?? 0;

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

        // Calcular nivel basado en puntos
        $nivel = floor($puntosTotales / 100) + 1;
        $progresoNivel = $puntosTotales % 100;

        return $this->json([
            'user' => [
                'id' => $user->getId(),
                'email' => $user->getEmail(),
                'nombre' => $user->getNombre(),
                'apellido' => $user->getApellido(),
                'roles' => $user->getRoles(),
                'imagen' => $user->getImagen() ? [
                    'url' => $user->getImagen()->getUrl()
                ] : null
            ],
            'estadisticas' => [
                'nivel' => $nivel,
                'puntosTotales' => $puntosTotales,
                'progresoNivel' => $progresoNivel,
                'totalCursos' => $totalCursos,
                'cursosCompletados' => $cursosCompletados,
                'porcentajeCompletado' => $totalCursos > 0 ? ($cursosCompletados / $totalCursos) * 100 : 0
            ],
            'cursos' => $cursos,
            'logros' => $logros
        ]);
    }
} 