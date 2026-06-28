<?php
require_once __DIR__ . '/../vendor/autoload.php';

use Dotenv\Dotenv;
use App\Config\Database;
use PDOException;

// Charger les variables d'environnement
$dotenv = Dotenv::createImmutable(__DIR__ . '/../');
$dotenv->load();

try {
    $db = Database::getInstance();
    
    echo "Creating security_logs table for Rate Limiting...\n";
    
    $sql = "CREATE TABLE IF NOT EXISTS security_logs (
        id INT AUTO_INCREMENT PRIMARY KEY,
        ip_address VARCHAR(45) NOT NULL,
        activity_type VARCHAR(50) NOT NULL,
        details TEXT DEFAULT NULL,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        INDEX idx_ip_activity (ip_address, activity_type, created_at)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;";
    
    try {
        $db->execute($sql);
        echo "Table 'security_logs' created successfully.\n";
    } catch (PDOException $e) {
        throw $e;
    }
    
    echo "Migration completed successfully!\n";
    
} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
    exit(1);
}
