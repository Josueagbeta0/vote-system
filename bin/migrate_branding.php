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
    
    echo "Updating organizations table schema for Branding...\n";
    
    // Add logo_url column
    try {
        $db->execute("ALTER TABLE organizations ADD COLUMN logo_url VARCHAR(255) DEFAULT NULL AFTER slug");
        echo "Added 'logo_url' column.\n";
    } catch (PDOException $e) {
        if (strpos($e->getMessage(), 'Duplicate column name') !== false) {
            echo "'logo_url' column already exists.\n";
        } else {
            throw $e;
        }
    }

    // Add primary_color column
    try {
        $db->execute("ALTER TABLE organizations ADD COLUMN primary_color VARCHAR(7) DEFAULT '#0d6efd' AFTER logo_url"); // Default Bootstrap Blue
        echo "Added 'primary_color' column.\n";
    } catch (PDOException $e) {
        if (strpos($e->getMessage(), 'Duplicate column name') !== false) {
            echo "'primary_color' column already exists.\n";
        } else {
            throw $e;
        }
    }
    
    echo "Migration completed successfully!\n";
    
} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
    exit(1);
}
