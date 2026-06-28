<?php require_once __DIR__ . '/../layouts/header.php'; ?>

<div class="container py-5">
    <div class="row justify-content-center">
        <div class="col-lg-6">
            <div class="card border-0 shadow-lg">
                <div class="card-header bg-primary text-white text-center py-4">
                    <i class="bi bi-shield-check" style="font-size: 3rem;"></i>
                    <h3 class="mt-3 mb-0">Vérifier mon Vote</h3>
                    <p class="mb-0 opacity-75">Assurez-vous que votre vote a été comptabilisé</p>
                </div>
                
                <div class="card-body p-5">
                    <form method="POST" action="<?php echo BASE_URL; ?>/vote/process-verify">
                        <div class="mb-4">
                            <label for="token" class="form-label fw-bold">
                                <i class="bi bi-key-fill"></i> Token de Vérification
                            </label>
                            <input type="text" 
                                   class="form-control form-control-lg font-monospace" 
                                   id="token" 
                                   name="token" 
                                   placeholder="Entrez votre token de vérification"
                                   required
                                   autofocus>
                            <small class="text-muted">
                                Le token vous a été fourni après avoir voté
                            </small>
                        </div>
                        
                        <div class="alert alert-info">
                            <i class="bi bi-info-circle"></i>
                            <strong>Où trouver mon token ?</strong>
                            <ul class="mb-0 mt-2">
                                <li>Sur la page de confirmation après votre vote</li>
                                <li>Dans l'email de confirmation qui vous a été envoyé</li>
                            </ul>
                        </div>
                        
                        <button type="submit" class="btn btn-primary btn-lg w-100">
                            <i class="bi bi-search"></i> Vérifier
                        </button>
                    </form>
                </div>
            </div>
            
            <!-- Informations supplémentaires -->
            <div class="card border-0 shadow-sm mt-4">
                <div class="card-body">
                    <h6 class="card-title">
                        <i class="bi bi-question-circle text-primary"></i> 
                        Pourquoi vérifier mon vote ?
                    </h6>
                    <p class="card-text text-muted small mb-0">
                        La vérification vous permet de confirmer que votre vote a bien été enregistré 
                        et comptabilisé dans le système. C'est une garantie supplémentaire de transparence 
                        et d'intégrité du processus électoral.
                    </p>
                </div>
            </div>
        </div>
    </div>
</div>

<?php require_once __DIR__ . '/../layouts/footer.php'; ?>