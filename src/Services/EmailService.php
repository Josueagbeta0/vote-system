<?php
namespace App\Services;

use App\Services\QueueService;

/**
 * EmailService - Gestion des emails (vérification, notifications)
 */

class EmailService {
    private $queueService;
    
    public function __construct() {
        $this->queueService = new QueueService();
    }
    
    /**
     * Envoyer un email de vérification (Via Queue)
     */
    public function sendVerificationEmail($email, $token, $userName) {
        return $this->queueService->push(\App\Jobs\SendVerificationEmailJob::class, [
            'email' => $email,
            'token' => $token,
            'user_name' => $userName
        ]);
    }

    /**
     * Envoyer un email de vérification (Immédiat)
     */
    public function sendVerificationEmailImmediate($email, $token, $userName) {
        $verificationLink = BASE_URL . "/auth/verify-email?token=" . $token;
        
        $subject = "Vérifiez votre adresse email - " . APP_NAME;
        
        $message = $this->getVerificationEmailTemplate($userName, $verificationLink);
        
        // Headers pour HTML
        $headers = "MIME-Version: 1.0" . "\r\n";
        $headers .= "Content-type:text/html;charset=UTF-8" . "\r\n";
        $headers .= "From: " . APP_NAME . " <noreply@votesystem.com>" . "\r\n";
        
        // En développement, on log au lieu d'envoyer
        if (APP_DEBUG) {
            logMessage("EMAIL DE VÉRIFICATION pour $email : $verificationLink");
            
            // Sauvegarder dans un fichier pour le développement
            $emailLog = STORAGE_PATH . '/logs/emails.log';
            $logContent = "\n\n=== EMAIL DE VÉRIFICATION ===\n";
            $logContent .= "Date: " . date('Y-m-d H:i:s') . "\n";
            $logContent .= "Pour: $email\n";
            $logContent .= "Lien: $verificationLink\n";
            $logContent .= "========================\n";
            file_put_contents($emailLog, $logContent, FILE_APPEND);
            
            return true;
        }
        
        // En production, envoyer vraiment l'email
        return mail($email, $subject, $message, $headers);
    }
    
    /**
     * Template HTML pour l'email de vérification
     */
    private function getVerificationEmailTemplate($userName, $link) {
        return "
        <!DOCTYPE html>
        <html>
        <head>
            <meta charset='UTF-8'>
            <style>
                body { font-family: Arial, sans-serif; line-height: 1.6; color: #333; }
                .container { max-width: 600px; margin: 0 auto; padding: 20px; }
                .header { background: linear-gradient(135deg, #4F46E5 0%, #6366F1 100%); 
                         color: white; padding: 30px; text-align: center; border-radius: 10px 10px 0 0; }
                .content { background: #f9fafb; padding: 30px; border-radius: 0 0 10px 10px; }
                .button { display: inline-block; background: #4F46E5; color: white; 
                         padding: 15px 30px; text-decoration: none; border-radius: 5px; 
                         font-weight: bold; margin: 20px 0; }
                .footer { text-align: center; margin-top: 20px; color: #6b7280; font-size: 12px; }
            </style>
        </head>
        <body>
            <div class='container'>
                <div class='header'>
                    <h1>🔐 Vérification de votre compte</h1>
                </div>
                <div class='content'>
                    <p>Bonjour <strong>$userName</strong>,</p>
                    
                    <p>Merci de vous être inscrit sur <strong>" . APP_NAME . "</strong> !</p>
                    
                    <p>Pour finaliser votre inscription et sécuriser votre compte, veuillez cliquer sur le bouton ci-dessous pour vérifier votre adresse email :</p>
                    
                    <center>
                        <a href='$link' class='button'>✓ Vérifier mon email</a>
                    </center>
                    
                    <p style='color: #6b7280; font-size: 14px;'>
                        Si le bouton ne fonctionne pas, copiez et collez ce lien dans votre navigateur :<br>
                        <a href='$link'>$link</a>
                    </p>
                    
                    <p style='color: #ef4444; font-weight: bold;'>
                        ⚠️ Ce lien expire dans 24 heures.
                    </p>
                    
                    <p style='color: #6b7280; font-size: 13px; margin-top: 30px;'>
                        Si vous n'avez pas créé de compte sur notre plateforme, vous pouvez ignorer cet email en toute sécurité.
                    </p>
                </div>
                <div class='footer'>
                    <p>&copy; " . date('Y') . " " . APP_NAME . " - Système de vote sécurisé</p>
                    <p>Cet email a été envoyé automatiquement, merci de ne pas y répondre.</p>
                </div>
            </div>
        </body>
        </html>
        ";
    }
    
    /**
     * Générer un token de vérification
     */
    public function generateVerificationToken() {
        return bin2hex(random_bytes(32));
    }
    
    /**
     * Envoyer une notification de vote (Via Queue)
     */
    public function sendVoteConfirmation($email, $userName, $electionTitle, $verificationCode) {
        return $this->queueService->push(\App\Jobs\SendVoteConfirmationJob::class, [
            'email' => $email,
            'user_name' => $userName,
            'election_title' => $electionTitle,
            'verification_code' => $verificationCode
        ]);
    }

    /**
     * Envoyer une notification de vote (Immédiat)
     */
    public function sendVoteConfirmationImmediate($email, $userName, $electionTitle, $verificationCode) {
        $subject = "Confirmation de vote - " . APP_NAME;
        
        $message = "
        <!DOCTYPE html>
        <html>
        <head>
            <meta charset='UTF-8'>
            <style>
                body { font-family: Arial, sans-serif; line-height: 1.6; color: #333; }
                .container { max-width: 600px; margin: 0 auto; padding: 20px; }
                .header { background: linear-gradient(135deg, #10B981 0%, #059669 100%); 
                         color: white; padding: 30px; text-align: center; border-radius: 10px 10px 0 0; }
                .content { background: #f9fafb; padding: 30px; border-radius: 0 0 10px 10px; }
                .code-box { background: white; border: 2px dashed #10B981; 
                           padding: 20px; text-align: center; border-radius: 5px; 
                           font-size: 24px; font-weight: bold; letter-spacing: 3px; 
                           color: #10B981; margin: 20px 0; }
                .footer { text-align: center; margin-top: 20px; color: #6b7280; font-size: 12px; }
            </style>
        </head>
        <body>
            <div class='container'>
                <div class='header'>
                    <h1>✓ Vote enregistré avec succès</h1>
                </div>
                <div class='content'>
                    <p>Bonjour <strong>$userName</strong>,</p>
                    
                    <p>Votre vote pour l'élection <strong>\"$electionTitle\"</strong> a été enregistré avec succès.</p>
                    
                    <p><strong>Code de vérification :</strong></p>
                    <div class='code-box'>$verificationCode</div>
                    
                    <p style='color: #6b7280; font-size: 14px;'>
                        Conservez ce code pour vérifier ultérieurement que votre vote a bien été comptabilisé.
                        Votre choix reste strictement anonyme.
                    </p>
                    
                    <p style='color: #10B981; font-weight: bold;'>
                        🔒 Votre vote est sécurisé et anonyme
                    </p>
                </div>
                <div class='footer'>
                    <p>&copy; " . date('Y') . " " . APP_NAME . "</p>
                </div>
            </div>
        </body>
        </html>
        ";
        
        $headers = "MIME-Version: 1.0" . "\r\n";
        $headers .= "Content-type:text/html;charset=UTF-8" . "\r\n";
        $headers .= "From: " . APP_NAME . " <noreply@votesystem.com>" . "\r\n";
        
        if (APP_DEBUG) {
            logMessage("EMAIL CONFIRMATION VOTE pour $email : Code $verificationCode");
            return true;
        }
        
        return mail($email, $subject, $message, $headers);
    }
}
