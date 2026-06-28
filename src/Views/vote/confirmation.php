<?php require_once __DIR__ . '/../layouts/header.php'; ?>

<div class="container py-5">
    <div class="row justify-content-center">
        <div class="col-lg-8">
            <!-- Animation de succès -->
            <div class="text-center mb-5">
                <div class="success-animation">
                    <svg class="checkmark" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 52 52">
                        <circle class="checkmark-circle" cx="26" cy="26" r="25" fill="none"/>
                        <path class="checkmark-check" fill="none" d="M14.1 27.2l7.1 7.2 16.7-16.8"/>
                    </svg>
                </div>
                <h1 class="display-4 text-success fw-bold mt-4">
                    Vote Enregistré !
                </h1>
                <p class="lead text-muted">
                    Merci d'avoir participé à cette élection
                </p>
            </div>
            
            <!-- Informations -->
            <div class="card border-0 shadow-lg mb-4">
                <div class="card-body p-4">
                    <h4 class="card-title mb-4">
                        <i class="bi bi-info-circle text-primary"></i> Détails de votre Vote
                    </h4>
                    
                    <div class="row g-3">
                        <div class="col-md-6">
                            <div class="p-3 bg-light rounded">
                                <small class="text-muted d-block mb-1">Élection</small>
                                <strong><?php echo e($election_title); ?></strong>
                            </div>
                        </div>
                        
                        <div class="col-md-6">
                            <div class="p-3 bg-light rounded">
                                <small class="text-muted d-block mb-1">Date et Heure</small>
                                <strong><?php echo date('d/m/Y à H:i:s'); ?></strong>
                            </div>
                        </div>
                        
                        <div class="col-12">
                            <div class="p-3 bg-success bg-opacity-10 rounded border border-success">
                                <small class="text-muted d-block mb-2">
                                    <i class="bi bi-key-fill text-success"></i> Token de Vérification
                                </small>
                                <div class="input-group">
                                    <input type="text" 
                                           class="form-control form-control-lg font-monospace" 
                                           id="verificationToken"
                                           value="<?php echo e($verification_token); ?>" 
                                           readonly>
                                    <button class="btn btn-success" 
                                            type="button" 
                                            onclick="copyToken()">
                                        <i class="bi bi-clipboard"></i> Copier
                                    </button>
                                </div>
                                <small class="text-muted mt-2 d-block">
                                    <i class="bi bi-info-circle"></i>
                                    Conservez ce token pour vérifier ultérieurement que votre vote a été comptabilisé
                                </small>
                            </div>
                        </div>
                        
                        <div class="col-12">
                            <div class="p-3 bg-light rounded">
                                <small class="text-muted d-block mb-1">
                                    <i class="bi bi-shield-check"></i> Hash de Sécurité
                                </small>
                                <code class="small"><?php echo e($vote_hash); ?>...</code>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            
            <!-- Garanties -->
            <div class="card border-0 shadow-lg mb-4">
                <div class="card-body p-4">
                    <h5 class="card-title mb-3">
                        <i class="bi bi-shield-lock text-success"></i> Garanties de Sécurité
                    </h5>
                    
                    <div class="row g-3">
                        <div class="col-md-6">
                            <div class="d-flex align-items-start">
                                <div class="flex-shrink-0">
                                    <div class="rounded-circle bg-success bg-opacity-10 p-3">
                                        <i class="bi bi-incognito text-success fs-4"></i>
                                    </div>
                                </div>
                                <div class="flex-grow-1 ms-3">
                                    <h6 class="mb-1">Anonymat Total</h6>
                                    <p class="text-muted small mb-0">
                                        Votre choix est complètement anonyme. Personne ne peut savoir pour qui vous avez voté.
                                    </p>
                                </div>
                            </div>
                        </div>
                        
                        <div class="col-md-6">
                            <div class="d-flex align-items-start">
                                <div class="flex-shrink-0">
                                    <div class="rounded-circle bg-success bg-opacity-10 p-3">
                                        <i class="bi bi-lock-fill text-success fs-4"></i>
                                    </div>
                                </div>
                                <div class="flex-grow-1 ms-3">
                                    <h6 class="mb-1">Chiffrement AES-256</h6>
                                    <p class="text-muted small mb-0">
                                        Votre vote est chiffré avec un algorithme militaire de niveau bancaire.
                                    </p>
                                </div>
                            </div>
                        </div>
                        
                        <div class="col-md-6">
                            <div class="d-flex align-items-start">
                                <div class="flex-shrink-0">
                                    <div class="rounded-circle bg-success bg-opacity-10 p-3">
                                        <i class="bi bi-check-circle-fill text-success fs-4"></i>
                                    </div>
                                </div>
                                <div class="flex-grow-1 ms-3">
                                    <h6 class="mb-1">Intégrité Garantie</h6>
                                    <p class="text-muted small mb-0">
                                        Votre vote est protégé par une blockchain et ne peut pas être modifié.
                                    </p>
                                </div>
                            </div>
                        </div>
                        
                        <div class="col-md-6">
                            <div class="d-flex align-items-start">
                                <div class="flex-shrink-0">
                                    <div class="rounded-circle bg-success bg-opacity-10 p-3">
                                        <i class="bi bi-eye-fill text-success fs-4"></i>
                                    </div>
                                </div>
                                <div class="flex-grow-1 ms-3">
                                    <h6 class="mb-1">Vérifiable</h6>
                                    <p class="text-muted small mb-0">
                                        Vous pouvez vérifier à tout moment que votre vote a été comptabilisé.
                                    </p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            
            <!-- Email de confirmation -->
            <div class="alert alert-info">
                <i class="bi bi-envelope-check"></i>
                <strong>Email envoyé !</strong> Un email de confirmation a été envoyé à 
                <strong><?php echo e($_SESSION['user']['email']); ?></strong>
                avec votre token de vérification.
            </div>
            
            <!-- Actions -->
            <div class="d-flex gap-3 justify-content-center">
                <a href="<?php echo BASE_URL; ?>/voter/dashboard" class="btn btn-primary btn-lg px-5">
                    <i class="bi bi-house-door"></i> Retour au Dashboard
                </a>
                <a href="<?php echo BASE_URL; ?>/vote/verify" class="btn btn-outline-success btn-lg px-5">
                    <i class="bi bi-shield-check"></i> Vérifier mon Vote
                </a>
            </div>
        </div>
    </div>
</div>

<style>
.success-animation {
    margin: 0 auto;
    width: 150px;
}

.checkmark {
    width: 150px;
    height: 150px;
    border-radius: 50%;
    display: block;
    stroke-width: 2;
    stroke: #10B981;
    stroke-miterlimit: 10;
    box-shadow: inset 0px 0px 0px #10B981;
    animation: fill .4s ease-in-out .4s forwards, scale .3s ease-in-out .9s both;
}

.checkmark-circle {
    stroke-dasharray: 166;
    stroke-dashoffset: 166;
    stroke-width: 2;
    stroke-miterlimit: 10;
    stroke: #10B981;
    fill: none;
    animation: stroke 0.6s cubic-bezier(0.65, 0, 0.45, 1) forwards;
}

.checkmark-check {
    transform-origin: 50% 50%;
    stroke-dasharray: 48;
    stroke-dashoffset: 48;
    stroke: #10B981;
    animation: stroke 0.3s cubic-bezier(0.65, 0, 0.45, 1) 0.8s forwards;
}

@keyframes stroke {
    100% {
        stroke-dashoffset: 0;
    }
}

@keyframes scale {
    0%, 100% {
        transform: none;
    }
    50% {
        transform: scale3d(1.1, 1.1, 1);
    }
}

@keyframes fill {
    100% {
        box-shadow: inset 0px 0px 0px 60px #10B981;
    }
}
</style>

<script>
function copyToken() {
    const tokenInput = document.getElementById('verificationToken');
    tokenInput.select();
    document.execCommand('copy');
    
    // Feedback visuel
    const btn = event.target.closest('button');
    const originalHTML = btn.innerHTML;
    btn.innerHTML = '<i class="bi bi-check"></i> Copié !';
    btn.classList.remove('btn-success');
    btn.classList.add('btn-secondary');
    
    setTimeout(() => {
        btn.innerHTML = originalHTML;
        btn.classList.remove('btn-secondary');
        btn.classList.add('btn-success');
    }, 2000);
}

// Animation d'entrée
document.addEventListener('DOMContentLoaded', function() {
    setTimeout(() => {
        document.querySelector('.success-animation').style.opacity = '1';
    }, 100);
});
</script>

<?php require_once __DIR__ . '/../layouts/footer.php'; ?>