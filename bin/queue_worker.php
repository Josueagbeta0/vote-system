<?php
/**
 * Queue Worker Script
 * Run this in background: php bin/queue_worker.php
 */

require_once __DIR__ . '/../vendor/autoload.php';

// Charger l'environnement
$dotenv = Dotenv\Dotenv::createImmutable(__DIR__ . '/..');
$dotenv->safeLoad();

require_once __DIR__ . '/../config/app.php';

use App\Services\QueueService;

// Define constants if not defined (from .env usually)
if (!defined('APP_DEBUG')) define('APP_DEBUG', true);
if (!defined('STORAGE_PATH')) define('STORAGE_PATH', __DIR__ . '/../storage');

function workerLog($message, $level = 'INFO') {
    $logFile = __DIR__ . '/../storage/logs/worker.log';
    $date = date('Y-m-d H:i:s');
    $logEntry = "[$date] [$level] $message" . PHP_EOL;
    
    // Ensure directory exists
    $dir = dirname($logFile);
    if (!is_dir($dir)) {
        mkdir($dir, 0755, true);
    }
    
    file_put_contents($logFile, $logEntry, FILE_APPEND);
    echo $logEntry;
}

try {
    $queueService = new QueueService();
    
    workerLog("Worker started. Listening for jobs...");
    
    while (true) {
        try {
            $processed = $queueService->work();
            
            if (!$processed) {
                // Sleep for 2 seconds if no jobs
                sleep(2);
            }
        } catch (Exception $e) {
            workerLog("Worker Exception: " . $e->getMessage(), 'ERROR');
            sleep(5);
        }
    }
} catch (Exception $e) {
    workerLog("Fatal Error: " . $e->getMessage(), 'CRITICAL');
    exit(1);
}
