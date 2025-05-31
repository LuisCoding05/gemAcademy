<?php

namespace App\Controller;

use Doctrine\DBAL\Connection;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

class HealthCheckController extends AbstractController
{
    public function __construct(
        private Connection $connection
    ) {
    }

    #[Route('/', name: 'app_health_check', methods: ['GET'])]
    public function index(): Response
    {
        return new Response($this->generateHealthCheckHtml(), 200, [
            'Content-Type' => 'text/html'
        ]);
    }

    #[Route('/health', name: 'app_health_check_json', methods: ['GET'])]
    public function healthCheck(): JsonResponse
    {
        $checks = $this->performHealthChecks();
        
        return new JsonResponse([
            'status' => $checks['overall_status'],
            'timestamp' => date('Y-m-d H:i:s'),
            'application' => 'GEM Academy',
            'version' => '1.0.0',
            'checks' => $checks['details']
        ], $checks['overall_status'] === 'healthy' ? 200 : 503);
    }

    #[Route('/status', name: 'app_status', methods: ['GET'])]
    public function status(): JsonResponse
    {
        return new JsonResponse([
            'status' => 'online',
            'message' => 'GEM Academy API is running',
            'timestamp' => date('Y-m-d H:i:s'),
            'environment' => $_ENV['APP_ENV'] ?? 'unknown'
        ]);
    }

    private function performHealthChecks(): array
    {
        $checks = [];
        $overallHealthy = true;

        // Check database connection
        try {
            $this->connection->connect();
            $result = $this->connection->fetchOne('SELECT 1');
            $checks['database'] = [
                'status' => 'healthy',
                'message' => 'Database connection successful',
                'details' => 'Connected to PostgreSQL'
            ];
        } catch (\Exception $e) {
            $checks['database'] = [
                'status' => 'unhealthy',
                'message' => 'Database connection failed',
                'error' => $e->getMessage()
            ];
            $overallHealthy = false;
        }

        // Check if tables exist
        try {
            $tableCount = $this->connection->fetchOne(
                "SELECT COUNT(*) FROM information_schema.tables WHERE table_schema = 'public' AND table_type = 'BASE TABLE'"
            );
            $checks['database_schema'] = [
                'status' => $tableCount > 0 ? 'healthy' : 'warning',
                'message' => "Found {$tableCount} tables in database",
                'table_count' => (int) $tableCount
            ];
        } catch (\Exception $e) {
            $checks['database_schema'] = [
                'status' => 'unhealthy',
                'message' => 'Could not check database schema',
                'error' => $e->getMessage()
            ];
            $overallHealthy = false;
        }

        // Check cache directory
        $cacheDir = $this->getParameter('kernel.cache_dir');
        $checks['cache'] = [
            'status' => is_dir($cacheDir) && is_writable($cacheDir) ? 'healthy' : 'unhealthy',
            'message' => is_dir($cacheDir) ? 'Cache directory accessible' : 'Cache directory not accessible',
            'path' => $cacheDir
        ];

        // Check log directory
        $logDir = $this->getParameter('kernel.logs_dir');
        $checks['logs'] = [
            'status' => is_dir($logDir) && is_writable($logDir) ? 'healthy' : 'warning',
            'message' => is_dir($logDir) ? 'Log directory accessible' : 'Log directory not accessible',
            'path' => $logDir
        ];

        // Check environment variables
        $requiredEnvVars = ['DATABASE_URL', 'APP_SECRET'];
        $missingVars = [];
        foreach ($requiredEnvVars as $var) {
            if (empty($_ENV[$var])) {
                $missingVars[] = $var;
            }
        }

        $checks['environment'] = [
            'status' => empty($missingVars) ? 'healthy' : 'unhealthy',
            'message' => empty($missingVars) ? 'All required environment variables are set' : 'Missing required environment variables',
            'missing_variables' => $missingVars
        ];

        if (!empty($missingVars)) {
            $overallHealthy = false;
        }

        return [
            'overall_status' => $overallHealthy ? 'healthy' : 'unhealthy',
            'details' => $checks
        ];
    }

    private function generateHealthCheckHtml(): string
    {
        $checks = $this->performHealthChecks();
        $isHealthy = $checks['overall_status'] === 'healthy';
        
        $html = '<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>GEM Academy - Health Check</title>
    <style>
        body {
            font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, sans-serif;
            margin: 0;
            padding: 20px;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
            color: #333;
        }
        .container {
            max-width: 800px;
            margin: 0 auto;
            background: white;
            border-radius: 12px;
            box-shadow: 0 20px 40px rgba(0,0,0,0.1);
            overflow: hidden;
        }
        .header {
            background: ' . ($isHealthy ? '#4ade80' : '#ef4444') . ';
            color: white;
            padding: 30px;
            text-align: center;
        }
        .header h1 {
            margin: 0;
            font-size: 2.5rem;
            font-weight: 300;
        }
        .status {
            font-size: 1.2rem;
            margin-top: 10px;
            opacity: 0.9;
        }
        .content {
            padding: 30px;
        }
        .info-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
            gap: 20px;
            margin-bottom: 30px;
        }
        .info-card {
            background: #f8fafc;
            border-left: 4px solid #3b82f6;
            padding: 20px;
            border-radius: 8px;
        }
        .info-card h3 {
            margin: 0 0 10px 0;
            color: #1e293b;
            font-size: 1.1rem;
        }
        .info-card p {
            margin: 5px 0;
            color: #64748b;
        }
        .checks {
            margin-top: 30px;
        }
        .check-item {
            display: flex;
            align-items: center;
            padding: 15px;
            margin: 10px 0;
            border-radius: 8px;
            background: #f8fafc;
            border-left: 4px solid #10b981;
        }
        .check-item.warning {
            border-left-color: #f59e0b;
            background: #fffbeb;
        }
        .check-item.error {
            border-left-color: #ef4444;
            background: #fef2f2;
        }
        .check-status {
            width: 12px;
            height: 12px;
            border-radius: 50%;
            margin-right: 15px;
            background: #10b981;
        }
        .check-status.warning {
            background: #f59e0b;
        }
        .check-status.error {
            background: #ef4444;
        }
        .check-details {
            flex: 1;
        }
        .check-name {
            font-weight: 600;
            color: #1e293b;
        }
        .check-message {
            color: #64748b;
            font-size: 0.9rem;
            margin-top: 5px;
        }
        .actions {
            margin-top: 30px;
            text-align: center;
        }
        .btn {
            display: inline-block;
            padding: 12px 24px;
            margin: 0 10px;
            background: #3b82f6;
            color: white;
            text-decoration: none;
            border-radius: 6px;
            font-weight: 500;
            transition: background 0.2s;
        }
        .btn:hover {
            background: #2563eb;
        }
        .btn-secondary {
            background: #6b7280;
        }
        .btn-secondary:hover {
            background: #4b5563;
        }
        @media (max-width: 768px) {
            .info-grid {
                grid-template-columns: 1fr;
            }
            .header h1 {
                font-size: 2rem;
            }
            .container {
                margin: 10px;
            }
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h1>🎓 GEM Academy</h1>
            <div class="status">
                ' . ($isHealthy ? '✅ Sistema Funcionando Correctamente' : '⚠️ Sistema con Problemas') . '
            </div>
        </div>
        
        <div class="content">
            <div class="info-grid">
                <div class="info-card">
                    <h3>🚀 Estado del Sistema</h3>
                    <p><strong>Estado:</strong> ' . ucfirst($checks['overall_status']) . '</p>
                    <p><strong>Timestamp:</strong> ' . date('Y-m-d H:i:s') . '</p>
                    <p><strong>Entorno:</strong> ' . ($_ENV['APP_ENV'] ?? 'unknown') . '</p>
                </div>
                
                <div class="info-card">
                    <h3>🔧 Versión</h3>
                    <p><strong>Aplicación:</strong> GEM Academy</p>
                    <p><strong>Versión:</strong> 1.0.0</p>
                    <p><strong>Framework:</strong> Symfony</p>
                </div>
                
                <div class="info-card">
                    <h3>🌐 Servidor</h3>
                    <p><strong>PHP:</strong> ' . PHP_VERSION . '</p>
                    <p><strong>Servidor:</strong> ' . ($_SERVER['SERVER_SOFTWARE'] ?? 'Apache') . '</p>
                    <p><strong>Host:</strong> ' . ($_SERVER['HTTP_HOST'] ?? 'localhost') . '</p>
                </div>
                
                <div class="info-card">
                    <h3>💾 Base de Datos</h3>
                    <p><strong>Tipo:</strong> PostgreSQL</p>
                    <p><strong>Estado:</strong> ' . ucfirst($checks['details']['database']['status']) . '</p>
                    <p><strong>Tablas:</strong> ' . ($checks['details']['database_schema']['table_count'] ?? 0) . '</p>
                </div>
            </div>
            
            <div class="checks">
                <h2>🔍 Verificaciones del Sistema</h2>';

        foreach ($checks['details'] as $checkName => $check) {
            $statusClass = $check['status'] === 'healthy' ? '' : ($check['status'] === 'warning' ? 'warning' : 'error');
            $statusIcon = $check['status'] === 'healthy' ? '✅' : ($check['status'] === 'warning' ? '⚠️' : '❌');
            
            $html .= '
                <div class="check-item ' . $statusClass . '">
                    <div class="check-status ' . $statusClass . '"></div>
                    <div class="check-details">
                        <div class="check-name">' . $statusIcon . ' ' . ucfirst(str_replace('_', ' ', $checkName)) . '</div>
                        <div class="check-message">' . htmlspecialchars($check['message']) . '</div>
                    </div>
                </div>';
        }

        $html .= '
            </div>
            
            <div class="actions">
                <a href="/health" class="btn">Ver JSON Status</a>
                <a href="/status" class="btn btn-secondary">API Status</a>
            </div>
        </div>
    </div>
</body>
</html>';

        return $html;
    }
}
