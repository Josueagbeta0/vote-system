<?php
require_once __DIR__ . '/../vendor/autoload.php';

use App\Config\Database;
use Dotenv\Dotenv;
use PDOException;

// Charger les variables d'environnement
$dotenv = Dotenv::createImmutable(__DIR__ . '/../');
$dotenv->safeLoad();

$db = Database::getInstance();

// 1. Lister les admins existants
echo "--- Admins existants ---\n";
$admins = $db->query("SELECT id, first_name, last_name, email FROM users WHERE role = 'admin'");

if (count($admins) > 0) {
    foreach ($admins as $admin) {
        echo "ID: {$admin['id']} | Nom: {$admin['first_name']} {$admin['last_name']} | Email: {$admin['email']}\n";
    }
} else {
    echo "Aucun administrateur trouve.\n";
    
    // 2. Proposer d'en creer un par defaut
    echo "\nCreation d'un admin par defaut (admin@votesystem.com / admin123)...\n";
    
    $password = password_hash('admin123', PASSWORD_BCRYPT);
    $sql = "INSERT INTO users (first_name, last_name, email, password_hash, role, is_verified, created_at) 
            VALUES ('Admin', 'System', 'admin@votesystem.com', ?, 'admin', 1, NOW())";
            
    try {
        $db->execute($sql, [$password]);
        echo "Admin cree avec succes !\n";
        echo "Email: admin@votesystem.com\n";
        echo "Password: admin123\n";
    } catch (PDOException $e) {
        echo "Erreur lors de la creation : " . $e->getMessage() . "\n";
    }
}
