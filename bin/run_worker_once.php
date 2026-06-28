<?php
require_once __DIR__ . '/../vendor/autoload.php';

$dotenv = Dotenv\Dotenv::createImmutable(__DIR__ . '/..');
$dotenv->safeLoad();

require_once __DIR__ . '/../config/app.php';

use App\Services\QueueService;

echo "Running worker once...\n";
try {
    $svc = new QueueService();
    $processed = $svc->work();
    if ($processed) {
        echo "Job processed successfully.\n";
    } else {
        echo "No jobs found.\n";
    }
} catch (Exception $e) {
    echo "Worker Error: " . $e->getMessage() . "\n";
}
