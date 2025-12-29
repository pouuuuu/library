/**
 * Système de modal simple en JavaScript vanilla
 * Pour afficher des messages d'authentification
 */

/**
 * Crée et affiche une modal avec un message et un bouton d'action
 * 
 * @param {string} message - Le message à afficher
 * @param {string} buttonText - Le texte du bouton d'action
 * @param {string} buttonUrl - L'URL vers laquelle rediriger au clic sur le bouton
 */
function showAuthModal(message, buttonText, buttonUrl) {
    // Créer l'overlay (fond sombre)
    const overlay = document.createElement('div');
    overlay.className = 'auth-modal-overlay';
    overlay.style.cssText = `
        position: fixed;
        top: 0;
        left: 0;
        width: 100%;
        height: 100%;
        background-color: rgba(0, 0, 0, 0.6);
        display: flex;
        justify-content: center;
        align-items: center;
        z-index: 10000;
        animation: fadeIn 0.3s ease;
    `;

    // Créer la modal
    const modal = document.createElement('div');
    modal.className = 'auth-modal';
    modal.style.cssText = `
        position: relative;
        background: white;
        padding: 2rem;
        border-radius: 8px;
        max-width: 400px;
        width: 90%;
        box-shadow: 0 4px 20px rgba(0, 0, 0, 0.3);
        animation: slideIn 0.3s ease;
        text-align: center;
    `;

    // Message
    const messageEl = document.createElement('p');
    messageEl.textContent = message;
    messageEl.style.cssText = `
        margin: 0 0 1.5rem 0;
        font-size: 1.1rem;
        color: #333;
        line-height: 1.5;
    `;

    // Bouton d'action
    const button = document.createElement('a');
    // Si l'URL contient déjà des paramètres, ajouter le paramètre de redirection
    // Sinon, construire l'URL avec le paramètre de redirection
    let finalUrl = buttonUrl;
    if (buttonUrl && buttonUrl !== '#') {
        // Si c'est un lien vers une ressource, sauvegarder cette URL pour redirection après connexion
        // On va passer l'URL de la ressource dans l'URL de login pour qu'elle soit sauvegardée
        const urlParams = new URLSearchParams(window.location.search);
        const resourceParam = urlParams.get('book') || urlParams.get('film');
        
        if (resourceParam) {
            // Si on est déjà sur une page de ressource, sauvegarder cette URL
            const currentUrl = window.location.href;
            finalUrl = buttonUrl + (buttonUrl.includes('?') ? '&' : '?') + 'redirect=' + encodeURIComponent(currentUrl);
        } else {
            // Sinon, utiliser l'URL telle quelle
            finalUrl = buttonUrl;
        }
    }
    button.href = finalUrl;
    button.textContent = buttonText;
    button.className = 'button main-button';
    button.style.cssText = `
        display: inline-block;
        margin-top: 1rem;
    `;

    // Bouton de fermeture (X)
    const closeBtn = document.createElement('button');
    closeBtn.innerHTML = '&times;';
    closeBtn.style.cssText = `
        position: absolute;
        top: 0.5rem;
        right: 0.5rem;
        background: none;
        border: none;
        font-size: 1.5rem;
        cursor: pointer;
        color: #666;
        padding: 0.25rem 0.5rem;
        line-height: 1;
    `;
    closeBtn.onclick = () => closeModal();

    // Fonction pour fermer la modal
    function closeModal() {
        overlay.style.animation = 'fadeOut 0.3s ease';
        modal.style.animation = 'slideOut 0.3s ease';
        setTimeout(() => {
            document.body.removeChild(overlay);
        }, 300);
    }

    // Fermer en cliquant sur l'overlay (mais pas sur la modal elle-même)
    overlay.onclick = (e) => {
        if (e.target === overlay) {
            closeModal();
        }
    };

    // Assembler la modal
    modal.appendChild(closeBtn);
    modal.appendChild(messageEl);
    modal.appendChild(button);
    overlay.appendChild(modal);
    document.body.appendChild(overlay);

    // Ajouter les animations CSS si elles n'existent pas déjà
    if (!document.getElementById('auth-modal-styles')) {
        const style = document.createElement('style');
        style.id = 'auth-modal-styles';
        style.textContent = `
            @keyframes fadeIn {
                from { opacity: 0; }
                to { opacity: 1; }
            }
            @keyframes fadeOut {
                from { opacity: 1; }
                to { opacity: 0; }
            }
            @keyframes slideIn {
                from {
                    transform: translateY(-50px);
                    opacity: 0;
                }
                to {
                    transform: translateY(0);
                    opacity: 1;
                }
            }
            @keyframes slideOut {
                from {
                    transform: translateY(0);
                    opacity: 1;
                }
                to {
                    transform: translateY(-50px);
                    opacity: 0;
                }
            }
        `;
        document.head.appendChild(style);
    }
}

