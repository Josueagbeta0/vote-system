<?php
namespace App\Services;

use App\Config\Database;
use Exception;

/**
 * FraudDetectionService - Détection et prévention de la fraude
 */

class FraudDetectionService {
    private $db;
    
    public function __construct() {
        $this->db = Database::getInstance();
    }
    
    /**
     * Vérifier si un utilisateur peut s'inscrire
     * Retourne ['allowed' => bool, 'reason' => string]
     */
    public function canRegister($data) {
        $checks = [];
        
        // 1. Vérifier l'IP
        $ipCheck = $this->checkIP($data['ip_address']);
        if (!$ipCheck['allowed']) {
            return $ipCheck;
        }
        $checks['ip'] = $ipCheck;
        
        // 2. Vérifier l'empreinte du navigateur
        if (isset($data['browser_fingerprint'])) {
            $fingerprintCheck = $this->checkFingerprint($data['browser_fingerprint']);
            if (!$fingerprintCheck['allowed']) {
                return $fingerprintCheck;
            }
            $checks['fingerprint'] = $fingerprintCheck;
        }
        
        // 3. Vérifier le numéro de téléphone (si fourni)
        if (!empty($data['phone_number'])) {
            $phoneCheck = $this->checkPhoneNumber($data['phone_number']);
            if (!$phoneCheck['allowed']) {
                return $phoneCheck;
            }
            $checks['phone'] = $phoneCheck;
        }
        
        // 4. Vérifier l'ID national (si fourni)
        if (!empty($data['national_id'])) {
            $idCheck = $this->checkNationalID($data['national_id']);
            if (!$idCheck['allowed']) {
                return $idCheck;
            }
            $checks['national_id'] = $idCheck;
        }
        
        // 5. Vérifier les patterns suspects
        $patternCheck = $this->checkSuspiciousPatterns($data);
        if (!$patternCheck['allowed']) {
            return $patternCheck;
        }
        
        return ['allowed' => true, 'checks' => $checks];
    }
    
    /**
     * Vérifier l'adresse IP
     */
    private function checkIP($ipAddress) {
        // Vérifier si l'IP est blacklistée
        $query = "SELECT * FROM ip_blacklist 
                  WHERE ip_address = :ip 
                  AND (is_permanent = 1 OR expires_at > NOW())
                  LIMIT 1";
        
        $blacklisted = $this->db->queryOne($query, [':ip' => $ipAddress]);
        
        if ($blacklisted) {
            $this->logSecurityEvent(null, $ipAddress, 'suspicious_ip', 'high', 
                'Tentative d\'inscription depuis une IP blacklistée');
            
            return [
                'allowed' => false,
                'reason' => 'Cette adresse IP est bloquée. Contactez l\'administrateur.'
            ];
        }
        
        // Compter le nombre de comptes créés depuis cette IP
        $query = "SELECT COUNT(*) as count FROM users WHERE registration_ip = :ip";
        $result = $this->db->queryOne($query, [':ip' => $ipAddress]);
        $accountCount = $result['count'];
        
        // Récupérer la limite depuis la config
        $maxAccounts = $this->getConfig('max_accounts_per_ip', 3);
        
        if ($accountCount >= $maxAccounts) {
            $this->logSecurityEvent(null, $ipAddress, 'multiple_accounts', 'high',
                "Tentative de création d'un {$accountCount}e compte depuis la même IP");
            
            return [
                'allowed' => false,
                'reason' => "Nombre maximum de comptes atteint pour cette connexion. Si vous avez perdu vos identifiants, utilisez la récupération de mot de passe."
            ];
        }
        
        // Avertissement si proche de la limite
        if ($accountCount >= $maxAccounts - 1) {
            return [
                'allowed' => true,
                'warning' => 'Dernier compte autorisé depuis cette connexion'
            ];
        }
        
        return ['allowed' => true];
    }
    
    /**
     * Vérifier l'empreinte digitale du navigateur
     */
    private function checkFingerprint($fingerprint) {
        if (empty($fingerprint)) {
            return ['allowed' => true];
        }
        
        // Chercher des comptes avec la même empreinte
        $query = "SELECT id, email, created_at FROM users 
                  WHERE browser_fingerprint = :fingerprint 
                  ORDER BY created_at DESC";
        
        $existingAccounts = $this->db->query($query, [':fingerprint' => $fingerprint]);
        
        if (count($existingAccounts) > 0) {
            $this->logSecurityEvent(null, $_SERVER['REMOTE_ADDR'] ?? 'unknown', 
                'duplicate_fingerprint', 'medium',
                'Empreinte digitale déjà utilisée par ' . count($existingAccounts) . ' compte(s)');
            
            // Bloquer si plus de 2 comptes avec la même empreinte
            if (count($existingAccounts) >= 2) {
                return [
                    'allowed' => false,
                    'reason' => 'Un compte existe déjà avec cet appareil. Veuillez vous connecter ou utiliser un autre appareil.'
                ];
            }
            
            return [
                'allowed' => true,
                'warning' => 'Appareil déjà utilisé pour un autre compte'
            ];
        }
        
        return ['allowed' => true];
    }
    
    /**
     * Vérifier le numéro de téléphone
     */
    private function checkPhoneNumber($phoneNumber) {
        $query = "SELECT id, email FROM users WHERE phone_number = :phone LIMIT 1";
        $existing = $this->db->queryOne($query, [':phone' => $phoneNumber]);
        
        if ($existing) {
            $this->logSecurityEvent(null, $_SERVER['REMOTE_ADDR'] ?? 'unknown',
                'multiple_accounts', 'high',
                'Tentative d\'utiliser un numéro de téléphone déjà enregistré');
            
            return [
                'allowed' => false,
                'reason' => 'Ce numéro de téléphone est déjà associé à un compte.'
            ];
        }
        
        return ['allowed' => true];
    }
    
    /**
     * Vérifier l'ID national
     */
    private function checkNationalID($nationalID) {
        $query = "SELECT id, email FROM users WHERE national_id = :id LIMIT 1";
        $existing = $this->db->queryOne($query, [':id' => $nationalID]);
        
        if ($existing) {
            $this->logSecurityEvent(null, $_SERVER['REMOTE_ADDR'] ?? 'unknown',
                'multiple_accounts', 'critical',
                'Tentative d\'utiliser un ID national déjà enregistré');
            
            return [
                'allowed' => false,
                'reason' => 'Ce numéro d\'identité est déjà associé à un compte.'
            ];
        }
        
        return ['allowed' => true];
    }
    
    /**
     * Détecter des patterns suspects
     */
    private function checkSuspiciousPatterns($data) {
        // Vérifier si email jetable (domaines suspects)
        $disposableEmailDomains = [
            'tempmail.com', 'guerrillamail.com', '10minutemail.com',
            'mailinator.com', 'throwaway.email', 'temp-mail.org'
        ];
        
        $emailDomain = substr(strrchr($data['email'], "@"), 1);
        if (in_array(strtolower($emailDomain), $disposableEmailDomains)) {
            $this->logSecurityEvent(null, $data['ip_address'], 'suspicious_ip', 'medium',
                'Tentative d\'inscription avec un email jetable');
            
            return [
                'allowed' => false,
                'reason' => 'Les adresses email temporaires ne sont pas autorisées.'
            ];
        }
        
        // Vérifier la rapidité d'inscription (rate limiting)
        $recentRegistrations = $this->getRecentRegistrations($data['ip_address'], 3600); // 1 heure
        
        if ($recentRegistrations > 3) {
            $this->logSecurityEvent(null, $data['ip_address'], 'rate_limit', 'high',
                'Trop d\'inscriptions en peu de temps');
            
            return [
                'allowed' => false,
                'reason' => 'Trop de tentatives d\'inscription. Veuillez réessayer dans 1 heure.'
            ];
        }
        
        return ['allowed' => true];
    }
    
    /**
     * Enregistrer un événement de sécurité
     */
    private function logSecurityEvent($userId, $ipAddress, $eventType, $severity, $description, $metadata = []) {
        $query = "INSERT INTO security_logs (user_id, ip_address, event_type, severity, description, metadata)
                  VALUES (:user_id, :ip_address, :event_type, :severity, :description, :metadata)";
        
        $params = [
            ':user_id' => $userId,
            ':ip_address' => $ipAddress,
            ':event_type' => $eventType,
            ':severity' => $severity,
            ':description' => $description,
            ':metadata' => json_encode($metadata)
        ];
        
        try {
            $this->db->execute($query, $params);
            logMessage("Événement sécurité: $eventType - $description", 'WARNING');
        } catch (Exception $e) {
            logMessage("Erreur log sécurité: " . $e->getMessage(), 'ERROR');
        }
    }
    
    /**
     * Détecter les relations entre comptes
     */
    public function detectAccountRelationships($userId) {
        $user = $this->getUserInfo($userId);
        if (!$user) return;
        
        // Trouver les comptes avec la même IP
        $query = "SELECT id FROM users 
                  WHERE registration_ip = :ip 
                  AND id != :user_id";
        
        $sameIPAccounts = $this->db->query($query, [
            ':ip' => $user['registration_ip'],
            ':user_id' => $userId
        ]);
        
        foreach ($sameIPAccounts as $account) {
            $this->recordRelationship($userId, $account['id'], 'same_ip', 0.70);
        }
        
        // Trouver les comptes avec la même empreinte
        if (!empty($user['browser_fingerprint'])) {
            $query = "SELECT id FROM users 
                      WHERE browser_fingerprint = :fingerprint 
                      AND id != :user_id";
            
            $sameFingerprintAccounts = $this->db->query($query, [
                ':fingerprint' => $user['browser_fingerprint'],
                ':user_id' => $userId
            ]);
            
            foreach ($sameFingerprintAccounts as $account) {
                $this->recordRelationship($userId, $account['id'], 'same_fingerprint', 0.90);
            }
        }
    }
    
    /**
     * Enregistrer une relation entre comptes
     */
    private function recordRelationship($userId1, $userId2, $type, $confidence) {
        $query = "INSERT INTO account_relationships (user_id_1, user_id_2, relationship_type, confidence_score)
                  VALUES (:user1, :user2, :type, :confidence)
                  ON DUPLICATE KEY UPDATE confidence_score = :confidence";
        
        try {
            $this->db->execute($query, [
                ':user1' => $userId1,
                ':user2' => $userId2,
                ':type' => $type,
                ':confidence' => $confidence
            ]);
        } catch (Exception $e) {
            logMessage("Erreur relation comptes: " . $e->getMessage(), 'ERROR');
        }
    }
    
    /**
     * Récupérer les infos d'un utilisateur
     */
    private function getUserInfo($userId) {
        $query = "SELECT * FROM users WHERE id = :id LIMIT 1";
        return $this->db->queryOne($query, [':id' => $userId]);
    }
    
    /**
     * Compter les inscriptions récentes depuis une IP
     */
    private function getRecentRegistrations($ipAddress, $seconds) {
        $query = "SELECT COUNT(*) as count FROM users 
                  WHERE registration_ip = :ip 
                  AND created_at > DATE_SUB(NOW(), INTERVAL :seconds SECOND)";
        
        $result = $this->db->queryOne($query, [
            ':ip' => $ipAddress,
            ':seconds' => $seconds
        ]);
        
        return $result['count'] ?? 0;
    }
    
    /**
     * Récupérer une valeur de configuration
     */
    private function getConfig($key, $default) {
        $query = "SELECT config_value FROM system_config WHERE config_key = :key LIMIT 1";
        $result = $this->db->queryOne($query, [':key' => $key]);
        
        return $result ? $result['config_value'] : $default;
    }
    
    /**
     * Obtenir les comptes suspects
     */
    public function getSuspiciousAccounts() {
        $query = "SELECT * FROM suspicious_accounts ORDER BY max_confidence DESC, related_accounts DESC";
        return $this->db->query($query);
    }
}