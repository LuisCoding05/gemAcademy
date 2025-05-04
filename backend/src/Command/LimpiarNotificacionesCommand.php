<?php

namespace App\Command;

use App\Entity\Notificacion;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

#[AsCommand(
    name: 'app:limpiar-notificaciones',
    description: 'Limpia notificaciones antiguas y recordatorios expirados'
)]
class LimpiarNotificacionesCommand extends Command
{
    private $entityManager;

    public function __construct(EntityManagerInterface $entityManager)
    {
        parent::__construct();
        $this->entityManager = $entityManager;
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $output->writeln('Iniciando limpieza de notificaciones...');

        try {
            $qb = $this->entityManager->createQueryBuilder();

            // Eliminar notificaciones leídas más antiguas de 30 días
            $result1 = $qb->delete(Notificacion::class, 'n')
                ->where('n.leida = :leida')
                ->andWhere('n.fechaLectura < :fecha')
                ->setParameter('leida', true)
                ->setParameter('fecha', new \DateTime('-30 days'))
                ->getQuery()
                ->execute();

            $output->writeln(sprintf('Se eliminaron %d notificaciones leídas antiguas', $result1));

            // Eliminar recordatorios expirados
            $qb = $this->entityManager->createQueryBuilder();
            $result2 = $qb->delete(Notificacion::class, 'n')
                ->where('n.tipo = :tipo')
                ->andWhere('n.fechaCreacion < :fecha')
                ->setParameter('tipo', Notificacion::TIPO_RECORDATORIO)
                ->setParameter('fecha', new \DateTime())
                ->getQuery()
                ->execute();

            $output->writeln(sprintf('Se eliminaron %d recordatorios expirados', $result2));

            return Command::SUCCESS;
        } catch (\Exception $e) {
            $output->writeln('<error>' . $e->getMessage() . '</error>');
            return Command::FAILURE;
        }
    }
}