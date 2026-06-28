<?php
/**
 * Helper Functions pour le Branding CSS (Inline pour éviter complexité autoload)
 */
if (!function_exists('hex2rgb')) {
    function hex2rgb($hex) {
        $hex = str_replace("#", "", $hex);
        if(strlen($hex) == 3) {
            $r = hexdec(substr($hex,0,1).substr($hex,0,1));
            $g = hexdec(substr($hex,1,1).substr($hex,1,1));
            $b = hexdec(substr($hex,2,1).substr($hex,2,1));
        } else {
            $r = hexdec(substr($hex,0,2));
            $g = hexdec(substr($hex,2,2));
            $b = hexdec(substr($hex,4,2));
        }
        return "$r, $g, $b";
    }
}

if (!function_exists('adjustDataBrightness')) {
    function adjustDataBrightness($hex, $steps) {
        $steps = max(-255, min(255, $steps));
        $hex = str_replace('#', '', $hex);
        if (strlen($hex) == 3) {
            $hex = str_repeat(substr($hex,0,1), 2).str_repeat(substr($hex,1,1), 2).str_repeat(substr($hex,2,1), 2);
        }
        $color_parts = str_split($hex, 2);
        $return = '#';
        foreach ($color_parts as $color) {
            $color   = hexdec($color); 
            $color   = max(0, min(255, $color + $steps)); 
            $return .= str_pad(dechex($color), 2, '0', STR_PAD_LEFT); 
        }
        return $return;
    }
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <title><?php echo $title ?? 'Vote Sécurisé'; ?></title>
    
    <!-- Font: Inter -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    
    <!-- Bootstrap Icons -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css">
    
    <!-- Custom CSS -->
    <link href="<?php echo ASSETS_URL; ?>/css/style.css" rel="stylesheet">

    <?php
    // Détection du Branding (Contexte Organisation)
    $brandingOrg = null;
    
    // 1. Si on est dans un contexte public (Login/Register avec ?org=slug) ou sélectionné via Portal
    if (isset($_SESSION['context_org'])) {
        $brandingOrg = $_SESSION['context_org'];
    }
    // 2. Si on est connecté, on utilise l'organisation de l'utilisateur
    elseif (isset($_SESSION['user']) && !empty($_SESSION['user']['organization_id'])) {
        // Optimisation: On pourrait stocker ça en session au login pour éviter une requête SQL à chaque page
        // Pour l'instant on suppose que c'est en session ou on le récupère via User Model si besoin
        // Simplification: On va charger l'org si elle n'est pas en session
        if (!isset($_SESSION['user_org_branding'])) {
            try {
                $orgModel = new \App\Models\Organization();
                $_SESSION['user_org_branding'] = $orgModel->findById($_SESSION['user']['organization_id']);
            } catch (Exception $e) {
                // En cas d'erreur, on continue sans branding
                $_SESSION['user_org_branding'] = null;
            }
        }
        $brandingOrg = $_SESSION['user_org_branding'] ?? null;
    }
    // 3. Fallback: Si un 'org' object est passé à la vue (ex: Login Controller)
    elseif (isset($org) && $org) {
        $brandingOrg = $org;
    }

    $primaryColor = $brandingOrg['primary_color'] ?? '#0d6efd'; // Bootstrap Primary Default
    ?>

    <style>
        :root {
            --bs-primary: <?php echo $primaryColor; ?>;
            --bs-primary-rgb: <?php echo hex2rgb($primaryColor); ?>;
            --bs-link-color: <?php echo $primaryColor; ?>;
            --bs-link-hover-color: <?php echo adjustDataBrightness($primaryColor, -20); ?>;
        }
        
        .btn-primary {
            --bs-btn-bg: <?php echo $primaryColor; ?>;
            --bs-btn-border-color: <?php echo $primaryColor; ?>;
            --bs-btn-hover-bg: <?php echo adjustDataBrightness($primaryColor, -10); ?>;
            --bs-btn-hover-border-color: <?php echo adjustDataBrightness($primaryColor, -15); ?>;
            --bs-btn-active-bg: <?php echo adjustDataBrightness($primaryColor, -20); ?>;
            --bs-btn-active-border-color: <?php echo adjustDataBrightness($primaryColor, -25); ?>;
        }
        
        .text-primary {
            color: <?php echo $primaryColor; ?> !important;
        }
        
        .bg-primary {
            background-color: <?php echo $primaryColor; ?> !important;
        }

        .navbar {
            background-color: <?php echo $primaryColor; ?> !important;
        }
        
        .nav-link {
            color: rgba(255, 255, 255, 0.85) !important;
        }
        
        .nav-link:hover, .nav-link.active {
            color: #fff !important;
        }
    </style>
</head>
<body>
    
    <!-- Navigation -->
    <nav class="navbar navbar-expand-lg navbar-dark shadow-sm">
        <div class="container">
            <a class="navbar-brand fw-bold" href="<?php echo BASE_URL; ?>">
                <?php if ($brandingOrg && !empty($brandingOrg['logo_url'])): ?>
                    <img src="<?php echo BASE_URL . $brandingOrg['logo_url']; ?>" alt="Logo" height="30" class="d-inline-block align-text-top me-2 bg-white rounded p-1">
                <?php else: ?>
                    <i class="bi bi-shield-check"></i> 
                <?php endif; ?>
                <?php echo $brandingOrg ? e($brandingOrg['name']) : 'Vote Sécurisé'; ?>
            </a>
            
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
                <span class="navbar-toggler-icon"></span>
            </button>
            
            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav ms-auto">
                    <?php if (isset($_SESSION['logged_in']) && $_SESSION['logged_in']): ?>
                        <!-- Menu pour utilisateurs connectés -->
                        <?php if ($_SESSION['user']['role'] === 'admin'): ?>
                            <li class="nav-item">
                                <a class="nav-link" href="<?php echo BASE_URL; ?>/admin/dashboard">
                                    <i class="bi bi-speedometer2"></i> Dashboard
                                </a>
                            </li>
                            <li class="nav-item">
                                <a class="nav-link" href="<?php echo BASE_URL; ?>/admin/elections">
                                    <i class="bi bi-calendar-event"></i> Élections
                                </a>
                            </li>
                        <?php else: ?>
                            <li class="nav-item">
                                <a class="nav-link" href="<?php echo BASE_URL; ?>/voter/dashboard">
                                    <i class="bi bi-house-door"></i> Accueil
                                </a>
                            </li>
                            <li class="nav-item">
                                <a class="nav-link" href="<?php echo BASE_URL; ?>/elections">
                                    <i class="bi bi-ballot"></i> Élections
                                </a>
                            </li>
                        <?php endif; ?>
                        
                        <li class="nav-item dropdown">
                            <a class="nav-link dropdown-toggle" href="#" id="userDropdown" role="button" data-bs-toggle="dropdown">
                                <i class="bi bi-person-circle"></i> 
                                <?php echo e($_SESSION['user']['first_name']); ?>
                            </a>
                            <ul class="dropdown-menu dropdown-menu-end">
                                <li>
                                    <a class="dropdown-item" href="#">
                                        <i class="bi bi-person"></i> Mon Profil
                                    </a>
                                </li>
                                <li><hr class="dropdown-divider"></li>
                                <li>
                                    <a class="dropdown-item text-danger" href="<?php echo BASE_URL; ?>/auth/logout">
                                        <i class="bi bi-box-arrow-right"></i> Déconnexion
                                    </a>
                                </li>
                            </ul>
                        </li>
                    <?php else: ?>
                        <!-- Menu pour visiteurs -->
                        <li class="nav-item">
                            <a class="nav-link" href="<?php echo BASE_URL; ?>/auth/login">
                                <i class="bi bi-box-arrow-in-right"></i> Connexion
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link btn btn-light text-primary ms-2 px-3" href="<?php echo BASE_URL; ?>/auth/register">
                                <i class="bi bi-person-plus"></i> Inscription
                            </a>
                        </li>
                    <?php endif; ?>
                </ul>
            </div>
        </div>
    </nav>
    
    <!-- Messages Flash -->
    <?php 
    $flash = getFlashMessage();
    if ($flash): 
        $alertClass = [
            'success' => 'alert-success',
            'danger' => 'alert-danger',
            'warning' => 'alert-warning',
            'info' => 'alert-info'
        ];
        $iconClass = [
            'success' => 'bi-check-circle',
            'danger' => 'bi-exclamation-circle',
            'warning' => 'bi-exclamation-triangle',
            'info' => 'bi-info-circle'
        ];
    ?>
    <div class="container mt-3">
        <div class="alert <?php echo $alertClass[$flash['type']]; ?> alert-dismissible fade show" role="alert">
            <i class="bi <?php echo $iconClass[$flash['type']]; ?>"></i>
            <?php echo $flash['message']; ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    </div>
    <?php endif; ?>
    
    <!-- Contenu principal -->
    <main class="py-4"><?php /* Le contenu des pages sera inséré ici */ ?>

