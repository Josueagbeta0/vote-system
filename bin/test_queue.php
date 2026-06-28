<?php
require_once __DIR__ . '/../vendor/autoload.php';

$dotenv = Dotenv\Dotenv::createImmutable(__DIR__ . '/..');
$dotenv->safeLoad();

require_once __DIR__ . '/../config/app.php';

use App\Services\EmailService;

try {
    echo "Initializing EmailService...\n";
    $svc = new EmailService();
    echo "Pushing job...\n";
    // Passing dummy data
    $svc->sendVerificationEmail("test@example.com", "token123", "TestUser");
    echo "Job pushed successfully to 'jobs' table.\n";
} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
}
