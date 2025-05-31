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
        ]);    }
    
    #[Route('/api/debug/doctrine', name: 'api_debug_doctrine', methods: ['GET'])]
    public function getDoctrine(): JsonResponse
    {
        try {
            $em = $this->entityManager;
            $config = $em->getConfiguration();
            
            $debug_info = [
                'proxy_dir' => $config->getProxyDir(),
                'auto_generate_proxies' => $config->getAutoGenerateProxyClasses(),
                'cache_dir' => $this->getParameter('kernel.cache_dir'),
                'proxy_dir_exists' => is_dir($config->getProxyDir()),
                'proxy_dir_writable' => is_writable($config->getProxyDir()),
                'proxy_files' => [],
                'entities' => []
            ];
            
            // Listar archivos proxy existentes
            if (is_dir($config->getProxyDir())) {
                $proxy_files = glob($config->getProxyDir() . '/*.php');
                if ($proxy_files) {
                    $debug_info['proxy_files'] = array_map('basename', $proxy_files);
                    $debug_info['proxy_count'] = count($proxy_files);
                } else {
                    $debug_info['proxy_files'] = [];
                    $debug_info['proxy_count'] = 0;
                }
            } else {
                $debug_info['proxy_count'] = 0;
                $debug_info['error'] = 'Proxy directory does not exist';
            }
            
            // Listar entidades registradas
            $metadata = $em->getMetadataFactory()->getAllMetadata();
            foreach ($metadata as $meta) {
                $debug_info['entities'][] = [
                    'class' => $meta->getName(),
                    'proxy_class' => $config->getProxyNamespace() . '\\' . basename(str_replace('\\', '/', $meta->getName())),
                ];
            }
            
            return new JsonResponse([
                'status' => 'success',
                'doctrine' => $debug_info,
                'timestamp' => date('Y-m-d H:i:s')
            ]);
            
        } catch (\Exception $e) {
            return new JsonResponse([
                'status' => 'error',
                'message' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
                'timestamp' => date('Y-m-d H:i:s')
            ], 500);
        }    }
    
    #[Route('/api/debug/jwt', name: 'api_debug_jwt', methods: ['GET'])]
    public function getJwtInfo(): JsonResponse
    {
        try {
            $projectDir = $this->getParameter('kernel.project_dir');
            $privateKeyPath = $projectDir . '/config/jwt/private.pem';
            $publicKeyPath = $projectDir . '/config/jwt/public.pem';
            
            $debug_info = [
                'private_key_path' => $privateKeyPath,
                'public_key_path' => $publicKeyPath,
                'private_key_exists' => file_exists($privateKeyPath),
                'public_key_exists' => file_exists($publicKeyPath),
                'private_key_readable' => is_readable($privateKeyPath),
                'public_key_readable' => is_readable($publicKeyPath),
                'jwt_secret_key_env' => $_ENV['JWT_SECRET_KEY'] ?? 'Not set',
                'jwt_public_key_env' => $_ENV['JWT_PUBLIC_KEY'] ?? 'Not set',
                'jwt_passphrase_env' => isset($_ENV['JWT_PASSPHRASE']) ? '***SET***' : 'Not set',
                'config_dir_exists' => is_dir($projectDir . '/config/jwt'),
                'config_dir_writable' => is_writable($projectDir . '/config/jwt'),
            ];
            
            // Intentar leer información básica de las claves (sin exponer contenido sensible)
            if (file_exists($privateKeyPath)) {
                $debug_info['private_key_size'] = filesize($privateKeyPath);
                $debug_info['private_key_modified'] = date('Y-m-d H:i:s', filemtime($privateKeyPath));
            }
            
            if (file_exists($publicKeyPath)) {
                $debug_info['public_key_size'] = filesize($publicKeyPath);
                $debug_info['public_key_modified'] = date('Y-m-d H:i:s', filemtime($publicKeyPath));
            }
            
            return new JsonResponse([
                'status' => 'success',
                'jwt' => $debug_info,
                'timestamp' => date('Y-m-d H:i:s')
            ]);
            
        } catch (\Exception $e) {
            return new JsonResponse([
                'status' => 'error',
                'message' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
                'timestamp' => date('Y-m-d H:i:s')
            ], 500);
        }
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
