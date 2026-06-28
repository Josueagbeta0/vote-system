<?php require_once __DIR__ . '/../../layouts/header.php'; ?>

<div class="container-fluid py-4">
    <div class="row">
        <!-- Sidebar -->
        <div class="col-md-2">
            <div class="list-group shadow-sm">
                <a href="<?php echo BASE_URL; ?>/admin/dashboard" class="list-group-item list-group-item-action">
                    <i class="bi bi-speedometer2 me-2"></i> Tableau de bord
                </a>
                <a href="<?php echo BASE_URL; ?>/admin/elections" class="list-group-item list-group-item-action">
                    <i class="bi bi-calendar-check me-2"></i> Élections
                </a>
                <a href="<?php echo BASE_URL; ?>/admin/users" class="list-group-item list-group-item-action active">
                    <i class="bi bi-people me-2"></i> Utilisateurs
                </a>
                <a href="<?php echo BASE_URL; ?>/admin/security/logs" class="list-group-item list-group-item-action">
                    <i class="bi bi-shield-lock me-2"></i> Logs Sécurité
                </a>
            </div>
        </div>

        <!-- Main Content -->
        <div class="col-md-10">
            <div class="d-flex justify-content-between align-items-center mb-4">
                <h2 class="fw-bold">Gestion des Utilisateurs</h2>
            </div>
            
            <div class="card shadow-sm">
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-hover align-middle">
                            <thead class="table-light">
                                <tr>
                                    <th>ID</th>
                                    <th>Utilisateur</th>
                                    <th>Email</th>
                                    <th>Rôle</th>
                                    <th>Statut</th>
                                    <th>Inscrit le</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($users as $user): ?>
                                <tr>
                                    <td>#<?php echo $user['id']; ?></td>
                                    <td>
                                        <div class="d-flex align-items-center">
                                            <div class="avatar-sm me-2 bg-primary text-white rounded-circle d-flex align-items-center justify-content-center" style="width: 32px; height: 32px;">
                                                <?php echo strtoupper(substr($user['first_name'], 0, 1) . substr($user['last_name'], 0, 1)); ?>
                                            </div>
                                            <div>
                                                <div class="fw-bold"><?php echo e($user['first_name'] . ' ' . $user['last_name']); ?></div>
                                            </div>
                                        </div>
                                    </td>
                                    <td><?php echo e($user['email']); ?></td>
                                    <td>
                                        <?php if ($user['role'] === 'admin'): ?>
                                            <span class="badge bg-purple text-white">Administrateur</span>
                                        <?php else: ?>
                                            <span class="badge bg-info text-dark">Électeur</span>
                                        <?php endif; ?>
                                    </td>
                                    <td>
                                        <?php if ($user['is_verified']): ?>
                                            <span class="badge bg-success">Vérifié</span>
                                        <?php else: ?>
                                            <span class="badge bg-warning text-dark">Non vérifié</span>
                                        <?php endif; ?>
                                    </td>
                                    <td><?php echo date('d/m/Y', strtotime($user['created_at'])); ?></td>
                                    <td>
                                        <button class="btn btn-sm btn-outline-primary" title="Éditer">
                                            <i class="bi bi-pencil"></i>
                                        </button>
                                        <?php if ($user['role'] !== 'admin' || $user['id'] !== $_SESSION['user']['id']): ?>
                                            <button class="btn btn-sm btn-outline-danger" title="Supprimer">
                                                <i class="bi bi-trash"></i>
                                            </button>
                                        <?php endif; ?>
                                    </td>
                                </tr>
                                <?php endforeach; ?>
                                
                                <?php if (empty($users)): ?>
                                <tr>
                                    <td colspan="7" class="text-center py-4 text-muted">Aucu utilisateur trouvé</td>
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
