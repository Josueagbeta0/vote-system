<?php
namespace App\Config;

use PDO;
use PDOException;
use Exception;

/**
 * Classe Database - Gestion de la connexion à la base de données
 * Utilise PDO avec préparation des requêtes pour la sécurité
 */

class Database {
    private static $instance = null;
    private $connection;
    
    // Configuration de la base de données
    private $host;
    private $dbname;
    private $username;
    private $password;
    private $charset;
    
    /**
     * Constructeur privé (Singleton)
     */
    private function __construct() {
        // Les variables d'environnement sont chargées par phpdotenv dans index.php
        
        $this->host = $_ENV['DB_HOST'] ?? 'localhost';
        $this->dbname = $_ENV['DB_NAME'] ?? 'vote_system';
        $this->username = $_ENV['DB_USER'] ?? 'root';
        $this->password = $_ENV['DB_PASSWORD'] ?? '';
        $this->charset = $_ENV['DB_CHARSET'] ?? 'utf8mb4';
        
        $this->connect();
    }
    
    /**
     * Établir la connexion à la base de données
     */
    private function connect() {
        try {
            $dsn = "mysql:host={$this->host};dbname={$this->dbname};charset={$this->charset}";
            
            $options = [
                PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                PDO::ATTR_EMULATE_PREPARES => false,
                PDO::MYSQL_ATTR_INIT_COMMAND => "SET NAMES {$this->charset}"
            ];
            
            $this->connection = new PDO($dsn, $this->username, $this->password, $options);
            
        } catch (PDOException $e) {
            // Log l'erreur (en production, ne pas afficher)
            error_log("Erreur de connexion DB: " . $e->getMessage());
            
            // Toujours afficher l'erreur en mode debug pour faciliter le débogage
            if (($_ENV['APP_DEBUG'] ?? 'false') === 'true' || defined('APP_DEBUG') && APP_DEBUG) {
                die("<h1 style='color: red;'>Erreur de connexion à la base de données</h1><p>" . htmlspecialchars($e->getMessage()) . "</p><p><strong>Vérifiez votre fichier .env et que MySQL est démarré.</strong></p>");
            } else {
                die("Erreur de connexion à la base de données. Contactez l'administrateur.");
            }
        }
    }
    
    /**
     * Récupérer l'instance unique (Singleton)
     */
    public static function getInstance() {
        if (self::$instance === null) {
            self::$instance = new self();
        }
        return self::$instance;
    }
    
    /**
     * Récupérer la connexion PDO
     */
    public function getConnection() {
        return $this->connection;
    }
    
    /**
     * Exécuter une requête SELECT
     * @param string $query - Requête SQL
     * @param array $params - Paramètres (optionnel)
     * @return array - Résultats
     */
    public function query($query, $params = []) {
        try {
            $stmt = $this->connection->prepare($query);
            $stmt->execute($params);
            return $stmt->fetchAll();
        } catch (PDOException $e) {
            error_log("Erreur requête: " . $e->getMessage());
            throw $e;
        }
    }
    
    /**
     * Exécuter une requête SELECT et retourner une seule ligne
     * @param string $query - Requête SQL
     * @param array $params - Paramètres (optionnel)
     * @return array|false - Résultat ou false
     */
    public function queryOne($query, $params = []) {
        try {
            $stmt = $this->connection->prepare($query);
            $stmt->execute($params);
            return $stmt->fetch();
        } catch (PDOException $e) {
            error_log("Erreur requête: " . $e->getMessage());
            throw $e;
        }
    }
    
    /**
     * Exécuter une requête INSERT, UPDATE, DELETE
     * @param string $query - Requête SQL
     * @param array $params - Paramètres (optionnel)
     * @return bool - Succès ou échec
     */
    public function execute($query, $params = []) {
        try {
            $stmt = $this->connection->prepare($query);
            return $stmt->execute($params);
        } catch (PDOException $e) {
            error_log("Erreur exécution: " . $e->getMessage());
            throw $e;
        }
    }
    
    /**
     * Récupérer le dernier ID inséré
     */
    public function lastInsertId() {
        return $this->connection->lastInsertId();
    }
    
    /**
     * Démarrer une transaction
     */
    public function beginTransaction() {
        return $this->connection->beginTransaction();
    }
    
    /**
     * Valider une transaction
     */
    public function commit() {
        return $this->connection->commit();
    }
    
    /**
     * Annuler une transaction
     */
    public function rollback() {
        return $this->connection->rollBack();
    }
    
    /**
     * Empêcher le clonage (Singleton)
     */
    private function __clone() {}
    
    /**
     * Empêcher la désérialisation (Singleton)
     */
    public function __wakeup() {
        throw new Exception("Cannot unserialize singleton");
    }
}
