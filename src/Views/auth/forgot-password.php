<?php require_once __DIR__ . '/../layouts/header.php'; ?>

<div class="container py-5">
    <div class="row justify-content-center">
        <div class="col-md-5">
            <div class="card border-0 shadow-lg">
                <div class="card-body p-5">
                    <div class="text-center mb-4">
                        <i class="bi bi-key text-warning" style="font-size: 4rem;"></i>
                        <h2 class="fw-bold mt-3">Mot de passe oublié ?</h2>
                        <p class="text-muted">Pas de problème, nous allons vous aider</p>
                    </div>
                    
                    <form method="POST" action="<?php echo BASE_URL; ?>/auth/process-forgot-password">
                        <!-- CSRF Token -->
                        <input type="hidden" name="csrf_token" value="<?php echo generateCsrfToken(); ?>">
                        
                        <!-- Email -->
                        <div class="mb-4">
                            <label for="email" class="form-label">
                                <i class="bi bi-envelope"></i> Adresse email
                            </label>
                            <input type="email" 
                                   class="form-control form-control-lg" 
                                   id="email" 
                                   name="email" 
                                   placeholder="votre@email.com"
                                   required 
                                   autofocus>
                            <small class="text-muted">
                                Entrez l'email utilisé lors de l'inscription
                            </small>
                        </div>
                        
                        <!-- Info -->
                        <div class="alert alert-info">
                            <i class="bi bi-info-circle"></i>
                            <small>
                                Nous vous enverrons un lien pour réinitialiser votre mot de passe. 
                                Ce lien expirera dans 1 heure.
                            </small>
                        </div>
                        
                        <!-- Submit Button -->
                        <button type="submit" class="btn btn-warning btn-lg w-100 mb-3">
                            <i class="bi bi-send"></i> Envoyer le lien
                        </button>
                        
                        <!-- Back to login -->
                        <div class="text-center">
                            <a href="<?php echo BASE_URL; ?>/auth/login" class="text-decoration-none">
                                <i class="bi bi-arrow-left"></i> Retour à la connexion
                            </a>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

<?php require_once __DIR__ . '/../layouts/footer.php'; ?>