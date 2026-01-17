<?php
require_once __DIR__ . "/../model/Model.php";
require_once __DIR__ . "/../model/AvisModel.php";
require_once __DIR__ . "/../model/EmpruntModel.php";

/**
 * Controller pour gérer l'affichage des détails des ressources (livres et films)
 */
class ResourceController
{
    private $model;
    private $avisModel;
    private $empruntModel;

    public function __construct()
    {
        $this->model = new Model();
        $this->avisModel = new AvisModel();
        $this->empruntModel = new EmpruntModel();
    }

    /**
     * Affiche les détails d'un film
     * Page protégée : nécessite une connexion
     */
    public function showFilm()
    {
        // Vérifier que l'utilisateur est connecté
        requireLogin();
        
        // Récupérer l'ID du film depuis $_GET
        $idFilm = isset($_GET['film']) ? (int)$_GET['film'] : 0;

        if ($idFilm <= 0) {
            $this->handleError("ID de film invalide");
            return;
        }

        // Récupérer le film depuis le modèle
        $film = $this->model->getFilmById($idFilm);

        if (!$film) {
            $this->handleError("Film non trouvé");
            return;
        }

        // Récupérer les avis du film
        $avis = $this->avisModel->getAvisByResource(null, $idFilm);
        
        // Récupérer la note moyenne
        $rating = $this->avisModel->getAverageRating(null, $idFilm);
        
        // Récupérer l'avis de l'utilisateur connecté s'il en a un
        $userAvis = null;
        if (!empty($_SESSION['user']['id'])) {
            $userAvis = $this->avisModel->getAvisByUserAndResource((int)$_SESSION['user']['id'], null, $idFilm);
        }

        // Vérifier si l'utilisateur a déjà emprunté ce film
        $isEmprunte = false;
        if (!empty($_SESSION['user']['id'])) {
            $isEmprunte = $this->empruntModel->exists((int)$_SESSION['user']['id'], null, $idFilm);
        }

        // Passer l'ID du film à la vue
        $idFilmForView = $idFilm;

        // Passer les données à la vue
        require __DIR__ . '/../view/resource/film.php';
    }

    /**
     * Affiche les détails d'un livre
     * Page protégée : nécessite une connexion
     */
    public function showBook()
    {
        // Vérifier que l'utilisateur est connecté
        requireLogin();
        
        // Récupérer l'ID du livre depuis $_GET
        $idBook = isset($_GET['book']) ? (int)$_GET['book'] : 0;

        if ($idBook <= 0) {
            $this->handleError("ID de livre invalide");
            return;
        }

        // Récupérer le livre depuis le modèle
        $book = $this->model->getBookById($idBook);

        if (!$book) {
            $this->handleError("Livre non trouvé");
            return;
        }

        // Récupérer les avis du livre
        $avis = $this->avisModel->getAvisByResource($idBook, null);
        
        // Récupérer la note moyenne
        $rating = $this->avisModel->getAverageRating($idBook, null);
        
        // Récupérer l'avis de l'utilisateur connecté s'il en a un
        $userAvis = null;
        if (!empty($_SESSION['user']['id'])) {
            $userAvis = $this->avisModel->getAvisByUserAndResource((int)$_SESSION['user']['id'], $idBook, null);
        }

        // Vérifier si l'utilisateur a déjà emprunté ce livre
        $isEmprunte = false;
        if (!empty($_SESSION['user']['id'])) {
            $isEmprunte = $this->empruntModel->exists((int)$_SESSION['user']['id'], $idBook, null);
        }

        // Passer l'ID du livre à la vue
        $idBookForView = $idBook;

        // Passer les données à la vue
        require __DIR__ . '/../view/resource/book.php';
    }

    /**
     * Gère les erreurs en affichant une page d'erreur
     */
    private function handleError($message)
    {
        setFlashError($message);
        $errorMessage = $message;
        require __DIR__ . '/../view/resource/error.php';
    }
}

