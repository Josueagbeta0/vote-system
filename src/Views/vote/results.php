<?php require_once __DIR__ . '/../layouts/header.php'; ?>

<div class="container py-5">
    <!-- Header -->
    <div class="row mb-4">
        <div class="col-12 text-center">
            <h1 class="display-4 fw-bold">
                <i class="bi bi-trophy-fill text-warning"></i> Résultats de l'Élection
            </h1>
            <p class="lead text-muted"><?php echo e($election['title']); ?></p>
            <span class="badge bg-danger fs-6">
                <i class="bi bi-lock-fill"></i> Élection Clôturée
            </span>
        </div>
    </div>
    
    <!-- Statistiques -->
    <div class="row g-3 mb-5">
        <div class="col-md-3">
            <div class="card border-0 shadow text-center h-100">
                <div class="card-body">
                    <i class="bi bi-people text-primary" style="font-size: 2.5rem;"></i>
                    <h3 class="mt-2 mb-0"><?php echo $stats['total_voters']; ?></h3>
                    <p class="text-muted mb-0">Électeurs Inscrits</p>
                </div>
            </div>
        </div>
        
        <div class="col-md-3">
            <div class="card border-0 shadow text-center h-100">
                <div class="card-body">
                    <i class="bi bi-check-circle text-success" style="font-size: 2.5rem;"></i>
                    <h3 class="mt-2 mb-0"><?php echo $stats['voted_count']; ?></h3>
                    <p class="text-muted mb-0">Ont Voté</p>
                </div>
            </div>
        </div>
        
        <div class="col-md-3">
            <div class="card border-0 shadow text-center h-100">
                <div class="card-body">
                    <i class="bi bi-percent text-info" style="font-size: 2.5rem;"></i>
                    <h3 class="mt-2 mb-0"><?php echo number_format($stats['participation_rate'], 1); ?>%</h3>
                    <p class="text-muted mb-0">Participation</p>
                </div>
            </div>
        </div>
        
        <div class="col-md-3">
            <div class="card border-0 shadow text-center h-100">
                <div class="card-body">
                    <i class="bi bi-box text-warning" style="font-size: 2.5rem;"></i>
                    <h3 class="mt-2 mb-0"><?php echo $stats['total_votes']; ?></h3>
                    <p class="text-muted mb-0">Total Votes</p>
                </div>
            </div>
        </div>
    </div>
    
    <?php if (!empty($results)): ?>
        <!-- Gagnant -->
        <div class="row mb-5">
            <div class="col-12">
                <div class="card border-0 shadow-lg bg-gradient-success text-white">
                    <div class="card-body text-center p-5">
                        <i class="bi bi-trophy-fill" style="font-size: 5rem;"></i>
                        <h2 class="mt-3 mb-3">GAGNANT</h2>
                        <h1 class="display-3 fw-bold"><?php echo e($results[0]['name']); ?></h1>
                        <h3 class="mt-3">
                            <?php echo $results[0]['vote_count']; ?> votes 
                            (<?php echo number_format(($results[0]['vote_count'] / $stats['total_votes']) * 100, 1); ?>%)
                        </h3>
                    </div>
                </div>
            </div>
        </div>
        
        <!-- Podium -->
        <?php if (count($results) >= 3): ?>
            <div class="row mb-5">
                <!-- 2ème place -->
                <div class="col-md-4 order-md-1">
                    <div class="card border-0 shadow h-100">
                        <div class="card-body text-center p-4">
                            <div class="position-badge bg-secondary text-white mb-3">2</div>
                            <?php if ($results[1]['photo_url']): ?>
                                <img src="<?php echo BASE_URL . $results[1]['photo_url']; ?>" 
                                     class="rounded-circle mb-3" 
                                     style="width: 100px; height: 100px; object-fit: cover;"
                                     alt="<?php echo e($results[1]['name']); ?>">
                            <?php endif; ?>
                            <h5><?php echo e($results[1]['name']); ?></h5>
                            <h3 class="text-secondary"><?php echo $results[1]['vote_count']; ?> votes</h3>
                            <p class="text-muted">
                                <?php echo number_format(($results[1]['vote_count'] / $stats['total_votes']) * 100, 1); ?>%
                            </p>
                        </div>
                    </div>
                </div>
                
                <!-- 1ère place -->
                <div class="col-md-4 order-md-0 mb-3 mb-md-0">
                    <div class="card border-0 shadow h-100">
                        <div class="card-body text-center p-4">
                            <div class="position-badge bg-warning text-dark mb-3">
                                <i class="bi bi-trophy-fill"></i>
                            </div>
                            <?php if ($results[0]['photo_url']): ?>
                                <img src="<?php echo BASE_URL . $results[0]['photo_url']; ?>" 
                                     class="rounded-circle mb-3" 
                                     style="width: 120px; height: 120px; object-fit: cover; border: 4px solid #ffc107;"
                                     alt="<?php echo e($results[0]['name']); ?>">
                            <?php endif; ?>
                            <h4><?php echo e($results[0]['name']); ?></h4>
                            <h2 class="text-warning"><?php echo $results[0]['vote_count']; ?> votes</h2>
                            <p class="text-muted">
                                <?php echo number_format(($results[0]['vote_count'] / $stats['total_votes']) * 100, 1); ?>%
                            </p>
                        </div>
                    </div>
                </div>
                
                <!-- 3ème place -->
                <div class="col-md-4 order-md-2">
                    <div class="card border-0 shadow h-100">
                        <div class="card-body text-center p-4">
                            <div class="position-badge bg-danger text-white mb-3">3</div>
                            <?php if ($results[2]['photo_url']): ?>
                                <img src="<?php echo BASE_URL . $results[2]['photo_url']; ?>" 
                                     class="rounded-circle mb-3" 
                                     style="width: 100px; height: 100px; object-fit: cover;"
                                     alt="<?php echo e($results[2]['name']); ?>">
                            <?php endif; ?>
                            <h5><?php echo e($results[2]['name']); ?></h5>
                            <h3 class="text-danger"><?php echo $results[2]['vote_count']; ?> votes</h3>
                            <p class="text-muted">
                                <?php echo number_format(($results[2]['vote_count'] / $stats['total_votes']) * 100, 1); ?>%
                            </p>
                        </div>
                    </div>
                </div>
            </div>
        <?php endif; ?>
        
        <!-- Graphique -->
        <div class="row mb-5">
            <div class="col-12">
                <div class="card border-0 shadow">
                    <div class="card-header bg-white">
                        <h5 class="mb-0">
                            <i class="bi bi-bar-chart-fill"></i> Répartition des Votes
                        </h5>
                    </div>
                    <div class="card-body">
                        <canvas id="resultsChart" height="80"></canvas>
                    </div>
                </div>
            </div>
        </div>
        
        <!-- Tableau complet -->
        <div class="row">
            <div class="col-12">
                <div class="card border-0 shadow">
                    <div class="card-header bg-white">
                        <h5 class="mb-0">
                            <i class="bi bi-list-ol"></i> Classement Complet
                        </h5>
                    </div>
                    <div class="card-body p-0">
                        <div class="table-responsive">
                            <table class="table table-hover mb-0">
                                <thead class="table-light">
                                    <tr>
                                        <th>Classement</th>
                                        <th>Candidat</th>
                                        <th>Nombre de Votes</th>
                                        <th>Pourcentage</th>
                                        <th>Visualisation</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php 
                                    $position = 1;
                                    foreach ($results as $result): 
                                        $percentage = ($result['vote_count'] / $stats['total_votes']) * 100;
                                    ?>
                                        <tr>
                                            <td class="text-center">
                                                <span class="badge bg-dark fs-5"><?php echo $position++; ?></span>
                                            </td>
                                            <td>
                                                <div class="d-flex align-items-center">
                                                    <?php if ($result['photo_url']): ?>
                                                        <img src="<?php echo BASE_URL . $result['photo_url']; ?>" 
                                                             class="rounded-circle me-3" 
                                                             style="width: 50px; height: 50px; object-fit: cover;"
                                                             alt="<?php echo e($result['name']); ?>">
                                                    <?php endif; ?>
                                                    <strong class="fs-5"><?php echo e($result['name']); ?></strong>
                                                </div>
                                            </td>
                                            <td>
                                                <span class="badge bg-primary fs-6">
                                                    <?php echo $result['vote_count']; ?>
                                                </span>
                                            </td>
                                            <td>
                                                <strong class="fs-5"><?php echo number_format($percentage, 2); ?>%</strong>
                                            </td>
                                            <td style="width: 300px;">
                                                <div class="progress" style="height: 30px;">
                                                    <div class="progress-bar bg-gradient-primary" 
                                                         style="width: <?php echo $percentage; ?>%">
                                                        <?php echo number_format($percentage, 1); ?>%
                                                    </div>
                                                </div>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    <?php else: ?>
        <div class="alert alert-info text-center">
            <i class="bi bi-info-circle" style="font-size: 3rem;"></i>
            <h4 class="mt-3">Aucun vote enregistré</h4>
            <p class="mb-0">Cette élection n'a reçu aucun vote.</p>
        </div>
    <?php endif; ?>
    
    <!-- Retour -->
    <div class="text-center mt-5">
        <a href="<?php echo BASE_URL; ?>/voter/dashboard" class="btn btn-primary btn-lg px-5">
            <i class="bi bi-arrow-left"></i> Retour au Dashboard
        </a>
    </div>
</div>

<style>
.bg-gradient-success {
    background: linear-gradient(135deg, #10B981 0%, #059669 100%);
}

.bg-gradient-primary {
    background: linear-gradient(90deg, #667eea, #764ba2);
}

.position-badge {
    width: 60px;
    height: 60px;
    border-radius: 50%;
    display: inline-flex;
    align-items: center;
    justify-content: center;
    font-size: 1.5rem;
    font-weight: bold;
}
</style>

<!-- Chart.js -->
<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.umd.min.js"></script>

<script>
const candidateNames = <?php echo json_encode(array_column($results, 'name')); ?>;
const voteCounts = <?php echo json_encode(array_column($results, 'vote_count')); ?>;

const colors = [
    'rgba(255, 193, 7, 0.8)',   // Or
    'rgba(192, 192, 192, 0.8)', // Argent
    'rgba(205, 127, 50, 0.8)',  // Bronze
    'rgba(102, 126, 234, 0.8)',
    'rgba(118, 75, 162, 0.8)',
    'rgba(237, 100, 166, 0.8)',
    'rgba(255, 159, 64, 0.8)'
];

const ctx = document.getElementById('resultsChart').getContext('2d');
new Chart(ctx, {
    type: 'doughnut',
    data: {
        labels: candidateNames,
        datasets: [{
            data: voteCounts,
            backgroundColor: colors,
            borderWidth: 3,
            borderColor: '#fff'
        }]
    },
    options: {
        responsive: true,
        plugins: {
            legend: {
                position: 'right',
                labels: {
                    font: {
                        size: 14
                    },
                    padding: 15
                }
            },
            tooltip: {
                callbacks: {
                    label: function(context) {
                        const total = context.dataset.data.reduce((a, b) => a + b, 0);
                        const percentage = ((context.parsed / total) * 100).toFixed(1);
                        return context.label + ': ' + context.parsed + ' votes (' + percentage + '%)';
                    }
                }
            }
        }
    }
});
</script>

<?php require_once __DIR__ . '/../layouts/footer.php'; ?>