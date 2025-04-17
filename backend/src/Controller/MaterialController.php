<?php

namespace App\Controller;

use App\Entity\Curso;
use App\Entity\Material;
use App\Entity\MaterialCompletado;
use App\Entity\Usuario;
use App\Entity\UsuarioCurso;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[IsGranted('ROLE_USER')]
final class MaterialController extends AbstractController
{
    public function __construct(
        private readonly EntityManagerInterface $entityManager
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
                    $materialCompletado->setFechaCompletado(new \DateTime());
                    
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
                    $usuarioCurso->setUltimaActualizacion(new \DateTime());
                    
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
}