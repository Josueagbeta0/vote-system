<?php require_once __DIR__ . '/../../layouts/header.php'; ?>

<div class="container py-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h2>
            <i class="bi bi-building"></i> Gestion des Établissements
        </h2>
        <a href="<?php echo BASE_URL; ?>/admin/organizations/create" class="btn btn-primary">
            <i class="bi bi-plus-lg"></i> Ajouter
        </a>
    </div>

    <div class="card shadow-sm">
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover mb-0">
                    <thead class="table-light">
                        <tr>
                            <th>Nom</th>
                            <th>Code Unique</th>
                            <th>Date Ajout</th>
                            <th class="text-end">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($organizations as $org): ?>
                        <tr>
                            <td>
                                <strong><?php echo e($org['name']); ?></strong>
                            </td>
                            <td>
                                <span class="badge bg-secondary font-monospace"><?php echo e($org['code']); ?></span>
                            </td>
                            <td><?php echo date('d/m/Y', strtotime($org['created_at'])); ?></td>
                            <td class="text-end">
                                <a href="<?php echo BASE_URL; ?>/admin/organizations/<?php echo $org['id']; ?>" class="btn btn-sm btn-outline-primary">
                                    <i class="bi bi-eye"></i> Gérer
                                </a>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                        
                        <?php if (empty($organizations)): ?>
                        <tr>
                            <td colspan="4" class="text-center py-4 text-muted">
                                Aucun établissement trouvé
                            </td>
                        </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<?php require_once __DIR__ . '/../../layouts/footer.php'; ?>
