<?php
// Script de migration pour la table jobs
require_once __DIR__ . '/../vendor/autoload.php';

$dotenv = Dotenv\Dotenv::createImmutable(__DIR__ . '/..');
$dotenv->safeLoad();

use App\Config\Database;

echo "Migration de la base de données...\n";

try {
    $db = Database::getInstance();
    
    // Schema compatible avec QueueService
    $sql = "CREATE TABLE IF NOT EXISTS jobs (
        id INT AUTO_INCREMENT PRIMARY KEY,
        queue VARCHAR(255) NOT NULL DEFAULT 'default',
        payload LONGTEXT NOT NULL,
        available_at DATETIME NOT NULL,
        reserved_at DATETIME NULL,
        created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
        INDEX idx_queue_available (queue, available_at)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;";
    
    $db->execute($sql);
    echo "Table 'jobs' créée avec succès.\n";
    
} catch (Exception $e) {
    echo "Erreur lors de la migration : " . $e->getMessage() . "\n";
    exit(1);
}
