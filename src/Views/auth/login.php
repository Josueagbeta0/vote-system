<?php require_once __DIR__ . '/../layouts/header.php'; ?>

<div class="container">
    <div class="row justify-content-center mt-5">
        <div class="col-md-5">
            <div class="card shadow-lg">
                <div class="card-body p-5">
                    <div class="text-center mb-4">
                        <?php if (isset($org) && $org): ?>
                            <div class="mb-3">
                                <span class="badge bg-primary fs-6"><?php echo e($org['name']); ?></span>
                            </div>
                        <?php endif; ?>
                        <i class="bi bi-shield-lock text-primary icon-lg"></i>
                        <h2 class="fw-bold mt-3">Connexion</h2>
                        <p class="text-muted">Accédez à votre espace de vote</p>
                    </div>
                    
                    <form method="POST" action="<?php echo BASE_URL; ?>/auth/process-login" id="loginForm">
                        <!-- CSRF Token -->
                        <input type="hidden" name="csrf_token" value="<?php echo generateCsrfToken(); ?>">
                        <input type="hidden" name="recaptcha_token" id="recaptcha_token">
                        
                        <!-- Google Login -->
                        <div class="mb-3">
                            <a href="<?php echo BASE_URL; ?>/auth/google" class="btn btn-light btn-lg w-100 border shadow-sm d-flex align-items-center justify-content-center">
                                <img src="https://upload.wikimedia.org/wikipedia/commons/5/53/Google_%22G%22_Logo.svg" 
                                     alt="Google" 
                                     style="width:24px; height:24px; margin-right:12px;">
                                <span>Continuer avec Google</span>
                            </a>
                        </div>

                        <div class="d-flex align-items-center mb-4">
                            <hr class="flex-grow-1 m-0 text-muted">
                            <span class="px-3 text-muted small">OU</span>
                            <hr class="flex-grow-1 m-0 text-muted">
                        </div>
                        
                        <!-- Email -->
                        <div class="mb-3">
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
                        </div>
                        
                        <!-- Password -->
                        <div class="mb-3">
                            <label for="password" class="form-label">
                                <i class="bi bi-lock"></i> Mot de passe
                            </label>
                            <div class="input-group">
                                <input type="password" 
                                       class="form-control form-control-lg" 
                                       id="password" 
                                       name="password" 
                                       placeholder="••••••••"
                                       required>
                                <button class="btn btn-outline-secondary" 
                                        type="button" 
                                        onclick="togglePassword()">
                                    <i class="bi bi-eye" id="toggleIcon"></i>
                                </button>
                            </div>
                        </div>
                        
                        <!-- Remember me & Forgot password -->
                        <div class="d-flex justify-content-between align-items-center mb-4">
                            <div class="form-check">
                                <input class="form-check-input" type="checkbox" id="remember" name="remember">
                                <label class="form-check-label" for="remember">
                                    Se souvenir de moi
                                </label>
                            </div>
                            <a href="<?php echo BASE_URL; ?>/auth/forgot-password" class="text-decoration-none">
                                Mot de passe oublié ?
                            </a>
                        </div>
                        
                        <!-- Submit Button -->
                        <button type="submit" class="btn btn-primary btn-lg w-100 mb-3">
                            <i class="bi bi-box-arrow-in-right"></i> Se connecter
                        </button>
                        
                        <!-- Register Link -->
                        <div class="text-center">
                            <p class="mb-0">
                                Pas encore de compte ? 
                                <a href="<?php echo BASE_URL; ?>/auth/register" class="fw-bold text-decoration-none">
                                    S'inscrire
                                </a>
                            </p>
                        </div>
                    </form>
                </div>
            </div>
            
            <!-- Security Info -->
            <div class="text-center mt-4 text-muted">
                <small>
                    <i class="bi bi-shield-check text-success"></i>
                    Connexion sécurisée avec chiffrement SSL/TLS
                </small>
            </div>
        </div>
    </div>
</div>

<script>
function togglePassword() {
    const passwordField = document.getElementById('password');
    const toggleIcon = document.getElementById('toggleIcon');
    
    if (passwordField.type === 'password') {
        passwordField.type = 'text';
        toggleIcon.classList.remove('bi-eye');
        toggleIcon.classList.add('bi-eye-slash');
    } else {
        passwordField.type = 'password';
        toggleIcon.classList.remove('bi-eye-slash');
        toggleIcon.classList.add('bi-eye');
    }
}
</script>

<?php if (defined('RECAPTCHA_SITE_KEY') && !empty(RECAPTCHA_SITE_KEY)): ?>
<script src="https://www.google.com/recaptcha/api.js?render=<?php echo RECAPTCHA_SITE_KEY; ?>"></script>
<script>
    document.getElementById('loginForm').addEventListener('submit', function(e) {
        if (document.getElementById('recaptcha_token').value) return;

        e.preventDefault();
        grecaptcha.ready(function() {
            grecaptcha.execute('<?php echo RECAPTCHA_SITE_KEY; ?>', {action: 'login'}).then(function(token) {
                document.getElementById('recaptcha_token').value = token;
                document.getElementById('loginForm').submit();
            });
        });
    });
</script>
<?php endif; ?>

<?php require_once __DIR__ . '/../layouts/footer.php'; ?>