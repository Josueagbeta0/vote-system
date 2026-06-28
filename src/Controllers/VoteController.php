<?php
namespace App\Controllers;

use App\Models\Election;
use App\Models\Candidate;
use App\Models\Voter;
use App\Models\Vote;
use App\Services\EmailService;
use App\Helpers\SecurityHelper;

/**
 * VoteController - Gestion du processus de vote
 */

class VoteController {
    private $electionModel;
    private $candidateModel;
    private $voterModel;
    private $voteModel;
    private $emailService;
    
    public function __construct() {
        // Vérifier que l'utilisateur est connecté
        if (!isset($_SESSION['logged_in']) || $_SESSION['logged_in'] !== true) {
            redirect('/auth/login', 'Veuillez vous connecter pour voter', 'warning');
            exit();
        }
        
        // Vérifier que c'est un électeur
        if ($_SESSION['user']['role'] !== 'voter') {
            redirect('/admin/dashboard');
            exit();
        }
        
        $this->electionModel = new Election();
        $this->candidateModel = new Candidate();
        $this->voterModel = new Voter();
        $this->voteModel = new Vote();
        $this->emailService = new EmailService();
    }
    
    /**
     * S'inscrire à une élection
     */
    public function register($electionId) {
        $election = $this->electionModel->findById($electionId);
        
        if (!$election) {
            redirect('/voter/dashboard', 'Élection introuvable', 'danger');
            return;
        }
        
        // Vérifier si l'élection est active
        if ($election['status'] !== 'active' && $election['status'] !== 'draft') {
            redirect('/voter/dashboard', 'Cette élection n\'accepte plus d\'inscriptions', 'warning');
            return;
        }
        
        $userId = $_SESSION['user']['id'];
        
        // Inscrire l'électeur
        $result = $this->voterModel->register($userId, $electionId);
        
        if ($result['success']) {
            redirect('/voter/dashboard', 'Vous êtes maintenant inscrit à cette élection !', 'success');
        } else {
            redirect('/voter/dashboard', $result['message'], 'warning');
        }
    }
    
    /**
     * Page de vote
     */
    public function cast($electionId) {
        $election = $this->electionModel->findById($electionId);
        
        if (!$election) {
            redirect('/voter/dashboard', 'Élection introuvable', 'danger');
            return;
        }
        
        $userId = $_SESSION['user']['id'];
        
        // Vérifier l'éligibilité
        $eligibility = $this->voterModel->canVote($userId, $electionId);
        
        if (!$eligibility['eligible']) {
            redirect('/voter/dashboard', $eligibility['reason'], 'danger');
            return;
        }
        
        // Récupérer les candidats
        $candidates = $this->candidateModel->getByElection($electionId);
        
        if (empty($candidates)) {
            redirect('/voter/dashboard', 'Aucun candidat disponible pour cette élection', 'warning');
            return;
        }
        
        view('vote.cast', [
            'title' => 'Voter - ' . $election['title'],
            'election' => $election,
            'candidates' => $candidates,
            'voter_id' => $eligibility['voter_id']
        ]);
    }
    
    /**
     * Traiter le vote
     */
    public function processVote($electionId) {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            redirect('/voter/dashboard');
            return;
        }
        
        // Vérifier CSRF
        if (!verifyCsrfToken($_POST['csrf_token'] ?? '')) {
            redirect('/vote/cast/' . $electionId, 'Token de sécurité invalide', 'danger');
            return;
        }
        
        $candidateId = (int)($_POST['candidate_id'] ?? 0);
        $voterId = (int)($_POST['voter_id'] ?? 0);
        
        if (!$candidateId || !$voterId) {
            redirect('/vote/cast/' . $electionId, 'Veuillez sélectionner un candidat', 'danger');
            return;
        }
        
        // Vérifier que le candidat appartient bien à cette élection
        $candidate = $this->candidateModel->findById($candidateId);
        
        if (!$candidate || $candidate['election_id'] != $electionId) {
            redirect('/vote/cast/' . $electionId, 'Candidat invalide', 'danger');
            return;
        }
        
        // Enregistrer le vote
        $result = $this->voteModel->cast($electionId, $candidateId, $voterId);
        
        if ($result['success']) {
            // Envoyer email de confirmation
            $election = $this->electionModel->findById($electionId);
            $user = $_SESSION['user'];
            
            $this->emailService->sendVoteConfirmation(
                $user['email'],
                $user['first_name'] . ' ' . $user['last_name'],
                $election['title'],
                $result['verification_token']
            );
            
            // Rediriger vers la page de confirmation
            $_SESSION['vote_confirmation'] = [
                'election_title' => $election['title'],
                'verification_token' => $result['verification_token'],
                'vote_hash' => $result['vote_hash']
            ];
            
            redirect('/vote/confirmation', '', 'success');
        } else {
            redirect('/vote/cast/' . $electionId, $result['message'], 'danger');
        }
    }
    
    /**
     * Page de confirmation après vote
     */
    public function confirmation() {
        if (!isset($_SESSION['vote_confirmation'])) {
            redirect('/voter/dashboard', 'Aucune confirmation de vote disponible', 'warning');
            return;
        }
        
        $data = $_SESSION['vote_confirmation'];
        unset($_SESSION['vote_confirmation']);
        
        view('vote.confirmation', [
            'title' => 'Vote Confirmé',
            'election_title' => $data['election_title'],
            'verification_token' => $data['verification_token'],
            'vote_hash' => $data['vote_hash']
        ]);
    }
    
    /**
     * Vérifier un vote avec le token
     */
    public function verify() {
        view('vote.verify', [
            'title' => 'Vérifier mon Vote'
        ]);
    }
    
    /**
     * Traiter la vérification
     */
    public function processVerify() {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            redirect('/vote/verify');
            return;
        }
        
        $token = SecurityHelper::sanitize($_POST['token'] ?? '');
        
        if (empty($token)) {
            redirect('/vote/verify', 'Veuillez entrer un token de vérification', 'danger');
            return;
        }
        
        $result = $this->voteModel->verifyToken($token);
        
        if ($result) {
            view('vote.verify-result', [
                'title' => 'Résultat de Vérification',
                'verified' => true,
                'election_title' => $result['election_title'],
                'voted_at' => $result['voted_at']
            ]);
        } else {
            view('vote.verify-result', [
                'title' => 'Résultat de Vérification',
                'verified' => false
            ]);
        }
    }
    
    /**
     * Voir les résultats publics d'une élection
     */
    public function results($electionId) {
        $election = $this->electionModel->findById($electionId);
        
        if (!$election) {
            redirect('/voter/dashboard', 'Élection introuvable', 'danger');
            return;
        }
        
        // Vérifier que l'élection est clôturée
        if ($election['status'] !== 'closed') {
            redirect('/voter/dashboard', 'Les résultats ne sont pas encore disponibles', 'warning');
            return;
        }
        
        $results = $this->electionModel->getResults($electionId);
        $stats = $this->electionModel->getStats($electionId);
        
        view('vote.results', [
            'title' => 'Résultats - ' . $election['title'],
            'election' => $election,
            'results' => $results,
            'stats' => $stats
        ]);
    }
}