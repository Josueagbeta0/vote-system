<?php require_once __DIR__ . '/../layouts/header.php'; ?>

<div class="container">
    <div class="row justify-content-center mt-5 mb-5">
        <div class="col-md-6">
            <div class="card shadow-lg">
                <div class="card-body p-5">
                    <div class="text-center mb-4">
                        <?php if (isset($org) && $org): ?>
                            <div class="mb-3">
                                <span class="badge bg-primary fs-6"><?php echo e($org['name']); ?></span>
                            </div>
                        <?php endif; ?>
                        <i class="bi bi-person-plus text-success icon-lg"></i>
                        <h2 class="fw-bold mt-3">Inscription</h2>
                        <p class="text-muted">Créez votre compte pour voter</p>
                    </div>
                    
                    <form method="POST" action="<?php echo BASE_URL; ?>/auth/process-register" id="registerForm">
                        <!-- CSRF Token -->
                        <input type="hidden" name="csrf_token" value="<?php echo generateCsrfToken(); ?>">
                        <input type="hidden" name="recaptcha_token" id="recaptcha_token">
                        
                        <div class="alert alert-info border-0 shadow-sm mb-4">
                            <div class="d-flex">
                                <i class="bi bi-shield-lock-fill fs-4 me-3"></i>
                                <div>
                                    <strong>Inscription Sécurisée</strong><br>
                                    <small>Vous devez être pré-enregistré par votre établissement pour créer un compte.</small>
                                </div>
                            </div>
                        </div>

                        <!-- Organization & ID -->
                        <div class="row mb-3">
                            <div class="col-md-6 mb-3 mb-md-0">
                                <label for="org_code" class="form-label">
                                    <i class="bi bi-building"></i> Code Établissement <span class="text-danger">*</span>
                                </label>
                                <?php if (isset($org) && $org): ?>
                                    <input type="hidden" name="org_slug" value="<?php echo e($org['slug']); ?>">
                                    <div class="input-group">
                                        <input type="text" 
                                               class="form-control font-monospace bg-light" 
                                               value="<?php echo e($org['code']); ?>" 
                                               disabled>
                                        <span class="input-group-text"><i class="bi bi-lock-fill"></i></span>
                                    </div>
                                    <!-- Field for backend validation if needed, or just use slug -->
                                    <input type="hidden" name="org_code" value="<?php echo e($org['code']); ?>">
                                <?php else: ?>
                                    <input type="text" 
                                           class="form-control font-monospace" 
                                           id="org_code" 
                                           name="org_code" 
                                           placeholder="Ex: LTC2024"
                                           required 
                                           autofocus>
                                <?php endif; ?>
                            </div>
                            <div class="col-md-6">
                                <label for="identifier" class="form-label">
                                    <i class="bi bi-card-heading"></i> Mon Matricule <span class="text-danger">*</span>
                                </label>
                                <input type="text" 
                                       class="form-control font-monospace" 
                                       id="identifier" 
                                       name="identifier" 
                                       placeholder="Ex: MAT123"
                                       required>
                            </div>
                        </div>

                        <div class="row">
                            <!-- First Name -->
                            <div class="col-md-6 mb-3">
                                <label for="first_name" class="form-label">
                                    <i class="bi bi-person"></i> Prénom <span class="text-danger">*</span>
                                </label>
                                <input type="text" 
                                       class="form-control" 
                                       id="first_name" 
                                       name="first_name" 
                                       placeholder="Jean"
                                       required 
                                       autofocus>
                            </div>
                            
                            <!-- Last Name -->
                            <div class="col-md-6 mb-3">
                                <label for="last_name" class="form-label">
                                    <i class="bi bi-person"></i> Nom <span class="text-danger">*</span>
                                </label>
                                <input type="text" 
                                       class="form-control" 
                                       id="last_name" 
                                       name="last_name" 
                                       placeholder="Dupont"
                                       required>
                            </div>
                        </div>
                        
                        <!-- Email -->
                        <div class="mb-3">
                            <label for="email" class="form-label">
                                <i class="bi bi-envelope"></i> Adresse email <span class="text-danger">*</span>
                            </label>
                            <input type="email" 
                                   class="form-control" 
                                   id="email" 
                                   name="email" 
                                   placeholder="jean.dupont@example.com"
                                   required>
                            <small class="text-muted">Utilisez une adresse valide pour la vérification</small>
                        </div>
                        
                        <!-- Phone Number (Optional but recommended) -->
                        <div class="mb-3">
                            <label for="phone_number" class="form-label">
                                <i class="bi bi-telephone"></i> Numéro de téléphone
                                <span class="badge bg-info">Recommandé</span>
                            </label>
                            <input type="tel" 
                                   class="form-control" 
                                   id="phone_number" 
                                   name="phone_number" 
                                   placeholder="+229 XX XX XX XX">
                            <small class="text-muted">Pour plus de sécurité contre les comptes multiples</small>
                        </div>
                        
                        <!-- Password -->
                        <div class="mb-3">
                            <label for="password" class="form-label">
                                <i class="bi bi-lock"></i> Mot de passe <span class="text-danger">*</span>
                            </label>
                            <div class="input-group">
                                <input type="password" 
                                       class="form-control" 
                                       id="password" 
                                       name="password" 
                                       placeholder="••••••••"
                                       required
                                       onkeyup="checkPasswordStrength()">
                                <button class="btn btn-outline-secondary" 
                                        type="button" 
                                        onclick="togglePassword('password')">
                                    <i class="bi bi-eye" id="toggleIcon1"></i>
                                </button>
                            </div>
                            
                            <!-- Password Strength Indicator -->
                            <div class="mt-2">
                                <small class="text-muted">Force du mot de passe :</small>
                                <div class="progress" style="height: 5px;">
                                    <div class="progress-bar" 
                                         id="passwordStrength" 
                                         role="progressbar" 
                                         style="width: 0%"></div>
                                </div>
                                <small id="passwordStrengthText" class="text-muted"></small>
                            </div>
                            
                            <!-- Password Requirements -->
                            <small class="text-muted d-block mt-2">
                                <i class="bi bi-info-circle"></i> Le mot de passe doit contenir :
                                <ul class="small mb-0 mt-1">
                                    <li id="req-length">Au moins 8 caractères</li>
                                    <li id="req-upper">Une majuscule</li>
                                    <li id="req-lower">Une minuscule</li>
                                    <li id="req-number">Un chiffre</li>
                                    <li id="req-special">Un caractère spécial</li>
                                </ul>
                            </small>
                        </div>
                        
                        <!-- Confirm Password -->
                        <div class="mb-3">
                            <label for="password_confirm" class="form-label">
                                <i class="bi bi-lock-fill"></i> Confirmer le mot de passe <span class="text-danger">*</span>
                            </label>
                            <div class="input-group">
                                <input type="password" 
                                       class="form-control" 
                                       id="password_confirm" 
                                       name="password_confirm" 
                                       placeholder="••••••••"
                                       required
                                       onkeyup="checkPasswordMatch()">
                                <button class="btn btn-outline-secondary" 
                                        type="button" 
                                        onclick="togglePassword('password_confirm')">
                                    <i class="bi bi-eye" id="toggleIcon2"></i>
                                </button>
                            </div>
                            <small id="passwordMatchText" class="text-danger"></small>
                        </div>
                        
                        <!-- Terms & Conditions -->
                        <div class="mb-4">
                            <div class="form-check">
                                <input class="form-check-input" 
                                       type="checkbox" 
                                       id="terms" 
                                       name="terms" 
                                       required>
                                <label class="form-check-label" for="terms">
                                    J'accepte les <a href="#" class="text-decoration-none">conditions d'utilisation</a> 
                                    et la <a href="#" class="text-decoration-none">politique de confidentialité</a>
                                </label>
                            </div>
                        </div>
                        
                        <!-- Submit Button -->
                        <button type="submit" class="btn btn-success btn-lg w-100 mb-3">
                            <i class="bi bi-person-check"></i> Créer mon compte
                        </button>
                        
                        <!-- Login Link -->
                        <div class="text-center">
                            <p class="mb-0">
                                Vous avez déjà un compte ? 
                                <a href="<?php echo BASE_URL; ?>/auth/login" class="fw-bold text-decoration-none">
                                    Se connecter
                                </a>
                            </p>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
function togglePassword(fieldId) {
    const field = document.getElementById(fieldId);
    const iconId = fieldId === 'password' ? 'toggleIcon1' : 'toggleIcon2';
    const icon = document.getElementById(iconId);
    
    if (field.type === 'password') {
        field.type = 'text';
        icon.classList.remove('bi-eye');
        icon.classList.add('bi-eye-slash');
    } else {
        field.type = 'password';
        icon.classList.remove('bi-eye-slash');
        icon.classList.add('bi-eye');
    }
}

function checkPasswordStrength() {
    const password = document.getElementById('password').value;
    const strengthBar = document.getElementById('passwordStrength');
    const strengthText = document.getElementById('passwordStrengthText');
    
    let strength = 0;
    const requirements = {
        length: password.length >= 8,
        upper: /[A-Z]/.test(password),
        lower: /[a-z]/.test(password),
        number: /[0-9]/.test(password),
        special: /[^A-Za-z0-9]/.test(password)
    };
    
    // Update requirement checkmarks
    document.getElementById('req-length').style.color = requirements.length ? 'green' : '';
    document.getElementById('req-upper').style.color = requirements.upper ? 'green' : '';
    document.getElementById('req-lower').style.color = requirements.lower ? 'green' : '';
    document.getElementById('req-number').style.color = requirements.number ? 'green' : '';
    document.getElementById('req-special').style.color = requirements.special ? 'green' : '';
    
    // Calculate strength
    Object.values(requirements).forEach(met => {
        if (met) strength += 20;
    });
    
    // Update progress bar
    strengthBar.style.width = strength + '%';
    
    if (strength <= 40) {
        strengthBar.className = 'progress-bar bg-danger';
        strengthText.textContent = 'Faible';
        strengthText.className = 'text-danger';
    } else if (strength <= 60) {
        strengthBar.className = 'progress-bar bg-warning';
        strengthText.textContent = 'Moyen';
        strengthText.className = 'text-warning';
    } else if (strength <= 80) {
        strengthBar.className = 'progress-bar bg-info';
        strengthText.textContent = 'Bon';
        strengthText.className = 'text-info';
    } else {
        strengthBar.className = 'progress-bar bg-success';
        strengthText.textContent = 'Excellent';
        strengthText.className = 'text-success';
    }
}

function checkPasswordMatch() {
    const password = document.getElementById('password').value;
    const confirm = document.getElementById('password_confirm').value;
    const matchText = document.getElementById('passwordMatchText');
    
    if (confirm.length > 0) {
        if (password === confirm) {
            matchText.textContent = '✓ Les mots de passe correspondent';
            matchText.className = 'text-success';
        } else {
            matchText.textContent = '✗ Les mots de passe ne correspondent pas';
            matchText.className = 'text-danger';
        }
    } else {
        matchText.textContent = '';
    }
}
</script>

<?php if (defined('RECAPTCHA_SITE_KEY') && !empty(RECAPTCHA_SITE_KEY)): ?>
<script src="https://www.google.com/recaptcha/api.js?render=<?php echo RECAPTCHA_SITE_KEY; ?>"></script>
<script>
    document.getElementById('registerForm').addEventListener('submit', function(e) {
        if (document.getElementById('recaptcha_token').value) return;
        
        e.preventDefault();
        grecaptcha.ready(function() {
            grecaptcha.execute('<?php echo RECAPTCHA_SITE_KEY; ?>', {action: 'register'}).then(function(token) {
                document.getElementById('recaptcha_token').value = token;
                document.getElementById('registerForm').submit();
            });
        });
    });
</script>
<?php endif; ?>

<?php require_once __DIR__ . '/../layouts/footer.php'; ?>