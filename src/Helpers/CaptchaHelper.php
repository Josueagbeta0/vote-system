<?php
namespace App\Helpers;

/**
 * CaptchaHelper - Vérification Google reCAPTCHA v3
 * 
 * Pour activer:
 * 1. Créer un compte sur https://www.google.com/recaptcha/admin
 * 2. Choisir reCAPTCHA v3
 * 3. Ajouter votre domaine (localhost pour dev)
 * 4. Copier les clés dans .env
 */

class CaptchaHelper {
    
    /**
     * Vérifier le token reCAPTCHA
     * 
     * @param string $token - Token reçu du formulaire
     * @param string $action - Action à vérifier (register, login, vote)
     * @return array ['success' => bool, 'score' => float, 'message' => string]
     */
    public static function verify($token, $action = 'submit') {
        // Récupérer la clé secrète depuis l'environnement
        $secretKey = getenv('RECAPTCHA_SECRET_KEY');
        
        // Si pas de clé configurée, bypass en mode dev
        if (empty($secretKey) || APP_DEBUG) {
            logMessage("CAPTCHA: Mode développement - vérification ignorée", 'INFO');
            return [
                'success' => true,
                'score' => 1.0,
                'message' => 'Mode développement'
            ];
        }
        
        // Préparer la requête vers Google
        $url = 'https://www.google.com/recaptcha/api/siteverify';
        $data = [
            'secret' => $secretKey,
            'response' => $token,
            'remoteip' => $_SERVER['REMOTE_ADDR'] ?? 'unknown'
        ];
        
        // Envoyer la requête
        $options = [
            'http' => [
                'header' => "Content-type: application/x-www-form-urlencoded\r\n",
                'method' => 'POST',
                'content' => http_build_query($data)
            ]
        ];
        
        $context = stream_context_create($options);
        $result = @file_get_contents($url, false, $context);
        
        if ($result === false) {
            logMessage("CAPTCHA: Erreur de connexion à Google", 'ERROR');
            // En cas d'erreur réseau, on laisse passer (éviter de bloquer l'inscription)
            return [
                'success' => true,
                'score' => 0.5,
                'message' => 'Erreur de vérification'
            ];
        }
        
        $response = json_decode($result);
        
        // Vérifier la réponse
        if (!$response || !$response->success) {
            logMessage("CAPTCHA: Vérification échouée", 'WARNING');
            return [
                'success' => false,
                'score' => 0,
                'message' => 'Vérification CAPTCHA échouée'
            ];
        }
        
        // Vérifier l'action
        if ($response->action !== $action) {
            logMessage("CAPTCHA: Action incorrecte - Attendu: $action, Reçu: {$response->action}", 'WARNING');
            return [
                'success' => false,
                'score' => 0,
                'message' => 'Action CAPTCHA invalide'
            ];
        }
        
        // Vérifier le score (0.0 = bot probable, 1.0 = humain probable)
        $minScore = 0.5; // Seuil recommandé
        
        if ($response->score < $minScore) {
            logMessage("CAPTCHA: Score trop faible ({$response->score}) - Possible bot", 'WARNING');
            return [
                'success' => false,
                'score' => $response->score,
                'message' => 'Vous semblez être un robot. Si vous êtes humain, veuillez réessayer.'
            ];
        }
        
        // Tout est OK
        logMessage("CAPTCHA: Vérification réussie - Score: {$response->score}", 'INFO');
        return [
            'success' => true,
            'score' => $response->score,
            'message' => 'Vérification réussie'
        ];
    }
    
    /**
     * Générer le script HTML pour reCAPTCHA v3
     * À inclure dans <head>
     */
    public static function getScriptTag() {
        $siteKey = getenv('RECAPTCHA_SITE_KEY');
        
        if (empty($siteKey)) {
            return '<!-- reCAPTCHA non configuré -->';
        }
        
        return "<script src='https://www.google.com/recaptcha/api.js?render={$siteKey}'></script>";
    }
    
    /**
     * Générer le code JavaScript pour soumettre avec reCAPTCHA
     * 
     * @param string $formId - ID du formulaire
     * @param string $action - Action à enregistrer
     */
    public static function getSubmitScript($formId, $action) {
        $siteKey = getenv('RECAPTCHA_SITE_KEY');
        
        if (empty($siteKey)) {
            return '<!-- reCAPTCHA non configuré -->';
        }
        
        return "
        <script>
        document.getElementById('{$formId}').addEventListener('submit', function(e) {
            e.preventDefault();
            
            grecaptcha.ready(function() {
                grecaptcha.execute('{$siteKey}', {action: '{$action}'}).then(function(token) {
                    // Ajouter le token au formulaire
                    let input = document.createElement('input');
                    input.type = 'hidden';
                    input.name = 'recaptcha_token';
                    input.value = token;
                    document.getElementById('{$formId}').appendChild(input);
                    
                    // Soumettre le formulaire
                    document.getElementById('{$formId}').submit();
                });
            });
        });
        </script>
        ";
    }
}