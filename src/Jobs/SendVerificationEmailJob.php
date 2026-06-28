<?php
namespace App\Jobs;

use App\Services\EmailService;

class SendVerificationEmailJob {
    public function handle($data) {
        $emailService = new EmailService();
        $emailService->sendVerificationEmailImmediate(
            $data['email'], 
            $data['token'], 
            $data['user_name']
        );
    }
}
