<?php
require_once __DIR__ . '/../vendor/autoload.php';
$dotenv = Dotenv\Dotenv::createImmutable(__DIR__ . '/..');
$dotenv->safeLoad();
use App\Config\Database;

$db = Database::getInstance();
$cols = $db->query("DESCRIBE users");
foreach ($cols as $col) {
    echo $col['Field'] . "\n";
}
