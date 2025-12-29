<?php
/**
 * Helper pour gérer les flash messages
 * Permet de stocker des messages temporaires dans la session
 * qui seront affichés une seule fois via Toastify.js
 */

/**
 * Définit un flash message
 * 
 * @param string $type Type du message : 'success', 'error', 'info', 'warning'
 * @param string $message Le message à afficher
 */
function setFlash(string $type, string $message): void
{
    // S'assurer que la session est démarrée
    if (session_status() === PHP_SESSION_NONE) {
        session_start();
    }
    
    if (!isset($_SESSION['flash_messages'])) {
        $_SESSION['flash_messages'] = [];
    }
    $_SESSION['flash_messages'][] = [
        'type' => $type,
        'message' => $message
    ];
}

/**
 * Récupère tous les flash messages et les supprime de la session
 * 
 * @return array Tableau de messages avec 'type' et 'message'
 */
function getFlashMessages(): array
{
    if (!isset($_SESSION['flash_messages'])) {
        return [];
    }
    
    $messages = $_SESSION['flash_messages'];
    unset($_SESSION['flash_messages']);
    
    return $messages;
}

/**
 * Helpers pratiques pour chaque type de message
 */
function setFlashSuccess(string $message): void
{
    setFlash('success', $message);
}

function setFlashError(string $message): void
{
    setFlash('error', $message);
}

function setFlashInfo(string $message): void
{
    setFlash('info', $message);
}

function setFlashWarning(string $message): void
{
    setFlash('warning', $message);
}

/**
 * Génère le script JSON pour injecter les flash messages dans la page
 * À placer avant la fermeture de </body>
 * 
 * @return string Le code HTML/JS à inclure dans la vue
 */
function renderFlashMessagesScript(): string
{
    $messages = getFlashMessages();
    
    if (empty($messages)) {
        return '';
    }
    
    // Encoder en JSON sans échapper les guillemets pour HTML
    // On utilise JSON_HEX_TAG pour éviter les balises </script> dans le JSON
    $jsonMessages = json_encode($messages, JSON_UNESCAPED_UNICODE | JSON_HEX_TAG | JSON_HEX_AMP);
    
    // Dans un script type="application/json", on ne doit pas utiliser htmlspecialchars
    // car le contenu est du JSON brut, pas du HTML
    // On échappe seulement les balises </script> pour éviter de fermer le script prématurément
    $jsonMessages = str_replace('</script>', '<\/script>', $jsonMessages);
    
    return '<script id="flash-messages-data" type="application/json">' . $jsonMessages . '</script>';
}

