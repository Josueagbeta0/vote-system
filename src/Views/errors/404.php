<?php require_once __DIR__ . '/../layouts/header.php'; ?>

<div class="container">
    <div class="row justify-content-center min-vh-100 align-items-center">
        <div class="col-md-6 text-center">
            <i class="bi bi-exclamation-triangle text-warning" style="font-size: 8rem;"></i>
            <h1 class="display-1 fw-bold">404</h1>
            <h2 class="mb-4">Page introuvable</h2>
            <p class="lead text-muted mb-4">
                Désolé, la page que vous recherchez n'existe pas ou a été déplacée.
            </p>
            <a href="<?php echo BASE_URL; ?>" class="btn btn-primary btn-lg">
                <i class="bi bi-house-door"></i> Retour à l'accueil
            </a>
        </div>
    </div>
</div>

<?php require_once __DIR__ . '/../layouts/footer.php'; ?>