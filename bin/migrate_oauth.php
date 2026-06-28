<?php
require_once __DIR__ . '/../vendor/autoload.php';

use App\Config\Database;
use PDOException;

try {
    $db = Database::getInstance();
    echo "Adding OAuth fields to users table...\n";

    // Add auth_provider, auth_provider_id, avatar_url
    // We check if column exists first (or just ignore error if duplicate)
    // SQL way:
    $sql = "ALTER TABLE users 
            ADD COLUMN auth_provider VARCHAR(20) DEFAULT 'local',
            ADD COLUMN auth_provider_id VARCHAR(255) DEFAULT NULL,
            ADD COLUMN avatar_url VARCHAR(255) DEFAULT NULL,
            ADD INDEX idx_auth_provider (auth_provider, auth_provider_id);";

    try {
        $db->execute($sql);
        echo "Columns added successfully.\n";
    } catch (PDOException $e) {
        // If columns already exist, this might fail, so we catch it
        echo "Note: " . $e->getMessage() . "\n";
    }

    // Make password nullable because OAuth users might not have one initially
    $sqlNullablePass = "ALTER TABLE users MODIFY COLUMN password VARCHAR(255) NULL;";
    try {
        $db->execute($sqlNullablePass);
        echo "Password column modified to support NULL.\n";
    } catch (PDOException $e) {
         echo "Note: " . $e->getMessage() . "\n";
    }

    echo "Migration completed.\n";

} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
}
