/**
 * Gestion des flash messages avec Toastify.js
 * Lit les messages depuis la session PHP et les affiche automatiquement
 */

(function() {
    'use strict';

    /**
     * Configuration des couleurs pour chaque type de message
     * Adaptées à la direction artistique du site
     */
    const toastConfig = {
        success: {
            background: 'linear-gradient(to right, #2e7d32, #4caf50)',
            text: '#ffffff'
        },
        error: {
            background: 'linear-gradient(to right, #c62828, #ef5350)',
            text: '#ffffff'
        },
        info: {
            background: 'linear-gradient(to right, #1565c0, #42a5f5)',
            text: '#ffffff'
        },
        warning: {
            background: 'linear-gradient(to right, #e65100, #ff9800)',
            text: '#ffffff'
        }
    };

    /**
     * Affiche un toast avec Toastify.js
     * 
     * @param {string} type - Type du message (success, error, info, warning)
     * @param {string} message - Le message à afficher
     */
    function showToast(type, message) {
        if (typeof Toastify === 'undefined') {
            console.error('Toastify n\'est pas disponible');
            return;
        }
        
        const config = toastConfig[type] || toastConfig.info;
        
        Toastify({
            text: message,
            duration: 4000, // 4 secondes
            gravity: 'top', // Position en haut
            position: 'right', // Position à droite
            style: {
                background: config.background,
                color: config.text,
                borderRadius: '8px',
                boxShadow: '0 4px 12px rgba(0, 0, 0, 0.3)',
                padding: '16px 20px',
                fontSize: '14px',
                fontWeight: '500'
            },
            close: true, // Bouton de fermeture
            stopOnFocus: true // Pause au survol
        }).showToast();
    }

    /**
     * Initialise les flash messages au chargement de la page
     * Lit les messages depuis le script JSON dans le DOM
     */
    function initFlashMessages() {
        // Chercher le script avec l'ID flash-messages-data
        const flashScript = document.getElementById('flash-messages-data');
        
        if (!flashScript) {
            // Pas de messages à afficher
            return;
        }

        try {
            const messages = JSON.parse(flashScript.textContent || '[]');
            
            if (!Array.isArray(messages) || messages.length === 0) {
                return;
            }
            
            // Afficher chaque message avec un léger délai pour éviter les chevauchements
            messages.forEach((msg, index) => {
                setTimeout(() => {
                    if (msg && msg.type && msg.message) {
                        showToast(msg.type, msg.message);
                    }
                }, index * 300); // Délai de 300ms entre chaque toast
            });
        } catch (e) {
            console.error('Erreur lors du parsing des flash messages:', e);
            console.error('Contenu du script:', flashScript.textContent);
        }
    }

    /**
     * Vérifie si Toastify est disponible et l'attend si nécessaire
     */
    function waitForToastify(callback, maxAttempts = 50) {
        if (typeof Toastify !== 'undefined' && typeof Toastify === 'function') {
            callback();
        } else if (maxAttempts > 0) {
            setTimeout(() => waitForToastify(callback, maxAttempts - 1), 100);
        } else {
            console.error('Toastify.js n\'a pas pu être chargé après 5 secondes');
        }
    }

    /**
     * Initialise les flash messages une fois que tout est prêt
     */
    function init() {
        // Attendre que le DOM soit chargé
        if (document.readyState === 'loading') {
            document.addEventListener('DOMContentLoaded', function() {
                waitForToastify(initFlashMessages);
            });
        } else {
            // Le DOM est déjà chargé, attendre Toastify
            waitForToastify(initFlashMessages);
        }
    }

    // Démarrer l'initialisation
    init();
})();

