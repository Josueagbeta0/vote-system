<?php require_once __DIR__ . '/layouts/header.php'; ?>

<!-- Hero Section -->
<section class="py-5 hero-bg">
    <div class="container">
        <div class="row align-items-center text-white">
            <div class="col-lg-6 mb-4 mb-lg-0">
                <h1 class="display-4 fw-bold mb-4">
                    <i class="bi bi-shield-lock"></i> Vote Sécurisé
                </h1>
                <p class="lead mb-4">
                    Participez aux élections en toute sécurité avec notre plateforme de vote électronique moderne, transparente et fiable.
                </p>
                <div class="d-flex gap-3">
                    <a href="<?php echo BASE_URL; ?>/auth/register" class="btn btn-light btn-lg px-4">
                        <i class="bi bi-person-plus"></i> S'inscrire
                    </a>
                    <a href="<?php echo BASE_URL; ?>/auth/login" class="btn btn-outline-light btn-lg px-4">
                        <i class="bi bi-box-arrow-in-right"></i> Se connecter
                    </a>
                </div>
            </div>
            <div class="col-lg-6 text-center">
                <i class="bi bi-ballot-heart" style="font-size: 15rem; opacity: 0.9;"></i>
            </div>
        </div>
    </div>
</section>

<!-- Features Section -->
<section class="py-5">
    <div class="container">
        <div class="text-center mb-5">
            <h2 class="fw-bold">Pourquoi choisir notre plateforme ?</h2>
            <p class="text-muted">Une solution de vote électronique sécurisée et transparente</p>
        </div>
        
        <div class="row g-4">
            <!-- Feature 1 -->
            <div class="col-md-4">
                <div class="card h-100 text-center p-4">
                    <div class="mb-3">
                        <i class="bi bi-shield-check text-primary" style="font-size: 4rem;"></i>
                    </div>
                    <h4>100% Sécurisé</h4>
                    <p class="text-muted">
                        Vos votes sont chiffrés avec l'algorithme AES-256 et protégés par une blockchain simplifiée garantissant leur intégrité.
                    </p>
                </div>
            </div>
            
            <!-- Feature 2 -->
            <div class="col-md-4">
                <div class="card h-100 text-center p-4">
                    <div class="mb-3">
                        <i class="bi bi-incognito text-success" style="font-size: 4rem;"></i>
                    </div>
                    <h4>Anonymat Garanti</h4>
                    <p class="text-muted">
                        Votre vote reste totalement anonyme. Personne, pas même les administrateurs, ne peut savoir pour qui vous avez voté.
                    </p>
                </div>
            </div>
            
            <!-- Feature 3 -->
            <div class="col-md-4">
                <div class="card h-100 text-center p-4">
                    <div class="mb-3">
                        <i class="bi bi-lightning-charge text-warning" style="font-size: 4rem;"></i>
                    </div>
                    <h4>Simple & Rapide</h4>
                    <p class="text-muted">
                        Interface intuitive permettant de voter en quelques clics. Résultats disponibles immédiatement après la clôture.
                    </p>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- How It Works Section -->
<section class="py-5 bg-light">
    <div class="container">
        <div class="text-center mb-5">
            <h2 class="fw-bold">Comment ça marche ?</h2>
            <p class="text-muted">Votez en 3 étapes simples</p>
        </div>
        
        <div class="row g-4">
            <!-- Step 1 -->
            <div class="col-md-4">
                <div class="text-center">
                    <div class="rounded-circle bg-primary text-white d-inline-flex align-items-center justify-content-center mb-3" 
                         style="width: 80px; height: 80px; font-size: 2rem; font-weight: bold;">
                        1
                    </div>
                    <h5>Inscrivez-vous</h5>
                    <p class="text-muted">
                        Créez votre compte en quelques secondes avec votre email et un mot de passe sécurisé.
                    </p>
                </div>
            </div>
            
            <!-- Step 2 -->
            <div class="col-md-4">
                <div class="text-center">
                    <div class="rounded-circle bg-success text-white d-inline-flex align-items-center justify-content-center mb-3" 
                         style="width: 80px; height: 80px; font-size: 2rem; font-weight: bold;">
                        2
                    </div>
                    <h5>Choisissez une élection</h5>
                    <p class="text-muted">
                        Consultez les élections en cours et découvrez les candidats avant de voter.
                    </p>
                </div>
            </div>
            
            <!-- Step 3 -->
            <div class="col-md-4">
                <div class="text-center">
                    <div class="rounded-circle bg-warning text-white d-inline-flex align-items-center justify-content-center mb-3" 
                         style="width: 80px; height: 80px; font-size: 2rem; font-weight: bold;">
                        3
                    </div>
                    <h5>Votez en toute sécurité</h5>
                    <p class="text-muted">
                        Exprimez votre choix en un clic. Un token de vérification vous sera fourni.
                    </p>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- Stats Section -->
<section class="py-5">
    <div class="container">
        <div class="row text-center">
            <div class="col-md-3 mb-4">
                <div class="card border-0 shadow-sm p-4">
                    <h2 class="text-primary fw-bold mb-0">
                        <i class="bi bi-shield-check"></i>
                    </h2>
                    <h3 class="fw-bold mb-0">100%</h3>
                    <p class="text-muted mb-0">Sécurisé</p>
                </div>
            </div>
            
            <div class="col-md-3 mb-4">
                <div class="card border-0 shadow-sm p-4">
                    <h2 class="text-success fw-bold mb-0">
                        <i class="bi bi-lock-fill"></i>
                    </h2>
                    <h3 class="fw-bold mb-0">AES-256</h3>
                    <p class="text-muted mb-0">Chiffrement</p>
                </div>
            </div>
            
            <div class="col-md-3 mb-4">
                <div class="card border-0 shadow-sm p-4">
                    <h2 class="text-info fw-bold mb-0">
                        <i class="bi bi-eye-slash"></i>
                    </h2>
                    <h3 class="fw-bold mb-0">Anonyme</h3>
                    <p class="text-muted mb-0">Vote Secret</p>
                </div>
            </div>
            
            <div class="col-md-3 mb-4">
                <div class="card border-0 shadow-sm p-4">
                    <h2 class="text-warning fw-bold mb-0">
                        <i class="bi bi-lightning-charge-fill"></i>
                    </h2>
                    <h3 class="fw-bold mb-0">Instantané</h3>
                    <p class="text-muted mb-0">Résultats</p>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- CTA Section -->
<section class="py-5 bg-primary text-white">
    <div class="container text-center">
        <h2 class="fw-bold mb-4">Prêt à voter en toute sécurité ?</h2>
        <p class="lead mb-4">
            Rejoignez-nous dès maintenant et participez aux élections de manière moderne et sécurisée.
        </p>
        <a href="<?php echo BASE_URL; ?>/auth/register" class="btn btn-light btn-lg px-5">
            <i class="bi bi-person-plus"></i> Commencer maintenant
        </a>
    </div>
</section>

<?php require_once __DIR__ . '/layouts/footer.php'; ?>