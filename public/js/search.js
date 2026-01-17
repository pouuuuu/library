/**
 * Gestion de la recherche avec autocomplete
 * Fonctionnalités :
 * - Autocomplete en temps réel avec debounce
 * - Navigation clavier (flèches haut/bas, entrée)
 * - Redirection vers la ressource au clic
 */

(function() {
    'use strict';

    // Configuration
    const DEBOUNCE_DELAY = 300; // ms
    const MIN_QUERY_LENGTH = 2;
    const MAX_SUGGESTIONS = 10;

    // Variables globales
    let searchInput = null;
    let suggestionsContainer = null;
    let currentSuggestions = [];
    let selectedIndex = -1;
    let debounceTimer = null;
    let autocompleteUrl = null;

    /**
     * Initialise le système de recherche
     */
    function initSearch() {
        // Trouver le champ de recherche
        searchInput = document.querySelector('.search-bar input[type="search"]');
        if (!searchInput) {
            return; // Pas de champ de recherche sur cette page
        }

        // Créer le conteneur pour les suggestions
        suggestionsContainer = document.createElement('div');
        suggestionsContainer.className = 'autocomplete-suggestions';
        suggestionsContainer.style.display = 'none';
        searchInput.parentElement.appendChild(suggestionsContainer);

        // Construire l'URL de l'autocomplete
        // Méthode 1: Utiliser l'attribut data-search-url si disponible
        const searchUrlAttr = searchInput.getAttribute('data-search-url');
        if (searchUrlAttr) {
            // L'attribut contient déjà l'URL complète (ex: /app/index.php?route=home)
            // On doit juste remplacer la partie route par action=autocomplete
            try {
                const url = new URL(searchUrlAttr, window.location.origin);
                // Remplacer tous les paramètres par action=autocomplete
                autocompleteUrl = url.pathname + '?action=autocomplete';
            } catch (e) {
                // Si ce n'est pas une URL absolue, traiter comme un chemin relatif
                const pathMatch = searchUrlAttr.match(/^(.+\/app\/index\.php)/);
                if (pathMatch) {
                    autocompleteUrl = pathMatch[1] + '?action=autocomplete';
                } else {
                    // Fallback: construire depuis le chemin actuel
                    autocompleteUrl = '/app/index.php?action=autocomplete';
                }
            }
        } else {
            // Méthode 2: Construire depuis le chemin actuel
            const currentPath = window.location.pathname;
            let basePath = '';
            
            // Trouver le chemin de base (avant /app/ ou /public/)
            if (currentPath.includes('/app/')) {
                basePath = currentPath.substring(0, currentPath.indexOf('/app/'));
            } else if (currentPath.includes('/public/')) {
                basePath = currentPath.substring(0, currentPath.indexOf('/public/'));
            } else {
                // Si on est directement sur index.php dans /app/
                const pathParts = currentPath.split('/').filter(p => p);
                // Retirer le dernier segment (index.php ou autre fichier)
                if (pathParts.length > 0 && pathParts[pathParts.length - 1] === 'index.php') {
                    pathParts.pop();
                }
                basePath = '/' + pathParts.join('/');
            }
            
            // S'assurer que basePath commence par /
            if (!basePath.startsWith('/')) {
                basePath = '/' + basePath;
            }
            
            autocompleteUrl = basePath + '/app/index.php?action=autocomplete';
        }

        // Événements
        searchInput.addEventListener('input', handleInput);
        searchInput.addEventListener('keydown', handleKeyDown);
        searchInput.addEventListener('focus', handleFocus);
        
        // Fermer les suggestions si on clique ailleurs
        document.addEventListener('click', function(e) {
            if (!searchInput.contains(e.target) && !suggestionsContainer.contains(e.target)) {
                hideSuggestions();
            }
        });

        // Gérer la soumission du formulaire (recherche simple)
        const searchForm = searchInput.closest('form') || searchInput.parentElement;
        if (searchForm && searchForm.tagName === 'FORM') {
            searchForm.addEventListener('submit', handleSubmit);
        } else {
            // Si pas de formulaire, créer un gestionnaire pour Enter
            searchInput.addEventListener('keydown', function(e) {
                if (e.key === 'Enter' && selectedIndex === -1) {
                    e.preventDefault();
                    performSearch(searchInput.value);
                }
            });
        }
    }

    /**
     * Gère la saisie dans le champ de recherche
     */
    function handleInput(e) {
        const query = e.target.value.trim();

        // Réinitialiser la sélection
        selectedIndex = -1;

        // Si la requête est trop courte, masquer les suggestions
        if (query.length < MIN_QUERY_LENGTH) {
            hideSuggestions();
            return;
        }

        // Debounce : annuler le timer précédent
        if (debounceTimer) {
            clearTimeout(debounceTimer);
        }

        // Programmer une nouvelle recherche après le délai
        debounceTimer = setTimeout(() => {
            fetchSuggestions(query);
        }, DEBOUNCE_DELAY);
    }

    /**
     * Récupère les suggestions depuis le serveur
     */
    function fetchSuggestions(query) {
        const url = autocompleteUrl + '&q=' + encodeURIComponent(query) + '&limit=' + MAX_SUGGESTIONS;

        fetch(url)
            .then(response => {
                // Vérifier le Content-Type pour s'assurer que c'est du JSON
                const contentType = response.headers.get('content-type');
                if (!contentType || !contentType.includes('application/json')) {
                    // Si ce n'est pas du JSON, lire le texte pour voir l'erreur
                    return response.text().then(text => {
                        console.error('Réponse non-JSON reçue:', text.substring(0, 200));
                        throw new Error('Réponse non-JSON reçue du serveur');
                    });
                }
                if (!response.ok) {
                    throw new Error('Erreur réseau: ' + response.status);
                }
                return response.json();
            })
            .then(data => {
                // Vérifier que data est bien un tableau
                if (Array.isArray(data)) {
                    currentSuggestions = data;
                    displaySuggestions(currentSuggestions);
                } else if (data && data.error) {
                    // Si c'est un objet avec une erreur, l'afficher
                    console.warn('Erreur du serveur:', data.error);
                    hideSuggestions();
                } else {
                    currentSuggestions = [];
                    displaySuggestions([]);
                }
            })
            .catch(error => {
                console.error('Erreur lors de la récupération des suggestions:', error);
                hideSuggestions();
            });
    }

    /**
     * Affiche les suggestions
     */
    function displaySuggestions(suggestions) {
        if (suggestions.length === 0) {
            hideSuggestions();
            return;
        }

        // Créer le HTML des suggestions
        let html = '<ul class="suggestions-list">';
        suggestions.forEach((suggestion, index) => {
            const typeIcon = suggestion.type === 'book' ? '📘' : '🎬';
            const authors = suggestion.authors && suggestion.authors.length > 0 
                ? ' - ' + suggestion.authors.join(', ') 
                : '';
            
            html += `
                <li class="suggestion-item" data-index="${index}" data-url="${escapeHtml(suggestion.url)}">
                    <div class="suggestion-content">
                        <span class="suggestion-icon">${typeIcon}</span>
                        <span class="suggestion-title">${escapeHtml(suggestion.title)}</span>
                        ${authors ? '<span class="suggestion-meta">' + escapeHtml(authors) + '</span>' : ''}
                    </div>
                </li>
            `;
        });
        html += '</ul>';

        suggestionsContainer.innerHTML = html;
        suggestionsContainer.style.display = 'block';

        // Ajouter les événements de clic
        const items = suggestionsContainer.querySelectorAll('.suggestion-item');
        items.forEach((item, index) => {
            item.addEventListener('click', () => {
                const url = item.getAttribute('data-url');
                if (url) {
                    window.location.href = url;
                }
            });

            item.addEventListener('mouseenter', () => {
                selectedIndex = index;
                updateSelection();
            });
        });
    }

    /**
     * Masque les suggestions
     */
    function hideSuggestions() {
        suggestionsContainer.style.display = 'none';
        selectedIndex = -1;
    }

    /**
     * Gère la navigation au clavier
     */
    function handleKeyDown(e) {
        if (!suggestionsContainer || suggestionsContainer.style.display === 'none') {
            return;
        }

        const items = suggestionsContainer.querySelectorAll('.suggestion-item');

        switch(e.key) {
            case 'ArrowDown':
                e.preventDefault();
                selectedIndex = Math.min(selectedIndex + 1, items.length - 1);
                updateSelection();
                break;

            case 'ArrowUp':
                e.preventDefault();
                selectedIndex = Math.max(selectedIndex - 1, -1);
                updateSelection();
                break;

            case 'Enter':
                e.preventDefault();
                if (selectedIndex >= 0 && selectedIndex < items.length) {
                    const url = items[selectedIndex].getAttribute('data-url');
                    if (url) {
                        window.location.href = url;
                    }
                } else {
                    // Si aucune suggestion sélectionnée, faire une recherche normale
                    performSearch(searchInput.value);
                }
                break;

            case 'Escape':
                e.preventDefault();
                hideSuggestions();
                searchInput.blur();
                break;
        }
    }

    /**
     * Met à jour la sélection visuelle
     */
    function updateSelection() {
        const items = suggestionsContainer.querySelectorAll('.suggestion-item');
        items.forEach((item, index) => {
            if (index === selectedIndex) {
                item.classList.add('selected');
                // Faire défiler l'élément sélectionné dans la vue
                item.scrollIntoView({ block: 'nearest', behavior: 'smooth' });
            } else {
                item.classList.remove('selected');
            }
        });
    }

    /**
     * Gère le focus sur le champ de recherche
     */
    function handleFocus(e) {
        const query = e.target.value.trim();
        if (query.length >= MIN_QUERY_LENGTH && currentSuggestions.length > 0) {
            displaySuggestions(currentSuggestions);
        }
    }

    /**
     * Gère la soumission du formulaire
     */
    function handleSubmit(e) {
        if (selectedIndex >= 0) {
            e.preventDefault();
            const items = suggestionsContainer.querySelectorAll('.suggestion-item');
            const url = items[selectedIndex].getAttribute('data-url');
            if (url) {
                window.location.href = url;
                return;
            }
        }
        // Sinon, laisser le formulaire se soumettre normalement
    }

    /**
     * Effectue une recherche normale (redirection vers la page de résultats)
     */
    function performSearch(query) {
        if (query.trim().length < MIN_QUERY_LENGTH) {
            return;
        }

        // Utiliser la même logique que pour autocompleteUrl
        const currentPath = window.location.pathname;
        let basePath = '';
        
        if (currentPath.includes('/app/')) {
            basePath = currentPath.substring(0, currentPath.indexOf('/app/'));
        } else if (currentPath.includes('/public/')) {
            basePath = currentPath.substring(0, currentPath.indexOf('/public/'));
        }
        
        const searchUrl = basePath + '/app/index.php?action=search&q=' + encodeURIComponent(query);
        window.location.href = searchUrl;
    }

    /**
     * Échappe le HTML pour éviter les injections XSS
     */
    function escapeHtml(text) {
        const div = document.createElement('div');
        div.textContent = text;
        return div.innerHTML;
    }

    // Initialiser quand le DOM est prêt
    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', initSearch);
    } else {
        initSearch();
    }
})();

