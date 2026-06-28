<?php
namespace App\Models;

use App\Config\Database;
use PDOException;

/**
 * Modèle Vote - Gestion des votes sécurisés
 */

class Vote {
    private $db;
    
    public function __construct() {
        $this->db = Database::getInstance();
    }
    
    /**
     * Enregistrer un vote (chiffré et anonyme)
     */
    public function cast($electionId, $candidateId, $voterId) {
        try {
            // Démarrer une transaction pour garantir l'atomicité
            $this->db->beginTransaction();
            
            // 1. VÉRIFICATION CRITIQUE: Verrouillage de la ligne pour éviter les conditions de course
            $query = "SELECT has_voted FROM voters 
                      WHERE id = :voter_id 
                      AND election_id = :election_id 
                      FOR UPDATE"; // LOCK pour empêcher double vote concurrent
            
            $voter = $this->db->queryOne($query, [
                ':voter_id' => $voterId,
                ':election_id' => $electionId
            ]);
            
            // 2. Vérifier que l'électeur n'a pas déjà voté (avec le verrou)
            if (!$voter || $voter['has_voted'] == 1) {
                $this->db->rollback();
                logMessage("TENTATIVE DE DOUBLE VOTE bloquée : Voter $voterId, Election $electionId", 'WARNING');
                return ['success' => false, 'message' => 'Vous avez déjà voté pour cette élection'];
            }
            
            // 2. Chiffrer le vote
            $encryptedVote = $this->encryptVote($candidateId);
            
            // 3. Générer un hash unique pour ce vote (blockchain simplifiée)
            $previousHash = $this->getLastVoteHash($electionId);
            $voteHash = $this->generateVoteHash($electionId, $candidateId, $previousHash);
            
            // 4. Enregistrer le vote
            $query = "INSERT INTO votes (election_id, candidate_id, encrypted_vote, vote_hash, previous_hash, ip_address) 
                      VALUES (:election_id, :candidate_id, :encrypted_vote, :vote_hash, :previous_hash, :ip_address)";
            
            $params = [
                ':election_id' => $electionId,
                ':candidate_id' => $candidateId,
                ':encrypted_vote' => $encryptedVote,
                ':vote_hash' => $voteHash,
                ':previous_hash' => $previousHash,
                ':ip_address' => $_SERVER['REMOTE_ADDR'] ?? 'unknown'
            ];
            
            $this->db->execute($query, $params);
            $voteId = $this->db->lastInsertId();
            
            // 5. Marquer l'électeur comme ayant voté
            $this->markAsVoted($voterId, $electionId);
            
            // 6. Incrémenter le compteur du candidat
            $candidateModel = new Candidate();
            $candidateModel->incrementVoteCount($candidateId);
            
            // 7. Générer un token de vérification pour l'électeur
            $verificationToken = $this->generateVerificationToken($voterId, $electionId, $voteHash);
            
            // Valider la transaction
            $this->db->commit();
            
            logMessage("Vote enregistré : Election $electionId, Vote ID: $voteId");
            
            return [
                'success' => true,
                'message' => 'Vote enregistré avec succès',
                'verification_token' => $verificationToken,
                'vote_hash' => substr($voteHash, 0, 8) // Premiers caractères pour affichage
            ];
            
        } catch (PDOException $e) {
            $this->db->rollback();
            logMessage("Erreur enregistrement vote : " . $e->getMessage(), 'ERROR');
            return ['success' => false, 'message' => 'Erreur lors de l\'enregistrement du vote'];
        }
    }
    
    /**
     * Chiffrer un vote avec AES-256
     */
    private function encryptVote($candidateId) {
        $key = ENCRYPTION_KEY;
        $iv = openssl_random_pseudo_bytes(openssl_cipher_iv_length('aes-256-cbc'));
        
        $encrypted = openssl_encrypt(
            $candidateId,
            'aes-256-cbc',
            $key,
            0,
            $iv
        );
        
        // Retourner IV + données chiffrées (encodé en base64)
        return base64_encode($iv . $encrypted);
    }
    
    /**
     * Déchiffrer un vote
     */
    public function decryptVote($encryptedVote) {
        $key = ENCRYPTION_KEY;
        $data = base64_decode($encryptedVote);
        
        $ivLength = openssl_cipher_iv_length('aes-256-cbc');
        $iv = substr($data, 0, $ivLength);
        $encrypted = substr($data, $ivLength);
        
        return openssl_decrypt(
            $encrypted,
            'aes-256-cbc',
            $key,
            0,
            $iv
        );
    }
    
    /**
     * Générer un hash pour le vote (blockchain)
     */
    private function generateVoteHash($electionId, $candidateId, $previousHash) {
        $data = $electionId . $candidateId . time() . $previousHash . rand();
        return hash('sha256', $data);
    }
    
    /**
     * Récupérer le hash du dernier vote d'une élection
     */
    private function getLastVoteHash($electionId) {
        $query = "SELECT vote_hash FROM votes 
                  WHERE election_id = :election_id 
                  ORDER BY timestamp DESC 
                  LIMIT 1";
        
        $result = $this->db->queryOne($query, [':election_id' => $electionId]);
        
        return $result ? $result['vote_hash'] : '0';
    }
    
    /**
     * Vérifier si un électeur a déjà voté
     */
    public function hasVoted($voterId, $electionId) {
        $query = "SELECT has_voted FROM voters 
                  WHERE id = :voter_id 
                  AND election_id = :election_id 
                  LIMIT 1";
        
        $result = $this->db->queryOne($query, [
            ':voter_id' => $voterId,
            ':election_id' => $electionId
        ]);
        
        return $result && $result['has_voted'] == 1;
    }
    
    /**
     * Marquer un électeur comme ayant voté
     */
    private function markAsVoted($voterId, $electionId) {
        $query = "UPDATE voters 
                  SET has_voted = 1, 
                      voted_at = NOW(),
                      ip_address = :ip_address,
                      user_agent = :user_agent
                  WHERE id = :voter_id 
                  AND election_id = :election_id";
        
        $params = [
            ':voter_id' => $voterId,
            ':election_id' => $electionId,
            ':ip_address' => $_SERVER['REMOTE_ADDR'] ?? 'unknown',
            ':user_agent' => $_SERVER['HTTP_USER_AGENT'] ?? 'unknown'
        ];
        
        return $this->db->execute($query, $params);
    }
    
    /**
     * Générer un token de vérification pour l'électeur
     */
    private function generateVerificationToken($voterId, $electionId, $voteHash) {
        $data = $voterId . $electionId . $voteHash . time();
        $token = hash('sha256', $data);
        
        // Sauvegarder le token dans la table voters
        $query = "UPDATE voters 
                  SET verification_token = :token 
                  WHERE id = :voter_id 
                  AND election_id = :election_id";
        
        $this->db->execute($query, [
            ':token' => $token,
            ':voter_id' => $voterId,
            ':election_id' => $electionId
        ]);
        
        return $token;
    }
    
    /**
     * Vérifier un token de vote
     */
    public function verifyToken($token) {
        $query = "SELECT v.*, e.title as election_title 
                  FROM voters v
                  LEFT JOIN elections e ON v.election_id = e.id
                  WHERE v.verification_token = :token 
                  AND v.has_voted = 1
                  LIMIT 1";
        
        return $this->db->queryOne($query, [':token' => $token]);
    }
    
    /**
     * Compter le nombre total de votes pour une élection
     */
    public function countByElection($electionId) {
        $query = "SELECT COUNT(*) as count FROM votes WHERE election_id = :election_id";
        $result = $this->db->queryOne($query, [':election_id' => $electionId]);
        
        return $result['count'] ?? 0;
    }
    
    /**
     * Vérifier l'intégrité de la blockchain des votes
     */
    public function verifyBlockchain($electionId) {
        $query = "SELECT vote_hash, previous_hash 
                  FROM votes 
                  WHERE election_id = :election_id 
                  ORDER BY timestamp ASC";
        
        $votes = $this->db->query($query, [':election_id' => $electionId]);
        
        if (empty($votes)) {
            return true;
        }
        
        $expectedPrevious = '0';
        
        foreach ($votes as $vote) {
            if ($vote['previous_hash'] !== $expectedPrevious) {
                return false; // Blockchain corrompue
            }
            $expectedPrevious = $vote['vote_hash'];
        }
        
        return true; // Blockchain intègre
    }
    
    /**
     * Récupérer tous les votes d'une élection (admin uniquement)
     */
    public function getAllByElection($electionId) {
        $query = "SELECT v.*, c.name as candidate_name
                  FROM votes v
                  LEFT JOIN candidates c ON v.candidate_id = c.id
                  WHERE v.election_id = :election_id
                  ORDER BY v.timestamp DESC";
        
        return $this->db->query($query, [':election_id' => $electionId]);
    }
}