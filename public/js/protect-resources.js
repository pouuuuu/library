/**
 * Script pour protéger l'accès aux ressources (livres/films)
 * Intercepte les clics sur les liens de ressources et affiche une modal si l'utilisateur n'est pas connecté
 */

document.addEventListener('DOMContentLoaded', () => {
    // Récupérer le statut de connexion depuis PHP
    let isLoggedIn = false;
    let loginUrl = '';
    try {
        const authStatusEl = document.getElementById('auth-status');
        if (authStatusEl) {
            const authStatus = JSON.parse(authStatusEl.textContent);
            // Convertir explicitement en booléen - vérifier strictement true
            isLoggedIn = authStatus.isLoggedIn === true;
            loginUrl = authStatus.loginUrl || '';
        } else {
            console.warn('Élément auth-status non trouvé dans le DOM');
        }
    } catch (e) {
        console.warn('Impossible de récupérer le statut de connexion:', e);
    }
    
    // Fonction pour vérifier si un lien pointe vers une ressource protégée
    function isResourceLink(href) {
        if (!href) return false;
        // Vérifier si le lien contient ?book= ou ?film=
        return href.includes('?book=') || href.includes('?film=');
    }
    
    // Intercepter tous les clics sur les liens de ressources
    document.addEventListener('click', (e) => {
        // Vérifier si le clic est sur un lien
        const link = e.target.closest('a');
        if (!link) return;
        
        const href = link.getAttribute('href');
        
        // Vérifier si c'est un lien vers une ressource protégée
        if (isResourceLink(href)) {
            // Si l'utilisateur n'est pas connecté, intercepter le clic
            if (!isLoggedIn) {
                e.preventDefault(); // Empêcher la navigation
                
                // Construire l'URL de redirection avec le paramètre redirect
                let redirectUrl = loginUrl || '#';
                if (href && href !== '#') {
                    // Construire l'URL complète de la ressource
                    let resourceUrl = href;
                    if (!href.startsWith('http')) {
                        // URL relative : construire l'URL complète depuis la base actuelle
                        if (href.startsWith('/')) {
                            resourceUrl = window.location.origin + href;
                        } else {
                            // URL relative : utiliser le chemin actuel comme base
                            const currentPath = window.location.pathname;
                            const basePath = currentPath.substring(0, currentPath.lastIndexOf('/'));
                            resourceUrl = window.location.origin + basePath + '/' + href;
                        }
                    }
                    redirectUrl = loginUrl + (loginUrl.includes('?') ? '&' : '?') + 'redirect=' + encodeURIComponent(resourceUrl);
                }
                
                // Afficher le modal d'authentification
                if (typeof showAuthModal === 'function') {
                    showAuthModal(
                        'Vous devez être connecté pour accéder à cette page',
                        'Se connecter',
                        redirectUrl
                    );
                } else {
                    // Fallback si le modal n'est pas disponible
                    alert('Vous devez être connecté pour accéder à cette page');
                    window.location.href = redirectUrl;
                }
            }
            // Si connecté, laisser le lien fonctionner normalement
        }
    }, true); // Utiliser la capture pour intercepter avant que le lien ne soit suivi
});

