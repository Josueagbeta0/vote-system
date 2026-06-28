<?php
namespace App\Models;

use App\Config\Database;

class EligibleVoter {
    private $db;

    public function __construct() {
        $this->db = Database::getInstance();
    }

    public function create($data) {
        $sql = "INSERT INTO eligible_voters (organization_id, identifier_code, first_name, last_name, email, image_url) 
                VALUES (:org_id, :code, :first_name, :last_name, :email, :image_url)";
        try {
            $this->db->execute($sql, [
                ':org_id' => $data['organization_id'],
                ':code' => $data['identifier_code'],
                ':first_name' => $data['first_name'],
                ':last_name' => $data['last_name'],
                ':email' => $data['email'] ?? null,
                ':image_url' => $data['image_url'] ?? null
            ]);
            return $this->db->lastInsertId();
        } catch (\Exception $e) {
            // Likely duplicate entry
            return false;
        }
    }

    public function findByCode($orgId, $code) {
        $sql = "SELECT * FROM eligible_voters WHERE organization_id = ? AND identifier_code = ? LIMIT 1";
        return $this->db->queryOne($sql, [$orgId, $code]);
    }
    
    public function getByOrganization($orgId) {
        return $this->db->query("SELECT * FROM eligible_voters WHERE organization_id = ? ORDER BY last_name ASC", [$orgId]);
    }

    public function markAsRegistered($id, $userId) {
        $sql = "UPDATE eligible_voters SET is_registered = 1, user_id = ? WHERE id = ?";
        return $this->db->execute($sql, [$userId, $id]);
    }
}
