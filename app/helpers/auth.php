<?php
/**
 * Helper pour gérer l'authentification des utilisateurs
 * Fonctions utilitaires pour vérifier le statut de connexion
 */

/**
 * Vérifie si un utilisateur est connecté
 * 
 * @return bool true si l'utilisateur est connecté, false sinon
 */
function isLoggedIn(): bool
{
    // S'assurer que la session est démarrée
    if (session_status() === PHP_SESSION_NONE) {
        session_start();
    }
    
    return isset($_SESSION['user']) && isset($_SESSION['user']['id']);
}

/**
 * Exige qu'un utilisateur soit connecté
 * Redirige vers la page de connexion avec un message d'erreur si non connecté
 * 
 * @param string|null $redirectUrl URL de redirection après connexion (optionnel)
 * @return void
 */
function requireLogin(?string $redirectUrl = null): void
{
    if (!isLoggedIn()) {
        // Construire l'URL complète avec tous les paramètres GET
        // Si aucune URL n'est fournie, construire l'URL actuelle
        if ($redirectUrl === null) {
            $currentUrl = APP_INDEX_URL;
            $queryParams = $_GET;
            
            // Construire la chaîne de requête
            if (!empty($queryParams)) {
                $queryString = http_build_query($queryParams);
                $currentUrl .= '?' . $queryString;
            }
            
            $redirectUrl = $currentUrl;
        }
        
        // Sauvegarder l'URL de redirection
        $_SESSION['redirect_after_login'] = $redirectUrl;
        
        // Afficher un message d'erreur uniquement si on n'est pas déjà sur la page de login
        // (pour éviter d'afficher le message si l'utilisateur accède directement à login)
        $currentRoute = $_GET['route'] ?? '';
        if ($currentRoute !== 'auth/login') {
            setFlashError("Vous devez être connecté pour accéder à cette page");
        }
        
        // Rediriger vers la page de connexion
        header('Location: ' . APP_INDEX_URL . '?route=auth/login');
        exit;
    }
}

