<?php require_once __DIR__ . '/../layouts/header.php'; ?>

<div class="container py-5">
    <div class="row justify-content-center">
        <div class="col-lg-8">
            <div class="d-flex align-items-center mb-4">
                <a href="<?php echo BASE_URL; ?>/" class="btn btn-outline-secondary me-3">
                    <i class="bi bi-arrow-left"></i> Retour
                </a>
                <h2 class="mb-0 fw-bold">Choisir votre <?php echo e($typeName); ?></h2>
            </div>

            <!-- Search Input -->
            <div class="card border-0 shadow-sm mb-4">
                <div class="card-body p-4">
                    <div class="input-group input-group-lg">
                        <span class="input-group-text bg-white border-end-0">
                            <i class="bi bi-search text-muted"></i>
                        </span>
                        <input type="text" id="orgSearch" class="form-control border-start-0" placeholder="Rechercher par nom ou code...">
                    </div>
                </div>
            </div>

            <!-- Results List -->
            <div id="orgList" class="list-group shadow-sm">
                <?php if (empty($organizations)): ?>
                    <div class="list-group-item p-5 text-center text-muted">
                        <i class="bi bi-inbox display-4 mb-3 d-block opacity-50"></i>
                        Aucune organisation trouvée dans cette catégorie.
                    </div>
                <?php else: ?>
                    <?php foreach ($organizations as $org): ?>
                        <a href="<?php echo BASE_URL; ?>/portal/access/<?php echo e($org['slug']); ?>" class="list-group-item list-group-item-action p-4 d-flex align-items-center justify-content-between org-item">
                            <div>
                                <h5 class="mb-1 fw-bold"><?php echo e($org['name']); ?></h5>
                                <small class="text-muted">Code: <?php echo e($org['code']); ?></small>
                            </div>
                            <span class="btn btn-sm btn-outline-primary rounded-pill px-3">
                                Choisir <i class="bi bi-chevron-right ms-1"></i>
                            </span>
                        </a>
                    <?php endforeach; ?>
                <?php endif; ?>
            </div>
            
            <div id="noResults" class="list-group-item p-5 text-center text-muted d-none shadow-sm bg-white rounded">
                <i class="bi bi-search display-4 mb-3 d-block opacity-50"></i>
                Aucun résultat ne correspond à votre recherche.
            </div>

        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const searchInput = document.getElementById('orgSearch');
    const orgItems = document.querySelectorAll('.org-item');
    const noResults = document.getElementById('noResults');

    searchInput.addEventListener('input', function(e) {
        const term = e.target.value.toLowerCase();
        let visibleCount = 0;

        orgItems.forEach(item => {
            const name = item.querySelector('h5').textContent.toLowerCase();
            const code = item.querySelector('small').textContent.toLowerCase();
            
            if (name.includes(term) || code.includes(term)) {
                item.style.display = ''; // Show
                visibleCount++;
            } else {
                item.style.display = 'none'; // Hide
            }
        });

        if (visibleCount === 0 && orgItems.length > 0) {
            noResults.classList.remove('d-none');
        } else {
            noResults.classList.add('d-none');
        }
    });
});
</script>

<?php require_once __DIR__ . '/../layouts/footer.php'; ?>
