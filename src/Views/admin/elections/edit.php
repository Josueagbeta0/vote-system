<?php require_once __DIR__ . '/../../layouts/header.php'; ?>

<div class="container py-4">
    <div class="row justify-content-center">
        <div class="col-lg-8">
            <div class="card border-0 shadow-sm">
                <div class="card-header bg-warning text-dark">
                    <h4 class="mb-0">
                        <i class="bi bi-pencil"></i> Éditer l'Élection
                    </h4>
                </div>
                
                <div class="card-body p-4">
                    <form method="POST" action="<?php echo BASE_URL; ?>/admin/elections/update/<?php echo $election['id']; ?>">
                        <!-- CSRF Token -->
                        <input type="hidden" name="csrf_token" value="<?php echo generateCsrfToken(); ?>">
                        
                        <!-- Titre -->
                        <div class="mb-4">
                            <label for="title" class="form-label fw-bold">
                                <i class="bi bi-trophy"></i> Titre de l'Élection <span class="text-danger">*</span>
                            </label>
                            <input type="text" 
                                   class="form-control form-control-lg" 
                                   id="title" 
                                   name="title" 
                                   value="<?php echo e($election['title']); ?>"
                                   required
                                   autofocus>
                        </div>
                        
                        <!-- Description -->
                        <div class="mb-4">
                            <label for="description" class="form-label fw-bold">
                                <i class="bi bi-file-text"></i> Description
                            </label>
                            <textarea class="form-control" 
                                      id="description" 
                                      name="description" 
                                      rows="4"><?php echo e($election['description']); ?></textarea>
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
                                       value="<?php echo date('Y-m-d\TH:i', strtotime($election['start_date'])); ?>"
                                       required>
                            </div>
                            
                            <div class="col-md-6">
                                <label for="end_date" class="form-label fw-bold">
                                    <i class="bi bi-calendar-check"></i> Date de Fin <span class="text-danger">*</span>
                                </label>
                                <input type="datetime-local" 
                                       class="form-control" 
                                       id="end_date" 
                                       name="end_date" 
                                       value="<?php echo date('Y-m-d\TH:i', strtotime($election['end_date'])); ?>"
                                       required>
                            </div>
                        </div>
                        
                        <?php if ($election['status'] !== 'draft'): ?>
                            <div class="alert alert-warning">
                                <i class="bi bi-exclamation-triangle"></i>
                                <strong>Attention :</strong> Cette élection est déjà <?php echo $election['status'] === 'active' ? 'active' : 'clôturée'; ?>.
                                Modifier les dates peut affecter les votes en cours.
                            </div>
                        <?php endif; ?>
                        
                        <!-- Boutons -->
                        <div class="d-flex gap-2">
                            <button type="submit" class="btn btn-warning btn-lg px-5">
                                <i class="bi bi-check-circle"></i> Enregistrer les Modifications
                            </button>
                            <a href="<?php echo BASE_URL; ?>/admin/elections/view/<?php echo $election['id']; ?>" 
                               class="btn btn-outline-secondary btn-lg">
                                <i class="bi bi-x-circle"></i> Annuler
                            </a>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

<?php require_once __DIR__ . '/../../layouts/footer.php'; ?>