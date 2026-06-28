<?php
require_once __DIR__ . '/../vendor/autoload.php';

use App\Config\Database;
use Dotenv\Dotenv;

// Charger les variables d'environnement
$dotenv = Dotenv::createImmutable(__DIR__ . '/../');
$dotenv->safeLoad();

$db = Database::getInstance();

echo "Reinitialisation du mot de passe admin...\n";

$email = 'admin@votesystem.com';
$newPassword = 'admin123';
$hash = password_hash($newPassword, PASSWORD_BCRYPT);

// Vérifier si l'utilisateur existe
$user = $db->queryOne("SELECT * FROM users WHERE email = ?", [$email]);

if ($user) {
    // Mise à jour
    $sql = "UPDATE users SET password_hash = ?, role = 'admin', is_verified = 1 WHERE email = ?";
    try {
        $db->execute($sql, [$hash, $email]);
        echo "SUCCES : Mot de passe pour $email mis a jour (colonne password_hash) vers '$newPassword'\n";
    } catch (Exception $e) {
        echo "ERREUR : " . $e->getMessage() . "\n";
    }
} else {
    // Création
    $sql = "INSERT INTO users (first_name, last_name, email, password_hash, role, is_verified, created_at) 
            VALUES ('Admin', 'System', ?, ?, 'admin', 1, NOW())";
    try {
        $db->execute($sql, [$email, $hash]);
        echo "SUCCES : Admin cree avec le mot de passe '$newPassword'\n";
    } catch (Exception $e) {
        echo "ERREUR : " . $e->getMessage() . "\n";
    }
}
