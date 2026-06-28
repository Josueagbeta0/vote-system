<?php
namespace App\Controllers;

use App\Models\User;
use App\Models\Election;
use App\Models\Candidate;
use App\Models\Vote;
use App\Models\Voter;
use App\Helpers\SecurityHelper;
use App\Services\FraudDetectionService;
use App\Config\Database;

/**
 * AdminController - Espace administrateur
 */

class AdminController {
    private $userModel;
    private $electionModel;
    private $candidateModel;
    private $voteModel;
    private $voterModel;
    private $organizationModel;
    private $eligibleVoterModel;
    
    public function __construct() {
        // Vérifier que l'utilisateur est connecté
        if (!isset($_SESSION['logged_in']) || $_SESSION['logged_in'] !== true) {
            redirect('/auth/login', 'Veuillez vous connecter', 'warning');
            exit();
        }
        
        // Vérifier que c'est bien un admin
        if ($_SESSION['user']['role'] !== 'admin') {
            redirect('/voter/dashboard', 'Accès non autorisé', 'danger');
            exit();
        }
        
        $this->userModel = new User();
        $this->electionModel = new Election();
        $this->candidateModel = new Candidate();
        $this->voteModel = new Vote();
        $this->voterModel = new Voter();
        $this->organizationModel = new \App\Models\Organization();
        $this->eligibleVoterModel = new \App\Models\EligibleVoter();
    }
    
    /**
     * Dashboard principal
     */
    public function dashboard() {
        // Statistiques globales
        $stats = [
            'total_users' => $this->userModel->count(),
            'total_voters' => $this->userModel->count('voter'),
            'total_elections' => $this->electionModel->count(),
            'active_elections' => $this->electionModel->count('active'),
            'closed_elections' => $this->electionModel->count('closed')
        ];
        
        // Dernières élections
        $recentElections = $this->electionModel->getAll();
        $recentElections = array_slice($recentElections, 0, 5);
        
        // Activité récente (logs)
        $recentActivity = $this->getRecentActivity();

        // [ANALYTICS] Données pour les graphiques
        $analytics = [
            'votes_history' => $this->electionModel->getVotesOverTime(7),
            'turnout' => $this->electionModel->getGlobalTurnout()
        ];
        
        view('admin.dashboard', [
            'title' => 'Dashboard Admin',
            'stats' => $stats,
            'recentElections' => $recentElections,
            'recentActivity' => $recentActivity,
            'analytics' => $analytics
        ]);
    }
    
    /**
     * Gestion des utilisateurs
     */
    public function users() {
        $users = $this->userModel->getAllVoters(); // Ou une méthode getAll() plus générique si elle existe
        // Note: getAllVoters ne retourne que les électeurs, pour l'admin on veut peut-être tout le monde
        // On va utiliser getAllByRole pour l'instant ou créer une nouvelle méthode getAll dans User model si besoin.
        // Regardons User model... il a getAllByRole et getAllVoters. 
        // Modifions pour utiliser une requête custom ou getAllByRole('voter') pour l'instant.
        // Mieux: affichons tous les utilisateurs.
        
        $db = Database::getInstance();
        $users = $db->query("SELECT * FROM users ORDER BY created_at DESC");

        view('admin.users.index', [
            'title' => 'Gestion des Utilisateurs',
            'users' => $users
        ]);
    }

    /**
     * Liste des élections
     */
    public function elections() {
        $elections = $this->electionModel->getAll();
        
        view('admin.elections.index', [
            'title' => 'Gestion des Élections',
            'elections' => $elections
        ]);
    }
    
    /**
     * Créer une élection (formulaire)
     */
    /**
     * Créer une élection (formulaire)
     */
    public function createElection() {
        $organizations = $this->organizationModel->getAll();
        
        view('admin.elections.create', [
            'title' => 'Créer une Élection',
            'organizations' => $organizations
        ]);
    }
    
    /**
     * Enregistrer une élection
     */
    public function storeElection() {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            redirect('/admin/elections');
            return;
        }
        
        // Vérifier CSRF
        if (!verifyCsrfToken($_POST['csrf_token'] ?? '')) {
            redirect('/admin/elections/create', 'Token de sécurité invalide', 'danger');
            return;
        }
        
        $data = [
            'title' => SecurityHelper::sanitize($_POST['title'] ?? ''),
            'description' => SecurityHelper::sanitize($_POST['description'] ?? ''),
            'start_date' => $_POST['start_date'] ?? '',
            'end_date' => $_POST['end_date'] ?? '',
            'organization_id' => !empty($_POST['organization_id']) ? (int)$_POST['organization_id'] : null,
            'created_by' => $_SESSION['user']['id']
        ];
        
        // Validation
        $errors = [];
        
        if (empty($data['title'])) {
            $errors[] = 'Le titre est requis';
        }
        
        if (empty($data['start_date']) || empty($data['end_date'])) {
            $errors[] = 'Les dates sont requises';
        }
        
        // Si aucun organistion n'est sélectionnée, on force l'organisation par défaut ou on oblige ?
        // On oblige pour l'instant
        if (empty($data['organization_id'])) {
             // Si l'admin n'a pas sélectionné (par ex: champ invisible ou oublié), on regarde si user a un org_id
             // Mais admin est "Super Admin", donc il doit choisir.
             $errors[] = 'Veuillez sélectionner un établissement pour cette élection';
        }
        
        if (strtotime($data['end_date']) <= strtotime($data['start_date'])) {
            $errors[] = 'La date de fin doit être après la date de début';
        }
        
        if (!empty($errors)) {
            $_SESSION['errors'] = $errors;
            redirect('/admin/elections/create', implode('<br>', $errors), 'danger');
            return;
        }
        
        $electionId = $this->electionModel->create($data);
        
        if ($electionId) {
            redirect('/admin/elections/view/' . $electionId, 'Élection créée avec succès', 'success');
        } else {
            redirect('/admin/elections/create', 'Erreur lors de la création', 'danger');
        }
    }
    
    /**
     * Voir une élection
     */
    public function viewElection($id) {
        $election = $this->electionModel->findById($id);
        
        if (!$election) {
            redirect('/admin/elections', 'Élection introuvable', 'danger');
            return;
        }
        
        $candidates = $this->candidateModel->getByElection($id);
        $voters = $this->voterModel->getByElection($id);
        $stats = $this->electionModel->getStats($id);
        
        view('admin.elections.view', [
            'title' => $election['title'],
            'election' => $election,
            'candidates' => $candidates,
            'voters' => $voters,
            'stats' => $stats
        ]);
    }
    
    /**
     * Éditer une élection (formulaire)
     */
    public function editElection($id) {
        $election = $this->electionModel->findById($id);
        
        if (!$election) {
            redirect('/admin/elections', 'Élection introuvable', 'danger');
            return;
        }
        
        view('admin.elections.edit', [
            'title' => 'Éditer - ' . $election['title'],
            'election' => $election
        ]);
    }
    
    /**
     * Mettre à jour une élection
     */
    public function updateElection($id) {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            redirect('/admin/elections');
            return;
        }
        
        if (!verifyCsrfToken($_POST['csrf_token'] ?? '')) {
            redirect('/admin/elections/edit/' . $id, 'Token invalide', 'danger');
            return;
        }
        
        $data = [
            'title' => SecurityHelper::sanitize($_POST['title'] ?? ''),
            'description' => SecurityHelper::sanitize($_POST['description'] ?? ''),
            'start_date' => $_POST['start_date'] ?? '',
            'end_date' => $_POST['end_date'] ?? ''
        ];
        
        if ($this->electionModel->update($id, $data)) {
            redirect('/admin/elections/view/' . $id, 'Élection mise à jour', 'success');
        } else {
            redirect('/admin/elections/edit/' . $id, 'Erreur de mise à jour', 'danger');
        }
    }
    
    /**
     * Supprimer une élection
     */
    public function deleteElection($id) {
        if ($this->electionModel->delete($id)) {
            redirect('/admin/elections', 'Élection supprimée', 'success');
        } else {
            redirect('/admin/elections', 'Erreur de suppression', 'danger');
        }
    }
    
    /**
     * Changer le statut d'une élection
     */
    public function changeElectionStatus($id, $status) {
        $validStatuses = ['draft', 'active', 'closed', 'archived'];
        
        if (!in_array($status, $validStatuses)) {
            redirect('/admin/elections', 'Statut invalide', 'danger');
            return;
        }
        
        if ($this->electionModel->changeStatus($id, $status)) {
            redirect('/admin/elections/view/' . $id, "Élection $status", 'success');
        } else {
            redirect('/admin/elections/view/' . $id, 'Erreur', 'danger');
        }
    }
    
    /**
     * Ajouter un candidat (formulaire)
     */
    public function addCandidate($electionId) {
        $election = $this->electionModel->findById($electionId);
        
        if (!$election) {
            redirect('/admin/elections', 'Élection introuvable', 'danger');
            return;
        }
        
        view('admin.candidates.create', [
            'title' => 'Ajouter un Candidat',
            'election' => $election
        ]);
    }
    
    /**
     * Enregistrer un candidat
     */
    public function storeCandidate($electionId) {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            redirect('/admin/elections/view/' . $electionId);
            return;
        }
        
        if (!verifyCsrfToken($_POST['csrf_token'] ?? '')) {
            redirect('/admin/elections/' . $electionId . '/add-candidate', 'Token invalide', 'danger');
            return;
        }
        
        $data = [
            'election_id' => $electionId,
            'name' => SecurityHelper::sanitize($_POST['name'] ?? ''),
            'description' => SecurityHelper::sanitize($_POST['description'] ?? ''),
            'position' => (int)($_POST['position'] ?? 0)
        ];
        
        // Upload de photo
        if (isset($_FILES['photo']) && $_FILES['photo']['error'] === UPLOAD_ERR_OK) {
            $validation = SecurityHelper::validateUploadedImage($_FILES['photo']);
            
            if ($validation['valid']) {
                $filename = SecurityHelper::generateSecureFilename($_FILES['photo']['name']);
                $uploadPath = UPLOAD_PATH . '/candidates/' . $filename;
                
                if (move_uploaded_file($_FILES['photo']['tmp_name'], $uploadPath)) {
                    $data['photo_url'] = '/uploads/candidates/' . $filename;
                }
            }
        }
        
        $candidateId = $this->candidateModel->create($data);
        
        if ($candidateId) {
            redirect('/admin/elections/view/' . $electionId, 'Candidat ajouté', 'success');
        } else {
            redirect('/admin/elections/' . $electionId . '/add-candidate', 'Erreur', 'danger');
        }
    }
    
    /**
     * Supprimer un candidat
     */
    public function deleteCandidate($electionId, $candidateId) {
        if ($this->candidateModel->delete($candidateId)) {
            redirect('/admin/elections/view/' . $electionId, 'Candidat supprimé', 'success');
        } else {
            redirect('/admin/elections/view/' . $electionId, 'Erreur', 'danger');
        }
    }
    
    /**
     * Résultats d'une élection
     */
    public function results($electionId) {
        $election = $this->electionModel->findById($electionId);
        
        if (!$election) {
            redirect('/admin/elections', 'Élection introuvable', 'danger');
            return;
        }
        
        $results = $this->electionModel->getResults($electionId);
        $stats = $this->electionModel->getStats($electionId);
        
        view('admin.elections.results', [
            'title' => 'Résultats - ' . $election['title'],
            'election' => $election,
            'results' => $results,
            'stats' => $stats
        ]);
    }
    
    /**
     * Logs de sécurité
     */
    public function securityLogs() {
        $db = Database::getInstance();
        
        $query = "SELECT sl.*, u.email
                  FROM security_logs sl
                  LEFT JOIN users u ON sl.user_id = u.id
                  ORDER BY sl.created_at DESC
                  LIMIT 100";
        
        $logs = $db->query($query);
        
        view('admin.security.logs', [
            'title' => 'Logs de Sécurité',
            'logs' => $logs
        ]);
    }
    
    /**
     * Comptes suspects
     */
    public function suspiciousAccounts() {
        $fraudDetection = new FraudDetectionService();
        $suspicious = $fraudDetection->getSuspiciousAccounts();
        
        view('admin.security.suspicious', [
            'title' => 'Comptes Suspects',
            'accounts' => $suspicious
        ]);
    }
    
    /**
     * Liste des organisations
     */
    public function organizations() {
        $organizations = $this->organizationModel->getAll();
        
        view('admin.organizations.index', [
            'title' => 'Gestion des Établissements',
            'organizations' => $organizations
        ]);
    }

    /**
     * Créer une organisation
     */
    public function createOrganization() {
        view('admin.organizations.create', [
            'title' => 'Ajouter un Établissement'
        ]);
    }

    /**
     * Enregistrer une organisation
     */
    public function storeOrganization() {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') return;
        
        if (!verifyCsrfToken($_POST['csrf_token'] ?? '')) {
            redirect('/admin/organizations/create', 'Token invalide', 'danger');
            return;
        }

        $data = [
            'name' => SecurityHelper::sanitize($_POST['name'] ?? ''),
            'code' => SecurityHelper::sanitize($_POST['code'] ?? '')
        ];

        if (empty($data['name']) || empty($data['code'])) {
            redirect('/admin/organizations/create', 'Tous les champs sont requis', 'danger');
            return;
        }

        // Vérifier unicité du code
        if ($this->organizationModel->findByCode($data['code'])) {
            redirect('/admin/organizations/create', 'Ce code établissement existe déjà', 'danger');
            return;
        }

        if ($this->organizationModel->create($data)) {
            redirect('/admin/organizations', 'Établissement créé avec succès', 'success');
        } else {
            redirect('/admin/organizations/create', 'Erreur lors de la création', 'danger');
        }
    }

    /**
     * Voir une organisation et ses électeurs éligibles
     */
    public function viewOrganization($id) {
        $organization = $this->organizationModel->findById($id);
        if (!$organization) {
            redirect('/admin/organizations', 'Introuvable', 'danger');
            return;
        }

        $eligibleVoters = $this->eligibleVoterModel->getByOrganization($id);

        view('admin.organizations.view', [
            'title' => $organization['name'],
            'organization' => $organization,
            'eligibleVoters' => $eligibleVoters
        ]);
    }

    /**
     * Importer des électeurs (CSV)
     */
    public function importVoters($orgId) {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') return;

        if (!isset($_FILES['voters_file']) || $_FILES['voters_file']['error'] !== UPLOAD_ERR_OK) {
            redirect('/admin/organizations/' . $orgId, 'Veuillez sélectionner un fichier CSV valide', 'danger');
            return;
        }

        $file = $_FILES['voters_file']['tmp_name'];
        $handle = fopen($file, "r");
        
        if ($handle === FALSE) {
            redirect('/admin/organizations/' . $orgId, 'Erreur lecture fichier', 'danger');
            return;
        }

        $count = 0;
        $errors = 0;

        // Skip header row if exists (Simple check: is first column 'identifier' or similar?)
        // Assuming format: Identifier, FirstName, LastName, Email
        
        while (($data = fgetcsv($handle, 1000, ",")) !== FALSE) {
            // Skip empty rows or header
            if (empty($data[0]) || strtolower($data[0]) == 'identifiant') continue;

            $voterData = [
                'organization_id' => $orgId,
                'identifier_code' => trim($data[0]),
                'first_name' => trim($data[1] ?? ''),
                'last_name' => trim($data[2] ?? ''),
                'email' => isset($data[3]) ? trim($data[3]) : null
            ];

            if ($this->eligibleVoterModel->create($voterData)) {
                $count++;
            } else {
                $errors++;
            }
        }
        
        fclose($handle);
        
        $msg = "Import terminé : $count électeurs ajoutés.";
        if ($errors > 0) $msg .= " ($errors doublons ignorés)";
        
        redirect('/admin/organizations/' . $orgId, $msg, 'success');
    }

    /**
     * Exporter en PDF
     */
    public function exportPdf($electionId) {
        $election = $this->electionModel->findById($electionId);
        
        if (!$election) {
            redirect('/admin/elections', 'Introuvable', 'danger');
            return;
        }

        $results = $this->electionModel->getResults($electionId);
        $stats = $this->electionModel->getStats($electionId);
        
        // Récupérer le nom de l'orga si besoin (déjà fait dans findById si join faite, sinon on peut l'ajouter)
        // Check findById: fait un LEFT JOIN users u (created_by). 
        // On n'a pas le nom de l'organisation dans findById actuellement, mais on a l'ID.
        // On va le charger.
        if (!empty($election['organization_id'])) {
            $org = $this->organizationModel->findById($election['organization_id']);
            $election['organization_name'] = $org['name'];
        } else {
            $election['organization_name'] = 'Global';
        }

        $exporter = new \App\Services\ExportService();
        $exporter->generateResultsPDF($election, $results, $stats);
    }

    /**
     * Exporter en CSV (Excel)
     */
    public function exportCsv($electionId) {
        $election = $this->electionModel->findById($electionId);
        
        if (!$election) {
            redirect('/admin/elections', 'Introuvable', 'danger');
            return;
        }

        $results = $this->electionModel->getResults($electionId);
        $stats = $this->electionModel->getStats($electionId);

        $exporter = new \App\Services\ExportService();
        $exporter->generateResultsCSV($election, $results, $stats);
    }

    /**
     * Récupérer l'activité récente
     */
    private function getRecentActivity() {
        $db = Database::getInstance();
        
        // Check if table exists (in case user didn't run migration)
        // But we have security_logs now.
        // Let's use security_logs instead of audit_logs if audit doesn't exist?
        // Wait, dashboard view calls getRecentActivity.
        // Let's just fix the query to use security_logs as we just created it.
        // Or keep audit_logs if it was there before. The migration I made was security_logs.
        // Let's use security_logs for recent activity.
        
        $query = "SELECT activity_type as action, created_at as timestamp 
                  FROM security_logs 
                  ORDER BY created_at DESC 
                  LIMIT 10";
        
        try {
            return $db->query($query);
        } catch (\Exception $e) {
            return [];
        }
    }
}