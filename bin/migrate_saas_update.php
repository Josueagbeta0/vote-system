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
    
    echo "Updating organizations table schema...\n";
    
    // Add type column
    try {
        $db->execute("ALTER TABLE organizations ADD COLUMN type ENUM('school', 'university', 'company', 'other') NOT NULL DEFAULT 'other' AFTER code");
        echo "Added 'type' column.\n";
    } catch (PDOException $e) {
        if (strpos($e->getMessage(), 'Duplicate column name') !== false) {
            echo "'type' column already exists.\n";
        } else {
            throw $e;
        }
    }

    // Add slug column
    try {
        $db->execute("ALTER TABLE organizations ADD COLUMN slug VARCHAR(100) UNIQUE AFTER name");
        echo "Added 'slug' column.\n";
    } catch (PDOException $e) {
        if (strpos($e->getMessage(), 'Duplicate column name') !== false) {
            echo "'slug' column already exists.\n";
        } else {
            throw $e;
        }
    }
    
    // Update existing records to have slugs (basic sanitization)
    $orgs = $db->query("SELECT id, name FROM organizations WHERE slug IS NULL");
    
    foreach ($orgs as $org) {
        // Simple slug generation: lower case, replace spaces/special chars with hyphens
        $slug = strtolower(trim(preg_replace('/[^A-Za-z0-9-]+/', '-', $org['name'])));
        // Append ID if empty or just to be safe (collision handling is basic here)
        if (empty($slug)) $slug = 'org-' . $org['id'];
        
        // Ensure uniqueness (simple append)
        $check = $db->queryOne("SELECT id FROM organizations WHERE slug = :slug", [':slug' => $slug]);
        if ($check) {
            $slug .= '-' . $org['id'];
        }
        
        $db->execute("UPDATE organizations SET slug = :slug WHERE id = :id", [
            ':slug' => $slug,
            ':id' => $org['id']
        ]);
        echo "Updated slug for Organization ID {$org['id']}: $slug\n";
    }

    echo "Migration completed successfully!\n";
    
} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
    exit(1);
}
