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
    
}
