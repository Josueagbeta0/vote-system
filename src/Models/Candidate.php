<?php
namespace App\Models;

use App\Config\Database;
use PDOException;

/**
 * Modèle Candidate - Gestion des candidats
 */

class Candidate {
    private $db;
    
    public function __construct() {
        $this->db = Database::getInstance();
    }
    
    /**
     * Créer un nouveau candidat
     */
    public function create($data) {
        $query = "INSERT INTO candidates (election_id, name, description, photo_url, position) 
                  VALUES (:election_id, :name, :description, :photo_url, :position)";
        
        $params = [
            ':election_id' => $data['election_id'],
            ':name' => $data['name'],
            ':description' => $data['description'] ?? null,
            ':photo_url' => $data['photo_url'] ?? null,
            ':position' => $data['position'] ?? 0
        ];
        
        try {
            $this->db->execute($query, $params);
            $candidateId = $this->db->lastInsertId();
            
            logMessage("Nouveau candidat créé : {$data['name']} (ID: $candidateId)");
            
            return $candidateId;
        } catch (PDOException $e) {
            logMessage("Erreur création candidat : " . $e->getMessage(), 'ERROR');
            return false;
        }
    }
    
    /**
     * Récupérer un candidat par ID
     */
    public function findById($id) {
        $query = "SELECT c.*, e.title as election_title
                  FROM candidates c
                  LEFT JOIN elections e ON c.election_id = e.id
                  WHERE c.id = :id 
                  LIMIT 1";
        
        return $this->db->queryOne($query, [':id' => $id]);
    }
    
    /**
     * Récupérer tous les candidats d'une élection
     */
    public function getByElection($electionId) {
        $query = "SELECT * FROM candidates 
                  WHERE election_id = :election_id 
                  ORDER BY position ASC, name ASC";
        
        return $this->db->query($query, [':election_id' => $electionId]);
    }
    
    /**
     * Mettre à jour un candidat
     */
    public function update($id, $data) {
        $fields = [];
        $params = [':id' => $id];
        
        if (isset($data['name'])) {
            $fields[] = "name = :name";
            $params[':name'] = $data['name'];
        }
        if (isset($data['description'])) {
            $fields[] = "description = :description";
            $params[':description'] = $data['description'];
        }
        if (isset($data['photo_url'])) {
            $fields[] = "photo_url = :photo_url";
            $params[':photo_url'] = $data['photo_url'];
        }
        if (isset($data['position'])) {
            $fields[] = "position = :position";
            $params[':position'] = $data['position'];
        }
        
        if (empty($fields)) {
            return false;
        }
        
        $query = "UPDATE candidates SET " . implode(', ', $fields) . " WHERE id = :id";
        
        try {
            return $this->db->execute($query, $params);
        } catch (PDOException $e) {
            logMessage("Erreur mise à jour candidat : " . $e->getMessage(), 'ERROR');
            return false;
        }
    }
    
    /**
     * Supprimer un candidat
     */
    public function delete($id) {
        // Récupérer les infos du candidat avant suppression (pour supprimer la photo)
        $candidate = $this->findById($id);
        
        $query = "DELETE FROM candidates WHERE id = :id";
        
        try {
            $result = $this->db->execute($query, [':id' => $id]);
            
            // Supprimer la photo si elle existe
            if ($candidate && $candidate['photo_url']) {
                $photoPath = PUBLIC_PATH . $candidate['photo_url'];
                if (file_exists($photoPath)) {
                    unlink($photoPath);
                }
            }
            
            logMessage("Candidat supprimé : ID $id");
            return $result;
        } catch (PDOException $e) {
            logMessage("Erreur suppression candidat : " . $e->getMessage(), 'ERROR');
            return false;
        }
    }
    
    /**
     * Incrémenter le compteur de votes d'un candidat
     */
    public function incrementVoteCount($candidateId) {
        $query = "UPDATE candidates SET vote_count = vote_count + 1 WHERE id = :id";
        
        try {
            return $this->db->execute($query, [':id' => $candidateId]);
        } catch (PDOException $e) {
            logMessage("Erreur incrémentation votes : " . $e->getMessage(), 'ERROR');
            return false;
        }
    }
    
    /**
     * Récupérer le nombre de votes d'un candidat
     */
    public function getVoteCount($candidateId) {
        $query = "SELECT vote_count FROM candidates WHERE id = :id LIMIT 1";
        $result = $this->db->queryOne($query, [':id' => $candidateId]);
        
        return $result ? $result['vote_count'] : 0;
    }
    
    /**
     * Compter le nombre de candidats dans une élection
     */
    public function countByElection($electionId) {
        $query = "SELECT COUNT(*) as count FROM candidates WHERE election_id = :election_id";
        $result = $this->db->queryOne($query, [':election_id' => $electionId]);
        
        return $result['count'] ?? 0;
    }
}