/**
 * Script pour la page Nouveautés
 * Gère le filtrage dynamique des ressources via AJAX
 */

document.addEventListener('DOMContentLoaded', () => {
    const catalog = document.getElementById('catalog');
    const loadingMessage = document.getElementById('loading-message');
    const noResults = document.getElementById('no-results');
    const filterChips = document.querySelectorAll('.filter-chip');
    
    // État actuel des filtres
    let currentFilters = {
        type: '',
        theme: ''
    };

    // Initialiser les filtres depuis l'URL au chargement
    const urlParams = new URLSearchParams(window.location.search);
    currentFilters.type = urlParams.get('type') || '';
    currentFilters.theme = urlParams.get('theme') || '';

    /**
     * Échappe les caractères HTML pour éviter les injections XSS
     */
    function escapeHtml(str) {
        if (typeof str !== 'string') return str ?? '';
        const div = document.createElement('div');
        div.textContent = str;
        return div.innerHTML;
    }

    /**
     * Charge les ressources filtrées depuis le serveur
     */
    async function loadResources(page = 1) {
        // Afficher le message de chargement
        loadingMessage.classList.remove('hidden');
        noResults.classList.add('hidden');
        catalog.innerHTML = '';

        // Construire l'URL avec les paramètres de filtrage
        const params = new URLSearchParams();
        params.append('route', 'nouveautes');
        params.append('action', 'news');
        if (currentFilters.type) {
            params.append('type', currentFilters.type);
        }
        if (currentFilters.theme) {
            params.append('theme', currentFilters.theme);
        }
        params.append('limit', '20');
        params.append('page', page.toString());

        const url = window.location.pathname + '?' + params.toString();

        try {
            const response = await fetch(url, {
                method: 'GET',
                headers: {
                    'X-Requested-With': 'XMLHttpRequest'
                }
            });

            if (!response.ok) {
                throw new Error('Erreur lors du chargement des ressources');
            }

            const data = await response.json();

            // Masquer le message de chargement
            loadingMessage.classList.add('hidden');

            if (!data.success || !data.resources || data.resources.length === 0) {
                noResults.classList.remove('hidden');
                return;
            }

            // Afficher les ressources
            displayResources(data.resources);
            
            // Mettre à jour la pagination et le comptage
            updatePagination(data.totalCount, data.totalPages, data.currentPage);

        } catch (error) {
            console.error('Erreur lors du chargement des ressources:', error);
            loadingMessage.classList.add('hidden');
            noResults.classList.remove('hidden');
            noResults.innerHTML = '<p>Erreur lors du chargement des ressources. Veuillez réessayer.</p>';
        }
    }
    
    /**
     * Met à jour la pagination et le comptage après un filtrage
     */
    function updatePagination(totalCount, totalPages, currentPage) {
        // Trouver ou créer le conteneur de pagination
        let paginationContainer = document.querySelector('.pagination-container');
        if (!paginationContainer) {
            // Créer le conteneur de pagination s'il n'existe pas
            const section = document.querySelector('section#bot');
            paginationContainer = document.createElement('div');
            paginationContainer.className = 'pagination-container';
            section.appendChild(paginationContainer);
        }
        
        // Mettre à jour ou créer le texte d'information
        let paginationInfo = paginationContainer.querySelector('.pagination-info');
        if (!paginationInfo) {
            paginationInfo = document.createElement('div');
            paginationInfo.className = 'pagination-info';
            paginationContainer.insertBefore(paginationInfo, paginationContainer.firstChild);
        }
        paginationInfo.textContent = `Page ${currentPage} sur ${totalPages} (${totalCount} ressources)`;
        
        // Mettre à jour les liens de pagination
        let pagination = paginationContainer.querySelector('.pagination');
        if (!pagination) {
            pagination = document.createElement('div');
            pagination.className = 'pagination';
            paginationContainer.appendChild(pagination);
        }
        
        // Fonction helper pour construire l'URL de pagination avec les filtres
        function buildPaginationUrl(pageNum) {
            const params = [];
            params.push('route=nouveautes');
            if (currentFilters.type) {
                params.push('type=' + encodeURIComponent(currentFilters.type));
            }
            if (currentFilters.theme) {
                params.push('theme=' + encodeURIComponent(currentFilters.theme));
            }
            params.push('page=' + pageNum);
            return '?' + params.join('&');
        }
        
        // Générer le HTML de pagination
        let paginationHtml = '';
        
        // Bouton Précédent
        if (currentPage > 1) {
            paginationHtml += `<a href="${buildPaginationUrl(currentPage - 1)}" class="pagination-button">← Précédent</a>`;
        } else {
            paginationHtml += `<span class="pagination-button disabled">← Précédent</span>`;
        }
        
        // Numéros de page
        const startPage = Math.max(1, currentPage - 2);
        const endPage = Math.min(totalPages, currentPage + 2);
        
        if (startPage > 1) {
            paginationHtml += `<a href="${buildPaginationUrl(1)}" class="pagination-number">1</a>`;
            if (startPage > 2) {
                paginationHtml += `<span class="pagination-ellipsis">...</span>`;
            }
        }
        
        for (let i = startPage; i <= endPage; i++) {
            if (i === currentPage) {
                paginationHtml += `<span class="pagination-number active">${i}</span>`;
            } else {
                paginationHtml += `<a href="${buildPaginationUrl(i)}" class="pagination-number">${i}</a>`;
            }
        }
        
        if (endPage < totalPages) {
            if (endPage < totalPages - 1) {
                paginationHtml += `<span class="pagination-ellipsis">...</span>`;
            }
            paginationHtml += `<a href="${buildPaginationUrl(totalPages)}" class="pagination-number">${totalPages}</a>`;
        }
        
        // Bouton Suivant
        if (currentPage < totalPages) {
            paginationHtml += `<a href="${buildPaginationUrl(currentPage + 1)}" class="pagination-button">Suivant →</a>`;
        } else {
            paginationHtml += `<span class="pagination-button disabled">Suivant →</span>`;
        }
        
        pagination.innerHTML = paginationHtml;
        
        // Afficher ou masquer le conteneur de pagination
        if (totalPages > 1) {
            paginationContainer.style.display = 'flex';
        } else {
            paginationContainer.style.display = 'none';
        }
    }

    /**
     * Affiche les ressources dans le catalogue
     */
    function displayResources(resources) {
        catalog.innerHTML = '';

        resources.forEach(resource => {
            const card = document.createElement('a');
            card.href = `?${resource.type === 'book' ? 'book' : 'film'}=${resource.id}`;
            card.className = 'resource-card';
            card.setAttribute('data-type', resource.type);

            // Construire l'image
            const imageContainer = document.createElement('div');
            imageContainer.className = 'resource-image-container';
            
            const img = document.createElement('img');
            img.src = resource.poster || '/public/img/default-cover.png';
            img.alt = escapeHtml(resource.title);
            img.onerror = function() {
                this.src = '/public/img/default-cover.png';
            };

            // Badge "Nouveau"
            const badgeNew = document.createElement('span');
            badgeNew.className = 'badge badge-new';
            badgeNew.textContent = '✨ Nouveau';

            // Badge type
            const badgeType = document.createElement('span');
            badgeType.className = `badge badge-type ${resource.type === 'book' ? 'badge-book' : 'badge-film'}`;
            badgeType.textContent = resource.type === 'book' ? '📘 Livre' : '🎬 Film';

            imageContainer.appendChild(img);
            imageContainer.appendChild(badgeNew);
            imageContainer.appendChild(badgeType);

            // Construire les informations
            const info = document.createElement('div');
            info.className = 'resource-info';

            const title = document.createElement('h2');
            title.textContent = escapeHtml(resource.title);
            info.appendChild(title);

            // Informations spécifiques selon le type
            if (resource.type === 'book') {
                if (resource.authors && resource.authors.length > 0) {
                    const authorsP = document.createElement('p');
                    authorsP.className = 'resource-meta';
                    authorsP.textContent = `Auteurs : ${resource.authors.map(a => escapeHtml(a)).join(', ')}`;
                    info.appendChild(authorsP);
                }
                if (resource.publishYear) {
                    const yearP = document.createElement('p');
                    yearP.className = 'resource-meta';
                    yearP.textContent = `Année : ${escapeHtml(String(resource.publishYear))}`;
                    info.appendChild(yearP);
                }
                if (resource.language) {
                    const langP = document.createElement('p');
                    langP.className = 'resource-meta';
                    langP.textContent = `Langue : ${escapeHtml(resource.language)}`;
                    info.appendChild(langP);
                }
            } else {
                if (resource.productionYear) {
                    const yearP = document.createElement('p');
                    yearP.className = 'resource-meta';
                    yearP.textContent = `Année : ${escapeHtml(String(resource.productionYear))}`;
                    info.appendChild(yearP);
                }
                if (resource.duration) {
                    const durationP = document.createElement('p');
                    durationP.className = 'resource-meta';
                    durationP.textContent = `Durée : ${escapeHtml(String(resource.duration))} min`;
                    info.appendChild(durationP);
                }
            }

            // Date d'ajout
            if (resource.dateAdded) {
                const dateP = document.createElement('p');
                dateP.className = 'resource-date';
                const date = new Date(resource.dateAdded);
                dateP.textContent = `Ajouté le ${date.toLocaleDateString('fr-FR')}`;
                info.appendChild(dateP);
            }

            card.appendChild(imageContainer);
            card.appendChild(info);
            catalog.appendChild(card);
        });
    }

    /**
     * Met à jour les filtres actifs
     */
    function updateFilters(filterType, value) {
        if (filterType === 'type') {
            currentFilters.type = value;
        } else if (filterType === 'theme') {
            currentFilters.theme = value;
        }

        // Mettre à jour l'état visuel des chips
        filterChips.forEach(chip => {
            const chipFilterType = chip.getAttribute('data-filter');
            const chipValue = chip.getAttribute(`data-${chipFilterType}`) || '';
            
            if (chipFilterType === filterType) {
                if (chipValue === value) {
                    chip.classList.add('active');
                } else {
                    chip.classList.remove('active');
                }
            }
        });

            // Mettre à jour l'URL sans recharger la page (réinitialiser à la page 1 lors du filtrage)
        const params = new URLSearchParams();
        if (currentFilters.type) {
            params.append('type', currentFilters.type);
        }
        if (currentFilters.theme) {
            params.append('theme', currentFilters.theme);
        }
        params.append('page', '1'); // Réinitialiser à la page 1 lors du filtrage
        
        const newUrl = window.location.pathname + '?route=nouveautes' + 
                      (params.toString() ? '&' + params.toString() : '');
        window.history.pushState({}, '', newUrl);

        // Recharger les ressources (page 1)
        loadResources(1);
    }
    
    /**
     * Gère les clics sur les liens de pagination
     */
    function handlePaginationClick(event) {
        const link = event.target.closest('a');
        if (!link) return;
        
        event.preventDefault();
        
        // Extraire le numéro de page de l'URL du lien
        const url = new URL(link.href, window.location.origin);
        let page = parseInt(url.searchParams.get('page')) || 1;
        
        // Utiliser les filtres actuels (pas ceux de l'URL du lien qui peuvent être obsolètes)
        // Construire la nouvelle URL avec les filtres actuels
        const params = new URLSearchParams();
        params.append('route', 'nouveautes');
        if (currentFilters.type) {
            params.append('type', currentFilters.type);
        }
        if (currentFilters.theme) {
            params.append('theme', currentFilters.theme);
        }
        params.append('page', page.toString());
        
        const newUrl = window.location.pathname + '?' + params.toString();
        
        // Mettre à jour l'URL dans le navigateur
        window.history.pushState({}, '', newUrl);
        
        // Charger les ressources pour cette page avec les filtres actuels
        loadResources(page);
    }

    // Ajouter les écouteurs d'événements sur les chips de filtrage
    filterChips.forEach(chip => {
        chip.addEventListener('click', () => {
            const filterType = chip.getAttribute('data-filter');
            const value = chip.getAttribute(`data-${filterType}`) || '';
            updateFilters(filterType, value);
        });
    });
    
    // Intercepter les clics sur la pagination (délégation d'événements)
    // Utiliser capture pour intercepter avant que le lien ne soit suivi
    document.addEventListener('click', (event) => {
        const paginationLink = event.target.closest('.pagination a');
        if (paginationLink) {
            handlePaginationClick(event);
        }
    }, true); // Utiliser capture phase
    
    // Initialiser l'état visuel des chips de filtrage depuis l'URL
    filterChips.forEach(chip => {
        const chipFilterType = chip.getAttribute('data-filter');
        const chipValue = chip.getAttribute(`data-${chipFilterType}`) || '';
        
        // Retirer toutes les classes active d'abord
        chip.classList.remove('active');
        
        // Ajouter active si c'est le filtre actuel
        if (chipFilterType === 'type') {
            if ((!currentFilters.type && chipValue === '') || chipValue === currentFilters.type) {
                chip.classList.add('active');
            }
        } else if (chipFilterType === 'theme') {
            if ((!currentFilters.theme && chipValue === '') || chipValue === currentFilters.theme) {
                chip.classList.add('active');
            }
        }
    });
    
    // Si des filtres sont actifs dans l'URL, s'assurer que la pagination est correcte
    // En fait, la pagination côté serveur devrait déjà être correcte, donc on ne fait rien
    // sauf si on filtre dynamiquement via JavaScript
});

