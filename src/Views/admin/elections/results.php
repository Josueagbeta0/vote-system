<?php require_once __DIR__ . '/../../layouts/header.php'; ?>

<div class="container-fluid py-4">
    <!-- Header -->
    <div class="row mb-4">
        <div class="col-md-8">
            <h2>
                <i class="bi bi-bar-chart"></i> Résultats de l'Élection
            </h2>
            <p class="text-muted"><?php echo e($election['title']); ?></p>
        </div>
        <div class="col-md-4 text-md-end">
            <div class="btn-group me-2">
                <a href="<?php echo BASE_URL; ?>/admin/elections/<?php echo $election['id']; ?>/export/pdf" class="btn btn-primary">
                    <i class="bi bi-file-earmark-pdf"></i> Rapport PDF
                </a>
                <a href="<?php echo BASE_URL; ?>/admin/elections/<?php echo $election['id']; ?>/export/csv" class="btn btn-success">
                    <i class="bi bi-filetype-csv"></i> Excel (CSV)
                </a>
            </div>
            <button onclick="window.print()" class="btn btn-outline-dark me-2">
                <i class="bi bi-printer"></i>
            </button>
            <a href="<?php echo BASE_URL; ?>/admin/elections/view/<?php echo $election['id']; ?>" 
               class="btn btn-outline-secondary">
                <i class="bi bi-arrow-left"></i> Retour
            </a>
        </div>
    </div>
    
    <!-- Statistiques générales -->
    <div class="row g-3 mb-4">
        <div class="col-md-3">
            <div class="card border-0 shadow-sm text-center">
                <div class="card-body">
                    <i class="bi bi-people text-primary" style="font-size: 2.5rem;"></i>
                    <h3 class="mt-2 mb-0"><?php echo $stats['total_voters']; ?></h3>
                    <p class="text-muted mb-0">Électeurs Inscrits</p>
                </div>
            </div>
        </div>
        
        <div class="col-md-3">
            <div class="card border-0 shadow-sm text-center">
                <div class="card-body">
                    <i class="bi bi-check-circle text-success" style="font-size: 2.5rem;"></i>
                    <h3 class="mt-2 mb-0"><?php echo $stats['voted_count']; ?></h3>
                    <p class="text-muted mb-0">Ont Voté</p>
                </div>
            </div>
        </div>
        
        <div class="col-md-3">
            <div class="card border-0 shadow-sm text-center">
                <div class="card-body">
                    <i class="bi bi-percent text-info" style="font-size: 2.5rem;"></i>
                    <h3 class="mt-2 mb-0"><?php echo number_format($stats['participation_rate'], 1); ?>%</h3>
                    <p class="text-muted mb-0">Participation</p>
                </div>
            </div>
        </div>
        
        <div class="col-md-3">
            <div class="card border-0 shadow-sm text-center">
                <div class="card-body">
                    <i class="bi bi-person-badge text-warning" style="font-size: 2.5rem;"></i>
                    <h3 class="mt-2 mb-0"><?php echo $stats['candidate_count']; ?></h3>
                    <p class="text-muted mb-0">Candidats</p>
                </div>
            </div>
        </div>
    </div>
    
    <div class="row">
        <!-- Graphique -->
        <div class="col-lg-8 mb-4">
            <div class="card border-0 shadow-sm">
                <div class="card-header bg-white">
                    <h5 class="mb-0"><i class="bi bi-pie-chart"></i> Répartition des Votes</h5>
                </div>
                <div class="card-body">
                    <canvas id="resultsChart" height="100"></canvas>
                </div>
            </div>
        </div>
        
        <!-- Classement -->
        <div class="col-lg-4 mb-4">
            <div class="card border-0 shadow-sm">
                <div class="card-header bg-white">
                    <h5 class="mb-0"><i class="bi bi-trophy"></i> Classement</h5>
                </div>
                <div class="card-body p-0">
                    <div class="list-group list-group-flush">
                        <?php 
                        $position = 1;
                        $colors = ['success', 'info', 'warning', 'secondary', 'dark'];
                        foreach ($results as $index => $result): 
                            $percentage = $stats['total_votes'] > 0 ? 
                                         ($result['vote_count'] / $stats['total_votes']) * 100 : 0;
                            $colorClass = $colors[min($index, count($colors) - 1)];
                        ?>
                            <div class="list-group-item">
                                <div class="d-flex align-items-center">
                                    <div class="flex-shrink-0">
                                        <div class="rounded-circle bg-<?php echo $colorClass; ?> text-white 
                                                    d-flex align-items-center justify-content-center"
                                             style="width: 40px; height: 40px; font-weight: bold;">
                                            <?php echo $position++; ?>
                                        </div>
                                    </div>
                                    <div class="flex-grow-1 ms-3">
                                        <h6 class="mb-1"><?php echo e($result['name']); ?></h6>
                                        <div class="progress" style="height: 20px;">
                                            <div class="progress-bar bg-<?php echo $colorClass; ?>" 
                                                 style="width: <?php echo $percentage; ?>%">
                                                <?php echo $result['vote_count']; ?> votes
                                            </div>
                                        </div>
                                        <small class="text-muted">
                                            <?php echo number_format($percentage, 1); ?>% des votes
                                        </small>
                                    </div>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                </div>
            </div>
            
            <?php if (!empty($results)): ?>
                <div class="card border-0 shadow-sm mt-3">
                    <div class="card-body text-center bg-success text-white">
                        <h4 class="mb-2">🏆 Gagnant</h4>
                        <h3 class="mb-0"><?php echo e($results[0]['name']); ?></h3>
                        <p class="mb-0 mt-2">
                            Avec <strong><?php echo $results[0]['vote_count']; ?> votes</strong>
                            (<?php echo number_format(($results[0]['vote_count'] / $stats['total_votes']) * 100, 1); ?>%)
                        </p>
                    </div>
                </div>
            <?php endif; ?>
        </div>
    </div>
    
    <!-- Tableau détaillé -->
    <div class="card border-0 shadow-sm">
        <div class="card-header bg-white">
            <h5 class="mb-0"><i class="bi bi-table"></i> Détails des Résultats</h5>
        </div>
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover mb-0">
                    <thead class="table-light">
                        <tr>
                            <th>Position</th>
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
                            $percentage = $stats['total_votes'] > 0 ? 
                                         ($result['vote_count'] / $stats['total_votes']) * 100 : 0;
                        ?>
                            <tr>
                                <td>
                                    <strong class="fs-4"><?php echo $position++; ?></strong>
                                </td>
                                <td>
                                    <div class="d-flex align-items-center">
                                        <?php if ($result['photo_url']): ?>
                                            <img src="<?php echo BASE_URL . $result['photo_url']; ?>" 
                                                 class="rounded-circle me-2" 
                                                 style="width: 40px; height: 40px; object-fit: cover;"
                                                 alt="<?php echo e($result['name']); ?>">
                                        <?php else: ?>
                                            <div class="rounded-circle bg-secondary text-white me-2 
                                                        d-flex align-items-center justify-content-center"
                                                 style="width: 40px; height: 40px;">
                                                <i class="bi bi-person"></i>
                                            </div>
                                        <?php endif; ?>
                                        <strong><?php echo e($result['name']); ?></strong>
                                    </div>
                                </td>
                                <td>
                                    <span class="badge bg-primary fs-6">
                                        <?php echo $result['vote_count']; ?>
                                    </span>
                                </td>
                                <td>
                                    <strong><?php echo number_format($percentage, 2); ?>%</strong>
                                </td>
                                <td style="width: 300px;">
                                    <div class="progress" style="height: 25px;">
                                        <div class="progress-bar bg-gradient" 
                                             style="width: <?php echo $percentage; ?>%; 
                                                   background: linear-gradient(90deg, #667eea, #764ba2);">
                                            <?php echo number_format($percentage, 1); ?>%
                                        </div>
                                    </div>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                    <tfoot class="table-light">
                        <tr>
                            <td colspan="2"><strong>TOTAL</strong></td>
                            <td>
                                <span class="badge bg-dark fs-6">
                                    <?php echo $stats['total_votes']; ?>
                                </span>
                            </td>
                            <td><strong>100%</strong></td>
                            <td></td>
                        </tr>
                    </tfoot>
                </table>
            </div>
        </div>
    </div>
</div>

<!-- Chart.js -->
<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.umd.min.js"></script>

<script>
// Préparer les données pour le graphique
const candidateNames = <?php echo json_encode(array_column($results, 'name')); ?>;
const voteCounts = <?php echo json_encode(array_column($results, 'vote_count')); ?>;

// Couleurs dynamiques
const colors = [
    'rgba(102, 126, 234, 0.8)',
    'rgba(118, 75, 162, 0.8)',
    'rgba(237, 100, 166, 0.8)',
    'rgba(255, 159, 64, 0.8)',
    'rgba(75, 192, 192, 0.8)',
    'rgba(153, 102, 255, 0.8)',
    'rgba(255, 99, 132, 0.8)'
];

const borderColors = colors.map(color => color.replace('0.8', '1'));

// Créer le graphique
const ctx = document.getElementById('resultsChart').getContext('2d');
const resultsChart = new Chart(ctx, {
    type: 'bar',
    data: {
        labels: candidateNames,
        datasets: [{
            label: 'Nombre de votes',
            data: voteCounts,
            backgroundColor: colors,
            borderColor: borderColors,
            borderWidth: 2
        }]
    },
    options: {
        responsive: true,
        maintainAspectRatio: true,
        plugins: {
            legend: {
                display: false
            },
            title: {
                display: true,
                text: 'Distribution des Votes',
                font: {
                    size: 16,
                    weight: 'bold'
                }
            }
        },
        scales: {
            y: {
                beginAtZero: true,
                ticks: {
                    stepSize: 1
                }
            }
        }
    }
});

// Style d'impression
window.onbeforeprint = function() {
    document.body.classList.add('printing');
};
</script>

<style>
@media print {
    .btn, nav, .card-header .btn {
        display: none !important;
    }
}
</style>

<?php require_once __DIR__ . '/../../layouts/footer.php'; ?>