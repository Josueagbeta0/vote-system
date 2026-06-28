<?php
/**
 * Configuration de l'application
 */

// Charger les variables d'environnement si pas déjà fait
if (!getenv('APP_NAME')) {
    $envFile = __DIR__ . '/../.env';
    if (file_exists($envFile)) {
        $lines = file($envFile, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
        foreach ($lines as $line) {
            if (strpos(trim($line), '#') === 0) continue;
            list($key, $value) = explode('=', $line, 2);
            putenv(trim($key) . '=' . trim($value, '"\''));
        }
    }
}

// Configuration générale
define('APP_NAME', getenv('APP_NAME') ?: 'Vote Sécurisé');
define('APP_URL', getenv('APP_URL') ?: 'http://localhost/vote-system');
define('APP_DEBUG', getenv('APP_DEBUG') === 'true');

// Timezone
date_default_timezone_set(getenv('TIMEZONE') ?: 'Africa/Porto-Novo');

// Gestion des erreurs
if (APP_DEBUG) {
    error_reporting(E_ALL);
    ini_set('display_errors', 1);
} else {
    error_reporting(0);
    ini_set('display_errors', 0);
    ini_set('log_errors', 1);
    ini_set('error_log', __DIR__ . '/../storage/logs/error.log');
}

// Configuration des sessions
ini_set('session.cookie_httponly', 1);
ini_set('session.use_only_cookies', 1);
ini_set('session.cookie_secure', isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? 1 : 0);
ini_set('session.cookie_samesite', 'Strict');

// Durée de vie de la session
$sessionLifetime = (int)(getenv('SESSION_LIFETIME') ?: 3600);
ini_set('session.gc_maxlifetime', $sessionLifetime);
session_set_cookie_params($sessionLifetime);

// Démarrer la session
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Régénérer l'ID de session pour la sécurité
if (!isset($_SESSION['initiated'])) {
    session_regenerate_id(true);
    $_SESSION['initiated'] = true;
    $_SESSION['created_at'] = time();
}

// Vérifier l'expiration de la session
if (isset($_SESSION['created_at']) && (time() - $_SESSION['created_at'] > $sessionLifetime)) {
    session_unset();
    session_destroy();
    session_start();
}

// Mise à jour du dernier temps d'activité
$_SESSION['last_activity'] = time();

// Chemins de l'application
define('ROOT_PATH', dirname(__DIR__));
define('PUBLIC_PATH', ROOT_PATH . '/public');
define('STORAGE_PATH', ROOT_PATH . '/storage');
define('UPLOAD_PATH', PUBLIC_PATH . '/uploads');
define('LOG_PATH', STORAGE_PATH . '/logs');

// URLs
define('BASE_URL', rtrim(APP_URL, '/'));
define('ASSETS_URL', BASE_URL . '/public/assets');

// Créer les dossiers nécessaires s'ils n'existent pas
$directories = [
    STORAGE_PATH . '/logs',
    STORAGE_PATH . '/cache',
    STORAGE_PATH . '/sessions',
    UPLOAD_PATH,
    UPLOAD_PATH . '/candidates'
];

foreach ($directories as $dir) {
    if (!is_dir($dir)) {
        mkdir($dir, 0755, true);
    }
}

// Configuration de sécurité
define('MAX_LOGIN_ATTEMPTS', (int)(getenv('MAX_LOGIN_ATTEMPTS') ?: 5));
define('LOGIN_BLOCK_DURATION', (int)(getenv('LOGIN_BLOCK_DURATION') ?: 15));
define('ENCRYPTION_KEY', getenv('ENCRYPTION_KEY') ?: 'change_this_key_in_production');

// ReCaptcha Config
define('RECAPTCHA_SITE_KEY', getenv('RECAPTCHA_SITE_KEY') ?: $_ENV['RECAPTCHA_SITE_KEY'] ?? '');

// Constantes utiles
define('PASSWORD_MIN_LENGTH', 8);
define('UPLOAD_MAX_SIZE', 2 * 1024 * 1024); // 2 MB
define('ALLOWED_IMAGE_TYPES', ['image/jpeg', 'image/png', 'image/jpg']);

/**
 * Fonction helper pour inclure les vues
 */
function view($viewName, $data = []) {
    extract($data);
    $viewPath = ROOT_PATH . '/src/Views/' . str_replace('.', '/', $viewName) . '.php';
    
    if (file_exists($viewPath)) {
        require $viewPath;
    } else {
        die("Vue introuvable : $viewName");
    }
}

/**
 * Fonction helper pour les redirections
 */
function redirect($path, $message = null, $type = 'info') {
    if ($message) {
        $_SESSION['flash_message'] = $message;
        $_SESSION['flash_type'] = $type;
    }
    header("Location: " . BASE_URL . $path);
    exit();
}

/**
 * Fonction helper pour générer un token CSRF
 */
function generateCsrfToken() {
    if (!isset($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }
    return $_SESSION['csrf_token'];
}

/**
 * Fonction helper pour vérifier un token CSRF
 */
function verifyCsrfToken($token) {
    return isset($_SESSION['csrf_token']) && hash_equals($_SESSION['csrf_token'], $token);
}

/**
 * Fonction helper pour afficher les messages flash
 */
function getFlashMessage() {
    if (isset($_SESSION['flash_message'])) {
        $message = $_SESSION['flash_message'];
        $type = $_SESSION['flash_type'] ?? 'info';
        unset($_SESSION['flash_message'], $_SESSION['flash_type']);
        return ['message' => $message, 'type' => $type];
    }
    return null;
}

/**
 * Fonction helper pour échapper les données HTML
 */
function e($string) {
    return htmlspecialchars($string, ENT_QUOTES, 'UTF-8');
}

/**
 * Fonction helper pour logger
 */
function logMessage($message, $level = 'INFO') {
    if (getenv('ENABLE_LOGGING') === 'true') {
        $logFile = LOG_PATH . '/app.log';
        $timestamp = date('Y-m-d H:i:s');
        $logEntry = "[$timestamp] [$level] $message" . PHP_EOL;
        file_put_contents($logFile, $logEntry, FILE_APPEND);
    }
}