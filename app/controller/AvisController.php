<?php

require_once __DIR__ . '/../model/AvisModel.php';

/**
 * Contrôleur pour gérer les actions sur les avis
 * Permet de créer, modifier et supprimer des avis sur les ressources
 */
class AvisController
{
    private $avisModel;

    public function __construct()
    {
        $this->avisModel = new AvisModel();
    }

    /**
     * Vérifie que l'utilisateur est connecté
     * @throws Exception Si l'utilisateur n'est pas connecté
     */
    private function requireLogin(): void
    {
        if (empty($_SESSION['user']) || empty($_SESSION['user']['id'])) {
            throw new Exception("Vous devez être connecté pour effectuer cette action.");
        }
    }

    /**
     * Gère la création d'un nouvel avis
     * Redirige vers la page de la ressource après création
     */
    public function create(): void
    {
        $this->requireLogin();

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            setFlashError("Méthode non autorisée.");
            header('Location: ' . APP_INDEX_URL . '?route=home');
            exit;
        }

        $idUtilisateur = (int)$_SESSION['user']['id'];
        $idLivre = !empty($_POST['idLivre']) ? (int)$_POST['idLivre'] : null;
        $idFilm = !empty($_POST['idFilm']) ? (int)$_POST['idFilm'] : null;
        $note = !empty($_POST['note']) ? (int)$_POST['note'] : 0;
        $text = trim((string)($_POST['text'] ?? ''));
        $objet = trim((string)($_POST['objet'] ?? ''));

        // Validation
        if ($idLivre === null && $idFilm === null) {
            setFlashError("Ressource non spécifiée.");
            $this->redirectToResource($idLivre, $idFilm);
            return;
        }

        if ($note < 1 || $note > 5) {
            setFlashError("La note doit être entre 1 et 5.");
            $this->redirectToResource($idLivre, $idFilm);
            return;
        }

        if (empty($text)) {
            setFlashError("Le commentaire est obligatoire.");
            $this->redirectToResource($idLivre, $idFilm);
            return;
        }

        // Note: On ne fait PAS htmlspecialchars() ici car :
        // - Les données doivent être stockées brutes en base de données
        // - L'échappement HTML se fait à l'affichage dans les vues (protection XSS)
        // - Faire htmlspecialchars() ici puis dans les vues créerait un double échappement

        try {
            $this->avisModel->createAvis($idUtilisateur, $note, $text, $objet, $idLivre, $idFilm);
            setFlashSuccess("Votre avis a été ajouté avec succès.");
        } catch (Exception $e) {
            setFlashError($e->getMessage());
        }

        $this->redirectToResource($idLivre, $idFilm);
    }

    /**
     * Gère la modification d'un avis existant
     * Redirige vers la page de la ressource après modification
     */
    public function update(): void
    {
        $this->requireLogin();

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            setFlashError("Méthode non autorisée.");
            header('Location: ' . APP_INDEX_URL . '?route=home');
            exit;
        }

        $idUtilisateur = (int)$_SESSION['user']['id'];
        $idAvis = !empty($_POST['idAvis']) ? (int)$_POST['idAvis'] : 0;
        $idLivre = !empty($_POST['idLivre']) ? (int)$_POST['idLivre'] : null;
        $idFilm = !empty($_POST['idFilm']) ? (int)$_POST['idFilm'] : null;
        $note = !empty($_POST['note']) ? (int)$_POST['note'] : 0;
        $text = trim((string)($_POST['text'] ?? ''));
        $objet = trim((string)($_POST['objet'] ?? ''));

        // Validation
        if ($idAvis <= 0) {
            setFlashError("Identifiant d'avis invalide.");
            $this->redirectToResource($idLivre, $idFilm);
            return;
        }

        if ($note < 1 || $note > 5) {
            setFlashError("La note doit être entre 1 et 5.");
            $this->redirectToResource($idLivre, $idFilm);
            return;
        }

        if (empty($text)) {
            setFlashError("Le commentaire est obligatoire.");
            $this->redirectToResource($idLivre, $idFilm);
            return;
        }

        // Note: On ne fait PAS htmlspecialchars() ici car :
        // - Les données doivent être stockées brutes en base de données
        // - L'échappement HTML se fait à l'affichage dans les vues (protection XSS)
        // - Faire htmlspecialchars() ici puis dans les vues créerait un double échappement

        try {
            $this->avisModel->updateAvis($idAvis, $idUtilisateur, $note, $text, $objet);
            setFlashSuccess("Votre avis a été modifié avec succès.");
        } catch (Exception $e) {
            setFlashError($e->getMessage());
        }

        $this->redirectToResource($idLivre, $idFilm);
    }

    /**
     * Gère la suppression d'un avis
     * Redirige vers la page de la ressource après suppression
     */
    public function delete(): void
    {
        $this->requireLogin();

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            setFlashError("Méthode non autorisée.");
            header('Location: ' . APP_INDEX_URL . '?route=home');
            exit;
        }

        $idUtilisateur = (int)$_SESSION['user']['id'];
        $idAvis = !empty($_POST['idAvis']) ? (int)$_POST['idAvis'] : 0;
        $idLivre = !empty($_POST['idLivre']) ? (int)$_POST['idLivre'] : null;
        $idFilm = !empty($_POST['idFilm']) ? (int)$_POST['idFilm'] : null;

        // Validation
        if ($idAvis <= 0) {
            setFlashError("Identifiant d'avis invalide.");
            $this->redirectToResource($idLivre, $idFilm);
            return;
        }

        try {
            $this->avisModel->deleteAvis($idAvis, $idUtilisateur);
            setFlashSuccess("Votre avis a été supprimé avec succès.");
        } catch (Exception $e) {
            setFlashError($e->getMessage());
        }

        $this->redirectToResource($idLivre, $idFilm);
    }

    /**
     * Redirige vers la page de la ressource (livre ou film)
     * @param int|null $idLivre ID du livre
     * @param int|null $idFilm ID du film
     */
    private function redirectToResource(?int $idLivre, ?int $idFilm): void
    {
        if ($idLivre !== null) {
            header('Location: ' . APP_INDEX_URL . '?book=' . $idLivre);
        } elseif ($idFilm !== null) {
            header('Location: ' . APP_INDEX_URL . '?film=' . $idFilm);
        } else {
            header('Location: ' . APP_INDEX_URL . '?route=home');
        }
        exit;
    }
}

