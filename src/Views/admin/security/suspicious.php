<?php require_once __DIR__ . '/../../layouts/header.php'; ?>

<div class="container-fluid py-4">
    <div class="row mb-4">
        <div class="col-md-6">
            <h2><i class="bi bi-eye"></i> Comptes Suspects</h2>
            <p class="text-muted">Détection automatique des comptes liés</p>
        </div>
        <div class="col-md-6 text-md-end">
            <a href="<?php echo BASE_URL; ?>/admin/security/logs" class="btn btn-outline-warning">
                <i class="bi bi-shield-exclamation"></i> Logs de Sécurité
            </a>
            <a href="<?php echo BASE_URL; ?>/admin/dashboard" class="btn btn-outline-secondary">
                <i class="bi bi-arrow-left"></i> Retour
            </a>
        </div>
    </div>
    
    <?php if (empty($accounts)): ?>
        <div class="card border-0 shadow-sm">
            <div class="card-body text-center py-5">
                <i class="bi bi-shield-check text-success" style="font-size: 5rem;"></i>
                <h4 class="mt-3">Aucun compte suspect détecté</h4>
                <p class="text-muted">Tous les comptes semblent légitimes</p>
            </div>
        </div>
    <?php else: ?>
        <div class="alert alert-warning">
            <i class="bi bi-exclamation-triangle"></i>
            <strong>Attention :</strong> Ces comptes présentent des similarités suspectes. 
            Vérifiez manuellement avant de prendre une décision.
        </div>
        
        <div class="card border-0 shadow-sm">
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-hover mb-0">
                        <thead class="table-light">
                            <tr>
                                <th>Email</th>
                                <th>IP d'Inscription</th>
                                <th>Empreinte</th>
                                <th>Date de Création</th>
                                <th>Comptes Liés</th>
                                <th>Score Confiance</th>
                                <th>Comptes Même IP</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($accounts as $account): ?>
                                <?php
                                $confidencePercent = $account['max_confidence'] * 100;
                                $dangerLevel = $confidencePercent >= 80 ? 'danger' : 
                                              ($confidencePercent >= 50 ? 'warning' : 'info');
                                ?>
                                <tr>
                                    <td>
                                        <strong><?php echo e($account['email']); ?></strong>
                                    </td>
                                    <td>
                                        <code><?php echo e($account['registration_ip']); ?></code>
                                    </td>
                                    <td>
                                        <small class="text-muted font-monospace">
                                            <?php echo e(substr($account['browser_fingerprint'], 0, 16)); ?>...
                                        </small>
                                    </td>
                                    <td>
                                        <small><?php echo date('d/m/Y H:i', strtotime($account['created_at'])); ?></small>
                                    </td>
                                    <td>
                                        <span class="badge bg-<?php echo $dangerLevel; ?>">
                                            <?php echo $account['related_accounts']; ?> compte(s)
                                        </span>
                                    </td>
                                    <td>
                                        <div class="progress" style="height: 20px; width: 100px;">
                                            <div class="progress-bar bg-<?php echo $dangerLevel; ?>" 
                                                 style="width: <?php echo $confidencePercent; ?>%">
                                                <?php echo round($confidencePercent); ?>%
                                            </div>
                                        </div>
                                    </td>
                                    <td>
                                        <span class="badge bg-secondary">
                                            <?php echo $account['accounts_same_ip']; ?>
                                        </span>
                                    </td>
                                    <td>
                                        <div class="btn-group btn-group-sm">
                                            <button class="btn btn-outline-info" 
                                                    onclick="viewDetails(<?php echo $account['id']; ?>)"
                                                    title="Voir détails">
                                                <i class="bi bi-info-circle"></i>
                                            </button>
                                            <button class="btn btn-outline-danger" 
                                                    onclick="blockUser(<?php echo $account['id']; ?>)"
                                                    title="Bloquer">
                                                <i class="bi bi-ban"></i>
                                            </button>
                                        </div>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
        
        <!-- Légende -->
        <div class="card border-0 shadow-sm mt-4">
            <div class="card-body">
                <h6><i class="bi bi-info-circle"></i> Légende</h6>
                <div class="row">
                    <div class="col-md-4">
                        <span class="badge bg-danger">Score élevé (80%+)</span>
                        <p class="small text-muted mb-0">Très probable que ce soient des comptes multiples</p>
                    </div>
                    <div class="col-md-4">
                        <span class="badge bg-warning">Score moyen (50-79%)</span>
                        <p class="small text-muted mb-0">Possibilité de comptes liés, à vérifier</p>
                    </div>
                    <div class="col-md-4">
                        <span class="badge bg-info">Score faible (0-49%)</span>
                        <p class="small text-muted mb-0">Similarités mineures, probablement légitime</p>
                    </div>
                </div>
            </div>
        </div>
    <?php endif; ?>
</div>

<script>
function viewDetails(userId) {
    alert('Fonctionnalité en développement : Afficher tous les détails du compte #' + userId);
    // TODO: Implémenter modal avec détails complets
}

function blockUser(userId) {
    if (confirm('Êtes-vous sûr de vouloir bloquer ce compte ?\n\nL\'utilisateur ne pourra plus se connecter.')) {
        // TODO: Implémenter le blocage
        alert('Fonctionnalité en développement : Bloquer le compte #' + userId);
    }
}
</script>

<?php require_once __DIR__ . '/../../layouts/footer.php'; ?>