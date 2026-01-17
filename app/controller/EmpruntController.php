<?php

require_once __DIR__ . '/../model/EmpruntModel.php';
require_once __DIR__ . '/../model/ResourceModel.php';

/**
 * Contrôleur pour gérer les emprunts de ressources
 * Gère l'emprunt de livres et de films par les utilisateurs connectés
 */
class EmpruntController
{
    private $empruntModel;
    private $resourceModel;

    public function __construct()
    {
        $this->empruntModel = new EmpruntModel();
        $this->resourceModel = new ResourceModel();
    }

    /**
     * Traite une demande d'emprunt
     * Vérifie que l'utilisateur est connecté et que la ressource n'est pas déjà empruntée
     */
    public function emprunter()
    {
        // Vérifier que l'utilisateur est connecté
        requireLogin();

        // Récupérer l'ID de l'utilisateur depuis la session
        $userId = $_SESSION['user']['id'] ?? null;
        if (!$userId) {
            setFlashError("Erreur : utilisateur non identifié.");
            redirect('home');
            return;
        }

        // Vérifier que la requête est en POST
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            setFlashError("Méthode non autorisée.");
            redirect('home');
            return;
        }

        // Récupérer les paramètres
        $bookId = isset($_POST['idLivre']) && !empty($_POST['idLivre']) ? (int)$_POST['idLivre'] : null;
        $filmId = isset($_POST['idFilm']) && !empty($_POST['idFilm']) ? (int)$_POST['idFilm'] : null;
        $type = isset($_POST['type']) ? trim($_POST['type']) : '';

        // Validation : soit livre soit film
        if ($bookId === null && $filmId === null) {
            setFlashError("Erreur : aucune ressource spécifiée.");
            redirect('home');
            return;
        }

        // Déterminer le type et l'ID de la ressource pour la redirection
        $resourceId = null;
        $resourceType = null;
        $redirectUrl = null;

        if ($bookId !== null) {
            $resourceId = $bookId;
            $resourceType = 'livre';
            $redirectUrl = APP_INDEX_URL . '?book=' . $bookId;
        } else {
            $resourceId = $filmId;
            $resourceType = 'film';
            $redirectUrl = APP_INDEX_URL . '?film=' . $filmId;
        }

        try {
            // Vérifier si l'emprunt existe déjà
            if ($this->empruntModel->exists($userId, $bookId, $filmId)) {
                setFlashError("Vous avez déjà emprunté cette ressource.");
                header('Location: ' . $redirectUrl);
                exit;
            }

            // Créer l'emprunt
            $this->empruntModel->createEmprunt($userId, $bookId, $filmId);

            // Incrémenter le compteur d'emprunts de la ressource
            if ($bookId !== null) {
                $this->resourceModel->incrementBookNbEmprunts($bookId);
            } else {
                $this->resourceModel->incrementFilmNbEmprunts($filmId);
            }

            // Message de succès
            setFlashSuccess("Ressource empruntée avec succès !");
            
            // Rediriger vers la page de la ressource
            header('Location: ' . $redirectUrl);
            exit;

        } catch (Exception $e) {
            // Gérer les erreurs
            setFlashError("Erreur lors de l'emprunt : " . $e->getMessage());
            header('Location: ' . $redirectUrl);
            exit;
        }
    }

    /**
     * Traite une demande de suppression d'emprunt
     * Vérifie que l'utilisateur est connecté et que l'emprunt lui appartient
     */
    public function supprimer()
    {
        // Vérifier que l'utilisateur est connecté
        requireLogin();

        // Récupérer l'ID de l'utilisateur depuis la session
        $userId = $_SESSION['user']['id'] ?? null;
        if (!$userId) {
            setFlashError("Erreur : utilisateur non identifié.");
            redirect('dashboard');
            return;
        }

        // Vérifier que la requête est en POST
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            setFlashError("Méthode non autorisée.");
            redirect('dashboard');
            return;
        }

        // Récupérer les paramètres
        $bookId = isset($_POST['idLivre']) && !empty($_POST['idLivre']) ? (int)$_POST['idLivre'] : null;
        $filmId = isset($_POST['idFilm']) && !empty($_POST['idFilm']) ? (int)$_POST['idFilm'] : null;

        // Validation : soit livre soit film
        if ($bookId === null && $filmId === null) {
            setFlashError("Erreur : aucune ressource spécifiée.");
            redirect('dashboard');
            return;
        }

        try {
            // Supprimer l'emprunt
            $deleted = $this->empruntModel->deleteEmprunt($userId, $bookId, $filmId);

            if ($deleted) {
                // Décrémenter le compteur d'emprunts de la ressource
                if ($bookId !== null) {
                    // Note: On pourrait décrémenter, mais pour l'instant on garde juste l'incrémentation
                    // car nbEmprunts représente le total historique, pas le nombre actuel
                }

                setFlashSuccess("Emprunt supprimé avec succès !");
            } else {
                setFlashError("Erreur : emprunt non trouvé ou vous n'êtes pas autorisé à le supprimer.");
            }

            // Rediriger vers le dashboard
            redirect('dashboard');

        } catch (Exception $e) {
            // Gérer les erreurs
            setFlashError("Erreur lors de la suppression : " . $e->getMessage());
            redirect('dashboard');
        }
    }
}

