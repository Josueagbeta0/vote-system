<?php
namespace App\Models;

use App\Config\Database;
use PDOException;

/**
 * Modèle Election - Gestion des élections
 */

class Election {
    private $db;
    
    public function __construct() {
        $this->db = Database::getInstance();
    }
    
    /**
     * Créer une nouvelle élection
     */
    public function create($data) {
        $query = "INSERT INTO elections (title, description, start_date, end_date, status, created_by, organization_id) 
                  VALUES (:title, :description, :start_date, :end_date, :status, :created_by, :organization_id)";
        
        $params = [
            ':title' => $data['title'],
            ':description' => $data['description'] ?? null,
            ':start_date' => $data['start_date'],
            ':end_date' => $data['end_date'],
            ':status' => $data['status'] ?? 'draft',
            ':created_by' => $data['created_by'],
            ':organization_id' => $data['organization_id'] ?? null
        ];
        
        try {
            $this->db->execute($query, $params);
            $electionId = $this->db->lastInsertId();
            
            logMessage("Nouvelle élection créée : {$data['title']} (ID: $electionId)");
            
            return $electionId;
        } catch (PDOException $e) {
            logMessage("Erreur création élection : " . $e->getMessage(), 'ERROR');
            return false;
        }
    }
    
    /**
     * Récupérer une élection par ID
     */
    public function findById($id) {
        $query = "SELECT e.*, u.first_name, u.last_name 
                  FROM elections e
                  LEFT JOIN users u ON e.created_by = u.id
                  WHERE e.id = :id 
                  LIMIT 1";
        
        return $this->db->queryOne($query, [':id' => $id]);
    }
    
    /**
     * Récupérer toutes les élections (filtrées par organisation)
     */
    public function getAll($status = null, $organizationId = null) {
        $conditions = [];
        $params = [];
        
        if ($status) {
            $conditions[] = "e.status = :status";
            $params[':status'] = $status;
        }
        
        if ($organizationId) {
            $conditions[] = "e.organization_id = :org_id";
            $params[':org_id'] = $organizationId;
        }
        
        $whereClause = !empty($conditions) ? "WHERE " . implode(' AND ', $conditions) : "";
        
        $query = "SELECT e.*, u.first_name, u.last_name,
                  (SELECT COUNT(*) FROM candidates WHERE election_id = e.id) as candidate_count,
                  (SELECT COUNT(*) FROM voters WHERE election_id = e.id AND has_voted = 1) as vote_count
                  FROM elections e
                  LEFT JOIN users u ON e.created_by = u.id
                  $whereClause
                  ORDER BY e.created_at DESC";
        
        return $this->db->query($query, $params);
    }
    
    /**
     * Récupérer les élections actives (en cours)
     */
    /**
     * Récupérer les élections actives (en cours)
     */
    public function getActive($organizationId = null) {
        $query = "SELECT e.*, 
                  (SELECT COUNT(*) FROM candidates WHERE election_id = e.id) as candidate_count
                  FROM elections e
                  WHERE e.status = 'active'
                  AND e.start_date <= NOW()
                  AND e.end_date >= NOW()";
        
        $params = [];
        if ($organizationId) {
            $query .= " AND e.organization_id = :org_id";
            $params[':org_id'] = $organizationId;
        }
        
        $query .= " ORDER BY e.start_date ASC";
        
        return $this->db->query($query, $params);
    }
    
    /**
     * Mettre à jour une élection
     */
    public function update($id, $data) {
        $fields = [];
        $params = [':id' => $id];
        
        if (isset($data['title'])) {
            $fields[] = "title = :title";
            $params[':title'] = $data['title'];
        }
        if (isset($data['description'])) {
            $fields[] = "description = :description";
            $params[':description'] = $data['description'];
        }
        if (isset($data['start_date'])) {
            $fields[] = "start_date = :start_date";
            $params[':start_date'] = $data['start_date'];
        }
        if (isset($data['end_date'])) {
            $fields[] = "end_date = :end_date";
            $params[':end_date'] = $data['end_date'];
        }
        if (isset($data['status'])) {
            $fields[] = "status = :status";
            $params[':status'] = $data['status'];
        }
        
        if (empty($fields)) {
            return false;
        }
        
        $query = "UPDATE elections SET " . implode(', ', $fields) . " WHERE id = :id";
        
        try {
            return $this->db->execute($query, $params);
        } catch (PDOException $e) {
            logMessage("Erreur mise à jour élection : " . $e->getMessage(), 'ERROR');
            return false;
        }
    }
    
    /**
     * Changer le statut d'une élection
     */
    public function changeStatus($id, $status) {
        $query = "UPDATE elections SET status = :status WHERE id = :id";
        
        try {
            $result = $this->db->execute($query, [':id' => $id, ':status' => $status]);
            logMessage("Statut de l'élection $id changé en : $status");
            return $result;
        } catch (PDOException $e) {
            logMessage("Erreur changement statut élection : " . $e->getMessage(), 'ERROR');
            return false;
        }
    }
    
    /**
     * Démarrer une élection
     */
    public function start($id) {
        return $this->changeStatus($id, 'active');
    }
    
    /**
     * Clôturer une élection
     */
    public function close($id) {
        return $this->changeStatus($id, 'closed');
    }
    
    /**
     * Supprimer une élection (avec tous ses candidats et votes)
     */
    public function delete($id) {
        try {
            // Démarrer une transaction
            $this->db->beginTransaction();
            
            // Supprimer les votes
            $this->db->execute("DELETE FROM votes WHERE election_id = :id", [':id' => $id]);
            
            // Supprimer les électeurs
            $this->db->execute("DELETE FROM voters WHERE election_id = :id", [':id' => $id]);
            
            // Supprimer les candidats
            $this->db->execute("DELETE FROM candidates WHERE election_id = :id", [':id' => $id]);
            
            // Supprimer l'élection
            $this->db->execute("DELETE FROM elections WHERE id = :id", [':id' => $id]);
            
            // Valider la transaction
            $this->db->commit();
            
            logMessage("Élection supprimée : ID $id");
            return true;
        } catch (PDOException $e) {
            // Annuler en cas d'erreur
            $this->db->rollback();
            logMessage("Erreur suppression élection : " . $e->getMessage(), 'ERROR');
            return false;
        }
    }
    
    /**
     * Récupérer les résultats d'une élection
     */
    public function getResults($electionId) {
        $query = "SELECT c.*, COUNT(v.id) as vote_count
                  FROM candidates c
                  LEFT JOIN votes v ON c.id = v.candidate_id
                  WHERE c.election_id = :election_id
                  GROUP BY c.id
                  ORDER BY vote_count DESC, c.name ASC";
        
        return $this->db->query($query, [':election_id' => $electionId]);
    }
    
    /**
     * Récupérer les statistiques d'une élection
     */
    public function getStats($electionId) {
        $stats = [];
        
        // Nombre total d'électeurs inscrits
        $query = "SELECT COUNT(*) as total FROM voters WHERE election_id = :id";
        $result = $this->db->queryOne($query, [':id' => $electionId]);
        $stats['total_voters'] = $result['total'];
        
        // Nombre d'électeurs ayant voté
        $query = "SELECT COUNT(*) as voted FROM voters WHERE election_id = :id AND has_voted = 1";
        $result = $this->db->queryOne($query, [':id' => $electionId]);
        $stats['voted_count'] = $result['voted'];
        
        // Taux de participation
        if ($stats['total_voters'] > 0) {
            $stats['participation_rate'] = ($stats['voted_count'] / $stats['total_voters']) * 100;
        } else {
            $stats['participation_rate'] = 0;
        }
        
        // Nombre de candidats
        $query = "SELECT COUNT(*) as total FROM candidates WHERE election_id = :id";
        $result = $this->db->queryOne($query, [':id' => $electionId]);
        $stats['candidate_count'] = $result['total'];
        
        // Total des votes
        $query = "SELECT COUNT(*) as total FROM votes WHERE election_id = :id";
        $result = $this->db->queryOne($query, [':id' => $electionId]);
        $stats['total_votes'] = $result['total'];
        
        return $stats;
    }
    
    /**
     * Vérifier si une élection est en cours
     */
    public function isActive($electionId) {
        $query = "SELECT status, start_date, end_date 
                  FROM elections 
                  WHERE id = :id 
                  LIMIT 1";
        
        $election = $this->db->queryOne($query, [':id' => $electionId]);
        
        if (!$election) {
            return false;
        }
        
        $now = time();
        $startTime = strtotime($election['start_date']);
        $endTime = strtotime($election['end_date']);
        
        return $election['status'] === 'active' && $now >= $startTime && $now <= $endTime;
    }
    
    public function count($status = null) {
        if ($status) {
            $query = "SELECT COUNT(*) as count FROM elections WHERE status = :status";
            $result = $this->db->queryOne($query, [':status' => $status]);
        } else {
            $query = "SELECT COUNT(*) as count FROM elections";
            $result = $this->db->queryOne($query);
        }
        
        return $result['count'] ?? 0;
    }

    /**
     * [ANALYTICS] Récupérer l'évolution des votes par jour sur les X derniers jours
     */
    public function getVotesOverTime($days = 7) {
        $query = "SELECT DATE(created_at) as date, COUNT(*) as count 
                  FROM votes 
                  WHERE created_at >= DATE_SUB(NOW(), INTERVAL :days DAY)
                  GROUP BY DATE(created_at) 
                  ORDER BY date ASC";
        
        return $this->db->query($query, [':days' => $days]);
    }

    /**
     * [ANALYTICS] Statistiques globales de participation
     */
    public function getGlobalTurnout() {
        // Total Eligible (whitelist)
        $totalQuery = "SELECT COUNT(*) as cnt FROM eligible_voters";
        $totalResult = $this->db->queryOne($totalQuery);
        $total = $totalResult['cnt'] ?? 0;

        // Total Registered (users)
        $registeredQuery = "SELECT COUNT(*) as cnt FROM eligible_voters WHERE is_registered = 1";
        $registeredResult = $this->db->queryOne($registeredQuery);
        $registered = $registeredResult['cnt'] ?? 0;

        // Total Votes Cast (votes table)
        // Note: Unique votes per election per user logic handled by app, count rows here.
        $votesQuery = "SELECT COUNT(*) as cnt FROM votes";
        $votesResult = $this->db->queryOne($votesQuery);
        $votes = $votesResult['cnt'] ?? 0;

        return [
            'total_eligible' => $total,
            'registered' => $registered,
            'votes_cast' => $votes
        ];
    }
}