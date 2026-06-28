<?php
require_once __DIR__ . '/../vendor/autoload.php';

use App\Config\Database;
use Dotenv\Dotenv;

// Charger les variables d'environnement
$dotenv = Dotenv::createImmutable(__DIR__ . '/../');
$dotenv->safeLoad();

$db = Database::getInstance();

echo "Début de la migration de sécurité...\n";

try {
    // 1. Table Organizations
    echo "Création de la table 'organizations'...\n";
    $sqlOrg = "CREATE TABLE IF NOT EXISTS organizations (
        id INT AUTO_INCREMENT PRIMARY KEY,
        name VARCHAR(255) NOT NULL,
        code VARCHAR(50) NOT NULL UNIQUE,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;";
    $db->execute($sqlOrg);

    // 2. Table Eligible Voters (Whitelist)
    echo "Création de la table 'eligible_voters'...\n";
    $sqlEligible = "CREATE TABLE IF NOT EXISTS eligible_voters (
        id INT AUTO_INCREMENT PRIMARY KEY,
        organization_id INT NOT NULL,
        identifier_code VARCHAR(100) NOT NULL,
        first_name VARCHAR(100) NOT NULL,
        last_name VARCHAR(100) NOT NULL,
        email VARCHAR(255) NULL,
        is_registered BOOLEAN DEFAULT FALSE,
        user_id INT NULL,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        image_url VARCHAR(255) NULL,
        UNIQUE KEY unique_voter_org (organization_id, identifier_code),
        FOREIGN KEY (organization_id) REFERENCES organizations(id) ON DELETE CASCADE,
        FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE SET NULL
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;";
    $db->execute($sqlEligible);

    // 3. Modifier Users Table
    echo "Mise à jour de la table 'users'...\n";
    
    // Check if columns exist before adding
    $columns = $db->query("SHOW COLUMNS FROM users");
    $hasOrgId = false;
    $hasIdentifier = false;
    
    foreach ($columns as $col) {
        if ($col['Field'] === 'organization_id') $hasOrgId = true;
        if ($col['Field'] === 'identifier_code') $hasIdentifier = true;
    }
    
    if (!$hasOrgId) {
        $db->execute("ALTER TABLE users ADD COLUMN organization_id INT NULL AFTER role");
        $db->execute("ALTER TABLE users ADD CONSTRAINT fk_users_org FOREIGN KEY (organization_id) REFERENCES organizations(id) ON DELETE SET NULL");
        echo "- Colonne organization_id ajoutée.\n";
    }
    
    if (!$hasIdentifier) {
        $db->execute("ALTER TABLE users ADD COLUMN identifier_code VARCHAR(100) NULL AFTER organization_id");
        echo "- Colonne identifier_code ajoutée.\n";
    }

    // 4. Modifier Elections Table
    echo "Mise à jour de la table 'elections'...\n";
    $columns = $db->query("SHOW COLUMNS FROM elections");
    $hasOrgIdElec = false;
    
    foreach ($columns as $col) {
        if ($col['Field'] === 'organization_id') $hasOrgIdElec = true;
    }
    
    if (!$hasOrgIdElec) {
        $db->execute("ALTER TABLE elections ADD COLUMN organization_id INT NULL AFTER id");
        $db->execute("ALTER TABLE elections ADD CONSTRAINT fk_elections_org FOREIGN KEY (organization_id) REFERENCES organizations(id) ON DELETE CASCADE");
        echo "- Colonne organization_id ajoutée.\n";
    }

    // 5. Créer une organisation par défaut pour l'admin et les données existantes
    $existingRefOrg = $db->queryOne("SELECT id FROM organizations WHERE code = 'DEFAULT'");
    if (!$existingRefOrg) {
        $db->execute("INSERT INTO organizations (name, code) VALUES ('Organisation Principale', 'DEFAULT')");
        $orgId = $db->lastInsertId();
        echo "Organisation par défaut créée (ID: $orgId).\n";
        
        // Assigner cette org aux utilisateurs et élections existants sans org
        $db->execute("UPDATE users SET organization_id = ? WHERE organization_id IS NULL AND role != 'admin'", [$orgId]);
        $db->execute("UPDATE elections SET organization_id = ? WHERE organization_id IS NULL", [$orgId]);
        echo "Données existantes liées à l'organisation par défaut.\n";
    }

    echo "Migration terminée avec succès !\n";

} catch (Exception $e) {
    echo "ERREUR CRITIQUE : " . $e->getMessage() . "\n";
}
