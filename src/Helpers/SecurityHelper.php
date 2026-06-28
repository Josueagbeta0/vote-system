<?php
namespace App\Helpers;

/**
 * SecurityHelper - Fonctions de sécurité
 */

class SecurityHelper {
    
    /**
     * Nettoyer les données d'entrée (XSS prevention)
     */
    public static function sanitize($data) {
        if (is_array($data)) {
            foreach ($data as $key => $value) {
                $data[$key] = self::sanitize($value);
            }
            return $data;
        }
        
        return htmlspecialchars(strip_tags(trim($data)), ENT_QUOTES, 'UTF-8');
    }
    
    /**
     * Valider un email
     */
    public static function validateEmail($email) {
        return filter_var($email, FILTER_VALIDATE_EMAIL) !== false;
    }
    
    /**
     * Valider un mot de passe fort
     */
    public static function validatePassword($password) {
        $errors = [];
        
        // Longueur minimale
        if (strlen($password) < PASSWORD_MIN_LENGTH) {
            $errors[] = "Le mot de passe doit contenir au moins " . PASSWORD_MIN_LENGTH . " caractères";
        }
        
        // Au moins une majuscule
        if (!preg_match('/[A-Z]/', $password)) {
            $errors[] = "Le mot de passe doit contenir au moins une majuscule";
        }
        
        // Au moins une minuscule
        if (!preg_match('/[a-z]/', $password)) {
            $errors[] = "Le mot de passe doit contenir au moins une minuscule";
        }
        
        // Au moins un chiffre
        if (!preg_match('/[0-9]/', $password)) {
            $errors[] = "Le mot de passe doit contenir au moins un chiffre";
        }
        
        // Au moins un caractère spécial
        if (!preg_match('/[^A-Za-z0-9]/', $password)) {
            $errors[] = "Le mot de passe doit contenir au moins un caractère spécial";
        }
        
        return [
            'valid' => empty($errors),
            'errors' => $errors
        ];
    }
    
    /**
     * Générer un mot de passe aléatoire sécurisé
     */
    public static function generatePassword($length = 12) {
        $chars = 'abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789!@#$%^&*()';
        $password = '';
        
        for ($i = 0; $i < $length; $i++) {
            $password .= $chars[random_int(0, strlen($chars) - 1)];
        }
        
        return $password;
    }
    
    /**
     * Vérifier les tentatives de connexion (Rate limiting)
     */
    public static function checkLoginAttempts($email) {
        $key = 'login_attempts_' . md5($email);
        
        if (!isset($_SESSION[$key])) {
            $_SESSION[$key] = [
                'count' => 0,
                'first_attempt' => time(),
                'locked_until' => 0
            ];
        }
        
        $attempts = $_SESSION[$key];
        
        // Vérifier si le compte est verrouillé
        if ($attempts['locked_until'] > time()) {
            $remainingTime = ceil(($attempts['locked_until'] - time()) / 60);
            return [
                'allowed' => false,
                'message' => "Compte temporairement verrouillé. Réessayez dans $remainingTime minute(s)."
            ];
        }
        
        // Réinitialiser si plus de 15 minutes depuis la première tentative
        if (time() - $attempts['first_attempt'] > 900) {
            $_SESSION[$key] = [
                'count' => 0,
                'first_attempt' => time(),
                'locked_until' => 0
            ];
        }
        
        return ['allowed' => true];
    }
    
    /**
     * Enregistrer une tentative de connexion échouée
     */
    public static function recordFailedLogin($email) {
        $key = 'login_attempts_' . md5($email);
        
        if (!isset($_SESSION[$key])) {
            $_SESSION[$key] = [
                'count' => 0,
                'first_attempt' => time(),
                'locked_until' => 0
            ];
        }
        
        $_SESSION[$key]['count']++;
        
        // Verrouiller après MAX_LOGIN_ATTEMPTS tentatives
        if ($_SESSION[$key]['count'] >= MAX_LOGIN_ATTEMPTS) {
            $_SESSION[$key]['locked_until'] = time() + (LOGIN_BLOCK_DURATION * 60);
            
            logMessage("Compte verrouillé pour tentatives multiples : $email", 'WARNING');
        }
    }
    
    /**
     * Réinitialiser les tentatives de connexion après succès
     */
    public static function resetLoginAttempts($email) {
        $key = 'login_attempts_' . md5($email);
        unset($_SESSION[$key]);
    }
    
    /**
     * Valider un fichier uploadé (image)
     */
    public static function validateUploadedImage($file) {
        $errors = [];
        
        // Vérifier si un fichier a été uploadé
        if (!isset($file['tmp_name']) || empty($file['tmp_name'])) {
            return ['valid' => false, 'errors' => ['Aucun fichier sélectionné']];
        }
        
        // Vérifier les erreurs d'upload
        if ($file['error'] !== UPLOAD_ERR_OK) {
            $errors[] = 'Erreur lors de l\'upload du fichier';
        }
        
        // Vérifier la taille
        if ($file['size'] > UPLOAD_MAX_SIZE) {
            $errors[] = 'Le fichier est trop volumineux (max ' . (UPLOAD_MAX_SIZE / 1024 / 1024) . ' MB)';
        }
        
        // Vérifier le type MIME
        $finfo = finfo_open(FILEINFO_MIME_TYPE);
        $mimeType = finfo_file($finfo, $file['tmp_name']);
        finfo_close($finfo);
        
        if (!in_array($mimeType, ALLOWED_IMAGE_TYPES)) {
            $errors[] = 'Format de fichier non autorisé (JPG, PNG uniquement)';
        }
        
        // Vérifier que c'est vraiment une image
        if (!getimagesize($file['tmp_name'])) {
            $errors[] = 'Le fichier n\'est pas une image valide';
        }
        
        return [
            'valid' => empty($errors),
            'errors' => $errors,
            'mime_type' => $mimeType
        ];
    }
    
    /**
     * Générer un nom de fichier sécurisé et unique
     */
    public static function generateSecureFilename($originalName) {
        $extension = pathinfo($originalName, PATHINFO_EXTENSION);
        $filename = bin2hex(random_bytes(16)) . '_' . time();
        
        return $filename . '.' . strtolower($extension);
    }
    
    /**
     * Protéger contre les attaques par injection SQL (validation supplémentaire)
     */
    public static function containsSqlInjection($string) {
        $sqlKeywords = [
            'SELECT', 'INSERT', 'UPDATE', 'DELETE', 'DROP', 'CREATE', 'ALTER',
            'UNION', 'WHERE', 'FROM', 'TABLE', 'DATABASE', '--', '/*', '*/'
        ];
        
        $upper = strtoupper($string);
        
        foreach ($sqlKeywords as $keyword) {
            if (strpos($upper, $keyword) !== false) {
                return true;
            }
        }
        
        return false;
    }
    
    /**
     * Générer un token aléatoire sécurisé
     */
    public static function generateToken($length = 32) {
        return bin2hex(random_bytes($length));
    }
    
    /**
     * Hasher une donnée (pour vérification d'intégrité)
     */
    public static function hash($data) {
        return hash('sha256', $data);
    }
    
    /**
     * Vérifier l'origine de la requête (CSRF basique)
     */
    public static function verifyRequestOrigin() {
        if (!isset($_SERVER['HTTP_REFERER'])) {
            return false;
        }
        
        $referer = parse_url($_SERVER['HTTP_REFERER']);
        $host = $_SERVER['HTTP_HOST'];
        
        return $referer['host'] === $host;
    }
    
    /**
     * Logger une activité suspecte
     */
    public static function logSuspiciousActivity($activity, $details = []) {
        $ip = $_SERVER['REMOTE_ADDR'] ?? 'unknown';
        $userAgent = $_SERVER['HTTP_USER_AGENT'] ?? 'unknown';
        
        $logData = [
            'timestamp' => date('Y-m-d H:i:s'),
            'ip' => $ip,
            'user_agent' => $userAgent,
            'activity' => $activity,
            'details' => $details
        ];
        
        $logMessage = json_encode($logData);
        
        // Logger dans le fichier de sécurité
        $logFile = LOG_PATH . '/security.log';
        file_put_contents($logFile, $logMessage . PHP_EOL, FILE_APPEND);
        
        logMessage("Activité suspecte : $activity", 'WARNING');
    }
    
    /**
     * Vérifier si l'IP est bloquée par Rate Limit (Base de données)
     * @param string $type Type d'activité (login_attempt, register_attempt, etc.)
     * @param int $limit Nombre max d'essais
     * @param int $windowTime Fenêtre de temps en secondes
     */
    public static function checkRateLimit($type, $limit = 5, $windowTime = 300) {
        // En mode CLI ou sans DB, on bypass (sauf si DB dispo)
        try {
            $db = \App\Config\Database::getInstance();
        } catch (\Exception $e) {
            return ['allowed' => true];
        }

        $ip = $_SERVER['REMOTE_ADDR'] ?? 'unknown';
        
        // Nettoyage des vieux logs (optimisation simple, 1 chance sur 100)
        if (rand(1, 100) === 1) {
            $db->execute("DELETE FROM security_logs WHERE created_at < NOW() - INTERVAL 24 HOUR");
        }

        // Compter les tentatives récentes
        $count = $db->queryOne(
            "SELECT COUNT(*) as cnt FROM security_logs 
             WHERE ip_address = :ip 
             AND activity_type = :type 
             AND created_at > NOW() - INTERVAL :window SECOND",
            [
                ':ip' => $ip, 
                ':type' => $type,
                ':window' => $windowTime
            ]
        );

        if ($count && $count['cnt'] >= $limit) {
            $wait = ceil($windowTime / 60);
            return [
                'allowed' => false,
                'message' => "Trop de tentatives. Veuillez réessayer dans {$wait} minutes."
            ];
        }

        return ['allowed' => true];
    }

    /**
     * Enregistrer une activité pour le Rate Limiting
     */
    public static function recordActivity($type, $details = null) {
        try {
            $db = \App\Config\Database::getInstance();
            $ip = $_SERVER['REMOTE_ADDR'] ?? 'unknown';
            
            $db->execute(
                "INSERT INTO security_logs (ip_address, activity_type, details) VALUES (:ip, :type, :details)",
                [
                    ':ip' => $ip,
                    ':type' => $type,
                    ':details' => $details ? json_encode($details) : null
                ]
            );
        } catch (\Exception $e) {
            // Fail silently to not break flow log
        }
    }

    /**
     * Vérifier si l'IP est dans la liste noire
     */
    public static function isBlacklistedIP($ip) {
        // À personnaliser : liste des IPs bloquées
        $blacklist = [];
        
        return in_array($ip, $blacklist);
    }
    /**
     * Vérifier le ReCaptcha Google v3
     */
    public static function verifyRecaptcha($token) {
        $secretKey = getenv('RECAPTCHA_SECRET_KEY') ?: $_ENV['RECAPTCHA_SECRET_KEY'];
        
        if (empty($secretKey)) {
            // Si pas de clé configurée, on laisse passer (mode dev)
            return true;
        }
        
        $url = 'https://www.google.com/recaptcha/api/siteverify';
        $data = [
            'secret' => $secretKey,
            'response' => $token,
            'remoteip' => $_SERVER['REMOTE_ADDR']
        ];
        
        $options = [
            'http' => [
                'header' => "Content-type: application/x-www-form-urlencoded\r\n",
                'method' => 'POST',
                'content' => http_build_query($data)
            ]
        ];
        
        $context = stream_context_create($options);
        $result = file_get_contents($url, false, $context);
        $response = json_decode($result);
        
        // On vérifie le succès et le score (pour v3, score entre 0.0 et 1.0)
        // 0.5 est un seuil standard
        return $response->success && $response->score >= 0.5;
    }
}