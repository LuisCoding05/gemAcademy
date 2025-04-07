<?php

namespace App\Controller;

use App\Entity\Log;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

final class TestController extends AbstractController
{
    public function __construct(private readonly EntityManagerInterface $entityManager){
        
    }
    #[Route('/api/logs', name: 'app_logs')]
    #[IsGranted('ROLE_ADMIN')]
    public function index(Request $request): JsonResponse
    {
        // Parámetros de paginación
        $page = $request->query->getInt('page', 1);
        $limit = $request->query->getInt('limit', 10);
        $offset = ($page - 1) * $limit;
        
        // Parámetros de filtrado
        $username = $request->query->get('username', '');
        $email = $request->query->get('email', '');
        $startDate = $request->query->get('startDate', '');
        $endDate = $request->query->get('endDate', '');
        
        // Construir la consulta
        $qb = $this->entityManager->createQueryBuilder();
        $qb->select('l')
           ->from(Log::class, 'l')
           ->join('l.usuario', 'u')
           ->orderBy('l.fecha', 'DESC');
        
        // Aplicar filtros si se proporcionan
        if (!empty($username)) {
            $qb->andWhere('u.username LIKE :username')
               ->setParameter('username', $username . '%');
        }
        
        if (!empty($email)) {
            $qb->andWhere('u.email LIKE :email')
               ->setParameter('email', $email . '%');
        }
        
        if (!empty($startDate)) {
            $qb->andWhere('l.fecha >= :startDate')
               ->setParameter('startDate', new \DateTime($startDate));
        }
        
        if (!empty($endDate)) {
            $qb->andWhere('l.fecha <= :endDate')
               ->setParameter('endDate', new \DateTime($endDate));
        }
        
        // Obtener el total de registros para la paginación
        $countQb = clone $qb;
        $countQb->select('COUNT(l.id)');
        $totalLogs = $countQb->getQuery()->getSingleScalarResult();
        
        // Aplicar paginación
        $qb->setFirstResult($offset)
           ->setMaxResults($limit);
        
        // Ejecutar la consulta
        $logs = $qb->getQuery()->getResult();
        
        // Formatear los resultados
        $logsArray = array_map(function(Log $log) {
            return [
                'id' => $log->getId(),
                'fecha' => $log->getFecha()->format('d/m/Y H:i:s'),
                'usuario' => [
                    'id' => $log->getUsuario()->getId(),
                    'nombre' => $log->getUsuario()->getNombre() . " " . $log->getUsuario()->getApellido(),
                    'username' => $log->getUsuario()->getUsername(),
                    'email' => $log->getUsuario()->getEmail()
                ]
            ];
        }, $logs);
    
        return $this->json([
            "logs" => $logsArray,
            "pagination" => [
                "total" => $totalLogs,
                "page" => $page,
                "limit" => $limit,
                "pages" => ceil($totalLogs / $limit)
            ]
        ]);
    }
}
