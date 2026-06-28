<?php require_once __DIR__ . '/../layouts/header.php'; ?>

<div class="container py-5">
    <div class="row justify-content-center">
        <div class="col-md-6">
            <div class="card border-0 shadow-lg">
                <div class="card-body p-5 text-center">
                    <i class="bi bi-envelope-check text-success" style="font-size: 5rem;"></i>
                    <h2 class="mt-4 mb-3">Vérifiez votre email</h2>
                    <p class="text-muted mb-4">
                        Un email de vérification a été envoyé à <strong><?php echo e($_SESSION['user']['email'] ?? 'votre adresse'); ?></strong>
                    </p>
                    
                    <div class="alert alert-info text-start">
                        <h6><i class="bi bi-info-circle"></i> Prochaines étapes :</h6>
                        <ol class="mb-0">
                            <li>Consultez votre boîte de réception</li>
                            <li>Cliquez sur le lien de vérification</li>
                            <li>Revenez sur cette page pour vous connecter</li>
                        </ol>
                    </div>
                    
                    <div class="mt-4">
                        <p class="text-muted small">Vous n'avez pas reçu l'email ?</p>
                        <form method="POST" action="<?php echo BASE_URL; ?>/auth/resend-verification">
                            <input type="hidden" name="csrf_token" value="<?php echo generateCsrfToken(); ?>">
                            <button type="submit" class="btn btn-outline-primary">
                                <i class="bi bi-arrow-repeat"></i> Renvoyer l'email
                            </button>
                        </form>
                    </div>
                    
                    <hr class="my-4">
                    
                    <div>
                        <a href="<?php echo BASE_URL; ?>/auth/login" class="btn btn-primary">
                            <i class="bi bi-box-arrow-in-right"></i> Aller à la connexion
                        </a>
                    </div>
                </div>
            </div>
            
            <!-- Tips -->
            <div class="card border-0 shadow-sm mt-4">
                <div class="card-body">
                    <h6 class="card-title">
                        <i class="bi bi-lightbulb text-warning"></i> Astuces
                    </h6>
                    <ul class="small text-muted mb-0">
                        <li>Vérifiez votre dossier spam/courrier indésirable</li>
                        <li>L'email peut prendre quelques minutes à arriver</li>
                        <li>Le lien expire après 24 heures</li>
                    </ul>
                </div>
            </div>
        </div>
    </div>
</div>

<?php require_once __DIR__ . '/../layouts/footer.php'; ?>