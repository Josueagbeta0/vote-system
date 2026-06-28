<?php
namespace App\Controllers;

use App\Models\Organization;

/**
 * PortalController - Point d'entrée public pour le SaaS
 */
class PortalController {
    private $orgModel;

    public function __construct() {
        $this->orgModel = new Organization();
    }

    /**
     * Page d'accueil "Hub"
     */
    public function index() {
        // Si déjà connecté, rediriger vers le dashboard approprié
        if (isset($_SESSION['logged_in']) && $_SESSION['logged_in'] === true) {
            $this->redirectToDashboard();
            return;
        }
        
        view('portal.index', [
            'title' => 'Portail de Vote - Choisissez votre espace'
        ]);
    }

    /**
     * Page de sélection pour une catégorie donnée (ex: Écoles)
     */
    public function selectCategory($type) {
        $validTypes = ['school', 'university', 'company', 'other'];
        
        if (!in_array($type, $validTypes)) {
            redirect('/', 'Catégorie invalide', 'danger');
            return;
        }
        
        $organizations = $this->orgModel->getByType($type);
        
        view('portal.select_org', [
            'title' => 'Choisir votre ' . $this->translateType($type),
            'type' => $type,
            'typeName' => $this->translateType($type),
            'organizations' => $organizations
        ]);
    }
    
    /**
     * Recherche AJAX d'organisations
     */
    public function search() {
        $query = $_GET['q'] ?? '';
        $type = $_GET['type'] ?? null;
        
        $results = $this->orgModel->search($query, $type);
        
        header('Content-Type: application/json');
        echo json_encode($results);
    }
    
    /**
     * Rediriger vers la page de login de l'organisation
     */
    public function accessOrganization($slug) {
        $org = $this->orgModel->findBySlug($slug);
        
        if (!$org) {
            redirect('/', 'Établissement introuvable', 'danger');
            return;
        }
        
        // Stocker l'organisation sélectionnée en session pour le contexte
        $_SESSION['context_org'] = $org;
        
        // Rediriger vers login avec paramètre
        redirect('/auth/login?org=' . $org['slug']);
    }

    private function translateType($type) {
        $map = [
            'school' => 'École',
            'university' => 'Université',
            'company' => 'Entreprise',
            'other' => 'Autre Organisation'
        ];
        return $map[$type] ?? 'Organisation';
    }

    private function redirectToDashboard() {
        if (!isset($_SESSION['user'])) return;
        
        $role = $_SESSION['user']['role'];
        switch ($role) {
            case 'admin': redirect('/admin/dashboard'); break;
            case 'voter': redirect('/voter/dashboard'); break;
            default: redirect('/');
        }
    }
}
