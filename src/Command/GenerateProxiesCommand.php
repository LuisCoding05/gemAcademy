<?php

namespace App\Command;

use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\Tools\Console\Helper\EntityManagerHelper;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

#[AsCommand(
    name: 'app:doctrine:generate-proxies',
    description: 'Generates Doctrine proxy classes for all entities',
)]
class GenerateProxiesCommand extends Command
{
    public function __construct(
        private readonly EntityManagerInterface $entityManager
    ) {
        parent::__construct();
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);
        
        try {
            $em = $this->entityManager;
            $config = $em->getConfiguration();
            
            $io->info('Starting proxy generation...');
            $io->info('Proxy directory: ' . $config->getProxyDir());
            
            // Crear directorio si no existe
            if (!is_dir($config->getProxyDir())) {
                mkdir($config->getProxyDir(), 0775, true);
                $io->success('Created proxy directory: ' . $config->getProxyDir());
            }
            
            // Verificar permisos
            if (!is_writable($config->getProxyDir())) {
                $io->error('Proxy directory is not writable: ' . $config->getProxyDir());
                return Command::FAILURE;
            }
            
            // Obtener todas las entidades
            $metadata = $em->getMetadataFactory()->getAllMetadata();
            $io->info(sprintf('Found %d entities', count($metadata)));
            
            // Generar proxies
            $proxyFactory = $em->getProxyFactory();
            $proxyClasses = [];
            
            foreach ($metadata as $meta) {
                $proxyClass = $proxyFactory->generateProxyClasses([$meta]);
                $proxyClasses[] = $meta->getName();
                $io->writeln('Generated proxy for: ' . $meta->getName());
            }
            
            $io->success(sprintf('Successfully generated %d proxy classes', count($proxyClasses)));
            
            // Verificar archivos generados
            $proxyFiles = glob($config->getProxyDir() . '/*.php');
            $io->info(sprintf('Proxy files created: %d', count($proxyFiles ?: [])));
            
            return Command::SUCCESS;
            
        } catch (\Exception $e) {
            $io->error('Error generating proxies: ' . $e->getMessage());
            $io->error('Stack trace: ' . $e->getTraceAsString());
            return Command::FAILURE;
        }
    }
}
