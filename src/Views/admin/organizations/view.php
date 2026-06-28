<?php require_once __DIR__ . '/../../layouts/header.php'; ?>

<div class="container py-4">
    <nav aria-label="breadcrumb">
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="<?php echo BASE_URL; ?>/admin/organizations">Établissements</a></li>
            <li class="breadcrumb-item active" aria-current="page"><?php echo e($organization['name']); ?></li>
        </ol>
    </nav>

    <div class="row">
        <!-- Infos & Import -->
        <div class="col-md-4 mb-4">
            <div class="card shadow-sm mb-4">
                <div class="card-body text-center">
                    <div class="avatar-lg bg-primary text-white rounded-circle d-flex align-items-center justify-content-center mx-auto mb-3" style="width: 64px; height: 64px; font-size: 24px;">
                        <?php echo strtoupper(substr($organization['name'], 0, 2)); ?>
                    </div>
                    <h4><?php echo e($organization['name']); ?></h4>
                    <span class="badge bg-secondary font-monospace fs-6"><?php echo e($organization['code']); ?></span>
                </div>
            </div>

            <div class="card shadow-sm">
                <div class="card-header bg-success text-white">
                    <h5 class="mb-0"><i class="bi bi-file-earmark-spreadsheet"></i> Importer des Électeurs</h5>
                </div>
                <div class="card-body">
                    <p class="small text-muted">
                        Le fichier CSV doit avoir les colonnes :<br>
                        <code>Matricule, Prénom, Nom, Email(Optionnel)</code>
                    </p>
                    <form method="POST" action="<?php echo BASE_URL; ?>/admin/organizations/<?php echo $organization['id']; ?>/import-voters" enctype="multipart/form-data">
                        <div class="mb-3">
                            <input type="file" name="voters_file" class="form-control" accept=".csv" required>
                        </div>
                        <button type="submit" class="btn btn-success w-100">
                            <i class="bi bi-upload"></i> Charger la liste
                        </button>
                    </form>
                </div>
            </div>
        </div>

        <!-- Whitelist -->
        <div class="col-md-8">
            <div class="card shadow-sm">
                <div class="card-header bg-white d-flex justify-content-between align-items-center">
                    <h5 class="mb-0">Liste des Électeurs Autorisés (Whitelist)</h5>
                    <span class="badge bg-primary"><?php echo count($eligibleVoters); ?> total</span>
                </div>
                <div class="card-body p-0">
                    <div class="table-responsive" style="max-height: 500px; overflow-y: auto;">
                        <table class="table table-striped table-hover mb-0">
                            <thead class="table-light sticky-top">
                                <tr>
                                    <th>Matricule</th>
                                    <th>Nom Complet</th>
                                    <th>Email</th>
                                    <th>Statut</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($eligibleVoters as $voter): ?>
                                <tr>
                                    <td class="font-monospace fw-bold"><?php echo e($voter['identifier_code']); ?></td>
                                    <td><?php echo e($voter['first_name'] . ' ' . $voter['last_name']); ?></td>
                                    <td><?php echo e($voter['email'] ?? '-'); ?></td>
                                    <td>
                                        <?php if ($voter['is_registered']): ?>
                                            <span class="badge bg-success"><i class="bi bi-check-circle"></i> Inscrit</span>
                                        <?php else: ?>
                                            <span class="badge bg-secondary">En attente</span>
                                        <?php endif; ?>
                                    </td>
                                </tr>
                                <?php endforeach; ?>
                                
                                <?php if (empty($eligibleVoters)): ?>
                                <tr>
                                    <td colspan="4" class="text-center py-4 text-muted">
                                        Aucun électeur dans la liste blanche. Importez-en un pour commencer.
                                    </td>
                                </tr>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<?php require_once __DIR__ . '/../../layouts/footer.php'; ?>
