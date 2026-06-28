<?php
namespace App\Controllers;

use App\Models\User;
use App\Models\Organization;
use App\Services\EmailService;
use App\Helpers\SecurityHelper;

/**
 * AuthController - Gestion de l'authentification
 */

class AuthController {
    private $userModel;
    private $emailService;
    private $orgModel;
    
    public function __construct() {
        $this->userModel = new User();
        $this->emailService = new EmailService();
        $this->orgModel = new Organization();
    }
    
    /**
     * Afficher la page de connexion
     */
    public function login() {
        // Si déjà connecté, rediriger vers le dashboard
        if ($this->isLoggedIn()) {
            $this->redirectToDashboard();
            return;
        }
        
        $org = null;
        if (isset($_GET['org'])) {
            $slug = SecurityHelper::sanitize($_GET['org']);
            $org = $this->orgModel->findBySlug($slug);
        }
        
        view('auth.login', [
            'title' => 'Connexion' . ($org ? ' - ' . $org['name'] : ''),
            'org' => $org
        ]);
    }
    
    /**
     * Traiter la connexion
     */
    public function processLogin() {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            redirect('/auth/login');
            return;
        }
        
        // Vérifier le token CSRF
        if (!verifyCsrfToken($_POST['csrf_token'] ?? '')) {
            redirect('/auth/login', 'Token de sécurité invalide', 'danger');
            return;
        }

        // Vérifier ReCaptcha
        if (!SecurityHelper::verifyRecaptcha($_POST['recaptcha_token'] ?? '')) {
            redirect('/auth/login', 'Vérification de sécurité échouée (spam détecté)', 'danger');
            return;
        }
        
        // Récupérer et nettoyer les données
        $email = SecurityHelper::sanitize($_POST['email'] ?? '');
        $password = $_POST['password'] ?? '';
        
        // Validation
        if (empty($email) || empty($password)) {
            redirect('/auth/login', 'Veuillez remplir tous les champs', 'danger');
            return;
        }
        
        if (!SecurityHelper::validateEmail($email)) {
            redirect('/auth/login', 'Email invalide', 'danger');
            return;
        }
        
        // Vérifier les tentatives de connexion (Rate Limiting DB-Based)
        $limitCheck = SecurityHelper::checkRateLimit('login_attempt', 5, 300); // 5 essais / 5 mins
        if (!$limitCheck['allowed']) {
            redirect('/auth/login', $limitCheck['message'], 'danger');
            return;
        }
        
        // Vérifier les identifiants
        $user = $this->userModel->verifyCredentials($email, $password);
        
        if (!$user) {
            SecurityHelper::recordActivity('login_attempt', ['email' => $email, 'status' => 'failed']);
            SecurityHelper::recordFailedLogin($email); // Legacy session check (double sécurité)
            SecurityHelper::logSuspiciousActivity('failed_login', ['email' => $email]);
            
            redirect('/auth/login', 'Email ou mot de passe incorrect', 'danger');
            return;
        }
        
        // Vérifier si le compte est vérifié
        if (!$user['is_verified']) {
            redirect('/auth/login', 'Veuillez vérifier votre email avant de vous connecter', 'warning');
            return;
        }
        
        // Connexion réussie
        SecurityHelper::resetLoginAttempts($email);
        $this->createSession($user);
        
        logMessage("Connexion réussie : {$user['email']}");
        
        // Rediriger selon le rôle
        $this->redirectToDashboard();
    }
    
    /**
     * Afficher la page d'inscription
     */
    public function register() {
        if ($this->isLoggedIn()) {
            $this->redirectToDashboard();
            return;
        }
        
        $org = null;
        if (isset($_GET['org'])) {
            $slug = SecurityHelper::sanitize($_GET['org']);
            $org = $this->orgModel->findBySlug($slug);
        }
        
        view('auth.register', [
            'title' => 'Inscription' . ($org ? ' - ' . $org['name'] : ''),
            'org' => $org
        ]);
    }
    
    /**
     * Traiter l'inscription
     */
    public function processRegister() {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            redirect('/auth/register');
            return;
        }
        
        // Vérifier le token CSRF
        if (!verifyCsrfToken($_POST['csrf_token'] ?? '')) {
            redirect('/auth/register', 'Token de sécurité invalide', 'danger');
            return;
        }

        // Vérifier ReCaptcha
        if (!SecurityHelper::verifyRecaptcha($_POST['recaptcha_token'] ?? '')) {
            redirect('/auth/register', 'Vérification de sécurité échouée (spam détecté)', 'danger');
            return;
        }

        // Rate Limiting (Inscription)
        $limitCheck = SecurityHelper::checkRateLimit('register_attempt', 3, 600); // 3 essais / 10 mins
        if (!$limitCheck['allowed']) {
            redirect('/auth/register', $limitCheck['message'], 'danger');
            return;
        }
        
        // Récupérer et nettoyer les données
        $data = [
            'first_name' => SecurityHelper::sanitize($_POST['first_name'] ?? ''),
            'last_name' => SecurityHelper::sanitize($_POST['last_name'] ?? ''),
            'email' => SecurityHelper::sanitize($_POST['email'] ?? ''),
            'password' => $_POST['password'] ?? '',
            'password_confirm' => $_POST['password_confirm'] ?? '',
            'phone_number' => SecurityHelper::sanitize($_POST['phone_number'] ?? ''),
            'national_id' => SecurityHelper::sanitize($_POST['national_id'] ?? ''),
            'browser_fingerprint' => $_POST['browser_fingerprint'] ?? '',
            'device_fingerprint' => $_POST['device_fingerprint'] ?? '',
            'ip_address' => $_SERVER['REMOTE_ADDR'] ?? 'unknown'
        ];
        
        // Validation
        $errors = [];
        
        if (empty($data['first_name'])) {
            $errors[] = 'Le prénom est requis';
        }
        
        if (empty($data['last_name'])) {
            $errors[] = 'Le nom est requis';
        }
        
        if (empty($data['email']) || !SecurityHelper::validateEmail($data['email'])) {
            $errors[] = 'Email invalide';
        }
        
        // Vérifier si l'email existe déjà
        if ($this->userModel->emailExists($data['email'])) {
            $errors[] = 'Cet email est déjà utilisé';
        }
        
        // Valider le mot de passe
        $passwordValidation = SecurityHelper::validatePassword($data['password']);
        if (!$passwordValidation['valid']) {
            $errors = array_merge($errors, $passwordValidation['errors']);
        }
        
        // Vérifier la confirmation
        if ($data['password'] !== $data['password_confirm']) {
            $errors[] = 'Les mots de passe ne correspondent pas';
        }
        
        // Si erreurs, retourner
        if (!empty($errors)) {
            SecurityHelper::recordActivity('register_attempt', ['email' => $data['email'], 'errors' => $errors]);
            $_SESSION['register_errors'] = $errors;
            $_SESSION['register_data'] = $data;
            redirect('/auth/register', implode('<br>', $errors), 'danger');
            return;
        }
        
        // >>> NOUVELLE LOGIQUE DE SÉCURITÉ (SaaS) <<<
        
        $orgSlug = SecurityHelper::sanitize($_POST['org_slug'] ?? '');
        $orgCode = SecurityHelper::sanitize($_POST['org_code'] ?? '');
        $identifier = SecurityHelper::sanitize($_POST['identifier'] ?? '');
        
        if (empty($identifier)) {
             redirect('/auth/register', 'Le matricule est obligatoire.', 'danger');
             return;
        }

        $orgModel = new \App\Models\Organization();
        $organization = null;

        // 1. Trouver l'établissement
        if (!empty($orgSlug)) {
            $organization = $orgModel->findBySlug($orgSlug);
        } elseif (!empty($orgCode)) {
            $organization = $orgModel->findByCode($orgCode);
        }

        if (!$organization) {
            redirect('/auth/register', 'Établissement introuvable (Code ou lien invalide).', 'danger');
            return;
        }

        // Vérification du code si slug non utilisé (doublon de sécurité)
        if (empty($orgSlug) && empty($orgCode)) {
             redirect('/auth/register', 'Code établissement requis.', 'danger');
             return;
        }

        // 2. Vérifier la liste blanche
        $eligibleVoterModel = new \App\Models\EligibleVoter();
        $whitelistRecord = $eligibleVoterModel->findByCode($organization['id'], $identifier);
        
        if (!$whitelistRecord) {
            redirect('/auth/register', 'Ce matricule n\'est pas autorisé pour cet établissement.', 'danger');
            return;
        }
        
        // 3. Vérifier si déjà inscrit
        if ($whitelistRecord['is_registered']) {
            redirect('/auth/register', 'Ce matricule a déjà été utilisé pour créer un compte.', 'danger');
            return;
        }

        // Créer l'utilisateur (non vérifié par défaut)
        $userId = $this->userModel->create([
            'first_name' => $data['first_name'],
            'last_name' => $data['last_name'],
            'email' => $data['email'],
            'password' => $data['password'],
            'role' => 'voter',
            'is_verified' => false,
            'organization_id' => $organization['id'],
            'identifier_code' => $identifier
        ]);
        
        // Marquer comme inscrit dans la whitelist
        if ($userId) {
            $eligibleVoterModel->markAsRegistered($whitelistRecord['id'], $userId);

            // Générer token et envoyer email
            $token = $this->emailService->generateVerificationToken();
            $expiresAt = date('Y-m-d H:i:s', strtotime('+24 hours'));
            
            $this->userModel->update($userId, [
                'verification_token' => $token,
                'verification_expires_at' => $expiresAt
            ]);
            
            // Envoyer l'email via la queue
            $userName = $data['first_name'] . ' ' . $data['last_name'];
            $this->emailService->sendVerificationEmail($data['email'], $token, $userName);
            
            logMessage("Nouvel utilisateur inscrit : {$data['email']} (En attente de vérification)");
            redirect('/auth/login', 'Inscription réussie ! Veuillez consulter votre email pour activer votre compte.', 'success');
        } else {
            redirect('/auth/register', 'Erreur lors de l\'inscription', 'danger');
        }
    }
    
    /**
     * Déconnexion
     */
    public function logout() {
        if (isset($_SESSION['user'])) {
            logMessage("Déconnexion : {$_SESSION['user']['email']}");
        }
        
        // Détruire la session
        session_unset();
        session_destroy();
        
        redirect('/auth/login', 'Vous êtes maintenant déconnecté', 'info');
    }
    
    /**
     * Page mot de passe oublié
     */
    public function forgotPassword() {
        view('auth.forgot-password', [
            'title' => 'Mot de passe oublié'
        ]);
    }
    
    /**
     * Traiter la demande de réinitialisation
     */
    public function processForgotPassword() {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            redirect('/auth/forgot-password');
            return;
        }
        
        if (!verifyCsrfToken($_POST['csrf_token'] ?? '')) {
            redirect('/auth/forgot-password', 'Token de sécurité invalide', 'danger');
            return;
        }

        // Rate Limiting (Forgot Pwd)
        $limitCheck = SecurityHelper::checkRateLimit('forgot_password_attempt', 3, 600);
        if (!$limitCheck['allowed']) {
            redirect('/auth/forgot-password', $limitCheck['message'], 'danger');
            return;
        }
        
        $email = SecurityHelper::sanitize($_POST['email'] ?? '');
        
        if (empty($email) || !SecurityHelper::validateEmail($email)) {
            redirect('/auth/forgot-password', 'Email invalide', 'danger');
            return;
        }
        
        // Générer un token
        $token = $this->userModel->generateResetToken($email);
        
        if ($token) {
            // En production, envoyer un email avec le lien de réinitialisation
            // Pour le développement, on affiche le token
            
            logMessage("Token de réinitialisation généré pour : $email");
            
            $_SESSION['reset_token'] = $token; // Pour le dev
            redirect('/auth/reset-password?token=' . $token, 'Un lien de réinitialisation a été envoyé', 'success');
        } else {
            // Ne pas révéler si l'email existe ou non (sécurité)
            redirect('/auth/forgot-password', 'Si cet email existe, vous recevrez un lien de réinitialisation', 'info');
        }
    }
    
    /**
     * Page de réinitialisation du mot de passe
     */
    public function resetPassword() {
        $token = $_GET['token'] ?? '';
        
        if (empty($token)) {
            redirect('/auth/forgot-password', 'Token invalide', 'danger');
            return;
        }
        
        // Vérifier le token
        $user = $this->userModel->verifyResetToken($token);
        
        if (!$user) {
            redirect('/auth/forgot-password', 'Token invalide ou expiré', 'danger');
            return;
        }
        
        view('auth.reset-password', [
            'title' => 'Réinitialiser le mot de passe',
            'token' => $token
        ]);
    }
    
    /**
     * Traiter la réinitialisation du mot de passe
     */
    public function processResetPassword() {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            redirect('/auth/forgot-password');
            return;
        }
        
        if (!verifyCsrfToken($_POST['csrf_token'] ?? '')) {
            redirect('/auth/forgot-password', 'Token de sécurité invalide', 'danger');
            return;
        }
        
        $token = $_POST['token'] ?? '';
        $password = $_POST['password'] ?? '';
        $passwordConfirm = $_POST['password_confirm'] ?? '';
        
        // Validation
        if (empty($password) || empty($passwordConfirm)) {
            redirect('/auth/reset-password?token=' . $token, 'Veuillez remplir tous les champs', 'danger');
            return;
        }
        
        if ($password !== $passwordConfirm) {
            redirect('/auth/reset-password?token=' . $token, 'Les mots de passe ne correspondent pas', 'danger');
            return;
        }
        
        $passwordValidation = SecurityHelper::validatePassword($password);
        if (!$passwordValidation['valid']) {
            redirect('/auth/reset-password?token=' . $token, implode('<br>', $passwordValidation['errors']), 'danger');
            return;
        }
        
        // Réinitialiser le mot de passe
        if ($this->userModel->resetPassword($token, $password)) {
            redirect('/auth/login', 'Mot de passe réinitialisé avec succès', 'success');
        } else {
            redirect('/auth/reset-password?token=' . $token, 'Erreur lors de la réinitialisation', 'danger');
        }
    }
    
    /**
     * Créer une session utilisateur
     */
    private function createSession($user) {
        $_SESSION['user'] = [
            'id' => $user['id'],
            'email' => $user['email'],
            'first_name' => $user['first_name'],
            'last_name' => $user['last_name'],
            'role' => $user['role']
        ];
        
        $_SESSION['logged_in'] = true;
    }
    
    /**
     * Vérifier l'email via le token
     */
    public function verifyEmail() {
        $token = $_GET['token'] ?? '';
        
        if (empty($token)) {
            redirect('/auth/login', 'Token de vérification manquant.', 'danger');
            return;
        }
        
        $user = $this->userModel->verifyEmailToken($token);
        
        if ($user) {
            // Marquer comme vérifié et invalider le token
            $this->userModel->update($user['id'], [
                'is_verified' => true,
                'verification_token' => null,
                'verification_expires_at' => null
            ]);
            
            logMessage("Compte vérifié pour : {$user['email']}");
            redirect('/auth/login', 'Votre compte a été vérifié avec succès ! Vous pouvez maintenant vous connecter.', 'success');
        } else {
            redirect('/auth/login', 'Lien de vérification invalide ou expiré.', 'danger');
        }
    }

    private function isLoggedIn() {
        return isset($_SESSION['logged_in']) && $_SESSION['logged_in'] === true;
    }
    
    /**
     * Rediriger vers le dashboard approprié
     */
    private function redirectToDashboard() {
        if (!isset($_SESSION['user'])) {
            redirect('/auth/login');
            return;
        }

        $role = $_SESSION['user']['role'];
        
        switch ($role) {
            case 'admin':
                redirect('/admin/dashboard');
                break;
            case 'voter':
                redirect('/voter/dashboard');
                break;
            default:
                redirect('/');
        }
    }

    // --- GOOGLE AUTH ---

    /**
     * Rediriger vers Google
     */
    public function loginWithGoogle() {
        if (!isset($_ENV['GOOGLE_CLIENT_ID']) || !isset($_ENV['GOOGLE_CLIENT_SECRET'])) {
            redirect('/auth/login', 'Configuration Google manquante (CLIENT_ID/SECRET)', 'danger');
            return;
        }

        $provider = new \League\OAuth2\Client\Provider\Google([
            'clientId'     => $_ENV['GOOGLE_CLIENT_ID'],
            'clientSecret' => $_ENV['GOOGLE_CLIENT_SECRET'],
            'redirectUri'  => BASE_URL . '/auth/callback/google',
        ]);

        $authUrl = $provider->getAuthorizationUrl([
            'scope' => ['email', 'profile']
        ]);
        
        $_SESSION['oauth2state'] = $provider->getState();
        
        // Redirection
        header('Location: ' . $authUrl);
        exit;
    }

    /**
     * Callback Google
     */
    public function callbackGoogle() {
        if (!isset($_GET['code'])) {
            redirect('/auth/login', 'Erreur authentification (code manquant)', 'danger');
            return;
        }

        if (empty($_GET['state']) || empty($_SESSION['oauth2state']) || ($_GET['state'] !== $_SESSION['oauth2state'])) {
            unset($_SESSION['oauth2state']);
            redirect('/auth/login', 'Session invalide. Réessayez.', 'danger');
            return;
        }

        try {
            $provider = new \League\OAuth2\Client\Provider\Google([
                'clientId'     => $_ENV['GOOGLE_CLIENT_ID'],
                'clientSecret' => $_ENV['GOOGLE_CLIENT_SECRET'],
                'redirectUri'  => BASE_URL . '/auth/callback/google',
            ]);

            // Obtenir le token
            $token = $provider->getAccessToken('authorization_code', [
                'code' => $_GET['code']
            ]);

            // Obtenir les infos utilisateur
            $googleUser = $provider->getResourceOwner($token);
            
            $email = $googleUser->getEmail();
            $googleId = $googleUser->getId();
            $firstName = $googleUser->getFirstName();
            $lastName = $googleUser->getLastName();
            $avatar = $googleUser->getAvatar();

            // 1. Chercher par fournisseur
            $user = $this->userModel->findByAuthProvider('google', $googleId);

            if (!$user) {
                // 2. Chercher par email (si compte existant)
                $user = $this->userModel->findByEmail($email);

                if ($user) {
                    // Update user
                    $db = \App\Config\Database::getInstance();
                    $db->execute("UPDATE users SET auth_provider = 'google', auth_provider_id = :gid, avatar_url = :av WHERE id = :uid", [
                        ':gid' => $googleId,
                        ':av' => $avatar,
                        ':uid' => $user['id']
                    ]);
                } else {
                    // 3. Créer nouveau compte
                    $userId = $this->userModel->create([
                        'first_name' => $firstName,
                        'last_name' => $lastName,
                        'email' => $email,
                        'password' => null, 
                        'role' => 'voter',
                        'is_verified' => true,
                        'auth_provider' => 'google',
                        'auth_provider_id' => $googleId,
                        'avatar_url' => $avatar,
                        'organization_id' => null 
                    ]);
                    
                    if (!$userId) {
                        throw new \Exception("Impossible de créer le compte local.");
                    }
                    
                    $user = $this->userModel->findById($userId);
                }
            }

            // Connecter
            $this->createSession($user);
            
            // Redirect based on role
            if ($user['role'] === 'admin') {
                redirect('/admin/dashboard');
            } else {
                redirect('/voter/dashboard');
            }

        } catch (\Exception $e) {
            redirect('/auth/login', 'Erreur Google: ' . $e->getMessage(), 'danger');
        }
    }
}