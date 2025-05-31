<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Attribute\Route;

class DebugController extends AbstractController
{
    #[Route('/api/debug/logs', name: 'api_debug_logs', methods: ['GET'])]
    public function getLogs(): JsonResponse
    {
        $logFile = $this->getParameter('kernel.logs_dir') . '/prod.log';
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
            'timestamp' => date('Y-m-d H:i:s')
        ]);
    }
}
