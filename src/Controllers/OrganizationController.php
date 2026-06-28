<?php
namespace App\Controllers;

use App\Models\Organization;
use App\Models\User;
use App\Services\EmailService;
use App\Helpers\SecurityHelper;

/**
 * OrganizationController - Gestion des organisations (Publique)
 */
class OrganizationController {
    private $orgModel;
    private $userModel;
    private $emailService;

    public function __construct() {
        $this->orgModel = new Organization();
        $this->userModel = new User();
        $this->emailService = new EmailService();
    }

    /**
     * Formulaire de création d'organisation
     */
    public function create() {
        if (isset($_SESSION['logged_in']) && $_SESSION['logged_in'] === true) {
            // Si admin, dashboard. Si voter, dashboard.
             redirect($_SESSION['user']['role'] === 'admin' ? '/admin/dashboard' : '/voter/dashboard');
             return;
        }

        view('portal.create_org', [
            'title' => 'Créer votre espace de vote'
        ]);
    }

    /**
     * Traiter la création
     */
    public function store() {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            redirect('/create-organization');
            return;
        }

        if (!verifyCsrfToken($_POST['csrf_token'] ?? '')) {
            redirect('/create-organization', 'Token de sécurité invalide', 'danger');
            return;
        }

        // 1. Données Organisation
        $orgName = SecurityHelper::sanitize($_POST['org_name'] ?? '');
        $orgType = $_POST['org_type'] ?? 'other';
        $orgCode = strtoupper(preg_replace('/[^a-zA-Z0-9]/', '', $_POST['org_code'] ?? ''));

        // 2. Données Admin
        $firstName = SecurityHelper::sanitize($_POST['first_name'] ?? '');
        $lastName = SecurityHelper::sanitize($_POST['last_name'] ?? '');
        $email = SecurityHelper::sanitize($_POST['email'] ?? '');
        $password = $_POST['password'] ?? '';
        $confirm = $_POST['password_confirm'] ?? '';

        // Validation
        $errors = [];
        if (empty($orgName) || empty($orgCode)) $errors[] = "Nom et code de l'organisation requis";
        if (empty($firstName) || empty($lastName) || empty($email)) $errors[] = "Vos informations personnelles sont requises";
        if (strlen($password) < 8) $errors[] = "Mot de passe trop court";
        if ($password !== $confirm) $errors[] = "Les mots de passe ne correspondent pas";
        
        // Vérifier unicité Org Code
        if ($this->orgModel->findByCode($orgCode)) {
            $errors[] = "Ce code d'organisation est déjà pris";
        }
        
        // Vérifier unicité Email Admin
        if ($this->userModel->emailExists($email)) {
             $errors[] = "Cet email est déjà utilisé pour un autre compte";
        }

        if (!empty($errors)) {
            $_SESSION['create_org_errors'] = $errors;
            $_SESSION['create_org_data'] = $_POST;
            redirect('/create-organization', implode('<br>', $errors), 'danger');
            return;
        }

        // Générer Slug
        $slug = strtolower(trim(preg_replace('/[^A-Za-z0-9-]+/', '-', $orgName)));
        if ($this->orgModel->findBySlug($slug)) {
            $slug .= '-' . time();
        }

        // CRÉATION TRANSACTIONNELLE (Simulée car pas de transaction cross-model simple ici, on fait step by step avec cleanup si fail)
        // TODO: Ajouter support transaction DB dans le futur
        
        // 1. Créer Org
        $orgId = $this->orgModel->create([
            'name' => $orgName,
            'code' => $orgCode,
            'type' => $orgType,
            'slug' => $slug
        ]);

        if (!$orgId) {
            redirect('/create-organization', "Erreur lors de la création de l'organisation", 'danger');
            return;
        }

        // 2. Créer Admin User
        $userId = $this->userModel->create([
            'first_name' => $firstName,
            'last_name' => $lastName,
            'email' => $email,
            'password' => $password,
            'role' => 'admin', // ROLE ADMIN
            'is_verified' => false,
            'organization_id' => $orgId, // LIÉ À L'ORG
            'identifier_code' => 'ADMIN-' . time() // Fake ID pour admin
        ]);

        if (!$userId) {
            // Rollback Org (Manually)
            $this->orgModel->delete($orgId);
            redirect('/create-organization', "Erreur lors de la création du compte admin", 'danger');
            return;
        }

        // 3. Envoyer Email Validation
        $token = $this->emailService->generateVerificationToken();
        $expiresAt = date('Y-m-d H:i:s', strtotime('+24 hours'));
        
        $this->userModel->update($userId, [
            'verification_token' => $token,
            'verification_expires_at' => $expiresAt
        ]);
        
        $userName = $firstName . ' ' . $lastName;
        $this->emailService->sendVerificationEmail($email, $token, $userName);

        logMessage("Nouvelle Organisation créée : $orgName ($orgCode) par $email");

        redirect('/auth/login', "Organisation créée avec succès ! Veuillez vérifier votre email pour activer le compte administrateur.", 'success');
    }
}
