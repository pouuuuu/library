<?php
require_once __DIR__ . "/../model/Model.php";

/**
 * Controller pour gérer la recherche de ressources (livres et films)
 */
class SearchController
{
    private $model;

    public function __construct()
    {
        $this->model = new Model();
    }

    /**
     * Gère la recherche simple et avancée
     * Affiche les résultats de recherche
     */
    public function search()
    {
        $query = isset($_GET['q']) ? trim($_GET['q']) : '';
        $filters = [
            'type' => $_GET['type'] ?? '', // 'book', 'film', ou '' pour tous
            'language' => $_GET['language'] ?? '',
            'year_min' => isset($_GET['year_min']) && $_GET['year_min'] !== '' ? (int)$_GET['year_min'] : null,
            'year_max' => isset($_GET['year_max']) && $_GET['year_max'] !== '' ? (int)$_GET['year_max'] : null,
            'author' => isset($_GET['author']) ? trim($_GET['author']) : '',
        ];

        // Si pas de requête et pas de filtres, afficher le formulaire de recherche avancée
        if (empty($query) && empty(array_filter($filters))) {
            $this->showAdvancedSearchForm();
            return;
        }

        // Effectuer la recherche
        $results = $this->model->searchResources($query, $filters);

        // Passer les résultats et la requête à la vue
        // Les variables $query, $results et $filters seront disponibles dans la vue
        require __DIR__ . '/../view/search/results.php';
    }

    /**
     * Gère l'autocomplete (retourne JSON)
     */
    public function autocomplete()
    {
        // Désactiver l'affichage des erreurs pour les réponses JSON
        ini_set('display_errors', 0);
        error_reporting(0);
        
        // Vérifier que c'est une requête AJAX
        if (!isset($_GET['q'])) {
            http_response_code(400);
            header('Content-Type: application/json; charset=utf-8');
            echo json_encode(['error' => 'Paramètre q manquant']);
            exit;
        }

        $query = trim($_GET['q']);
        $limit = isset($_GET['limit']) ? (int)$_GET['limit'] : 10;

        // Si la requête est trop courte, retourner un tableau vide
        if (strlen($query) < 2) {
            header('Content-Type: application/json; charset=utf-8');
            echo json_encode([]);
            exit;
        }

        try {
            // Récupérer les suggestions
            $suggestions = $this->model->autocompleteSearch($query, $limit);

            // Retourner en JSON
            header('Content-Type: application/json; charset=utf-8');
            echo json_encode($suggestions, JSON_UNESCAPED_UNICODE);
        } catch (Exception $e) {
            // En cas d'erreur, retourner un tableau vide plutôt qu'une erreur
            header('Content-Type: application/json; charset=utf-8');
            http_response_code(500);
            echo json_encode(['error' => 'Erreur lors de la recherche']);
        }
        exit;
    }

    /**
     * Affiche le formulaire de recherche avancée
     */
    private function showAdvancedSearchForm()
    {
        // Récupérer les langues disponibles pour le select
        $languages = $this->model->getAvailableLanguages();
        
        require __DIR__ . '/../view/search/advanced.php';
    }
}

