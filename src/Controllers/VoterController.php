<?php
namespace App\Controllers;

use App\Models\Election;
use App\Models\Voter;
use App\Models\User;

/**
 * VoterController - Espace électeur
 */

class VoterController {
    private $electionModel;
    private $voterModel;
    private $userModel;
    
    public function __construct() {
        // Vérifier que l'utilisateur est connecté
        if (!isset($_SESSION['logged_in']) || $_SESSION['logged_in'] !== true) {
            redirect('/auth/login', 'Veuillez vous connecter', 'warning');
            exit();
        }
        
        // Vérifier que c'est bien un électeur
        if ($_SESSION['user']['role'] !== 'voter') {
            redirect('/admin/dashboard');
            exit();
        }
        
        $this->electionModel = new Election();
        $this->voterModel = new Voter();
        $this->userModel = new User();
    }
    
    /**
     * Dashboard électeur
     */
    public function dashboard() {
        $userId = $_SESSION['user']['id'];
        
        // Récupérer les détails de l'utilisateur pour avoir son organization_id
        $user = $this->userModel->findById($userId);
        $orgId = $user['organization_id'] ?? null;
        
        // Récupérer les élections actives (filtrées par organisation)
        $activeElections = $this->electionModel->getActive($orgId);
        
        // Récupérer les élections auxquelles l'électeur participe
        $myElections = $this->voterModel->getElectionsByUser($userId);
        
        // Statistiques
        $stats = [
            'total_elections' => count($myElections),
            'voted' => 0,
            'pending' => 0
        ];
        
        foreach ($myElections as $election) {
            if ($election['has_voted']) {
                $stats['voted']++;
            } else {
                $stats['pending']++;
            }
        }
        
        view('voter.dashboard', [
            'title' => 'Mon Espace - Vote Sécurisé',
            'activeElections' => $activeElections,
            'myElections' => $myElections,
            'stats' => $stats
        ]);
    }
}