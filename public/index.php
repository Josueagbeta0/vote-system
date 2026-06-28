<?php
/**
 * Point d'entrée de l'application
 * Routeur moderne avec AltoRouter
 */

// Activer l'affichage des erreurs pour le débogage
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// Gestionnaire d'erreurs personnalisé pour capturer les erreurs fatales
register_shutdown_function(function() {
    $error = error_get_last();
    if ($error !== NULL && in_array($error['type'], [E_ERROR, E_PARSE, E_CORE_ERROR, E_COMPILE_ERROR])) {
        echo "<h1 style='color: red;'>Erreur fatale détectée</h1>";
        echo "<p><strong>Message:</strong> " . htmlspecialchars($error['message']) . "</p>";
        echo "<p><strong>Fichier:</strong> " . htmlspecialchars($error['file']) . "</p>";
        echo "<p><strong>Ligne:</strong> " . $error['line'] . "</p>";
        if (defined('APP_DEBUG') && APP_DEBUG) {
            echo "<pre>" . htmlspecialchars(print_r($error, true)) . "</pre>";
        }
    }
});

use Dotenv\Dotenv;
use App\Models\User;

// Charger l'autoloader de Composer
require_once __DIR__ . '/../vendor/autoload.php';

// Charger les variables d'environnement
$dotenv = Dotenv::createImmutable(__DIR__ . '/../');
$dotenv->safeLoad();

// Charger la configuration (constantes et helpers)
require_once __DIR__ . '/../config/app.php';

// Initialiser le routeur
$router = new AltoRouter();

// Détection dynamique du dossier de base
$scriptDir = dirname($_SERVER['SCRIPT_NAME']); // ex: /vote-system/public
$requestUri = $_SERVER['REQUEST_URI'];

// Si l'URI commence par /vote-system/public, on utilise ce base path
if (strpos($requestUri, $scriptDir) === 0) {
    $basePath = $scriptDir;
} else {
    // Sinon on remonte d'un cran (mode Clean URL via .htaccess racine)
    $basePath = dirname($scriptDir);
}

// Nettoyage des slashes (Windows/Unix)
$basePath = str_replace('\\', '/', $basePath);
if ($basePath === '/' || $basePath === '.') $basePath = '';

$router->setBasePath($basePath);


// --- Routes ---

// Portal (Home)
$router->map('GET', '/', 'App\Controllers\PortalController#index', 'portal_home');
$router->map('GET', '/portal/[a:type]', 'App\Controllers\PortalController#selectCategory', 'portal_category');
$router->map('GET', '/portal/access/[a:slug]', 'App\Controllers\PortalController#accessOrganization', 'portal_access');
$router->map('GET', '/portal/search', 'App\Controllers\PortalController#search', 'portal_search');

// Org Signup
$router->map('GET', '/create-organization', 'App\Controllers\OrganizationController#create', 'org_create');
$router->map('POST', '/create-organization/store', 'App\Controllers\OrganizationController#store', 'org_store');

// Auth
$router->map('GET|POST', '/auth/login', 'App\Controllers\AuthController#login', 'auth_login');
$router->map('GET', '/auth/google', 'App\Controllers\AuthController#loginWithGoogle', 'auth_google_login');
$router->map('GET', '/auth/callback/google', 'App\Controllers\AuthController#callbackGoogle', 'auth_google_callback');
$router->map('POST', '/auth/process-login', 'App\Controllers\AuthController#processLogin', 'auth_process_login');
$router->map('GET|POST', '/auth/register', 'App\Controllers\AuthController#register', 'auth_register');
$router->map('POST', '/auth/process-register', 'App\Controllers\AuthController#processRegister', 'auth_process_register');
$router->map('GET', '/auth/verify-email', 'App\Controllers\AuthController#verifyEmail', 'auth_verify_email');
$router->map('GET', '/auth/logout', 'App\Controllers\AuthController#logout', 'auth_logout');
$router->map('GET|POST', '/auth/forgot-password', 'App\Controllers\AuthController#forgotPassword', 'auth_forgot_password');
$router->map('POST', '/auth/process-forgot-password', 'App\Controllers\AuthController#processForgotPassword', 'auth_process_forgot_password');
$router->map('GET|POST', '/auth/reset-password', 'App\Controllers\AuthController#resetPassword', 'auth_reset_password');
$router->map('POST', '/auth/process-reset-password', 'App\Controllers\AuthController#processResetPassword', 'auth_process_reset_password');

// Voter
$router->map('GET', '/voter/dashboard', 'App\Controllers\VoterController#dashboard', 'voter_dashboard');
$router->map('GET', '/vote/cast/[i:id]', 'App\Controllers\VoteController#cast', 'vote_cast');
$router->map('POST', '/vote/process/[i:id]', 'App\Controllers\VoteController#processVote', 'vote_process');
$router->map('GET', '/vote/confirmation', 'App\Controllers\VoteController#confirmation', 'vote_confirmation');
$router->map('GET', '/vote/verify', 'App\Controllers\VoteController#verify', 'vote_verify');
$router->map('POST', '/vote/verify', 'App\Controllers\VoteController#processVerify', 'vote_process_verify');
$router->map('GET', '/vote/results/[i:id]', 'App\Controllers\VoteController#results', 'vote_results');
$router->map('GET', '/vote/register/[i:id]', 'App\Controllers\VoteController#register', 'vote_register');

// Admin
$router->map('GET', '/admin/users', 'App\Controllers\AdminController#users', 'admin_users');
$router->map('GET', '/admin/dashboard', 'App\Controllers\AdminController#dashboard', 'admin_dashboard');
$router->map('GET', '/admin/elections', 'App\Controllers\AdminController#elections', 'admin_elections');
// stats
$router->map('GET', '/admin/elections/[i:id]/results', 'App\Controllers\AdminController#results', 'admin_election_results');
$router->map('GET', '/admin/elections/[i:id]/export/pdf', 'App\Controllers\AdminController#exportPdf', 'admin_election_export_pdf');
$router->map('GET', '/admin/elections/[i:id]/export/csv', 'App\Controllers\AdminController#exportCsv', 'admin_election_export_csv');
$router->map('GET', '/admin/elections/create', 'App\Controllers\AdminController#createElection', 'admin_elections_create');
$router->map('POST', '/admin/elections/store', 'App\Controllers\AdminController#storeElection', 'admin_elections_store');
$router->map('GET', '/admin/elections/view/[i:id]', 'App\Controllers\AdminController#viewElection', 'admin_elections_view');
$router->map('GET', '/admin/elections/edit/[i:id]', 'App\Controllers\AdminController#editElection', 'admin_elections_edit');
$router->map('POST', '/admin/elections/update/[i:id]', 'App\Controllers\AdminController#updateElection', 'admin_elections_update');
$router->map('POST', '/admin/elections/delete/[i:id]', 'App\Controllers\AdminController#deleteElection', 'admin_elections_delete');
$router->map('GET|POST', '/admin/elections/status/[i:id]/[a:status]', 'App\Controllers\AdminController#changeElectionStatus', 'admin_elections_status');
$router->map('GET', '/admin/elections/[i:id]/add-candidate', 'App\Controllers\AdminController#addCandidate', 'admin_add_candidate');
$router->map('POST', '/admin/elections/[i:id]/store-candidate', 'App\Controllers\AdminController#storeCandidate', 'admin_store_candidate');
$router->map('POST', '/admin/elections/[i:election_id]/candidate/[i:candidate_id]/delete', 'App\Controllers\AdminController#deleteCandidate', 'admin_delete_candidate');
// Admin - Organizations
$router->map('GET', '/admin/organizations', 'App\Controllers\AdminController#organizations', 'admin_organizations');
$router->map('GET', '/admin/organizations/create', 'App\Controllers\AdminController#createOrganization', 'admin_organizations_create');
$router->map('POST', '/admin/organizations/store', 'App\Controllers\AdminController#storeOrganization', 'admin_organizations_store');
$router->map('GET', '/admin/organizations/[i:id]', 'App\Controllers\AdminController#viewOrganization', 'admin_organizations_view');

// Admin - Whitelist
$router->map('POST', '/admin/organizations/[i:id]/import-voters', 'App\Controllers\AdminController#importVoters', 'admin_import_voters');

// Admin - Security
$router->map('GET', '/admin/security/logs', 'App\Controllers\AdminController#securityLogs', 'admin_security_logs');
$router->map('GET', '/admin/security/suspicious', 'App\Controllers\AdminController#suspiciousAccounts', 'admin_suspicious_accounts');

// Admin - Settings (Branding)
$router->map('GET', '/admin/settings', 'App\Controllers\AdminController#settings', 'admin_settings');
$router->map('POST', '/admin/settings/update', 'App\Controllers\AdminController#updateSettings', 'admin_settings_update');

// Match
$match = $router->match();

if ($match && is_callable($match['target'])) {
    call_user_func_array($match['target'], $match['params']);
} elseif ($match) {
    // Target is Class#Method string
    list($controllerName, $method) = explode('#', $match['target']);
    
    if (class_exists($controllerName)) {
        $controller = new $controllerName();
        if (method_exists($controller, $method)) {
            call_user_func_array([$controller, $method], $match['params']);
        } else {
            // Method not found
            http_response_code(404);
            view('errors.404', ['message' => 'Méthode introuvable']);
        }
    } else {
        // Class not found
        http_response_code(404);
        view('errors.404', ['message' => 'Contrôleur introuvable']);
    }
} else {
    // Route not found
    http_response_code(404);
    
    if (APP_DEBUG) {
        echo "<h1>404 Not Found</h1>";
        echo "<p>Aucune route ne correspond à l'URL demandée.</p>";
        echo "<h3>Debug Info:</h3>";
        echo "<ul>";
        echo "<li><strong>URL demandée:</strong> " . $_SERVER['REQUEST_URI'] . "</li>";
        echo "<li><strong>Base Path détecté:</strong> " . $basePath . "</li>";
        echo "<li><strong>Target Path (calculé):</strong> " . str_replace($basePath, '', $_SERVER['REQUEST_URI']) . "</li>";
        echo "<li><strong>Méthode:</strong> " . $_SERVER['REQUEST_METHOD'] . "</li>";
        echo "</ul>";
        echo "<p>Vérifiez que vos routes sont correctement définies dans public/index.php</p>";
    } else {
        view('errors.404');
    }
}