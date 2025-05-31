<?php

namespace App\Controller;

use App\Entity\Curso;
use App\Entity\UsuarioCurso;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

class CourseParticipantsController extends AbstractController
{
    public function __construct(
        private readonly EntityManagerInterface $entityManager
    ) {}

    #[Route('/api/courses/{id}/participants', name: 'api_course_participants', methods: ['GET'])]
    public function getParticipants(int $id): JsonResponse
    {
        $curso = $this->entityManager->getRepository(Curso::class)->find($id);

        if (!$curso) {
            return new JsonResponse(['error' => 'Curso no encontrado'], 404);
        }

        // Obtener el profesor
        $profesor = $curso->getProfesor();
        $profesorData = [
            'id' => $profesor->getId(),
            'nombre' => $profesor->getNombre(),
            'apellido' => $profesor->getApellido(),
            'apellido2' => $profesor->getApellido2(),
            'username' => $profesor->getUsername(),
            'imagen' => $profesor->getImagen() ? [
                'url' => $profesor->getImagen()->getUrl()
            ] : null
        ];

        // Obtener estudiantes
        $usuarioCursos = $this->entityManager->getRepository(UsuarioCurso::class)
            ->findBy(['idCurso' => $curso]);

        $estudiantes = [];
        foreach ($usuarioCursos as $usuarioCurso) {
            $estudiante = $usuarioCurso->getIdUsuario();
            $estudiantes[] = [
                'id' => $estudiante->getId(),
                'nombre' => $estudiante->getNombre(),
                'apellido' => $estudiante->getApellido(),
                'apellido2' => $estudiante->getApellido2(),
                'username' => $estudiante->getUsername(),
                'imagen' => $estudiante->getImagen() ? [
                    'url' => $estudiante->getImagen()->getUrl()
                ] : null
            ];
        }

        $response = new JsonResponse([
            'profesor' => $profesorData,
            'estudiantes' => $estudiantes
        ]);
        
        $response->headers->set('Content-Type', 'application/json');
        $response->headers->set('Access-Control-Allow-Origin', '*');
        $response->headers->set('Access-Control-Allow-Methods', 'GET');
        $response->headers->set('Access-Control-Allow-Headers', 'Content-Type,Authorization');
        
        return $response;
    }
}