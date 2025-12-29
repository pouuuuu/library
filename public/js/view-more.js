document.addEventListener('DOMContentLoaded', async () => {
    const catalog = document.querySelector('.catalog');
    const loadMoreBtn = document.getElementById('load-more');
    let offset = 0;
    
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
    
    // Compteur de clics pour les utilisateurs non connectés uniquement (max 2 clics)
    let clickCount = 0;
    const MAX_CLICKS_FOR_GUESTS = 2;

    function getColumnCount() {
        const gridStyle = window.getComputedStyle(catalog);
        return gridStyle.getPropertyValue('grid-template-columns').split(' ').length;
    }

    async function loadResources(rows = 2) {
        const cols = getColumnCount();
        const limit = cols * rows;

        let response;
        try {
            response = await fetch(`index.php?action=loadMore&offset=${offset}&limit=${limit}`);
        } catch (err) {
            console.error("Fetch failed:", err);
            loadMoreBtn.textContent = "Erreur réseau";
            return;
        }

        // Vérifier code HTTP
        if (!response.ok) {
            console.error("Server error:", response.status, response.statusText);
            loadMoreBtn.textContent = "Erreur serveur";
            return;
        }

        let resources;
        const text = await response.text();
        try {
            resources = JSON.parse(text);
        } catch (err) {
            // JSON invalide — afficher la réponse textuelle pour debug
            console.error("Réponse non-JSON reçue:", err);
            console.error("Contenu de la réponse:", text);
            loadMoreBtn.textContent = "Réponse invalide du serveur (voir console)";
            return;
        }

        if (!Array.isArray(resources) || resources.length === 0) {
            loadMoreBtn.textContent = "Plus de ressources à afficher";
            loadMoreBtn.disabled = true;
            return;
        }

        resources.forEach((r, idx) => {

            if (!r || typeof r !== 'object' || !r.type) {
                console.warn(`Ressource inattendue à l'index ${idx}:`, r);
                return; // skip cet élément (évite l'erreur)
            }

            const a = document.createElement('a');

            if (r.type === 'film') {
                a.href = `index.php?film=${encodeURIComponent(r.id)}`;
                
                // Construire les informations disponibles
                let infoHtml = `<h2>${escapeHtml(r.title)}</h2>`;
                
                if (r.duration) {
                    infoHtml += `<p>Durée : ${escapeHtml(String(r.duration))} min</p>`;
                }
                
                if (r.releaseDate) {
                    const releaseDate = formatDate(r.releaseDate);
                    infoHtml += `<p>Date de sortie : ${releaseDate}</p>`;
                }
                
                if (r.productionYear) {
                    infoHtml += `<p>Année : ${escapeHtml(String(r.productionYear))}</p>`;
                }
                
                if (r.languages && Array.isArray(r.languages) && r.languages.length > 0) {
                    infoHtml += `<p>Langues : ${r.languages.map(l => escapeHtml(l)).join(', ')}</p>`;
                }
                
                infoHtml += `<span class="badge">🎬 Film</span>`;
                
                a.innerHTML = `
                    <img src="${r.poster ? escapeHtml(r.poster) : '/public/img/default-poster.png'}" 
                         alt="Film ${escapeHtml(r.title)}"
                         onerror="this.src='/public/img/default-poster.png'">
                    <div>
                        ${infoHtml}
                    </div>
                `;
            } else {
                a.href = `index.php?book=${encodeURIComponent(r.id)}`;
                
                // Construire les informations disponibles
                let infoHtml = `<h2>${escapeHtml(r.title)}</h2>`;
                
                if (r.authors && Array.isArray(r.authors) && r.authors.length > 0) {
                    infoHtml += `<p>Auteurs : ${r.authors.map(a => escapeHtml(a)).join(', ')}</p>`;
                }
                
                if (r.publishYear) {
                    infoHtml += `<p>Année : ${escapeHtml(String(r.publishYear))}</p>`;
                }
                
                if (r.language) {
                    infoHtml += `<p>Langue : ${escapeHtml(r.language)}</p>`;
                }
                
                infoHtml += `<span class="badge">📘 Livre</span>`;
                
                a.innerHTML = `
                    <img src="${r.poster ? escapeHtml(r.poster) : '/public/img/default-cover.png'}" 
                         alt="Livre ${escapeHtml(r.title)}"
                         onerror="this.src='/public/img/default-cover.png'">
                    <div>
                        ${infoHtml}
                    </div>
                `;
            }

            // Note: Le gestionnaire de clic est géré par protect-resources.js
            // Le script global intercepte tous les liens de ressources automatiquement
            
            catalog.appendChild(a);
        });

        offset += resources.length;
    }

    function escapeHtml(str) {
        if (typeof str !== 'string') return str ?? '';
        return str.replace(/[&<>"']/g, s => ({
            '&': '&amp;',
            '<': '&lt;',
            '>': '&gt;',
            '"': '&quot;',
            "'": '&#39;'
        }[s]));
    }

    function formatDate(dateString) {
        if (!dateString) return 'N/A';
        try {
            const date = new Date(dateString);
            if (isNaN(date.getTime())) return dateString;
            return date.toLocaleDateString('fr-FR', { year: 'numeric', month: 'long', day: 'numeric' });
        } catch (e) {
            return dateString;
        }
    }

    await loadResources(2);
    
    // Gérer le clic sur le bouton "Afficher plus"
    loadMoreBtn.addEventListener('click', () => {
        // Si l'utilisateur est connecté, charger sans limitation
        if (isLoggedIn) {
            loadResources(2);
            return;
        }
        
        // Pour les utilisateurs non connectés : vérifier la limite de clics
        clickCount++;
        
        // Si le nombre de clics dépasse la limite, afficher le modal et bloquer
        if (clickCount > MAX_CLICKS_FOR_GUESTS) {
            // Afficher le modal d'authentification
            if (typeof showAuthModal === 'function') {
                showAuthModal(
                    'Connectez-vous pour voir plus de ressources',
                    'Se connecter',
                    loginUrl || '#'
                );
            } else {
                // Fallback si le modal n'est pas disponible
                alert('Connectez-vous pour voir plus de ressources');
                window.location.href = loginUrl || '#';
            }
            return; // Bloquer le chargement
        }
        
        // Charger les ressources normalement (pour les 2 premiers clics)
        loadResources(2);
    });
});
