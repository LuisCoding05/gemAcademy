<?php

namespace App\Controller;

use App\Entity\Curso;
use App\Entity\Material;
use App\Entity\MaterialCompletado;
use App\Entity\Usuario;
use App\Entity\UsuarioCurso;
use App\Entity\Fichero;
use App\Service\FileService;
use DateTime;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\File\Exception\FileException;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;
use Symfony\Component\String\Slugger\SluggerInterface;

#[IsGranted('ROLE_USER')]
final class MaterialController extends AbstractController
{
    public function __construct(
        private readonly EntityManagerInterface $entityManager,
        private readonly SluggerInterface $slugger,
        private readonly FileService $fileService
    ) {}

    #[Route('/api/item/{id}/material/{materialId}', name: 'app_material_detail', methods: ['GET'])]
    public function materialDetail($id, $materialId): JsonResponse
    {
        // Obtener el curso
        $curso = $this->entityManager->getRepository(Curso::class)->find($id);
        if (!$curso) {
            return $this->json([
                'message' => 'Curso no encontrado'
            ], 404);
        }

        // Obtener el material
        $material = $this->entityManager->getRepository(Material::class)->find($materialId);
        if (!$material || $material->getIdCurso()->getId() != $id) {
            return $this->json([
                'message' => 'Material no encontrado'
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
                'message' => 'No tienes acceso a este material'
            ], 403);
        }
        $materialCompletado = null;
        // Si es estudiante, marcar el material como completado
        if ($isEstudiante) {
            // Verificar si el material ya está marcado como completado
            $materialCompletado = $this->entityManager->getRepository(MaterialCompletado::class)
                ->findOneBy([
                    'usuarioCurso' => $usuarioCurso,
                    'material' => $material
                ]);

            // Si no está marcado como completado, crearlo
            if (!$materialCompletado) {
                try {
                    $materialCompletado = new MaterialCompletado();
                    $materialCompletado->setUsuarioCurso($usuarioCurso);
                    $materialCompletado->setMaterial($material);
                    $materialCompletado->setFechaCompletado(new DateTime());
                    
                    $this->entityManager->persist($materialCompletado);
                    
                    // Actualizar el contador de materiales completados
                    $materialesCompletadosActual = $usuarioCurso->getMaterialesCompletados() ?? 0;
                    $usuarioCurso->setMaterialesCompletados($materialesCompletadosActual + 1);
                    
                    // Actualizar el porcentaje completado
                    $totalItems = $curso->getTotalItems();
                    $itemsCompletados = $usuarioCurso->getMaterialesCompletados() + 
                                       $usuarioCurso->getTareasCompletadas() + 
                                       $usuarioCurso->getQuizzesCompletados();
                    
                    $porcentajeCompletado = ($totalItems > 0) ? 
                        round(($itemsCompletados / $totalItems) * 100, 2) : 0;
                    
                    $usuarioCurso->setPorcentajeCompletado(strval($porcentajeCompletado));
                    $usuarioCurso->setUltimaActualizacion(new DateTime());
                    
                    $this->entityManager->flush();
                } catch (\Exception $e) {
                    return $this->json([
                        'message' => 'Error al marcar el material como completado',
                        'error' => $e->getMessage()
                    ], 500);
                }
            }
        }

        // Formatear la respuesta
        $materialData = [
            "id" => $material->getId(),
            "titulo" => $material->getTitulo(),
            "descripcion" => $material->getDescripcion(),
            "contenido" => $material->getDescripcion(),
            "fechaPublicacion" => $material->getFechaPublicacion()->format('Y/m/d H:i:s'),
            "fichero" => $material->getFichero() ? [
                "id" => $material->getFichero()->getId(),
                "url" => $material->getFichero()->getRuta(),
                "nombreOriginal" => $material->getFichero()->getNombreOriginal()
            ] : null,
            "completado" => $materialCompletado !== null
        ];

        return $this->json($materialData);
    }

    #[Route('/api/item/{id}/material/create', name: 'app_material_create', methods: ['POST'])]
    public function createMaterial(Request $request, $id): JsonResponse
    {
        // Obtener el curso
        $curso = $this->entityManager->getRepository(Curso::class)->find($id);
        if (!$curso) {
            return $this->json(['message' => 'Curso no encontrado'], 404);
        }

        // Verificar que el usuario es el profesor del curso
        $user = $this->getUser();
        $usuario = $this->entityManager->getRepository(Usuario::class)
            ->findOneBy(['email' => $user->getUserIdentifier()]);

        if ($curso->getProfesor()->getId() !== $usuario->getId()) {
            return $this->json(['message' => 'No tienes permisos para crear materiales en este curso'], 403);
        }

        $data = json_decode($request->request->get('data'), true);
        
        // Validar datos requeridos
        if (!isset($data['titulo']) || !isset($data['descripcion'])) {
            return $this->json(['message' => 'El título y la descripción son requeridos'], 400);
        }

        try {
            $material = new Material();
            $material->setTitulo($data['titulo']);
            $material->setDescripcion($data['descripcion']);
            $material->setIdCurso($curso);
            $material->setFechaPublicacion(new DateTime());

            // Procesar ficheroId si existe
            if (isset($data['ficheroId'])) {
                $fichero = $this->entityManager->getRepository(Fichero::class)->find($data['ficheroId']);
                if ($fichero) {
                    $material->setFichero($fichero);
                }
            }

            $this->entityManager->persist($material);
            
            $this->entityManager->flush();

            return $this->json([
                'message' => 'Material creado exitosamente',
                'material' => [
                    'id' => $material->getId(),
                    'titulo' => $material->getTitulo(),
                    'descripcion' => $material->getDescripcion(),
                    'fechaPublicacion' => $material->getFechaPublicacion()->format('Y/m/d H:i:s'),
                    'fichero' => $material->getFichero() ? [
                        'id' => $material->getFichero()->getId(),
                        'nombreOriginal' => $material->getFichero()->getNombreOriginal(),
                        'url' => $material->getFichero()->getRuta()
                    ] : null
                ]
            ], 201);

        } catch (\Exception $e) {
            return $this->json([
                'message' => 'Error al crear el material: ' . $e->getMessage()
            ], 500);
        }
    }

    #[Route('/api/item/{id}/material/{materialId}/edit', name: 'app_material_edit', methods: ['POST'])]
    public function editMaterial(Request $request, $id, $materialId): JsonResponse
    {
        // Obtener el material y el curso
        $material = $this->entityManager->getRepository(Material::class)->find($materialId);
        if (!$material || $material->getIdCurso()->getId() != $id) {
            return $this->json(['message' => 'Material no encontrado'], 404);
        }

        // Verificar que el usuario es el profesor del curso
        $user = $this->getUser();
        $usuario = $this->entityManager->getRepository(Usuario::class)
            ->findOneBy(['email' => $user->getUserIdentifier()]);

        if ($material->getIdCurso()->getProfesor()->getId() !== $usuario->getId()) {
            return $this->json(['message' => 'No tienes permisos para editar este material'], 403);
        }

        $data = json_decode($request->request->get('data'), true);
        
        try {
            if (isset($data['titulo'])) {
                $material->setTitulo($data['titulo']);
            }
            if (isset($data['descripcion'])) {
                $material->setDescripcion($data['descripcion']);
            }

            // Procesar nuevo archivo si se proporciona
            $file = $request->files->get('file');
            if ($file) {
                // Si hay un archivo anterior, eliminarlo físicamente
                $ficheroAnterior = $material->getFichero();
                if ($ficheroAnterior) {
                    $rutaAnterior = $this->getParameter('kernel.project_dir') . '/public' . $ficheroAnterior->getRuta();
                    if (file_exists($rutaAnterior)) {
                        unlink($rutaAnterior);
                    }
                    $this->entityManager->remove($ficheroAnterior);
                }

                // Subir nuevo archivo
                $originalFilename = pathinfo($file->getClientOriginalName(), PATHINFO_FILENAME);
                $safeFilename = $this->slugger->slug($originalFilename);
                $newFilename = $safeFilename . '-' . uniqid() . '.' . $file->guessExtension();

                try {
                    $file->move(
                        $this->getParameter('uploads_directory'),
                        $newFilename
                    );

                    $fichero = new Fichero();
                    $fichero->setNombreOriginal($file->getClientOriginalName());
                    $fichero->setRuta('/uploads/' . $newFilename);
                    $fichero->setMimeType($file->getMimeType());
                    $fichero->setTamanio($file->getSize());
                    $fichero->setFechaSubida(new DateTime());
                    $fichero->setUsuario($usuario);
                    
                    $material->setFichero($fichero);
                    $this->entityManager->persist($fichero);
                } catch (FileException $e) {
                    return $this->json(['message' => 'Error al subir el archivo'], 500);
                }
            }

            $this->entityManager->flush();

            return $this->json([
                'message' => 'Material actualizado exitosamente',
                'material' => [
                    'id' => $material->getId(),
                    'titulo' => $material->getTitulo(),
                    'descripcion' => $material->getDescripcion(),
                    'fechaPublicacion' => $material->getFechaPublicacion()->format('Y/m/d H:i:s'),
                    'fichero' => $material->getFichero() ? [
                        'id' => $material->getFichero()->getId(),
                        'nombreOriginal' => $material->getFichero()->getNombreOriginal(),
                        'url' => $material->getFichero()->getRuta()
                    ] : null
                ]
            ]);

        } catch (\Exception $e) {
            return $this->json([
                'message' => 'Error al actualizar el material',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    #[Route('/api/item/{id}/material/{materialId}/delete', name: 'app_material_delete', methods: ['DELETE'])]
    public function deleteMaterial($id, $materialId): JsonResponse
    {
        // Obtener el material y el curso
        $material = $this->entityManager->getRepository(Material::class)->find($materialId);
        if (!$material || $material->getIdCurso()->getId() != $id) {
            return $this->json(['message' => 'Material no encontrado'], 404);
        }

        // Verificar que el usuario es el profesor del curso
        $user = $this->getUser();
        $usuario = $this->entityManager->getRepository(Usuario::class)
            ->findOneBy(['email' => $user->getUserIdentifier()]);

        if ($material->getIdCurso()->getProfesor()->getId() !== $usuario->getId()) {
            return $this->json(['message' => 'No tienes permisos para eliminar este material'], 403);
        }

        try {
            // Obtener todos los usuarios del curso y actualizar sus estadísticas
            $usuariosCurso = $this->entityManager->getRepository(UsuarioCurso::class)
                ->findBy(['idCurso' => $material->getIdCurso()]);

            foreach ($usuariosCurso as $usuarioCurso) {
                $materialCompletado = $this->entityManager->getRepository(MaterialCompletado::class)
                    ->findOneBy([
                        'usuarioCurso' => $usuarioCurso,
                        'material' => $material
                    ]);

                if ($materialCompletado) {
                    // Actualizar contador de materiales completados
                    $materialesCompletados = $usuarioCurso->getMaterialesCompletados();
                    if ($materialesCompletados > 0) {
                        $usuarioCurso->setMaterialesCompletados($materialesCompletados - 1);
                    }

                    // Eliminar el registro de material completado
                    $this->entityManager->remove($materialCompletado);
                }

                // Recalcular porcentaje completado
                $totalItems = $material->getIdCurso()->getTotalItems() - 1; // Restamos 1 por el material que se eliminará
                $itemsCompletados = $usuarioCurso->getMaterialesCompletados() + 
                                $usuarioCurso->getTareasCompletadas() + 
                                $usuarioCurso->getQuizzesCompletados();
                
                $porcentajeCompletado = ($totalItems > 0) ? 
                    round(($itemsCompletados / $totalItems) * 100, 2) : 0;
                
                $usuarioCurso->setPorcentajeCompletado(strval($porcentajeCompletado));
                $usuarioCurso->setUltimaActualizacion(new DateTime());
            }

            // Eliminar el archivo físico si existe
            if ($material->getFichero()) {
                $rutaArchivo = $this->getParameter('kernel.project_dir') . '/public' . $material->getFichero()->getRuta();
                if (file_exists($rutaArchivo)) {
                    unlink($rutaArchivo);
                }
                $this->entityManager->remove($material->getFichero());
            }

            // Eliminar el material
            $this->entityManager->remove($material);
            $this->entityManager->flush();

            return $this->json([
                'message' => 'Material eliminado exitosamente'
            ]);

        } catch (\Exception $e) {
            return $this->json([
                'message' => 'Error al eliminar el material',
                'error' => $e->getMessage()
            ], 500);
        }
    }
}