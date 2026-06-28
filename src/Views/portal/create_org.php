<?php require_once __DIR__ . '/../layouts/header.php'; ?>

<div class="container py-5">
    <div class="row justify-content-center">
        <div class="col-lg-8">
            <div class="card shadow-lg border-0">
                <div class="card-header bg-dark text-white p-4">
                    <h2 class="mb-0 fw-bold">
                        <i class="bi bi-rocket-takeoff margin-right-2"></i> Créer un Espace de Vote
                    </h2>
                    <p class="mb-0 text-white-50">Sécurisé, Rapide et Dédié à votre structure.</p>
                </div>
                <div class="card-body p-5">
                    
                    <?php 
                    $data = $_SESSION['create_org_data'] ?? [];
                    unset($_SESSION['create_org_data']);
                    ?>

                    <form method="POST" action="<?php echo BASE_URL; ?>/create-organization/store" id="createOrgForm">
                        <input type="hidden" name="csrf_token" value="<?php echo generateCsrfToken(); ?>">

                        <!-- Section Organisation -->
                        <h4 class="mb-3 text-primary border-bottom pb-2">1. Informations de la Structure</h4>
                        
                        <div class="row mb-3">
                            <div class="col-md-8">
                                <label class="form-label fw-bold">Nom de l'Organisation <span class="text-danger">*</span></label>
                                <input type="text" class="form-control" name="org_name" placeholder="Ex: Université de Parakou" value="<?php echo e($data['org_name'] ?? ''); ?>" required autofocus>
                            </div>
                            <div class="col-md-4">
                                <label class="form-label fw-bold">Type</label>
                                <select class="form-select" name="org_type">
                                    <option value="school" <?php echo ($data['org_type'] ?? '') == 'school' ? 'selected' : ''; ?>>École</option>
                                    <option value="university" <?php echo ($data['org_type'] ?? '') == 'university' ? 'selected' : ''; ?>>Université</option>
                                    <option value="company" <?php echo ($data['org_type'] ?? '') == 'company' ? 'selected' : ''; ?>>Entreprise</option>
                                    <option value="other" <?php echo ($data['org_type'] ?? '') == 'other' ? 'selected' : ''; ?>>Autre</option>
                                </select>
                            </div>
                        </div>

                        <div class="mb-4">
                            <label class="form-label fw-bold">Code Unique (pour les inscriptions) <span class="text-danger">*</span></label>
                            <input type="text" class="form-control font-monospace text-uppercase" name="org_code" placeholder="Ex: UNIVPARAKOU24" value="<?php echo e($data['org_code'] ?? ''); ?>" required>
                            <small class="text-muted">Ce code sera demandé à vos électeurs lors de leur inscription.</small>
                        </div>

                        <!-- Section Admin -->
                        <h4 class="mb-3 text-primary border-bottom pb-2 pt-3">2. Administrateur Principal</h4>
                        
                        <div class="row mb-3">
                            <div class="col-md-6">
                                <label class="form-label">Prénom <span class="text-danger">*</span></label>
                                <input type="text" class="form-control" name="first_name" value="<?php echo e($data['first_name'] ?? ''); ?>" required>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Nom <span class="text-danger">*</span></label>
                                <input type="text" class="form-control" name="last_name" value="<?php echo e($data['last_name'] ?? ''); ?>" required>
                            </div>
                        </div>

                        <div class="mb-3">
                            <label class="form-label">Email Administrateur <span class="text-danger">*</span></label>
                            <input type="email" class="form-control" name="email" value="<?php echo e($data['email'] ?? ''); ?>" required>
                        </div>

                        <div class="row mb-4">
                            <div class="col-md-6">
                                <label class="form-label">Mot de passe <span class="text-danger">*</span></label>
                                <input type="password" class="form-control" name="password" required minlength="8">
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Confirmer <span class="text-danger">*</span></label>
                                <input type="password" class="form-control" name="password_confirm" required minlength="8">
                            </div>
                        </div>

                        <div class="d-grid gap-2">
                            <button type="submit" class="btn btn-primary btn-lg">
                                <i class="bi bi-check-circle-fill"></i> Créer l'Organisation
                            </button>
                            <a href="<?php echo BASE_URL; ?>/" class="btn btn-light">Annuler</a>
                        </div>

                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

<?php require_once __DIR__ . '/../layouts/footer.php'; ?>
