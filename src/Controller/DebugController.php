<?php

namespace App\Controller;

use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Attribute\Route;

class DebugController extends AbstractController
{
    public function __construct(
        private readonly EntityManagerInterface $entityManager
    ) {}
    
    #[Route('/api/debug/logs', name: 'api_debug_logs', methods: ['GET'])]
    public function getLogs(): JsonResponse
    {
        $currentDate = date('Y-m-d');
        $logFile = $this->getParameter('kernel.logs_dir') . '/prod-' . $currentDate . '.log';
        $phpErrorLog = $this->getParameter('kernel.project_dir') . '/var/log/php_errors.log';
        
        $logs = [];
        
        // Symfony logs
        if (file_exists($logFile)) {
            $content = file_get_contents($logFile);
            $lastLines = array_slice(explode("\n", $content), -50); // Últimas 50 líneas
            $logs['symfony'] = implode("\n", $lastLines);
        } else {
            $logs['symfony'] = 'No symfony log file found';
        }
        
        // PHP error logs
        if (file_exists($phpErrorLog)) {
            $content = file_get_contents($phpErrorLog);
            $lastLines = array_slice(explode("\n", $content), -50); // Últimas 50 líneas
            $logs['php_errors'] = implode("\n", $lastLines);
        } else {
            $logs['php_errors'] = 'No PHP error log file found';
        }
        
        return $this->json([
            'logs' => $logs,
            'log_files' => [
                'symfony' => $logFile,
                'php_errors' => $phpErrorLog
            ],
            'timestamp' => date('Y-m-d H:i:s')
        ]);
    }
      #[Route('/api/debug/info', name: 'api_debug_info', methods: ['GET'])]
    public function getInfo(): JsonResponse
    {
        return $this->json([
            'php_version' => PHP_VERSION,
            'symfony_version' => \Symfony\Component\HttpKernel\Kernel::VERSION,
            'environment' => $this->getParameter('kernel.environment'),
            'debug' => $this->getParameter('kernel.debug'),
            'project_dir' => $this->getParameter('kernel.project_dir'),
            'logs_dir' => $this->getParameter('kernel.logs_dir'),
            'cache_dir' => $this->getParameter('kernel.cache_dir'),
            'loaded_extensions' => get_loaded_extensions(),
            'memory_limit' => ini_get('memory_limit'),
            'max_execution_time' => ini_get('max_execution_time'),
            'database_url_set' => $_ENV['DATABASE_URL'] ?? 'Not set',
            'app_env' => $_ENV['APP_ENV'] ?? 'Not set',
            'cors_origin' => $_ENV['CORS_ALLOW_ORIGIN'] ?? 'Not set',
            'timestamp' => date('Y-m-d H:i:s')
        ]);
    }
    
    #[Route('/api/debug/database', name: 'api_debug_database', methods: ['GET'])]
    public function getDatabaseInfo(): JsonResponse
    {
        try {
            $connection = $this->entityManager->getConnection();
            $platform = $connection->getDatabasePlatform()->getName();
            
            // Test simple query
            $stmt = $connection->prepare('SELECT 1 as test');
            $result = $stmt->executeQuery();
            $testResult = $result->fetchAssociative();
            
            return $this->json([
                'database_platform' => $platform,
                'connection_test' => $testResult ? 'SUCCESS' : 'FAILED',
                'timestamp' => date('Y-m-d H:i:s')
            ]);
        } catch (\Exception $e) {
            return $this->json([
                'error' => $e->getMessage(),
                'timestamp' => date('Y-m-d H:i:s')
            ], 500);
        }
    }
}
