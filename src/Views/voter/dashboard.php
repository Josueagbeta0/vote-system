<?php require_once __DIR__ . '/../layouts/header.php'; ?>

<div class="container py-4">
    <!-- Welcome Section -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="card bg-primary text-white">
                <div class="card-body p-4">
                    <h2 class="mb-2">
                        <i class="bi bi-person-circle"></i> 
                        Bienvenue, <?php echo e($_SESSION['user']['first_name']); ?> !
                    </h2>
                    <p class="mb-0 opacity-75">
                        <i class="bi bi-calendar-check"></i> 
                        Consultez les élections en cours et exercez votre droit de vote en toute sécurité.
                    </p>
                </div>
            </div>
        </div>
    </div>
    
    <!-- Statistiques -->
    <div class="row g-3 mb-4">
        <div class="col-md-4">
            <div class="card border-primary">
                <div class="card-body text-center">
                    <i class="bi bi-calendar-event text-primary" style="font-size: 2.5rem;"></i>
                    <h3 class="mt-2 mb-0"><?php echo $stats['total_elections']; ?></h3>
                    <p class="text-muted mb-0">Mes Élections</p>
                </div>
            </div>
        </div>
        
        <div class="col-md-4">
            <div class="card border-success">
                <div class="card-body text-center">
                    <i class="bi bi-check-circle text-success" style="font-size: 2.5rem;"></i>
                    <h3 class="mt-2 mb-0"><?php echo $stats['voted']; ?></h3>
                    <p class="text-muted mb-0">Votes Effectués</p>
                </div>
            </div>
        </div>
        
        <div class="col-md-4">
            <div class="card border-warning">
                <div class="card-body text-center">
                    <i class="bi bi-clock-history text-warning" style="font-size: 2.5rem;"></i>
                    <h3 class="mt-2 mb-0"><?php echo $stats['pending']; ?></h3>
                    <p class="text-muted mb-0">En Attente</p>
                </div>
            </div>
        </div>
    </div>
    
    <!-- Élections Actives -->
    <div class="row mb-4">
        <div class="col-12">
            <h4 class="mb-3">
                <i class="bi bi-ballot"></i> Élections en Cours
            </h4>
            
            <?php if (empty($activeElections)): ?>
                <div class="alert alert-info">
                    <i class="bi bi-info-circle"></i> 
                    Aucune élection n'est actuellement en cours.
                </div>
            <?php else: ?>
                <div class="row g-3">
                    <?php foreach ($activeElections as $election): ?>
                        <div class="col-md-6">
                            <div class="card h-100 shadow-sm">
                                <div class="card-body">
                                    <div class="d-flex justify-content-between align-items-start mb-2">
                                        <h5 class="card-title mb-0">
                                            <?php echo e($election['title']); ?>
                                        </h5>
                                        <span class="badge bg-success">
                                            <i class="bi bi-circle-fill"></i> Active
                                        </span>
                                    </div>
                                    
                                    <p class="text-muted small mb-3">
                                        <?php echo e(substr($election['description'], 0, 100)); ?>
                                        <?php if (strlen($election['description']) > 100) echo '...'; ?>
                                    </p>
                                    
                                    <div class="mb-3">
                                        <small class="text-muted">
                                            <i class="bi bi-calendar"></i> 
                                            Du <?php echo date('d/m/Y', strtotime($election['start_date'])); ?>
                                            au <?php echo date('d/m/Y', strtotime($election['end_date'])); ?>
                                        </small>
                                        <br>
                                        <small class="text-muted">
                                            <i class="bi bi-people"></i> 
                                            <?php echo $election['candidate_count']; ?> candidat(s)
                                        </small>
                                    </div>
                                    
                                    <?php
                                    // Vérifier si l'utilisateur est inscrit et s'il a voté
                                    $voterInfo = null;
                                    foreach ($myElections as $myElection) {
                                        if ($myElection['election_id'] == $election['id']) {
                                            $voterInfo = $myElection;
                                            break;
                                        }
                                    }
                                    ?>
                                    
                                    <?php if ($voterInfo): ?>
                                        <?php if ($voterInfo['has_voted']): ?>
                                            <button class="btn btn-success w-100" disabled>
                                                <i class="bi bi-check-circle"></i> Vous avez déjà voté
                                            </button>
                                        <?php else: ?>
                                            <a href="<?php echo BASE_URL; ?>/vote/cast/<?php echo $election['id']; ?>" 
                                               class="btn btn-primary w-100">
                                                <i class="bi bi-hand-index"></i> Voter Maintenant
                                            </a>
                                        <?php endif; ?>
                                    <?php else: ?>
                                        <a href="<?php echo BASE_URL; ?>/vote/register/<?php echo $election['id']; ?>" 
                                           class="btn btn-outline-primary w-100">
                                            <i class="bi bi-person-plus"></i> S'inscrire pour voter
                                        </a>
                                    <?php endif; ?>
                                </div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
        </div>
    </div>
    
    <!-- Mes Participations -->
    <div class="row">
        <div class="col-12">
            <h4 class="mb-3">
                <i class="bi bi-clock-history"></i> Historique de mes Participations
            </h4>
            
            <?php if (empty($myElections)): ?>
                <div class="alert alert-info">
                    <i class="bi bi-info-circle"></i> 
                    Vous n'êtes inscrit à aucune élection pour le moment.
                </div>
            <?php else: ?>
                <div class="card">
                    <div class="card-body">
                        <div class="table-responsive">
                            <table class="table table-hover mb-0">
                                <thead>
                                    <tr>
                                        <th>Élection</th>
                                        <th>Période</th>
                                        <th>Statut</th>
                                        <th>Date de vote</th>
                                        <th>Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($myElections as $election): ?>
                                        <tr>
                                            <td>
                                                <strong><?php echo e($election['title']); ?></strong>
                                            </td>
                                            <td>
                                                <small class="text-muted">
                                                    <?php echo date('d/m/Y', strtotime($election['start_date'])); ?> - 
                                                    <?php echo date('d/m/Y', strtotime($election['end_date'])); ?>
                                                </small>
                                            </td>
                                            <td>
                                                <?php if ($election['status'] === 'active'): ?>
                                                    <span class="badge bg-success">Active</span>
                                                <?php elseif ($election['status'] === 'closed'): ?>
                                                    <span class="badge bg-secondary">Clôturée</span>
                                                <?php else: ?>
                                                    <span class="badge bg-warning">En attente</span>
                                                <?php endif; ?>
                                            </td>
                                            <td>
                                                <?php if ($election['has_voted']): ?>
                                                    <span class="text-success">
                                                        <i class="bi bi-check-circle-fill"></i>
                                                        <?php echo date('d/m/Y H:i', strtotime($election['voted_at'])); ?>
                                                    </span>
                                                <?php else: ?>
                                                    <span class="text-muted">
                                                        <i class="bi bi-clock"></i> Pas encore voté
                                                    </span>
                                                <?php endif; ?>
                                            </td>
                                            <td>
                                                <?php if ($election['status'] === 'closed'): ?>
                                                    <a href="<?php echo BASE_URL; ?>/elections/results/<?php echo $election['election_id']; ?>" 
                                                       class="btn btn-sm btn-outline-primary">
                                                        <i class="bi bi-bar-chart"></i> Résultats
                                                    </a>
                                                <?php elseif (!$election['has_voted'] && $election['status'] === 'active'): ?>
                                                    <a href="<?php echo BASE_URL; ?>/vote/cast/<?php echo $election['election_id']; ?>" 
                                                       class="btn btn-sm btn-primary">
                                                        <i class="bi bi-hand-index"></i> Voter
                                                    </a>
                                                <?php endif; ?>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            <?php endif; ?>
        </div>
    </div>
</div>

<?php require_once __DIR__ . '/../layouts/footer.php'; ?>