<?php require_once __DIR__ . '/../layouts/header.php'; ?>

<div class="container-fluid py-4">
    <!-- Header -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="bg-gradient-primary text-white p-4 rounded-3 shadow">
                <h2 class="mb-2">
                    <i class="bi bi-speedometer2"></i> Dashboard Administrateur
                </h2>
                <p class="mb-0 opacity-75">
                    Bienvenue, <?php echo e($_SESSION['user']['first_name']); ?> ! 
                    Gérez vos élections et consultez les statistiques.
                </p>
            </div>
        </div>
    </div>
    
    <!-- Statistiques principales -->
    <div class="row g-3 mb-4">
        <div class="col-xl-3 col-md-6">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-body">
                    <div class="d-flex align-items-center">
                        <div class="flex-shrink-0">
                            <div class="rounded-circle bg-primary bg-opacity-10 p-3">
                                <i class="bi bi-people text-primary" style="font-size: 2rem;"></i>
                            </div>
                        </div>
                        <div class="flex-grow-1 ms-3">
                            <h6 class="text-muted mb-1">Utilisateurs</h6>
                            <h3 class="mb-0"><?php echo $stats['total_users']; ?></h3>
                            <small class="text-muted">
                                <?php echo $stats['total_voters']; ?> électeurs
                            </small>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        
        <div class="col-xl-3 col-md-6">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-body">
                    <div class="d-flex align-items-center">
                        <div class="flex-shrink-0">
                            <div class="rounded-circle bg-success bg-opacity-10 p-3">
                                <i class="bi bi-calendar-check text-success" style="font-size: 2rem;"></i>
                            </div>
                        </div>
                        <div class="flex-grow-1 ms-3">
                            <h6 class="text-muted mb-1">Élections Totales</h6>
                            <h3 class="mb-0"><?php echo $stats['total_elections']; ?></h3>
                            <small class="text-muted">
                                Tous statuts confondus
                            </small>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        
        <div class="col-xl-3 col-md-6">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-body">
                    <div class="d-flex align-items-center">
                        <div class="flex-shrink-0">
                            <div class="rounded-circle bg-warning bg-opacity-10 p-3">
                                <i class="bi bi-lightning-charge text-warning" style="font-size: 2rem;"></i>
                            </div>
                        </div>
                        <div class="flex-grow-1 ms-3">
                            <h6 class="text-muted mb-1">Élections Actives</h6>
                            <h3 class="mb-0"><?php echo $stats['active_elections']; ?></h3>
                            <small class="text-muted">
                                En cours actuellement
                            </small>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        
        <div class="col-xl-3 col-md-6">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-body">
                    <div class="d-flex align-items-center">
                        <div class="flex-shrink-0">
                            <div class="rounded-circle bg-secondary bg-opacity-10 p-3">
                                <i class="bi bi-archive text-secondary" style="font-size: 2rem;"></i>
                            </div>
                        </div>
                        <div class="flex-grow-1 ms-3">
                            <h6 class="text-muted mb-1">Élections Clôturées</h6>
                            <h3 class="mb-0"><?php echo $stats['closed_elections']; ?></h3>
                            <small class="text-muted">
                                Terminées
                            </small>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <!-- Analytics Charts -->
    <div class="row mb-4">
        <!-- Evolution des Votes -->
        <div class="col-lg-8">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-header bg-white border-0">
                    <h5 class="mb-0"><i class="bi bi-graph-up"></i> Tendance des Votes (7 derniers jours)</h5>
                </div>
                <div class="card-body">
                    <canvas id="votesChart" height="100"></canvas>
                </div>
            </div>
        </div>
        <!-- Participation Globale -->
        <div class="col-lg-4">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-header bg-white border-0">
                    <h5 class="mb-0"><i class="bi bi-pie-chart"></i> Participation Globale</h5>
                </div>
                <div class="card-body">
                    <canvas id="turnoutChart" height="200"></canvas>
                    <div class="mt-3 text-center small text-muted">
                        Basé sur les électeurs éligibles vs votes réels
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Actions rapides -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="card border-0 shadow-sm">
                <div class="card-body">
                    <h5 class="card-title mb-3">
                        <i class="bi bi-lightning"></i> Actions Rapides
                    </h5>
                    <div class="d-flex flex-wrap gap-2">
                        <a href="<?php echo BASE_URL; ?>/admin/elections/create" class="btn btn-primary">
                            <i class="bi bi-plus-circle"></i> Nouvelle Élection
                        </a>
                        <a href="<?php echo BASE_URL; ?>/admin/elections" class="btn btn-outline-primary">
                            <i class="bi bi-list-ul"></i> Gérer les Élections
                        </a>
                        <a href="<?php echo BASE_URL; ?>/admin/organizations" class="btn btn-outline-success">
                            <i class="bi bi-building"></i> Gérer les Établissements
                        </a>
                        <a href="<?php echo BASE_URL; ?>/admin/users" class="btn btn-outline-secondary">
                            <i class="bi bi-people"></i> Gérer les Utilisateurs
                        </a>
                        <a href="<?php echo BASE_URL; ?>/admin/security/logs" class="btn btn-outline-warning">
                            <i class="bi bi-shield-exclamation"></i> Logs de Sécurité
                        </a>
                        <a href="<?php echo BASE_URL; ?>/admin/security/suspicious" class="btn btn-outline-danger">
                            <i class="bi bi-eye"></i> Comptes Suspects
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <div class="row">
        <!-- Dernières élections -->
        <div class="col-lg-8 mb-4">
            <div class="card border-0 shadow-sm">
                <div class="card-header bg-white border-0 d-flex justify-content-between align-items-center">
                    <h5 class="mb-0">
                        <i class="bi bi-calendar-event"></i> Dernières Élections
                    </h5>
                    <a href="<?php echo BASE_URL; ?>/admin/elections" class="btn btn-sm btn-outline-primary">
                        Voir tout
                    </a>
                </div>
                <div class="card-body p-0">
                    <?php if (empty($recentElections)): ?>
                        <div class="p-4 text-center text-muted">
                            <i class="bi bi-inbox" style="font-size: 3rem;"></i>
                            <p class="mt-2 mb-0">Aucune élection créée</p>
                        </div>
                    <?php else: ?>
                        <div class="table-responsive">
                            <table class="table table-hover mb-0">
                                <thead class="table-light">
                                    <tr>
                                        <th>Titre</th>
                                        <th>Période</th>
                                        <th>Statut</th>
                                        <th>Votes</th>
                                        <th>Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($recentElections as $election): ?>
                                        <tr>
                                            <td>
                                                <strong><?php echo e($election['title']); ?></strong>
                                            </td>
                                            <td>
                                                <small class="text-muted">
                                                    <?php echo date('d/m/Y', strtotime($election['start_date'])); ?>
                                                    <br>
                                                    <?php echo date('d/m/Y', strtotime($election['end_date'])); ?>
                                                </small>
                                            </td>
                                            <td>
                                                <?php 
                                                $badges = [
                                                    'draft' => 'bg-secondary',
                                                    'active' => 'bg-success',
                                                    'closed' => 'bg-danger',
                                                    'archived' => 'bg-dark'
                                                ];
                                                $statusLabels = [
                                                    'draft' => 'Brouillon',
                                                    'active' => 'Active',
                                                    'closed' => 'Clôturée',
                                                    'archived' => 'Archivée'
                                                ];
                                                ?>
                                                <span class="badge <?php echo $badges[$election['status']]; ?>">
                                                    <?php echo $statusLabels[$election['status']]; ?>
                                                </span>
                                            </td>
                                            <td>
                                                <small>
                                                    <?php echo $election['vote_count']; ?> votes<br>
                                                    <?php echo $election['candidate_count']; ?> candidats
                                                </small>
                                            </td>
                                            <td>
                                                <div class="btn-group btn-group-sm">
                                                    <a href="<?php echo BASE_URL; ?>/admin/elections/view/<?php echo $election['id']; ?>" 
                                                       class="btn btn-outline-primary" title="Voir">
                                                        <i class="bi bi-eye"></i>
                                                    </a>
                                                    <a href="<?php echo BASE_URL; ?>/admin/elections/edit/<?php echo $election['id']; ?>" 
                                                       class="btn btn-outline-secondary" title="Éditer">
                                                        <i class="bi bi-pencil"></i>
                                                    </a>
                                                </div>
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
        
        <!-- Activité récente -->
        <div class="col-lg-4 mb-4">
            <div class="card border-0 shadow-sm">
                <div class="card-header bg-white border-0">
                    <h5 class="mb-0">
                        <i class="bi bi-clock-history"></i> Activité Récente
                    </h5>
                </div>
                <div class="card-body">
                    <?php if (empty($recentActivity)): ?>
                        <p class="text-muted text-center">Aucune activité récente</p>
                    <?php else: ?>
                        <div class="timeline">
                            <?php foreach ($recentActivity as $activity): ?>
                                <div class="timeline-item mb-3">
                                    <div class="d-flex">
                                        <div class="flex-shrink-0">
                                            <div class="rounded-circle bg-primary bg-opacity-10 p-2">
                                                <i class="bi bi-circle-fill text-primary" style="font-size: 0.5rem;"></i>
                                            </div>
                                        </div>
                                        <div class="flex-grow-1 ms-3">
                                            <p class="mb-1 small">
                                                <strong><?php echo e($activity['action']); ?></strong>
                                            </p>
                                            <p class="mb-0 text-muted" style="font-size: 0.75rem;">
                                                <i class="bi bi-clock"></i>
                                                <?php echo date('d/m/Y H:i', strtotime($activity['timestamp'])); ?>
                                            </p>
                                        </div>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
</div>



<?php require_once __DIR__ . '/../layouts/footer.php'; ?>

<!-- Chart.js Integration -->
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
document.addEventListener('DOMContentLoaded', function() {
    // 1. Line Chart: Votes History
    const votesCtx = document.getElementById('votesChart').getContext('2d');
    const votesData = <?php echo json_encode($analytics['votes_history'] ?? []); ?>;
    
    const labels = votesData.map(item => item.date);
    const data = votesData.map(item => item.count);

    new Chart(votesCtx, {
        type: 'line',
        data: {
            labels: labels,
            datasets: [{
                label: 'Votes',
                data: data,
                borderColor: '#0d6efd',
                backgroundColor: 'rgba(13, 110, 253, 0.1)',
                tension: 0.4,
                fill: true
            }]
        },
        options: {
            responsive: true,
            plugins: {
                legend: { display: false }
            },
            scales: {
                y: { beginAtZero: true, ticks: { stepSize: 1 } }
            }
        }
    });

    // 2. Doughnut Chart: Turnout
    const turnoutCtx = document.getElementById('turnoutChart').getContext('2d');
    const turnoutStats = <?php echo json_encode($analytics['turnout'] ?? []); ?>;
    
    const totalEligible = turnoutStats.total_eligible || 0;
    const totalVotes = turnoutStats.votes_cast || 0;
    const notVoted = totalEligible - totalVotes;

    new Chart(turnoutCtx, {
        type: 'doughnut',
        data: {
            labels: ['Ont voté', 'Non votants'],
            datasets: [{
                data: [totalVotes, notVoted],
                backgroundColor: ['#198754', '#e9ecef'],
                hoverOffset: 4
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: { position: 'bottom' }
            }
        }
    });
});
</script>