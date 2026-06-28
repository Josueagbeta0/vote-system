<?php require_once __DIR__ . '/../layouts/header.php'; ?>

<div class="container py-5">
    <div class="row justify-content-center">
        <div class="col-md-5">
            <div class="card border-0 shadow-lg">
                <div class="card-body p-5">
                    <div class="text-center mb-4">
                        <i class="bi bi-lock-fill text-primary" style="font-size: 4rem;"></i>
                        <h2 class="fw-bold mt-3">Nouveau mot de passe</h2>
                        <p class="text-muted">Choisissez un mot de passe sécurisé</p>
                    </div>
                    
                    <form method="POST" action="<?php echo BASE_URL; ?>/auth/process-reset-password" id="resetForm">
                        <!-- CSRF Token -->
                        <input type="hidden" name="csrf_token" value="<?php echo generateCsrfToken(); ?>">
                        <input type="hidden" name="token" value="<?php echo e($token); ?>">
                        
                        <!-- Password -->
                        <div class="mb-3">
                            <label for="password" class="form-label">
                                <i class="bi bi-lock"></i> Nouveau mot de passe
                            </label>
                            <div class="input-group">
                                <input type="password" 
                                       class="form-control" 
                                       id="password" 
                                       name="password" 
                                       required
                                       onkeyup="checkPasswordStrength()">
                                <button class="btn btn-outline-secondary" 
                                        type="button" 
                                        onclick="togglePassword('password', 'icon1')">
                                    <i class="bi bi-eye" id="icon1"></i>
                                </button>
                            </div>
                            
                            <!-- Password Strength -->
                            <div class="mt-2">
                                <div class="progress" style="height: 5px;">
                                    <div class="progress-bar" id="strengthBar" style="width: 0%"></div>
                                </div>
                                <small id="strengthText" class="text-muted"></small>
                            </div>
                        </div>
                        
                        <!-- Confirm Password -->
                        <div class="mb-4">
                            <label for="password_confirm" class="form-label">
                                <i class="bi bi-lock-fill"></i> Confirmer le mot de passe
                            </label>
                            <div class="input-group">
                                <input type="password" 
                                       class="form-control" 
                                       id="password_confirm" 
                                       name="password_confirm" 
                                       required
                                       onkeyup="checkMatch()">
                                <button class="btn btn-outline-secondary" 
                                        type="button" 
                                        onclick="togglePassword('password_confirm', 'icon2')">
                                    <i class="bi bi-eye" id="icon2"></i>
                                </button>
                            </div>
                            <small id="matchText"></small>
                        </div>
                        
                        <!-- Requirements -->
                        <div class="alert alert-info mb-4">
                            <small><strong>Critères :</strong></small>
                            <ul class="small mb-0 mt-1">
                                <li id="req1">Au moins 8 caractères</li>
                                <li id="req2">Une majuscule</li>
                                <li id="req3">Une minuscule</li>
                                <li id="req4">Un chiffre</li>
                                <li id="req5">Un caractère spécial</li>
                            </ul>
                        </div>
                        
                        <!-- Submit -->
                        <button type="submit" class="btn btn-primary btn-lg w-100">
                            <i class="bi bi-check-circle"></i> Réinitialiser
                        </button>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
function togglePassword(fieldId, iconId) {
    const field = document.getElementById(fieldId);
    const icon = document.getElementById(iconId);
    
    if (field.type === 'password') {
        field.type = 'text';
        icon.classList.replace('bi-eye', 'bi-eye-slash');
    } else {
        field.type = 'password';
        icon.classList.replace('bi-eye-slash', 'bi-eye');
    }
}

function checkPasswordStrength() {
    const password = document.getElementById('password').value;
    const bar = document.getElementById('strengthBar');
    const text = document.getElementById('strengthText');
    
    let strength = 0;
    const reqs = {
        length: password.length >= 8,
        upper: /[A-Z]/.test(password),
        lower: /[a-z]/.test(password),
        number: /[0-9]/.test(password),
        special: /[^A-Za-z0-9]/.test(password)
    };
    
    document.getElementById('req1').style.color = reqs.length ? 'green' : '';
    document.getElementById('req2').style.color = reqs.upper ? 'green' : '';
    document.getElementById('req3').style.color = reqs.lower ? 'green' : '';
    document.getElementById('req4').style.color = reqs.number ? 'green' : '';
    document.getElementById('req5').style.color = reqs.special ? 'green' : '';
    
    Object.values(reqs).forEach(met => { if (met) strength += 20; });
    
    bar.style.width = strength + '%';
    
    if (strength <= 40) {
        bar.className = 'progress-bar bg-danger';
        text.textContent = 'Faible';
        text.className = 'text-danger';
    } else if (strength <= 80) {
        bar.className = 'progress-bar bg-warning';
        text.textContent = 'Moyen';
        text.className = 'text-warning';
    } else {
        bar.className = 'progress-bar bg-success';
        text.textContent = 'Fort';
        text.className = 'text-success';
    }
}

function checkMatch() {
    const pwd = document.getElementById('password').value;
    const confirm = document.getElementById('password_confirm').value;
    const text = document.getElementById('matchText');
    
    if (confirm) {
        if (pwd === confirm) {
            text.textContent = '✓ Correspond';
            text.className = 'text-success';
        } else {
            text.textContent = '✗ Ne correspond pas';
            text.className = 'text-danger';
        }
    }
}
</script>

<?php require_once __DIR__ . '/../layouts/footer.php'; ?>