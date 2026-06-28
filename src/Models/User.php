<?php
namespace App\Models;

use App\Config\Database;
use PDOException;

/**
 * Modèle User - Gestion des utilisateurs
 */

class User {
    private $db;
    
    public function __construct() {
        $this->db = Database::getInstance();
    }
    
    /**
     * Créer un nouvel utilisateur
     */
    public function create($data) {
        $query = "INSERT INTO users (email, password_hash, role, first_name, last_name, is_verified, organization_id, identifier_code,
                  phone_number, national_id, registration_ip, browser_fingerprint, device_fingerprint, auth_provider, auth_provider_id, avatar_url) 
                  VALUES (:email, :password_hash, :role, :first_name, :last_name, :is_verified, :organization_id, :identifier_code,
                  :phone_number, :national_id, :registration_ip, :browser_fingerprint, :device_fingerprint, :auth_provider, :auth_provider_id, :avatar_url)";
        
        $params = [
            ':email' => $data['email'],
            ':password_hash' => !empty($data['password']) ? password_hash($data['password'], PASSWORD_BCRYPT) : null,
            ':role' => $data['role'] ?? 'voter',
            ':first_name' => $data['first_name'],
            ':last_name' => $data['last_name'],
            ':is_verified' => $data['is_verified'] ?? false,
            ':organization_id' => $data['organization_id'] ?? null,
            ':identifier_code' => $data['identifier_code'] ?? null,
            ':phone_number' => $data['phone_number'] ?? null,
            ':national_id' => $data['national_id'] ?? null,
            ':registration_ip' => $data['registration_ip'] ?? null,
            ':browser_fingerprint' => $data['browser_fingerprint'] ?? null,
            ':device_fingerprint' => $data['device_fingerprint'] ?? null,
            ':auth_provider' => $data['auth_provider'] ?? 'local',
            ':auth_provider_id' => $data['auth_provider_id'] ?? null,
            ':avatar_url' => $data['avatar_url'] ?? null
        ];
        
        try {
            $this->db->execute($query, $params);
            $userId = $this->db->lastInsertId();
            
            // Logger l'action
            logMessage("Nouvel utilisateur créé : {$data['email']} (ID: $userId)");
            
            return $userId;
        } catch (PDOException $e) {
            logMessage("Erreur création utilisateur : " . $e->getMessage(), 'ERROR');
            return false;
        }
    }
    
    /**
     * Trouver un utilisateur par email
     */
    public function findByEmail($email) {
        $query = "SELECT * FROM users WHERE email = :email LIMIT 1";
        return $this->db->queryOne($query, [':email' => $email]);
    }

    /**
     * Trouver un utilisateur par fournisseur OAuth
     */
    public function findByAuthProvider($provider, $providerId) {
        $query = "SELECT * FROM users WHERE auth_provider = :provider AND auth_provider_id = :id LIMIT 1";
        return $this->db->queryOne($query, [
            ':provider' => $provider, 
            ':id' => $providerId
        ]);
    }
    
    /**
     * Trouver un utilisateur par ID
     */
    public function findById($id) {
        $query = "SELECT * FROM users WHERE id = :id LIMIT 1";
        return $this->db->queryOne($query, [':id' => $id]);
    }
    
    /**
     * Vérifier si un email existe déjà
     */
    public function emailExists($email) {
        $query = "SELECT COUNT(*) as count FROM users WHERE email = :email";
        $result = $this->db->queryOne($query, [':email' => $email]);
        return $result['count'] > 0;
    }
    
    /**
     * Vérifier les identifiants de connexion
     */
    public function verifyCredentials($email, $password) {
        $user = $this->findByEmail($email);
        
        if (!$user) {
            return false;
        }
        
        if (password_verify($password, $user['password_hash'])) {
            // Mettre à jour la date de dernière connexion
            $this->updateLastLogin($user['id']);
            return $user;
        }
        
        return false;
    }
    
    /**
     * Mettre à jour la date de dernière connexion
     */
    public function updateLastLogin($userId) {
        $query = "UPDATE users SET last_login = NOW() WHERE id = :id";
        return $this->db->execute($query, [':id' => $userId]);
    }
    
    /**
     * Récupérer tous les utilisateurs avec un rôle spécifique
     */
    public function getAllByRole($role) {
        $query = "SELECT * FROM users WHERE role = :role ORDER BY created_at DESC";
        return $this->db->query($query, [':role' => $role]);
    }
    
    /**
     * Récupérer tous les électeurs
     */
    public function getAllVoters() {
        return $this->getAllByRole('voter');
    }
    
    /**
     * Mettre à jour un utilisateur
     */
    public function update($id, $data) {
        $fields = [];
        $params = [':id' => $id];
        
        // Construire dynamiquement la requête selon les champs fournis
        if (isset($data['first_name'])) {
            $fields[] = "first_name = :first_name";
            $params[':first_name'] = $data['first_name'];
        }
        if (isset($data['last_name'])) {
            $fields[] = "last_name = :last_name";
            $params[':last_name'] = $data['last_name'];
        }
        if (isset($data['email'])) {
            $fields[] = "email = :email";
            $params[':email'] = $data['email'];
        }
        if (isset($data['role'])) {
            $fields[] = "role = :role";
            $params[':role'] = $data['role'];
        }
        if (isset($data['is_verified'])) {
            $fields[] = "is_verified = :is_verified";
            $params[':is_verified'] = $data['is_verified'];
        }
        if (isset($data['verification_token'])) {
            $fields[] = "verification_token = :verification_token";
            $params[':verification_token'] = $data['verification_token'];
        }
        if (isset($data['verification_expires_at'])) {
            $fields[] = "verification_expires_at = :verification_expires_at";
            $params[':verification_expires_at'] = $data['verification_expires_at'];
        }
        
        if (empty($fields)) {
            return false;
        }
        
        $query = "UPDATE users SET " . implode(', ', $fields) . " WHERE id = :id";
        
        try {
            return $this->db->execute($query, $params);
        } catch (PDOException $e) {
            logMessage("Erreur mise à jour utilisateur : " . $e->getMessage(), 'ERROR');
            return false;
        }
    }
    
    /**
     * Changer le mot de passe
     */
    public function changePassword($userId, $newPassword) {
        $query = "UPDATE users SET password_hash = :password_hash WHERE id = :id";
        $params = [
            ':id' => $userId,
            ':password_hash' => password_hash($newPassword, PASSWORD_BCRYPT)
        ];
        
        try {
            $result = $this->db->execute($query, $params);
            logMessage("Mot de passe changé pour l'utilisateur ID: $userId");
            return $result;
        } catch (PDOException $e) {
            logMessage("Erreur changement mot de passe : " . $e->getMessage(), 'ERROR');
            return false;
        }
    }
    
    /**
     * Générer un token de réinitialisation de mot de passe
     */
    public function generateResetToken($email) {
        $user = $this->findByEmail($email);
        if (!$user) {
            return false;
        }
        
        $token = bin2hex(random_bytes(32));
        $expire = date('Y-m-d H:i:s', strtotime('+1 hour'));
        
        $query = "UPDATE users SET reset_token = :token, reset_token_expire = :expire WHERE id = :id";
        $params = [
            ':token' => $token,
            ':expire' => $expire,
            ':id' => $user['id']
        ];
        
        if ($this->db->execute($query, $params)) {
            return $token;
        }
        
        return false;
    }
    
    /**
     * Vérifier un token de réinitialisation
     */
    public function verifyResetToken($token) {
        $query = "SELECT * FROM users 
                  WHERE reset_token = :token 
                  AND reset_token_expire > NOW() 
                  LIMIT 1";
        
        return $this->db->queryOne($query, [':token' => $token]);
    }
    
    /**
     * Réinitialiser le mot de passe avec un token
     */
    public function resetPassword($token, $newPassword) {
        $user = $this->verifyResetToken($token);
        
        if (!$user) {
            return false;
        }
        
        $query = "UPDATE users 
                  SET password_hash = :password_hash, 
                      reset_token = NULL, 
                      reset_token_expire = NULL 
                  WHERE id = :id";
        
        $params = [
            ':password_hash' => password_hash($newPassword, PASSWORD_BCRYPT),
            ':id' => $user['id']
        ];
        
        try {
            $result = $this->db->execute($query, $params);
            logMessage("Mot de passe réinitialisé pour l'utilisateur ID: {$user['id']}");
            return $result;
        } catch (PDOException $e) {
            logMessage("Erreur réinitialisation mot de passe : " . $e->getMessage(), 'ERROR');
            return false;
        }
    }
    
    /**
     * Supprimer un utilisateur
     */
    public function delete($id) {
        $query = "DELETE FROM users WHERE id = :id";
        
        try {
            $result = $this->db->execute($query, [':id' => $id]);
            logMessage("Utilisateur supprimé : ID $id");
            return $result;
        } catch (PDOException $e) {
            logMessage("Erreur suppression utilisateur : " . $e->getMessage(), 'ERROR');
            return false;
        }
    }
    
    /**
     * Compter le nombre total d'utilisateurs
     */
    public function count($role = null) {
        if ($role) {
            $query = "SELECT COUNT(*) as count FROM users WHERE role = :role";
            $result = $this->db->queryOne($query, [':role' => $role]);
        } else {
            $query = "SELECT COUNT(*) as count FROM users";
            $result = $this->db->queryOne($query);
        }
        
        return $result['count'] ?? 0;
    }
    
    /**
     * Vérifier si un utilisateur est administrateur
     */
    public function isAdmin($userId) {
        $user = $this->findById($userId);
        return $user && $user['role'] === 'admin';
    }

    /**
     * Vérifier un token d'email
     */
    public function verifyEmailToken($token) {
        $query = "SELECT * FROM users WHERE verification_token = :token AND verification_expires_at > NOW() LIMIT 1";
        return $this->db->queryOne($query, [':token' => $token]);
    }
}