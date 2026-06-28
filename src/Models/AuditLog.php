<?php
namespace App\Models;

use App\Config\Database;
use PDO;
use PDOException;

/**
 * Modèle AuditLog - Gestion des logs d'audit
 */

class AuditLog {
    private $db;
    
    public function __construct() {
        $this->db = Database::getInstance();
    }
    
    /**
     * Enregistrer une action dans les logs
     */
    public function log($userId, $action, $entityType = null, $entityId = null, $details = null) {
        $query = "INSERT INTO audit_logs (user_id, action, entity_type, entity_id, details, ip_address, user_agent)
                  VALUES (:user_id, :action, :entity_type, :entity_id, :details, :ip_address, :user_agent)";
        
        $params = [
            ':user_id' => $userId,
            ':action' => $action,
            ':entity_type' => $entityType,
            ':entity_id' => $entityId,
            ':details' => $details,
            ':ip_address' => $_SERVER['REMOTE_ADDR'] ?? 'unknown',
            ':user_agent' => $_SERVER['HTTP_USER_AGENT'] ?? 'unknown'
        ];
        
        try {
            return $this->db->execute($query, $params);
        } catch (PDOException $e) {
            logMessage("Erreur log audit : " . $e->getMessage(), 'ERROR');
            return false;
        }
    }
    
    /**
     * Récupérer les logs d'un utilisateur
     */
    public function getUserLogs($userId, $limit = 100) {
        $query = "SELECT * FROM audit_logs 
                  WHERE user_id = :user_id 
                  ORDER BY timestamp DESC 
                  LIMIT :limit";
        
        $stmt = $this->db->getConnection()->prepare($query);
        $stmt->bindValue(':user_id', $userId, PDO::PARAM_INT);
        $stmt->bindValue(':limit', $limit, PDO::PARAM_INT);
        $stmt->execute();
        
        return $stmt->fetchAll();
    }
    
    /**
     * Récupérer tous les logs récents
     */
    public function getRecentLogs($limit = 100) {
        $query = "SELECT al.*, u.email 
                  FROM audit_logs al
                  LEFT JOIN users u ON al.user_id = u.id
                  ORDER BY al.timestamp DESC 
                  LIMIT :limit";
        
        $stmt = $this->db->getConnection()->prepare($query);
        $stmt->bindValue(':limit', $limit, PDO::PARAM_INT);
        $stmt->execute();
        
        return $stmt->fetchAll();
    }
    
    /**
     * Rechercher dans les logs
     */
    public function search($action = null, $userId = null, $dateFrom = null, $dateTo = null) {
        $query = "SELECT al.*, u.email 
                  FROM audit_logs al
                  LEFT JOIN users u ON al.user_id = u.id
                  WHERE 1=1";
        
        $params = [];
        
        if ($action) {
            $query .= " AND al.action LIKE :action";
            $params[':action'] = "%$action%";
        }
        
        if ($userId) {
            $query .= " AND al.user_id = :user_id";
            $params[':user_id'] = $userId;
        }
        
        if ($dateFrom) {
            $query .= " AND al.timestamp >= :date_from";
            $params[':date_from'] = $dateFrom;
        }
        
        if ($dateTo) {
            $query .= " AND al.timestamp <= :date_to";
            $params[':date_to'] = $dateTo;
        }
        
        $query .= " ORDER BY al.timestamp DESC LIMIT 500";
        
        return $this->db->query($query, $params);
    }
}