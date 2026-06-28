</main>
    
    <!-- Footer -->
    <footer class="footer mt-5">
        <div class="container">
            <div class="row">
                <div class="col-md-4 mb-3">
                    <h5><i class="bi bi-shield-check"></i> Vote Sécurisé</h5>
                    <p class="text-light">
                        Plateforme de vote électronique sécurisée garantissant l'anonymat et l'intégrité de vos votes.
                    </p>
                </div>
                
                <div class="col-md-4 mb-3">
                    <h6>Liens Rapides</h6>
                    <ul class="list-unstyled">
                        <li><a href="<?php echo BASE_URL; ?>" class="text-light text-decoration-none">Accueil</a></li>
                        <?php if (!isset($_SESSION['logged_in'])): ?>
                            <li><a href="<?php echo BASE_URL; ?>/auth/login" class="text-light text-decoration-none">Connexion</a></li>
                            <li><a href="<?php echo BASE_URL; ?>/auth/register" class="text-light text-decoration-none">Inscription</a></li>
                        <?php endif; ?>
                    </ul>
                </div>
                
                <div class="col-md-4 mb-3">
                    <h6>Sécurité</h6>
                    <p class="text-light">
                        <i class="bi bi-lock-fill text-success"></i> Chiffrement AES-256<br>
                        <i class="bi bi-fingerprint text-success"></i> Authentification sécurisée<br>
                        <i class="bi bi-shield-check text-success"></i> Votes anonymes
                    </p>
                </div>
            </div>
            
            <hr class="bg-light">
            
            <div class="row">
                <div class="col-md-12 text-center">
                    <p class="mb-0">
                        &copy; <?php echo date('Y'); ?> Vote Sécurisé - Projet de Licence Informatique
                    </p>
                </div>
            </div>
        </div>
    </footer>
    
    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    
    <!-- jQuery (optionnel) -->
    <script src="https://code.jquery.com/jquery-3.7.0.min.js"></script>
    
    <!-- Custom JS -->
    <script src="<?php echo ASSETS_URL; ?>/js/main.js"></script>
    
    <script>
        // Auto-dismiss alerts after 5 seconds
        setTimeout(function() {
            const alerts = document.querySelectorAll('.alert');
            alerts.forEach(alert => {
                const bsAlert = new bootstrap.Alert(alert);
                bsAlert.close();
            });
        }, 5000);
    </script>
</body>
</html>