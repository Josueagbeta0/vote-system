<?php require_once __DIR__ . '/../../layouts/header.php'; ?>

<div class="container-fluid py-4">
    <!-- Header -->
    <div class="row mb-4">
        <div class="col-md-8">
            <h2>
                <i class="bi bi-calendar-event"></i> <?php echo e($election['title']); ?>
            </h2>
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
            <span class="badge bg-<?php echo $badges[$election['status']]; ?> fs-6">
                <?php echo $statusLabels[$election['status']]; ?>
            </span>
        </div>
        <div class="col-md-4 text-md-end">
            <div class="btn-group">
                <a href="<?php echo BASE_URL; ?>/admin/elections/edit/<?php echo $election['id']; ?>" 
                   class="btn btn-outline-primary">
                    <i class="bi bi-pencil"></i> Éditer
                </a>
                
                <?php if ($election['status'] === 'draft'): ?>
                    <a href="<?php echo BASE_URL; ?>/admin/elections/<?php echo $election['id']; ?>/status/active" 
                       class="btn btn-success"
                       onclick="return confirm('Démarrer cette élection ?')">
                        <i class="bi bi-play-fill"></i> Démarrer
                    </a>
                <?php elseif ($election['status'] === 'active'): ?>
                    <a href="<?php echo BASE_URL; ?>/admin/elections/<?php echo $election['id']; ?>/status/closed" 
                       class="btn btn-danger"
                       onclick="return confirm('Clôturer cette élection ? Les votes seront définitifs.')">
                        <i class="bi bi-stop-fill"></i> Clôturer
                    </a>
                <?php endif; ?>
                
                <a href="<?php echo BASE_URL; ?>/admin/elections" class="btn btn-outline-secondary">
                    <i class="bi bi-arrow-left"></i> Retour
                </a>
            </div>
        </div>
    </div>
    
    <!-- Informations générales -->
    <div class="row mb-4">
        <div class="col-lg-8">
            <div class="card border-0 shadow-sm mb-4">
                <div class="card-body">
                    <h5 class="card-title"><i class="bi bi-info-circle"></i> Informations</h5>
                    <p class="card-text"><?php echo nl2br(e($election['description'])); ?></p>
                    
                    <div class="row mt-3">
                        <div class="col-md-6">
                            <p class="mb-2">
                                <i class="bi bi-calendar-event text-primary"></i>
                                <strong>Début :</strong> 
                                <?php echo date('d/m/Y à H:i', strtotime($election['start_date'])); ?>
                            </p>
                        </div>
                        <div class="col-md-6">
                            <p class="mb-2">
                                <i class="bi bi-calendar-check text-success"></i>
                                <strong>Fin :</strong> 
                                <?php echo date('d/m/Y à H:i', strtotime($election['end_date'])); ?>
                            </p>
                        </div>
                        <div class="col-md-6">
                            <p class="mb-0">
                                <i class="bi bi-person text-secondary"></i>
                                <strong>Créé par :</strong> 
                                <?php echo e($election['first_name'] . ' ' . $election['last_name']); ?>
                            </p>
                        </div>
                        <div class="col-md-6">
                            <p class="mb-0">
                                <i class="bi bi-clock text-muted"></i>
                                <strong>Créé le :</strong> 
                                <?php echo date('d/m/Y', strtotime($election['created_at'])); ?>
                            </p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        
        <div class="col-lg-4">
            <div class="card border-0 shadow-sm">
                <div class="card-body">
                    <h5 class="card-title"><i class="bi bi-graph-up"></i> Statistiques</h5>
                    
                    <div class="mb-3">
                        <div class="d-flex justify-content-between mb-1">
                            <span>Taux de participation</span>
                            <strong><?php echo number_format($stats['participation_rate'], 1); ?>%</strong>
                        </div>
                        <div class="progress">
                            <div class="progress-bar bg-success" 
                                 style="width: <?php echo $stats['participation_rate']; ?>%">
                            </div>
                        </div>
                    </div>
                    
                    <hr>
                    
                    <div class="d-flex justify-content-between mb-2">
                        <span><i class="bi bi-people"></i> Électeurs inscrits</span>
                        <strong class="text-primary"><?php echo $stats['total_voters']; ?></strong>
                    </div>
                    
                    <div class="d-flex justify-content-between mb-2">
                        <span><i class="bi bi-check-circle"></i> Ont voté</span>
                        <strong class="text-success"><?php echo $stats['voted_count']; ?></strong>
                    </div>
                    
                    <div class="d-flex justify-content-between mb-2">
                        <span><i class="bi bi-person-badge"></i> Candidats</span>
                        <strong class="text-info"><?php echo $stats['candidate_count']; ?></strong>
                    </div>
                    
                    <div class="d-flex justify-content-between">
                        <span><i class="bi bi-box"></i> Total votes</span>
                        <strong class="text-warning"><?php echo $stats['total_votes']; ?></strong>
                    </div>
                    
                    <?php if ($election['status'] === 'closed'): ?>
                        <a href="<?php echo BASE_URL; ?>/admin/elections/results/<?php echo $election['id']; ?>" 
                           class="btn btn-primary w-100 mt-3">
                            <i class="bi bi-bar-chart"></i> Voir les Résultats
                        </a>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
    
    <!-- Candidats -->
    <div class="card border-0 shadow-sm mb-4">
        <div class="card-header bg-white d-flex justify-content-between align-items-center">
            <h5 class="mb-0"><i class="bi bi-person-badge"></i> Candidats (<?php echo count($candidates); ?>)</h5>
            <?php if ($election['status'] === 'draft' || $election['status'] === 'active'): ?>
                <a href="<?php echo BASE_URL; ?>/admin/elections/<?php echo $election['id']; ?>/add-candidate" 
                   class="btn btn-sm btn-primary">
                    <i class="bi bi-plus-circle"></i> Ajouter un candidat
                </a>
            <?php endif; ?>
        </div>
        <div class="card-body">
            <?php if (empty($candidates)): ?>
                <div class="text-center py-5">
                    <i class="bi bi-inbox" style="font-size: 4rem; color: #ddd;"></i>
                    <p class="text-muted mt-3">Aucun candidat pour le moment</p>
                    <a href="<?php echo BASE_URL; ?>/admin/elections/<?php echo $election['id']; ?>/add-candidate" 
                       class="btn btn-primary">
                        <i class="bi bi-plus-circle"></i> Ajouter le premier candidat
                    </a>
                </div>
            <?php else: ?>
                <div class="row g-3">
                    <?php foreach ($candidates as $candidate): ?>
                        <div class="col-md-6 col-lg-4">
                            <div class="card h-100">
                                <?php if ($candidate['photo_url']): ?>
                                    <img src="<?php echo BASE_URL . $candidate['photo_url']; ?>" 
                                         class="card-img-top" 
                                         style="height: 200px; object-fit: cover;"
                                         alt="<?php echo e($candidate['name']); ?>">
                                <?php else: ?>
                                    <div class="card-img-top bg-secondary text-white d-flex align-items-center justify-content-center" 
                                         style="height: 200px;">
                                        <i class="bi bi-person-circle" style="font-size: 5rem;"></i>
                                    </div>
                                <?php endif; ?>
                                
                                <div class="card-body">
                                    <h5 class="card-title"><?php echo e($candidate['name']); ?></h5>
                                    <p class="card-text text-muted small">
                                        <?php echo e(substr($candidate['description'], 0, 100)); ?>
                                        <?php if (strlen($candidate['description']) > 100) echo '...'; ?>
                                    </p>
                                    
                                    <?php if ($election['status'] === 'closed'): ?>
                                        <div class="alert alert-success mb-0">
                                            <strong><?php echo $candidate['vote_count']; ?></strong> votes
                                        </div>
                                    <?php endif; ?>
                                </div>
                                
                                <?php if ($election['status'] !== 'closed'): ?>
                                    <div class="card-footer bg-white border-0">
                                        <a href="<?php echo BASE_URL; ?>/admin/elections/<?php echo $election['id']; ?>/delete-candidate/<?php echo $candidate['id']; ?>" 
                                           class="btn btn-sm btn-outline-danger w-100"
                                           onclick="return confirm('Supprimer ce candidat ?')">
                                            <i class="bi bi-trash"></i> Supprimer
                                        </a>
                                    </div>
                                <?php endif; ?>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
        </div>
    </div>
    
    <!-- Électeurs inscrits -->
    <div class="card border-0 shadow-sm">
        <div class="card-header bg-white">
            <h5 class="mb-0"><i class="bi bi-people"></i> Électeurs Inscrits (<?php echo count($voters); ?>)</h5>
        </div>
        <div class="card-body">
            <?php if (empty($voters)): ?>
                <p class="text-muted text-center">Aucun électeur inscrit</p>
            <?php else: ?>
                <div class="table-responsive">
                    <table class="table table-hover">
                        <thead>
                            <tr>
                                <th>Nom</th>
                                <th>Email</th>
                                <th>Statut</th>
                                <th>Date de vote</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($voters as $voter): ?>
                                <tr>
                                    <td><?php echo e($voter['first_name'] . ' ' . $voter['last_name']); ?></td>
                                    <td><?php echo e($voter['email']); ?></td>
                                    <td>
                                        <?php if ($voter['has_voted']): ?>
                                            <span class="badge bg-success">
                                                <i class="bi bi-check-circle"></i> A voté
                                            </span>
                                        <?php else: ?>
                                            <span class="badge bg-warning">
                                                <i class="bi bi-clock"></i> En attente
                                            </span>
                                        <?php endif; ?>
                                    </td>
                                    <td>
                                        <?php if ($voter['has_voted']): ?>
                                            <?php echo date('d/m/Y H:i', strtotime($voter['voted_at'])); ?>
                                        <?php else: ?>
                                            <span class="text-muted">-</span>
                                        <?php endif; ?>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            <?php endif; ?>
        </div>
    </div>
</div>

<?php require_once __DIR__ . '/../../layouts/footer.php'; ?>