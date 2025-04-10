<?php

namespace App\Controller;

use App\Entity\Curso;
use App\Entity\UsuarioCurso;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Attribute\Route;

final class CourseController extends AbstractController
{
    public function __construct(
        private readonly EntityManagerInterface $entityManager
    ) {}

    #[Route('/api/course', name: 'app_course')]
    public function index(Request $request): JsonResponse
    {
        // Parámetros de paginación
        $page = $request->query->getInt('page', 1);
        $limit = $request->query->getInt('limit', 4);
        $offset = ($page - 1) * $limit;
        
        // Parámetros de filtrado
        $nombreCurso = $request->query->get('nombre', '');
        $usernameProfesor = $request->query->get('username', '');
        
        // Construir la consulta
        $qb = $this->entityManager->createQueryBuilder();
        $qb->select('c')
           ->from(Curso::class, 'c')
           ->leftJoin('c.profesor', 'p')
           ->orderBy('c.fechaCreacion', 'DESC');
        
        // Aplicar filtros si se proporcionan
        if (!empty($nombreCurso)) {
            $qb->andWhere('c.nombre LIKE :nombre')
               ->setParameter('nombre', '%' . $nombreCurso . '%');
        }
        
        if (!empty($usernameProfesor)) {
            $qb->andWhere('p.username LIKE :username')
               ->setParameter('username', '%' . $usernameProfesor . '%');
        }
        
        // Obtener el total de registros para la paginación
        $countQb = clone $qb;
        $countQb->select('COUNT(c.id)');
        $totalCursos = $countQb->getQuery()->getSingleScalarResult();
        
        // Aplicar paginación
        $qb->setFirstResult($offset)
           ->setMaxResults($limit);
        
        // Ejecutar la consulta
        $cursosRaw = $qb->getQuery()->getResult();
        
        $cursos = [];
        
        foreach ($cursosRaw as $curso) {
            // Obtener el número de estudiantes inscritos
            $estudiantesInscritos = $this->entityManager->getRepository(UsuarioCurso::class)
                ->count(['idCurso' => $curso]);
                
            $cursos[] = [
                "id" => $curso->getId(),
                "nombre" => $curso->getNombre(),
                "profesor" => [
                    "id" => $curso->getProfesor()->getId(),
                    "imagen" => $curso->getProfesor()->getImagen() ? $curso->getProfesor()->getImagen()->getUrl() : null,
                    "nombre" => $curso->getProfesor()->getNombre() . " " . $curso->getProfesor()->getApellido(),
                    "username" => $curso->getProfesor()->getUsername()
                ],
                "imagen" => $curso->getImagen() ? $curso->getImagen()->getUrl() : null,
                "descripcion" => $curso->getDescripcion(),
                "fechaCreacion" => $curso->getFechaCreacion()->format('Y/m/d'),
                "estudiantes" => $estudiantesInscritos
            ];
        }
        
        return $this->json([
            'message' => 'Cursos disponibles:',
            'cursos' => $cursos,
            'pagination' => [
                'total' => $totalCursos,
                'page' => $page,
                'limit' => $limit,
                'pages' => ceil($totalCursos / $limit)
            ]
        ]);
    }

    #[Route('/api/course/{id}', name: 'app_course_detail')]
    public function detail(Request $request, $id): JsonResponse
    {
        $curso = $this->entityManager->getRepository(Curso::class)->find($id);
        
        // Obtener el número de estudiantes inscritos
        $estudiantesInscritos = $this->entityManager->getRepository(UsuarioCurso::class)
            ->count(['idCurso' => $curso]);

        $cursoData = [
            "id" => $curso->getId(),
            "nombre" => $curso->getNombre(),
            "descripcion" => $curso->getDescripcion(),
            "imagen" => $curso->getImagen() ? $curso->getImagen()->getUrl() : null,
            "fechaCreacion" => $curso->getFechaCreacion()->format('Y/m/d H:i:s'),
            "estudiantes" => $estudiantesInscritos,
            "profesor" => [
                "id" => $curso->getProfesor()->getId(),
                "imagen" => $curso->getProfesor()->getImagen() ? $curso->getProfesor()->getImagen()->getUrl() : null,
                "nombre" => $curso->getProfesor()->getNombre() . " " . $curso->getProfesor()->getApellido(),
                "username" => $curso->getProfesor()->getUsername()
            ],
        ];

        $cursoTareas = $curso->getTareas();
        $cursoTareasData = [];
        foreach ($cursoTareas as $tarea) {
            $cursoTareasData[] = [
                "id" => $tarea->getId(),
                "titulo" => $tarea->getTitulo(),
                "fechaPublicacion" => $tarea->getFechaPublicacion()->format('Y/m/d H:i:s'),
                "fechaLimite" => $tarea->getFechaLimite()->format('Y/m/d H:i:s'),
            ];
        }

        $cursoQuizz = $curso->getQuizzs();
        $cursoMateriales = $curso->getMaterials();
        $cursoForos = $curso->getForos();
        $cursoTareas = $curso->getTareas();

        $cursoQuizzData = [];
        foreach ($cursoQuizz as $quizz) {
            $cursoQuizzData[] = [
                "id" => $quizz->getId(),
                "titulo" => $quizz->getTitulo(),
                "fechaPublicacion" => $quizz->getFechaPublicacion()->format('Y/m/d H:i:s'),
                "fechaLimite" => $quizz->getFechaLimite()->format('Y/m/d H:i:s'),
            ];
        }

        $cursoMaterialesData = [];
        foreach ($cursoMateriales as $material) {
            $cursoMaterialesData[] = [
                "id" => $material->getId(),
                "titulo" => $material->getTitulo(),
                "descripcion" => $material->getDescripcion(),
                "fechaPublicacion" => $material->getFechaPublicacion()->format('Y/m/d H:i:s'),
            ];
        }

        $cursoForosData = [];
        foreach ($cursoForos as $foro) {
            $cursoForosData[] = [
                "id" => $foro->getId(),
                "titulo" => $foro->getTitulo(),
                "descripcion" => $foro->getDescripcion(),
                "mensajes" => $foro->getMensajeForos(),

            ];
        }

        return $this->json([
            "id" => $cursoData["id"],
            "nombre" => $cursoData["nombre"],
            "descripcion" => $cursoData["descripcion"],
            "imagen" => $cursoData["imagen"],
            "fechaCreacion" => $cursoData["fechaCreacion"],
            "estudiantes" => $cursoData["estudiantes"],
            "profesor" => $cursoData["profesor"],
            "tareas" => $cursoTareasData,
            "quizzes" => $cursoQuizzData,
            "materiales" => $cursoMaterialesData,
            "foros" => $cursoForosData,
        ]);
    }


}
