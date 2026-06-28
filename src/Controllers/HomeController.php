<?php
namespace App\Controllers;

/**
 * HomeController - Page d'accueil
 */

class HomeController {
    
    /**
     * Page d'accueil
     */
    public function index() {
        // Si connecté, rediriger vers le dashboard
        if (isset($_SESSION['logged_in']) && $_SESSION['logged_in']) {
            $role = $_SESSION['user']['role'] ?? 'voter';
            
            if ($role === 'admin') {
                redirect('/admin/dashboard');
            } else {
                redirect('/voter/dashboard');
            }
            return;
        }
        
        // Sinon, afficher la page d'accueil
        view('home', [
            'title' => 'Accueil - ' . APP_NAME
        ]);
    }
}