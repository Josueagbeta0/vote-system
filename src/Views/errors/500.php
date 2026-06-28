<?php require_once __DIR__ . '/../layouts/header.php'; ?>

<div class="container">
    <div class="row justify-content-center min-vh-100 align-items-center">
        <div class="col-md-6 text-center">
            <i class="bi bi-exclamation-octagon text-danger" style="font-size: 8rem;"></i>
            <h1 class="display-1 fw-bold">500</h1>
            <h2 class="mb-4">Erreur Interne du Serveur</h2>
            <p class="lead text-muted mb-4">
                Oups ! Quelque chose s'est mal passé de notre côté. 
                Nous travaillons pour résoudre ce problème.
            </p>
            
            <div class="alert alert-warning">
                <i class="bi bi-info-circle"></i>
                Si le problème persiste, veuillez contacter l'administrateur.
            </div>
            
            <div class="d-flex gap-2 justify-content-center">
                <a href="<?php echo BASE_URL; ?>" class="btn btn-primary btn-lg">
                    <i class="bi bi-house-door"></i> Retour à l'accueil
                </a>
                <button onclick="history.back()" class="btn btn-outline-secondary btn-lg">
                    <i class="bi bi-arrow-left"></i> Page précédente
                </button>
            </div>
        </div>
    </div>
</div>

<?php require_once __DIR__ . '/../layouts/footer.php'; ?>