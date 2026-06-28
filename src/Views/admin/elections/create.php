<?php require_once __DIR__ . '/../../layouts/header.php'; ?>

<div class="container py-4">
    <div class="row justify-content-center">
        <div class="col-lg-8">
            <div class="card border-0 shadow-sm">
                <div class="card-header bg-primary text-white">
                    <h4 class="mb-0">
                        <i class="bi bi-plus-circle"></i> Créer une Nouvelle Élection
                    </h4>
                </div>
                
                <div class="card-body p-4">
                    <form method="POST" action="<?php echo BASE_URL; ?>/admin/elections/store" id="createElectionForm">
                        <!-- CSRF Token -->
                        <input type="hidden" name="csrf_token" value="<?php echo generateCsrfToken(); ?>">
                        
                        <!-- Organization -->
                        <div class="mb-4">
                            <label for="organization_id" class="form-label fw-bold">
                                <i class="bi bi-building"></i> Établissement <span class="text-danger">*</span>
                            </label>
                            <select class="form-select form-select-lg" name="organization_id" id="organization_id" required>
                                <option value="" selected disabled>Choisir un établissement...</option>
                                <?php foreach ($organizations as $org): ?>
                                    <option value="<?php echo $org['id']; ?>"><?php echo e($org['name']); ?> (<?php echo e($org['code']); ?>)</option>
                                <?php endforeach; ?>
                            </select>
                        </div>

                        <!-- Titre -->
                        <div class="mb-4">
                            <label for="title" class="form-label fw-bold">
                                <i class="bi bi-trophy"></i> Titre de l'Élection <span class="text-danger">*</span>
                            </label>
                            <input type="text" 
                                   class="form-control form-control-lg" 
                                   id="title" 
                                   name="title" 
                                   placeholder="Ex: Élection du Délégué de Classe 2025"
                                   required
                                   autofocus>
                            <small class="text-muted">Choisissez un titre clair et explicite</small>
                        </div>
                        
                        <!-- Description -->
                        <div class="mb-4">
                            <label for="description" class="form-label fw-bold">
                                <i class="bi bi-file-text"></i> Description
                            </label>
                            <textarea class="form-control" 
                                      id="description" 
                                      name="description" 
                                      rows="4"
                                      placeholder="Décrivez l'objectif de cette élection, les modalités, etc."></textarea>
                            <small class="text-muted">Fournissez des détails pour aider les électeurs</small>
                        </div>
                        
                        <!-- Dates -->
                        <div class="row mb-4">
                            <div class="col-md-6">
                                <label for="start_date" class="form-label fw-bold">
                                    <i class="bi bi-calendar-event"></i> Date de Début <span class="text-danger">*</span>
                                </label>
                                <input type="datetime-local" 
                                       class="form-control" 
                                       id="start_date" 
                                       name="start_date" 
                                       required>
                                <small class="text-muted">Quand l'élection commence</small>
                            </div>
                            
                            <div class="col-md-6">
                                <label for="end_date" class="form-label fw-bold">
                                    <i class="bi bi-calendar-check"></i> Date de Fin <span class="text-danger">*</span>
                                </label>
                                <input type="datetime-local" 
                                       class="form-control" 
                                       id="end_date" 
                                       name="end_date" 
                                       required>
                                <small class="text-muted">Quand l'élection se termine</small>
                            </div>
                        </div>
                        
                        <!-- Informations -->
                        <div class="alert alert-info">
                            <i class="bi bi-info-circle"></i> <strong>Note :</strong>
                            <ul class="mb-0 mt-2">
                                <li>L'élection sera créée en mode <strong>"Brouillon"</strong></li>
                                <li>Vous devrez ajouter des <strong>candidats</strong> avant de l'activer</li>
                                <li>Les électeurs pourront voter uniquement pendant la période définie</li>
                                <li>Une fois clôturée, les résultats seront définitifs</li>
                            </ul>
                        </div>
                        
                        <!-- Boutons -->
                        <div class="d-flex gap-2">
                            <button type="submit" class="btn btn-primary btn-lg px-5">
                                <i class="bi bi-check-circle"></i> Créer l'Élection
                            </button>
                            <a href="<?php echo BASE_URL; ?>/admin/elections" class="btn btn-outline-secondary btn-lg">
                                <i class="bi bi-x-circle"></i> Annuler
                            </a>
                        </div>
                    </form>
                </div>
            </div>
            
            <!-- Conseils -->
            <div class="card border-0 shadow-sm mt-4">
                <div class="card-body">
                    <h5 class="card-title">
                        <i class="bi bi-lightbulb text-warning"></i> Conseils pour une bonne élection
                    </h5>
                    <ul class="mb-0">
                        <li>Définissez des <strong>dates claires</strong> et réalistes</li>
                        <li>Ajoutez une <strong>description complète</strong> pour informer les électeurs</li>
                        <li>Prévoyez du temps pour <strong>ajouter les candidats</strong> avant l'ouverture</li>
                        <li>Testez l'élection en mode brouillon avant de l'activer</li>
                        <li>Communiquez les <strong>dates importantes</strong> aux électeurs</li>
                    </ul>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
// Définir la date minimale à maintenant
document.addEventListener('DOMContentLoaded', function() {
    const now = new Date();
    now.setMinutes(now.getMinutes() - now.getTimezoneOffset());
    const dateString = now.toISOString().slice(0, 16);
    
    document.getElementById('start_date').min = dateString;
    document.getElementById('end_date').min = dateString;
    
    // Validation : date de fin > date de début
    document.getElementById('createElectionForm').addEventListener('submit', function(e) {
        const startDate = new Date(document.getElementById('start_date').value);
        const endDate = new Date(document.getElementById('end_date').value);
        
        if (endDate <= startDate) {
            e.preventDefault();
            alert('La date de fin doit être après la date de début !');
            return false;
        }
    });
});
</script>

<?php require_once __DIR__ . '/../../layouts/footer.php'; ?>