<?php
require_once __DIR__ . '/../vendor/autoload.php';

// Charger les variables d'environnement
$dotenv = Dotenv\Dotenv::createImmutable(__DIR__ . '/../');
$dotenv->safeLoad();

use App\Config\Database;
use PDOException;

$db = Database::getInstance();
$sql = file_get_contents(__DIR__ . '/create_jobs_table.sql');

try {
    $db->execute($sql);
    echo "Migration successful: jobs table created.\n";
} catch (PDOException $e) {
    echo "Migration failed: " . $e->getMessage() . "\n";
}
