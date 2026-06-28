<?php
namespace App\Models;

use App\Config\Database;
use PDOException;

/**
 * Modèle Voter - Gestion des électeurs inscrits aux élections
 */

class Voter {
    private $db;
    
    public function __construct() {
        $this->db = Database::getInstance();
    }
    
    /**
     * Inscrire un électeur à une élection
     */
    public function register($userId, $electionId) {
        // Vérifier si déjà inscrit
        if ($this->isRegistered($userId, $electionId)) {
            return ['success' => false, 'message' => 'Déjà inscrit à cette élection'];
        }
        
        $query = "INSERT INTO voters (user_id, election_id, has_voted) 
                  VALUES (:user_id, :election_id, 0)";
        
        $params = [
            ':user_id' => $userId,
            ':election_id' => $electionId
        ];
        
        try {
            $this->db->execute($query, $params);
            $voterId = $this->db->lastInsertId();
            
            logMessage("Électeur inscrit : User $userId, Election $electionId");
            
            return ['success' => true, 'voter_id' => $voterId];
        } catch (PDOException $e) {
            logMessage("Erreur inscription électeur : " . $e->getMessage(), 'ERROR');
            return ['success' => false, 'message' => 'Erreur lors de l\'inscription'];
        }
    }
    
    /**
     * Vérifier si un utilisateur est inscrit à une élection
     */
    public function isRegistered($userId, $electionId) {
        $query = "SELECT id FROM voters 
                  WHERE user_id = :user_id 
                  AND election_id = :election_id 
                  LIMIT 1";
        
        $result = $this->db->queryOne($query, [
            ':user_id' => $userId,
            ':election_id' => $electionId
        ]);
        
        return $result !== false;
    }
    
    /**
     * Récupérer l'enregistrement d'un électeur
     */
    public function getVoter($userId, $electionId) {
        $query = "SELECT * FROM voters 
                  WHERE user_id = :user_id 
                  AND election_id = :election_id 
                  LIMIT 1";
        
        return $this->db->queryOne($query, [
            ':user_id' => $userId,
            ':election_id' => $electionId
        ]);
    }
    
    /**
     * Récupérer tous les électeurs d'une élection
     */
    public function getByElection($electionId) {
        $query = "SELECT v.*, u.email, u.first_name, u.last_name
                  FROM voters v
                  LEFT JOIN users u ON v.user_id = u.id
                  WHERE v.election_id = :election_id
                  ORDER BY u.last_name ASC, u.first_name ASC";
        
        return $this->db->query($query, [':election_id' => $electionId]);
    }
    
    /**
     * Récupérer toutes les élections auxquelles un utilisateur est inscrit
     */
    public function getElectionsByUser($userId) {
        $query = "SELECT v.*, e.title, e.description, e.start_date, e.end_date, e.status
                  FROM voters v
                  LEFT JOIN elections e ON v.election_id = e.id
                  WHERE v.user_id = :user_id
                  ORDER BY e.start_date DESC";
        
        return $this->db->query($query, [':user_id' => $userId]);
    }
    
    /**
     * Vérifier si un électeur peut voter (éligibilité)
     */
    public function canVote($userId, $electionId) {
        // Vérifier si inscrit
        $voter = $this->getVoter($userId, $electionId);
        
        if (!$voter) {
            return ['eligible' => false, 'reason' => 'Non inscrit à cette élection'];
        }
        
        // Vérifier si déjà voté
        if ($voter['has_voted']) {
            return ['eligible' => false, 'reason' => 'Vous avez déjà voté'];
        }
        
        // Vérifier si l'élection est active
        $electionModel = new Election();
        if (!$electionModel->isActive($electionId)) {
            return ['eligible' => false, 'reason' => 'L\'élection n\'est pas en cours'];
        }
        
        return ['eligible' => true, 'voter_id' => $voter['id']];
    }
    
    /**
     * Compter les électeurs inscrits à une élection
     */
    public function countByElection($electionId) {
        $query = "SELECT COUNT(*) as count FROM voters WHERE election_id = :election_id";
        $result = $this->db->queryOne($query, [':election_id' => $electionId]);
        
        return $result['count'] ?? 0;
    }
    
    /**
     * Compter les électeurs ayant voté
     */
    public function countVoted($electionId) {
        $query = "SELECT COUNT(*) as count FROM voters 
                  WHERE election_id = :election_id 
                  AND has_voted = 1";
        
        $result = $this->db->queryOne($query, [':election_id' => $electionId]);
        
        return $result['count'] ?? 0;
    }
    
    /**
     * Calculer le taux de participation
     */
    public function getParticipationRate($electionId) {
        $total = $this->countByElection($electionId);
        $voted = $this->countVoted($electionId);
        
        if ($total == 0) {
            return 0;
        }
        
        return round(($voted / $total) * 100, 2);
    }
    
    /**
     * Désinscrire un électeur d'une élection
     */
    public function unregister($userId, $electionId) {
        // Vérifier si déjà voté (ne peut pas se désinscrire après vote)
        $voter = $this->getVoter($userId, $electionId);
        
        if ($voter && $voter['has_voted']) {
            return ['success' => false, 'message' => 'Impossible de se désinscrire après avoir voté'];
        }
        
        $query = "DELETE FROM voters 
                  WHERE user_id = :user_id 
                  AND election_id = :election_id 
                  AND has_voted = 0";
        
        try {
            $this->db->execute($query, [
                ':user_id' => $userId,
                ':election_id' => $electionId
            ]);
            
            logMessage("Électeur désinscrit : User $userId, Election $electionId");
            
            return ['success' => true, 'message' => 'Désinscription réussie'];
        } catch (PDOException $e) {
            logMessage("Erreur désinscription électeur : " . $e->getMessage(), 'ERROR');
            return ['success' => false, 'message' => 'Erreur lors de la désinscription'];
        }
    }
    
    /**
     * Inscrire plusieurs électeurs en masse (import CSV)
     */
    public function bulkRegister($electionId, $userIds) {
        $successCount = 0;
        $errorCount = 0;
        
        foreach ($userIds as $userId) {
            $result = $this->register($userId, $electionId);
            if ($result['success']) {
                $successCount++;
            } else {
                $errorCount++;
            }
        }
        
        return [
            'success' => $successCount,
            'errors' => $errorCount,
            'total' => count($userIds)
        ];
    }
}