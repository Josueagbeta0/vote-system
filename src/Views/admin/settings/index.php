<?php require_once __DIR__ . '/../../layouts/header.php'; ?>

<div class="row">
    <!-- Sidebar -->
    <div class="col-md-3">
        <?php require_once __DIR__ . '/../../layouts/admin_sidebar.php'; ?>
    </div>

    <!-- Main Content -->
    <div class="col-md-9">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h2 class="fw-bold">Paramètres de l'Organisation</h2>
        </div>

        <div class="card shadow-sm border-0">
            <div class="card-body p-4">
                
                <div class="alert alert-info mb-4">
                    <i class="bi bi-info-circle-fill me-2"></i>
                    Personnalisez l'apparence de votre espace de vote pour vos électeurs.
                </div>

                <form method="POST" action="<?php echo BASE_URL; ?>/admin/settings/update" enctype="multipart/form-data">
                    <input type="hidden" name="csrf_token" value="<?php echo generateCsrfToken(); ?>">
                    <input type="hidden" name="current_logo" value="<?php echo e($organization['logo_url'] ?? ''); ?>">

                    <!-- Logo Upload -->
                    <div class="mb-4">
                        <label class="form-label fw-bold">Logo de l'organisation</label>
                        <div class="row align-items-center">
                            <div class="col-auto">
                                <div class="bg-light border rounded d-flex align-items-center justify-content-center" style="width: 100px; height: 100px; overflow: hidden;">
                                    <?php if (!empty($organization['logo_url'])): ?>
                                        <img src="<?php echo BASE_URL . $organization['logo_url']; ?>" alt="Logo" class="img-fluid" style="max-height: 100%;">
                                    <?php else: ?>
                                        <i class="bi bi-image text-muted fs-1"></i>
                                    <?php endif; ?>
                                </div>
                            </div>
                            <div class="col">
                                <input type="file" class="form-control" name="logo" accept="image/*">
                                <small class="text-muted">Format: PNG, JPG (Max 2Mo). Carré recommandé.</small>
                            </div>
                        </div>
                    </div>

                    <!-- Color Picker -->
                    <div class="mb-4">
                        <label class="form-label fw-bold">Couleur Principale</label>
                        <div class="row align-items-center">
                            <div class="col-auto">
                                <input type="color" class="form-control form-control-color" id="primaryColorInput" name="primary_color" value="<?php echo e($organization['primary_color'] ?? '#0d6efd'); ?>" title="Choisir une couleur">
                            </div>
                            <div class="col">
                                <input type="text" class="form-control w-25" id="primaryColorText" value="<?php echo e($organization['primary_color'] ?? '#0d6efd'); ?>" readonly>
                            </div>
                        </div>
                        <small class="text-muted">Cette couleur sera utilisée pour les boutons, les liens et le thème général de votre espace.</small>
                    </div>

                    <div class="divider border-top my-4"></div>

                    <!-- Info (Read Only) -->
                    <div class="mb-4">
                        <h5 class="fw-bold mb-3">Informations Générales</h5>
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Nom</label>
                                <input type="text" class="form-control bg-light" value="<?php echo e($organization['name']); ?>" readonly>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Code établissement</label>
                                <input type="text" class="form-control bg-light font-monospace" value="<?php echo e($organization['code']); ?>" readonly>
                            </div>
                        </div>
                    </div>

                    <div class="d-flex justify-content-end">
                        <button type="submit" class="btn btn-primary px-4">
                            <i class="bi bi-save me-2"></i> Enregistrer les modifications
                        </button>
                    </div>

                </form>
            </div>
        </div>
    </div>
</div>

<script>
    document.getElementById('primaryColorInput').addEventListener('input', function(e) {
        document.getElementById('primaryColorText').value = e.target.value;
    });
</script>

<?php require_once __DIR__ . '/../../layouts/footer.php'; ?>
