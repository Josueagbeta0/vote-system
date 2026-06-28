<?php require_once __DIR__ . '/../layouts/header.php'; ?>

<div class="container py-5">
    <div class="text-center mb-5">
        <h1 class="display-4 fw-bold mb-3">Bienvenue sur VoteSecure</h1>
        <p class="lead text-muted">La plateforme de vote électronique sécurisée pour tous.</p>
    </div>

    <div class="row justify-content-center g-4">
        <!-- École -->
        <div class="col-md-4">
            <div class="card h-100 border-0 shadow-sm hover-shadow transition-all text-center p-4">
                <div class="card-body">
                    <div class="icon-circle bg-primary bg-opacity-10 text-primary mx-auto mb-4" style="width: 80px; height: 80px; border-radius: 50%; display: flex; align-items: center; justify-content: center;">
                        <i class="bi bi-backpack display-5"></i>
                    </div>
                    <h3 class="card-title fw-bold mb-3">École</h3>
                    <p class="card-text text-muted mb-4">Pour les écoles primaires, collèges et lycées. Votez pour vos délégués et projets scolaires.</p>
                    <a href="<?php echo BASE_URL; ?>/portal/school" class="btn btn-primary btn-lg w-100 rounded-pill">
                        Accéder
                    </a>
                </div>
            </div>
        </div>

        <!-- Université -->
        <div class="col-md-4">
            <div class="card h-100 border-0 shadow-sm hover-shadow transition-all text-center p-4">
                <div class="card-body">
                    <div class="icon-circle bg-success bg-opacity-10 text-success mx-auto mb-4" style="width: 80px; height: 80px; border-radius: 50%; display: flex; align-items: center; justify-content: center;">
                        <i class="bi bi-mortarboard display-5"></i>
                    </div>
                    <h3 class="card-title fw-bold mb-3">Université</h3>
                    <p class="card-text text-muted mb-4">Pour les facultés et grandes écoles. Élections BDE, conseils d'administration, etc.</p>
                    <a href="<?php echo BASE_URL; ?>/portal/university" class="btn btn-success btn-lg w-100 rounded-pill">
                        Accéder
                    </a>
                </div>
            </div>
        </div>

        <!-- Entreprise -->
        <div class="col-md-4">
            <div class="card h-100 border-0 shadow-sm hover-shadow transition-all text-center p-4">
                <div class="card-body">
                    <div class="icon-circle bg-info bg-opacity-10 text-info mx-auto mb-4" style="width: 80px; height: 80px; border-radius: 50%; display: flex; align-items: center; justify-content: center;">
                        <i class="bi bi-building display-5"></i>
                    </div>
                    <h3 class="card-title fw-bold mb-3">Entreprise</h3>
                    <p class="card-text text-muted mb-4">Pour les comités d'entreprise, syndicats et assemblées générales.</p>
                    <a href="<?php echo BASE_URL; ?>/portal/company" class="btn btn-info text-white btn-lg w-100 rounded-pill">
                        Accéder
                    </a>
                </div>
            </div>
        </div>
    </div>

    <div class="text-center mt-5 pt-4 border-top">
        <p class="mb-3">Vous souhaitez créer un espace de vote pour votre organisation ?</p>
        <a href="<?php echo BASE_URL; ?>/create-organization" class="btn btn-outline-dark">
            <i class="bi bi-plus-lg me-2"></i> Créer mon espace
        </a>
    </div>
</div>

<style>
.hover-shadow:hover {
    transform: translateY(-5px);
    box-shadow: 0 .5rem 1rem rgba(0,0,0,.15)!important;
}
.transition-all {
    transition: all 0.3s ease;
}
</style>

<?php require_once __DIR__ . '/../layouts/footer.php'; ?>
