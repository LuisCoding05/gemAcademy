<?php

namespace App\Controller;

use App\Entity\UsuarioNivel;
use App\Entity\Usuario;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Annotation\Route;

class RankingController extends AbstractController
{
    public function __construct(
        private EntityManagerInterface $entityManager
    ) {}

    #[Route('/api/ranking', name: 'get_global_ranking', methods: ['GET'])]
    public function getGlobalRanking(): JsonResponse
    {
        $qb = $this->entityManager->createQueryBuilder();
        
        // Obtener el top 10 usuarios por puntos
        $top10Query = $qb->select('u.id, u.username, u.nombre, u.apellido, i.url as imagen_url, n.nombre as nivel_nombre, n.numNivel, un.puntosActuales')
            ->from(UsuarioNivel::class, 'un')
            ->join('un.idUsuario', 'u')
            ->join('un.idNivel', 'n')
            ->leftJoin('u.imagen', 'i')
            ->orderBy('un.puntosActuales', 'DESC')
            ->setMaxResults(10)
            ->getQuery();

        $top10 = $top10Query->getResult();

        // Si hay un usuario autenticado, obtener su posición
        $userPosition = null;
        $currentUser = $this->getUser();
        
        if ($currentUser) {
            $positionQb = $this->entityManager->createQueryBuilder();
            $positionQuery = $positionQb->select('COUNT(un.id) + 1')
                ->from(UsuarioNivel::class, 'un')
                ->where('un.puntosActuales > (
                    SELECT un2.puntosActuales 
                    FROM App\Entity\UsuarioNivel un2 
                    WHERE un2.idUsuario = :userId
                )')
                ->setParameter('userId', $currentUser->getId())
                ->getQuery();

            $userPosition = $positionQuery->getSingleScalarResult();
        }

        // Formatear la respuesta
        $rankingList = array_map(function($user) {
            return [
                'id' => $user['id'],
                'username' => $user['username'],
                'nombre' => $user['nombre'] . ' ' . $user['apellido'],
                'imagen' => $user['imagen_url'],
                'nivel' => [
                    'nombre' => $user['nivel_nombre'],
                    'numero' => $user['numNivel']
                ],
                'puntos' => $user['puntosActuales']
            ];
        }, $top10);

        return new JsonResponse([
            'top10' => $rankingList,
            'userPosition' => $userPosition
        ]);
    }
}