<?php
require_once __DIR__ . '/../vendor/autoload.php';

use Dotenv\Dotenv;
use App\Config\Database;
use App\Models\User;
use App\Models\Election;
// Load env
$dotenv = Dotenv::createImmutable(__DIR__ . '/../');
$dotenv->safeLoad();

echo "Autoloading worked!\n";

try {
    $db = Database::getInstance();
    echo "Database connection successful!\n";
    
    $userModel = new User();
    $count = $userModel->count();
    echo "User count: $count\n";
    
    $electionModel = new Election();
    $elections = $electionModel->count();
    echo "Election count: $elections\n";
    
} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
    exit(1);
}

echo "Verification complete.\n";
