<?php
require_once __DIR__ . '/../vendor/autoload.php';

use Dotenv\Dotenv;
use App\Config\Database;

$dotenv = Dotenv::createImmutable(__DIR__ . '/../');
$dotenv->load();

$db = Database::getInstance();

echo "Fxing security_logs schema...\n";

try {
    // Attempt to add the column. If it fails (exists), it will throw, which we catch.
    $sql = "ALTER TABLE security_logs ADD COLUMN activity_type VARCHAR(50) NOT NULL DEFAULT 'unknown'";
    $db->execute($sql);
    echo "Column 'activity_type' added successfully.\n";
} catch (Exception $e) {
    echo "Could not add column (maybe exists?): " . $e->getMessage() . "\n";
}

try {
    // Also check for other columns just in case
    $sql = "ALTER TABLE security_logs ADD COLUMN details TEXT DEFAULT NULL";
    $db->execute($sql);
    echo "Column 'details' added successfully.\n";
} catch (Exception $e) {
    // Ignore
}

echo "Done.\n";
