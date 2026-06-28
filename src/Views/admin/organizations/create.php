<?php require_once __DIR__ . '/../../layouts/header.php'; ?>

<div class="container py-4">
    <div class="row justify-content-center">
        <div class="col-md-6">
            <div class="card shadow-sm">
                <div class="card-header bg-white">
                    <h4 class="mb-0">Ajouter un Établissement</h4>
                </div>
                <div class="card-body">
                    <form method="POST" action="<?php echo BASE_URL; ?>/admin/organizations/store">
                        <input type="hidden" name="csrf_token" value="<?php echo generateCsrfToken(); ?>">
                        
                        <div class="mb-3">
                            <label class="form-label">Nom de l'établissement</label>
                            <input type="text" name="name" class="form-control" placeholder="Ex: Lycée Technique de Cotonou" required>
                        </div>
                        
                        <div class="mb-3">
                            <label class="form-label">Code d'identification (Unique)</label>
                            <input type="text" name="code" class="form-control font-monospace" placeholder="Ex: LTC2024" required>
                            <small class="text-muted">Ce code sera utilisé pour identifier l'établissement.</small>
                        </div>
                        
                        <div class="d-flex justify-content-end gap-2">
                            <a href="<?php echo BASE_URL; ?>/admin/organizations" class="btn btn-light">Annuler</a>
                            <button type="submit" class="btn btn-primary">Créer</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

<?php require_once __DIR__ . '/../../layouts/footer.php'; ?>
