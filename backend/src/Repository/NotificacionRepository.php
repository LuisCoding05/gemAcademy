<?php

namespace App\Repository;

use App\Entity\Notificacion;
use App\Entity\Usuario;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Notificacion>
 */
class NotificacionRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Notificacion::class);
    }

    /**
     * @return Notificacion[] Returns an array of Notificacion objects
     */
    public function findByUsuario(Usuario $usuario, int $limit = 10): array
    {
        return $this->createQueryBuilder('n')
            ->andWhere('n.usuario = :usuario')
            ->setParameter('usuario', $usuario)
            ->orderBy('n.fechaCreacion', 'DESC')
            ->setMaxResults($limit)
            ->getQuery()
            ->getResult();
    }

    public function countUnreadByUsuario(Usuario $usuario): int
    {
        return $this->createQueryBuilder('n')
            ->select('COUNT(n.id)')
            ->andWhere('n.usuario = :usuario')
            ->andWhere('n.leida = :leida')
            ->setParameter('usuario', $usuario)
            ->setParameter('leida', false)
            ->getQuery()
            ->getSingleScalarResult();
    }
}