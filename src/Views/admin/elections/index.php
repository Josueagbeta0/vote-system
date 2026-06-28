<?php require_once __DIR__ . '/../../layouts/header.php'; ?>

<div class="container-fluid py-4">
    <div class="row mb-4">
        <div class="col-md-6">
            <h2><i class="bi bi-calendar-event"></i> Gestion des Élections</h2>
            <p class="text-muted">Créez et gérez toutes vos élections</p>
        </div>
        <div class="col-md-6 text-md-end">
            <a href="<?php echo BASE_URL; ?>/admin/elections/create" class="btn btn-primary">
                <i class="bi bi-plus-circle"></i> Nouvelle Élection
            </a>
            <a href="<?php echo BASE_URL; ?>/admin/dashboard" class="btn btn-outline-secondary">
                <i class="bi bi-arrow-left"></i> Retour
            </a>
        </div>
    </div>
    
    <?php if (empty($elections)): ?>
        <div class="card border-0 shadow-sm">
            <div class="card-body text-center py-5">
                <i class="bi bi-inbox" style="font-size: 5rem; color: #ddd;"></i>
                <h4 class="mt-3">Aucune élection</h4>
                <p class="text-muted">Commencez par créer votre première élection</p>
                <a href="<?php echo BASE_URL; ?>/admin/elections/create" class="btn btn-primary mt-2">
                    <i class="bi bi-plus-circle"></i> Créer une élection
                </a>
            </div>
        </div>
    <?php else: ?>
        <div class="row">
            <?php foreach ($elections as $election): ?>
                <div class="col-lg-6 col-xl-4 mb-4">
                    <div class="card h-100 border-0 shadow-sm">
                        <div class="card-header bg-white border-0 d-flex justify-content-between align-items-start">
                            <h5 class="mb-0"><?php echo e($election['title']); ?></h5>
                            <?php 
                            $badges = [
                                'draft' => 'secondary',
                                'active' => 'success',
                                'closed' => 'danger',
                                'archived' => 'dark'
                            ];
                            $statusLabels = [
                                'draft' => 'Brouillon',
                                'active' => 'Active',
                                'closed' => 'Clôturée',
                                'archived' => 'Archivée'
                            ];
                            ?>
                            <span class="badge bg-<?php echo $badges[$election['status']]; ?>">
                                <?php echo $statusLabels[$election['status']]; ?>
                            </span>
                        </div>
                        
                        <div class="card-body">
                            <p class="text-muted small">
                                <?php echo e(substr($election['description'], 0, 100)); ?>
                                <?php if (strlen($election['description']) > 100) echo '...'; ?>
                            </p>
                            
                            <div class="mb-3">
                                <small class="text-muted">
                                    <i class="bi bi-calendar"></i> 
                                    <strong>Début :</strong> <?php echo date('d/m/Y H:i', strtotime($election['start_date'])); ?>
                                </small>
                                <br>
                                <small class="text-muted">
                                    <i class="bi bi-calendar-check"></i> 
                                    <strong>Fin :</strong> <?php echo date('d/m/Y H:i', strtotime($election['end_date'])); ?>
                                </small>
                            </div>
                            
                            <div class="row g-2 mb-3">
                                <div class="col-6">
                                    <div class="border rounded p-2 text-center">
                                        <div class="text-primary fw-bold"><?php echo $election['candidate_count']; ?></div>
                                        <small class="text-muted">Candidats</small>
                                    </div>
                                </div>
                                <div class="col-6">
                                    <div class="border rounded p-2 text-center">
                                        <div class="text-success fw-bold"><?php echo $election['vote_count']; ?></div>
                                        <small class="text-muted">Votes</small>
                                    </div>
                                </div>
                            </div>
                            
                            <small class="text-muted">
                                <i class="bi bi-person"></i> 
                                Par <?php echo e($election['first_name'] . ' ' . $election['last_name']); ?>
                            </small>
                        </div>
                        
                        <div class="card-footer bg-white border-0">
                            <div class="d-flex gap-2">
                                <a href="<?php echo BASE_URL; ?>/admin/elections/view/<?php echo $election['id']; ?>" 
                                   class="btn btn-sm btn-primary flex-fill">
                                    <i class="bi bi-eye"></i> Voir
                                </a>
                                
                                <a href="<?php echo BASE_URL; ?>/admin/elections/edit/<?php echo $election['id']; ?>" 
                                   class="btn btn-sm btn-outline-secondary">
                                    <i class="bi bi-pencil"></i>
                                </a>
                                
                                <?php if ($election['status'] === 'draft'): ?>
                                    <a href="<?php echo BASE_URL; ?>/admin/elections/<?php echo $election['id']; ?>/status/active" 
                                       class="btn btn-sm btn-success"
                                       onclick="return confirm('Démarrer cette élection ?')">
                                        <i class="bi bi-play-fill"></i>
                                    </a>
                                <?php elseif ($election['status'] === 'active'): ?>
                                    <a href="<?php echo BASE_URL; ?>/admin/elections/<?php echo $election['id']; ?>/status/closed" 
                                       class="btn btn-sm btn-danger"
                                       onclick="return confirm('Clôturer cette élection ?')">
                                        <i class="bi bi-stop-fill"></i>
                                    </a>
                                <?php endif; ?>
                                
                                <a href="<?php echo BASE_URL; ?>/admin/elections/delete/<?php echo $election['id']; ?>" 
                                   class="btn btn-sm btn-outline-danger"
                                   onclick="return confirm('Supprimer cette élection ? Cette action est irréversible.')">
                                    <i class="bi bi-trash"></i>
                                </a>
                            </div>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>
</div>

<?php require_once __DIR__ . '/../../layouts/footer.php'; ?>