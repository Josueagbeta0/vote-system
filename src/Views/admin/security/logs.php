<?php require_once __DIR__ . '/../../layouts/header.php'; ?>

<div class="container-fluid py-4">
    <div class="row mb-4">
        <div class="col-md-6">
            <h2><i class="bi bi-shield-exclamation"></i> Logs de Sécurité</h2>
            <p class="text-muted">Surveillez les événements suspects</p>
        </div>
        <div class="col-md-6 text-md-end">
            <a href="<?php echo BASE_URL; ?>/admin/security/suspicious" class="btn btn-warning">
                <i class="bi bi-eye"></i> Comptes Suspects
            </a>
            <a href="<?php echo BASE_URL; ?>/admin/dashboard" class="btn btn-outline-secondary">
                <i class="bi bi-arrow-left"></i> Retour
            </a>
        </div>
    </div>
    
    <?php if (empty($logs)): ?>
        <div class="card border-0 shadow-sm">
            <div class="card-body text-center py-5">
                <i class="bi bi-shield-check text-success" style="font-size: 5rem;"></i>
                <h4 class="mt-3">Aucun événement suspect</h4>
                <p class="text-muted">Le système fonctionne normalement</p>
            </div>
        </div>
    <?php else: ?>
        <div class="card border-0 shadow-sm">
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-hover mb-0">
                        <thead class="table-light">
                            <tr>
                                <th style="width: 50px;">Sévérité</th>
                                <th>Type d'Événement</th>
                                <th>Utilisateur</th>
                                <th>Adresse IP</th>
                                <th>Description</th>
                                <th>Date</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($logs as $log): ?>
                                <?php
                                $severityBadges = [
                                    'low' => 'secondary',
                                    'medium' => 'warning',
                                    'high' => 'danger',
                                    'critical' => 'dark'
                                ];
                                $severityLabels = [
                                    'low' => 'Faible',
                                    'medium' => 'Moyen',
                                    'high' => 'Élevé',
                                    'critical' => 'Critique'
                                ];
                                $eventIcons = [
                                    'multiple_accounts' => 'bi-people',
                                    'suspicious_ip' => 'bi-shield-exclamation',
                                    'duplicate_fingerprint' => 'bi-fingerprint',
                                    'rate_limit' => 'bi-speedometer',
                                    'other' => 'bi-exclamation-circle'
                                ];
                                ?>
                                <tr>
                                    <td>
                                        <span class="badge bg-<?php echo $severityBadges[$log['severity']]; ?>">
                                            <?php echo strtoupper($log['severity']); ?>
                                        </span>
                                    </td>
                                    <td>
                                        <i class="bi <?php echo $eventIcons[$log['event_type']] ?? 'bi-info-circle'; ?>"></i>
                                        <strong><?php echo str_replace('_', ' ', ucfirst($log['event_type'])); ?></strong>
                                    </td>
                                    <td>
                                        <?php if ($log['email']): ?>
                                            <a href="#" title="<?php echo e($log['email']); ?>">
                                                <?php echo e(substr($log['email'], 0, 20)); ?>...
                                            </a>
                                        <?php else: ?>
                                            <span class="text-muted">N/A</span>
                                        <?php endif; ?>
                                    </td>
                                    <td>
                                        <code><?php echo e($log['ip_address']); ?></code>
                                    </td>
                                    <td>
                                        <?php echo e($log['description']); ?>
                                    </td>
                                    <td>
                                        <small class="text-muted">
                                            <?php echo date('d/m/Y H:i:s', strtotime($log['created_at'])); ?>
                                        </small>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
        
        <!-- Statistiques -->
        <div class="row mt-4 g-3">
            <?php
            $severityCounts = array_count_values(array_column($logs, 'severity'));
            $eventCounts = array_count_values(array_column($logs, 'event_type'));
            ?>
            
            <div class="col-md-6">
                <div class="card border-0 shadow-sm">
                    <div class="card-header bg-white">
                        <h6 class="mb-0"><i class="bi bi-bar-chart"></i> Par Sévérité</h6>
                    </div>
                    <div class="card-body">
                        <?php foreach (['critical' => 'Critique', 'high' => 'Élevé', 'medium' => 'Moyen', 'low' => 'Faible'] as $key => $label): ?>
                            <div class="d-flex justify-content-between mb-2">
                                <span><?php echo $label; ?></span>
                                <strong><?php echo $severityCounts[$key] ?? 0; ?></strong>
                            </div>
                        <?php endforeach; ?>
                    </div>
                </div>
            </div>
            
            <div class="col-md-6">
                <div class="card border-0 shadow-sm">
                    <div class="card-header bg-white">
                        <h6 class="mb-0"><i class="bi bi-pie-chart"></i> Par Type d'Événement</h6>
                    </div>
                    <div class="card-body">
                        <?php foreach ($eventCounts as $type => $count): ?>
                            <div class="d-flex justify-content-between mb-2">
                                <span><?php echo str_replace('_', ' ', ucfirst($type)); ?></span>
                                <strong><?php echo $count; ?></strong>
                            </div>
                        <?php endforeach; ?>
                    </div>
                </div>
            </div>
        </div>
    <?php endif; ?>
</div>

<?php require_once __DIR__ . '/../../layouts/footer.php'; ?>