<?php
namespace App\Models;

use App\Config\Database;

class Organization {
    private $db;

    public function __construct() {
        $this->db = Database::getInstance();
    }

    public function create($data) {
        $sql = "INSERT INTO organizations (name, code, type, slug, logo_url, primary_color) VALUES (:name, :code, :type, :slug, :logo, :color)";
        try {
            $this->db->execute($sql, [
                ':name' => $data['name'],
                ':code' => $data['code'],
                ':type' => $data['type'] ?? 'other',
                ':slug' => $data['slug'],
                ':logo' => $data['logo_url'] ?? null,
                ':color' => $data['primary_color'] ?? '#0d6efd' 
            ]);
            return $this->db->lastInsertId();
        } catch (\Exception $e) {
            logMessage("Erreur création d'organisation: " . $e->getMessage());
            return false;
        }
    }

    public function update($id, $data) {
        $fields = [];
        $params = [':id' => $id];
        
        if (isset($data['name'])) { $fields[] = "name = :name"; $params[':name'] = $data['name']; }
        if (isset($data['code'])) { $fields[] = "code = :code"; $params[':code'] = $data['code']; }
        if (isset($data['logo_url'])) { $fields[] = "logo_url = :logo"; $params[':logo'] = $data['logo_url']; }
        if (isset($data['primary_color'])) { $fields[] = "primary_color = :color"; $params[':color'] = $data['primary_color']; }
        
        if (empty($fields)) return true;
        
        $sql = "UPDATE organizations SET " . implode(', ', $fields) . " WHERE id = :id";
        
        try {
            return $this->db->execute($sql, $params);
        } catch (\Exception $e) {
            return false;
        }
    }

    public function getAll() {
        return $this->db->query("SELECT * FROM organizations ORDER BY created_at DESC");
    }

    public function findById($id) {
        return $this->db->queryOne("SELECT * FROM organizations WHERE id = ?", [$id]);
    }
    
    public function findByCode($code) {
        return $this->db->queryOne("SELECT * FROM organizations WHERE code = ?", [$code]);
    }

    public function delete($id) {
        return $this->db->execute("DELETE FROM organizations WHERE id = ?", [$id]);
    }

    public function findBySlug($slug) {
        return $this->db->queryOne("SELECT * FROM organizations WHERE slug = ?", [$slug]);
    }

    public function getByType($type) {
        return $this->db->query("SELECT * FROM organizations WHERE type = ? ORDER BY name ASC", [$type]);
    }

    public function search($query, $type = null) {
        $sql = "SELECT * FROM organizations WHERE (name LIKE :query OR code LIKE :query)";
        $params = [':query' => "%$query%"];
        
        if ($type) {
            $sql .= " AND type = :type";
            $params[':type'] = $type;
        }
        
        $sql .= " ORDER BY name ASC";
        return $this->db->query($sql, $params);
    }
}
