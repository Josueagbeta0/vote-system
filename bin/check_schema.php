<?php
require_once __DIR__ . '/public/index.php'; // Load config via index bootstrap logic (partial)
// Actually better to just load autoload and config directly to avoid router dispatch
require_once __DIR__ . '/vendor/autoload.php';
$dotenv = Dotenv\Dotenv::createImmutable(__DIR__);
$dotenv->load();

use App\Config\Database;

try {
    $db = Database::getInstance();
    $rows = $db->queryAll("DESCRIBE security_logs");
    echo "Columns in security_logs:\n";
    foreach ($rows as $r) {
        echo "- " . $r['Field'] . " (" . $r['Type'] . ")\n";
    }
} catch (Exception $e) {
    echo "Error: " . $e->getMessage();
}
