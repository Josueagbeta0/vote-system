<?php

namespace App\Jobs;

use App\Services\EmailService;

class SendVoteConfirmationJob {
    public function handle($data) {
        $emailService = new EmailService();
        $emailService->sendVoteConfirmationImmediate(
            $data['email'], 
            $data['user_name'], 
            $data['election_title'],
            $data['verification_code']
        );
    }
}
