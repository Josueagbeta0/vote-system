<?php require_once __DIR__ . '/../layouts/header.php'; ?>

<div class="container py-5">
    <div class="row justify-content-center">
        <div class="col-lg-6">
            <?php if ($verified): ?>
                <!-- Vérification RÉUSSIE -->
                <div class="card border-success shadow-lg">
                    <div class="card-header bg-success text-white text-center py-4">
                        <i class="bi bi-check-circle-fill" style="font-size: 5rem;"></i>
                        <h3 class="mt-3 mb-0">Vote Vérifié ✓</h3>
                    </div>
                    
                    <div class="card-body p-5 text-center">
                        <div class="alert alert-success">
                            <h5 class="alert-heading">
                                <i class="bi bi-shield-check"></i> Votre vote a été comptabilisé !
                            </h5>
                            <hr>
                            <p class="mb-0">
                                Nous confirmons que votre vote pour l'élection 
                                <strong>"<?php echo e($election_title); ?>"</strong> 
                                a bien été enregistré et sera pris en compte dans les résultats.
                            </p>
                        </div>
                        
                        <div class="mt-4">
                            <div class="row g-3">
                                <div class="col-12">
                                    <div class="p-3 bg-light rounded">
                                        <small class="text-muted d-block mb-1">Date du vote</small>
                                        <strong><?php echo date('d/m/Y à H:i:s', strtotime($voted_at)); ?></strong>
                                    </div>
                                </div>
                            </div>
                        </div>
                        
                        <div class="mt-4 p-4 bg-success bg-opacity-10 rounded">
                            <h6 class="text-success mb-3">
                                <i class="bi bi-info-circle-fill"></i> Garanties
                            </h6>
                            <div class="text-start">
                                <p class="small mb-2">
                                    <i class="bi bi-check2 text-success"></i>
                                    Votre vote est enregistré de manière sécurisée
                                </p>
                                <p class="small mb-2">
                                    <i class="bi bi-check2 text-success"></i>
                                    Votre choix reste totalement anonyme
                                </p>
                                <p class="small mb-0">
                                    <i class="bi bi-check2 text-success"></i>
                                    Votre vote ne peut pas être modifié ou supprimé
                                </p>
                            </div>
                        </div>
                        
                        <div class="mt-4">
                            <a href="<?php echo BASE_URL; ?>/voter/dashboard" class="btn btn-success btn-lg px-5">
                                <i class="bi bi-house-door"></i> Retour au Dashboard
                            </a>
                        </div>
                    </div>
                </div>
                
            <?php else: ?>
                <!-- Vérification ÉCHOUÉE -->
                <div class="card border-danger shadow-lg">
                    <div class="card-header bg-danger text-white text-center py-4">
                        <i class="bi bi-x-circle-fill" style="font-size: 5rem;"></i>
                        <h3 class="mt-3 mb-0">Token Non Reconnu</h3>
                    </div>
                    
                    <div class="card-body p-5 text-center">
                        <div class="alert alert-danger">
                            <h5 class="alert-heading">
                                <i class="bi bi-exclamation-triangle"></i> Vérification échouée
                            </h5>
                            <hr>
                            <p class="mb-0">
                                Le token que vous avez saisi ne correspond à aucun vote enregistré dans notre système.
                            </p>
                        </div>
                        
                        <div class="mt-4 p-4 bg-warning bg-opacity-10 rounded">
                            <h6 class="text-warning mb-3">
                                <i class="bi bi-lightbulb"></i> Raisons possibles
                            </h6>
                            <div class="text-start">
                                <p class="small mb-2">
                                    <i class="bi bi-arrow-right text-warning"></i>
                                    Le token est incorrect (vérifiez qu'il n'y a pas d'erreur de frappe)
                                </p>
                                <p class="small mb-2">
                                    <i class="bi bi-arrow-right text-warning"></i>
                                    Le token a expiré ou est invalide
                                </p>
                                <p class="small mb-0">
                                    <i class="bi bi-arrow-right text-warning"></i>
                                    Vous n'avez pas encore voté
                                </p>
                            </div>
                        </div>
                        
                        <div class="mt-4 d-flex gap-2 justify-content-center">
                            <a href="<?php echo BASE_URL; ?>/vote/verify" class="btn btn-warning btn-lg px-4">
                                <i class="bi bi-arrow-repeat"></i> Réessayer
                            </a>
                            <a href="<?php echo BASE_URL; ?>/voter/dashboard" class="btn btn-outline-secondary btn-lg px-4">
                                <i class="bi bi-house-door"></i> Dashboard
                            </a>
                        </div>
                    </div>
                </div>
            <?php endif; ?>
            
            <!-- Aide -->
            <div class="card border-0 shadow-sm mt-4">
                <div class="card-body">
                    <h6 class="card-title">
                        <i class="bi bi-question-circle text-primary"></i> 
                        Besoin d'aide ?
                    </h6>
                    <p class="card-text text-muted small">
                        Si vous rencontrez des difficultés pour vérifier votre vote, 
                        veuillez contacter un administrateur. Conservez précieusement votre 
                        token de vérification car c'est la seule preuve de votre participation.
                    </p>
                </div>
            </div>
        </div>
    </div>
</div>

<?php require_once __DIR__ . '/../layouts/footer.php'; ?>