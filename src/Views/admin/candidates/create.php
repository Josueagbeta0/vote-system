<?php require_once __DIR__ . '/../../layouts/header.php'; ?>

<div class="container py-4">
    <div class="row justify-content-center">
        <div class="col-lg-8">
            <div class="card border-0 shadow-sm">
                <div class="card-header bg-info text-white">
                    <h4 class="mb-0">
                        <i class="bi bi-person-plus"></i> Ajouter un Candidat
                    </h4>
                    <p class="mb-0 mt-1 opacity-75">
                        Pour l'élection : <strong><?php echo e($election['title']); ?></strong>
                    </p>
                </div>
                
                <div class="card-body p-4">
                    <form method="POST" 
                          action="<?php echo BASE_URL; ?>/admin/elections/<?php echo $election['id']; ?>/store-candidate"
                          enctype="multipart/form-data">
                        
                        <!-- CSRF Token -->
                        <input type="hidden" name="csrf_token" value="<?php echo generateCsrfToken(); ?>">
                        
                        <!-- Nom du candidat -->
                        <div class="mb-4">
                            <label for="name" class="form-label fw-bold">
                                <i class="bi bi-person"></i> Nom Complet du Candidat <span class="text-danger">*</span>
                            </label>
                            <input type="text" 
                                   class="form-control form-control-lg" 
                                   id="name" 
                                   name="name" 
                                   placeholder="Ex: Marie Dupont"
                                   required
                                   autofocus>
                        </div>
                        
                        <!-- Photo -->
                        <div class="mb-4">
                            <label for="photo" class="form-label fw-bold">
                                <i class="bi bi-image"></i> Photo du Candidat
                            </label>
                            <input type="file" 
                                   class="form-control" 
                                   id="photo" 
                                   name="photo" 
                                   accept="image/jpeg,image/png,image/jpg"
                                   onchange="previewImage(this)">
                            <small class="text-muted">
                                Format: JPG, PNG. Taille max: 2 MB. Recommandé: 400x400px
                            </small>
                            
                            <!-- Prévisualisation -->
                            <div id="imagePreview" class="mt-3" style="display: none;">
                                <img id="preview" 
                                     src="" 
                                     alt="Aperçu" 
                                     class="img-thumbnail" 
                                     style="max-width: 200px;">
                            </div>
                        </div>
                        
                        <!-- Description / Programme -->
                        <div class="mb-4">
                            <label for="description" class="form-label fw-bold">
                                <i class="bi bi-card-text"></i> Description / Programme
                            </label>
                            <textarea class="form-control" 
                                      id="description" 
                                      name="description" 
                                      rows="5"
                                      placeholder="Présentez le candidat, son parcours, ses propositions..."></textarea>
                            <small class="text-muted">
                                Les électeurs verront cette description avant de voter
                            </small>
                        </div>
                        
                        <!-- Position (ordre d'affichage) -->
                        <div class="mb-4">
                            <label for="position" class="form-label fw-bold">
                                <i class="bi bi-sort-numeric-down"></i> Ordre d'Affichage
                            </label>
                            <input type="number" 
                                   class="form-control" 
                                   id="position" 
                                   name="position" 
                                   value="0"
                                   min="0">
                            <small class="text-muted">
                                Les candidats seront affichés selon cet ordre (0 = premier)
                            </small>
                        </div>
                        
                        <!-- Informations -->
                        <div class="alert alert-info">
                            <i class="bi bi-info-circle"></i> <strong>Conseils :</strong>
                            <ul class="mb-0 mt-2">
                                <li>Utilisez une photo de qualité pour une meilleure présentation</li>
                                <li>Rédigez une description claire et concise</li>
                                <li>Mettez en avant les points forts du candidat</li>
                            </ul>
                        </div>
                        
                        <!-- Boutons -->
                        <div class="d-flex gap-2">
                            <button type="submit" class="btn btn-info text-white btn-lg px-5">
                                <i class="bi bi-check-circle"></i> Ajouter le Candidat
                            </button>
                            <a href="<?php echo BASE_URL; ?>/admin/elections/view/<?php echo $election['id']; ?>" 
                               class="btn btn-outline-secondary btn-lg">
                                <i class="bi bi-x-circle"></i> Annuler
                            </a>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
function previewImage(input) {
    const preview = document.getElementById('preview');
    const previewDiv = document.getElementById('imagePreview');
    
    if (input.files && input.files[0]) {
        // Vérifier la taille
        if (input.files[0].size > 2 * 1024 * 1024) {
            alert('Le fichier est trop volumineux (max 2 MB)');
            input.value = '';
            previewDiv.style.display = 'none';
            return;
        }
        
        const reader = new FileReader();
        
        reader.onload = function(e) {
            preview.src = e.target.result;
            previewDiv.style.display = 'block';
        }
        
        reader.readAsDataURL(input.files[0]);
    } else {
        previewDiv.style.display = 'none';
    }
}
</script>

<?php require_once __DIR__ . '/../../layouts/footer.php'; ?>