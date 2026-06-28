<?php
require_once __DIR__ . '/../vendor/autoload.php';
$dotenv = Dotenv\Dotenv::createImmutable(__DIR__ . '/..');
$dotenv->safeLoad();
use App\Config\Database;

$db = Database::getInstance();
echo "Migrating users table...\n";
try {
    $db->execute("ALTER TABLE users ADD COLUMN verification_token VARCHAR(255) NULL AFTER is_verified");
    echo "Added verification_token.\n";
} catch (Exception $e) { echo "verification_token exists or error: " . $e->getMessage() . "\n"; }

try {
    $db->execute("ALTER TABLE users ADD COLUMN verification_expires_at DATETIME NULL AFTER verification_token");
    echo "Added verification_expires_at.\n";
} catch (Exception $e) { echo "verification_expires_at exists or error: " . $e->getMessage() . "\n"; }
