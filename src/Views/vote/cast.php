<?php require_once __DIR__ . '/../layouts/header.php'; ?>

<div class="container py-5">
    <!-- Header avec compte à rebours -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="alert alert-primary d-flex justify-content-between align-items-center">
                <div>
                    <h4 class="alert-heading mb-1">
                        <i class="bi bi-ballot-heart"></i> <?php echo e($election['title']); ?>
                    </h4>
                    <p class="mb-0"><?php echo e($election['description']); ?></p>
                </div>
                <div class="text-end">
                    <small class="d-block text-muted">Clôture le</small>
                    <strong><?php echo date('d/m/Y à H:i', strtotime($election['end_date'])); ?></strong>
                </div>
            </div>
        </div>
    </div>
    
    <!-- Instructions -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="card border-info">
                <div class="card-body">
                    <h5 class="card-title text-info">
                        <i class="bi bi-info-circle-fill"></i> Instructions
                    </h5>
                    <ul class="mb-0">
                        <li>Lisez attentivement les informations sur chaque candidat</li>
                        <li>Sélectionnez le candidat de votre choix en cliquant sur sa carte</li>
                        <li>Validez votre vote en cliquant sur le bouton "Confirmer mon Vote"</li>
                        <li><strong>⚠️ Attention :</strong> Une fois validé, votre vote est définitif et ne peut être modifié</li>
                        <li><strong>🔒 Confidentialité :</strong> Votre choix reste totalement anonyme</li>
                    </ul>
                </div>
            </div>
        </div>
    </div>
    
    <!-- Formulaire de vote -->
    <form method="POST" action="<?php echo BASE_URL; ?>/vote/process/<?php echo $election['id']; ?>" id="voteForm">
        <input type="hidden" name="csrf_token" value="<?php echo generateCsrfToken(); ?>">
        <input type="hidden" name="voter_id" value="<?php echo $voter_id; ?>">
        <input type="hidden" name="candidate_id" id="selected_candidate" value="">
        
        <!-- Grille des candidats -->
        <div class="row g-4 mb-4" id="candidatesGrid">
            <?php foreach ($candidates as $candidate): ?>
                <div class="col-md-6 col-lg-4">
                    <div class="card candidate-card h-100" 
                         data-candidate-id="<?php echo $candidate['id']; ?>"
                         onclick="selectCandidate(<?php echo $candidate['id']; ?>)">
                        
                        <!-- Photo -->
                        <?php if ($candidate['photo_url']): ?>
                            <img src="<?php echo BASE_URL . $candidate['photo_url']; ?>" 
                                 class="card-img-top" 
                                 style="height: 250px; object-fit: cover;"
                                 alt="<?php echo e($candidate['name']); ?>">
                        <?php else: ?>
                            <div class="card-img-top bg-gradient-primary text-white 
                                        d-flex align-items-center justify-content-center" 
                                 style="height: 250px;">
                                <i class="bi bi-person-circle" style="font-size: 8rem;"></i>
                            </div>
                        <?php endif; ?>
                        
                        <!-- Indicateur de sélection -->
                        <div class="selection-indicator">
                            <i class="bi bi-check-circle-fill"></i>
                        </div>
                        
                        <!-- Contenu -->
                        <div class="card-body">
                            <h5 class="card-title fw-bold">
                                <i class="bi bi-person-badge text-primary"></i>
                                <?php echo e($candidate['name']); ?>
                            </h5>
                            
                            <?php if ($candidate['description']): ?>
                                <div class="candidate-description mb-3">
                                    <p class="card-text text-muted small">
                                        <?php echo substr(strip_tags($candidate['description']), 0, 100) . '...'; ?>
                                    </p>
                                </div>
                                <button type="button" 
                                        class="btn btn-sm btn-link text-decoration-none p-0 mb-3" 
                                        onclick="event.stopPropagation(); showProfile(<?php echo $candidate['id']; ?>)">
                                    <i class="bi bi-eye"></i> Voir le profil complet
                                </button>
                            <?php endif; ?>
                        </div>
                        
                        <div class="card-footer bg-white border-0 text-center">
                            <button type="button" 
                                    class="btn btn-outline-primary btn-lg w-100 select-btn"
                                    onclick="selectCandidate(<?php echo $candidate['id']; ?>)">
                                <i class="bi bi-hand-index"></i> Choisir
                            </button>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
        
        <!-- Zone de validation -->
        <div class="row">
            <div class="col-12">
                <div id="confirmationZone" class="card border-success" style="display: none;">
                    <div class="card-body text-center p-4">
                        <h4 class="text-success mb-3">
                            <i class="bi bi-check-circle-fill"></i> Vous avez sélectionné :
                        </h4>
                        <h3 id="selectedCandidateName" class="mb-4"></h3>
                        
                        <div class="alert alert-warning">
                            <i class="bi bi-exclamation-triangle"></i>
                            <strong>Êtes-vous sûr de votre choix ?</strong><br>
                            Une fois confirmé, votre vote sera définitif et ne pourra pas être modifié.
                        </div>
                        
                        <div class="d-flex gap-3 justify-content-center">
                            <button type="submit" class="btn btn-success btn-lg px-5">
                                <i class="bi bi-lock-fill"></i> Confirmer mon Vote
                            </button>
                            <button type="button" 
                                    class="btn btn-outline-secondary btn-lg px-5"
                                    onclick="cancelSelection()">
                                <i class="bi bi-x-circle"></i> Annuler
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </form>
    </form>
    
    <!-- Modal Profil Candidat -->
    <div class="modal fade" id="profileModal" tabindex="-1">
        <div class="modal-dialog modal-lg modal-dialog-centered">
            <div class="modal-content border-0">
                <div class="modal-header border-0 pb-0">
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body pb-5">
                    <div class="text-center mb-4">
                        <img id="modalPhoto" src="" class="rounded-circle shadow-lg mb-3" style="width: 150px; height: 150px; object-fit: cover;">
                        <h3 id="modalName" class="fw-bold"></h3>
                    </div>
                    <div class="px-4">
                        <h5 class="text-primary mb-3"><i class="bi bi-card-text"></i> Programme / Biographie</h5>
                        <div id="modalDescription" class="text-muted" style="white-space: pre-line; font-size: 1.1rem; line-height: 1.6;"></div>
                    </div>
                </div>
                <div class="modal-footer border-0 justify-content-center pb-4">
                    <button type="button" class="btn btn-primary btn-lg rounded-pill px-5" id="modalSelectBtn">
                        <i class="bi bi-hand-index"></i> Voter pour ce candidat
                    </button>
                </div>
            </div>
        </div>
    </div>
</div>

<style>
.candidate-card {
    cursor: pointer;
    transition: all 0.3s ease;
    border: 3px solid transparent;
    position: relative;
}

.candidate-card:hover {
    transform: translateY(-5px);
    box-shadow: 0 10px 30px rgba(0,0,0,0.2);
}

.candidate-card.selected {
    border-color: #10B981;
    box-shadow: 0 0 20px rgba(16, 185, 129, 0.3);
}

.selection-indicator {
    position: absolute;
    top: 15px;
    right: 15px;
    background: #10B981;
    color: white;
    width: 50px;
    height: 50px;
    border-radius: 50%;
    display: none;
    align-items: center;
    justify-content: center;
    font-size: 1.5rem;
    z-index: 10;
    box-shadow: 0 4px 10px rgba(0,0,0,0.3);
}

.candidate-card.selected .selection-indicator {
    display: flex;
    animation: bounceIn 0.5s;
}

@keyframes bounceIn {
    0% { transform: scale(0); }
    50% { transform: scale(1.2); }
    100% { transform: scale(1); }
}

.candidate-description {
    max-height: 150px;
    overflow-y: auto;
}

.bg-gradient-primary {
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
}

.select-btn {
    font-weight: bold;
    transition: all 0.3s;
}

.candidate-card.selected .select-btn {
    background: #10B981;
    color: white;
    border-color: #10B981;
}
</style>

<script>
let selectedCandidateId = null;
const candidates = <?php echo json_encode($candidates); ?>;

function selectCandidate(candidateId) {
    // Retirer la sélection précédente
    document.querySelectorAll('.candidate-card').forEach(card => {
        card.classList.remove('selected');
    });
    
    // Ajouter la nouvelle sélection
    const selectedCard = document.querySelector(`[data-candidate-id="${candidateId}"]`);
    selectedCard.classList.add('selected');
    
    // Mettre à jour l'input caché
    document.getElementById('selected_candidate').value = candidateId;
    selectedCandidateId = candidateId;
    
    // Trouver le nom du candidat
    const candidate = candidates.find(c => c.id == candidateId);
    document.getElementById('selectedCandidateName').textContent = candidate.name;
    
    // Afficher la zone de confirmation
    document.getElementById('confirmationZone').style.display = 'block';
    
    // Scroll vers la zone de confirmation
    document.getElementById('confirmationZone').scrollIntoView({ 
        behavior: 'smooth', 
        block: 'center' 
    });
}

function cancelSelection() {
    // Retirer toutes les sélections
    document.querySelectorAll('.candidate-card').forEach(card => {
        card.classList.remove('selected');
    });
    
    // Réinitialiser
    document.getElementById('selected_candidate').value = '';
    selectedCandidateId = null;
    
    // Cacher la zone de confirmation
    document.getElementById('confirmationZone').style.display = 'none';
}


function showProfile(candidateId) {
    const candidate = candidates.find(c => c.id == candidateId);
    
    // Remplir la modale
    document.getElementById('modalName').textContent = candidate.name;
    document.getElementById('modalDescription').textContent = candidate.description;
    
    const photo = document.getElementById('modalPhoto');
    if (candidate.photo_url) {
        photo.src = "<?php echo BASE_URL; ?>" + candidate.photo_url;
    } else {
        // Placeholder handling if needed, or hide image
        photo.src = 'https://via.placeholder.com/150'; 
    }
    
    // Bouton de sélection dans la modale
    const btn = document.getElementById('modalSelectBtn');
    btn.onclick = function() {
        selectCandidate(candidateId);
        bootstrap.Modal.getInstance(document.getElementById('profileModal')).hide();
    };
    
    // Afficher
    new bootstrap.Modal(document.getElementById('profileModal')).show();
}

// Validation avant soumission
document.getElementById('voteForm').addEventListener('submit', function(e) {
    if (!selectedCandidateId) {
        e.preventDefault();
        alert('Veuillez sélectionner un candidat !');
        return false;
    }
    
    // Double confirmation
    const candidate = candidates.find(c => c.id == selectedCandidateId);
    const confirmed = confirm(
        `Confirmez-vous votre vote pour :\n\n${candidate.name}\n\n` +
        `⚠️ Cette action est DÉFINITIVE et ne peut pas être annulée !`
    );
    
    if (!confirmed) {
        e.preventDefault();
        return false;
    }
});
</script>

<?php require_once __DIR__ . '/../layouts/footer.php'; ?>