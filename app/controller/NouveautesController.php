<?php
require_once __DIR__ . "/../model/Model.php";

/**
 * Controller pour la page Nouveautés (ressources récentes)
 * Page protégée : nécessite une connexion
 */
class NouveautesController
{
    private $model;

    public function __construct()
    {
        $this->model = new Model();
    }

    /**
     * Affiche la page Nouveautés ou répond aux requêtes AJAX
     */
    public function index()
    {
        // Vérifier que l'utilisateur est connecté
        requireLogin();

        // Si c'est une requête AJAX pour récupérer les ressources filtrées
        $isAjax = isset($_SERVER['HTTP_X_REQUESTED_WITH']) && 
                  strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest';
        
        if (isset($_GET['action']) && $_GET['action'] === 'news' && $isAjax) {
            $this->handleAjaxRequest();
            return;
        }

        // Récupérer les paramètres de filtrage
        $type = isset($_GET['type']) ? $_GET['type'] : null;
        $theme = isset($_GET['theme']) ? $_GET['theme'] : null;
        $limit = isset($_GET['limit']) ? (int)$_GET['limit'] : 20;
        $page = isset($_GET['page']) ? max(1, (int)$_GET['page']) : 1;
        $offset = ($page - 1) * $limit;

        // Valider le type
        if ($type !== null && !in_array($type, ['book', 'film'], true)) {
            $type = null;
        }

        // Récupérer le nombre total de ressources (pour la pagination)
        $totalCount = $this->model->countLatest($type, $theme);
        $totalPages = $totalCount > 0 ? ceil($totalCount / $limit) : 1;
        
        // S'assurer que la page demandée n'est pas supérieure au nombre total de pages
        if ($page > $totalPages) {
            $page = $totalPages;
            $offset = ($page - 1) * $limit;
        }
        
        // Récupérer les ressources les plus récentes
        $resources = $this->model->getLatest($limit, $offset, $type, $theme);

        // Récupérer la liste des thèmes disponibles
        $themes = $this->model->getAvailableThemes();

        // Passer les données à la vue
        require __DIR__ . '/../view/nouveautes.php';
    }

    /**
     * Gère les requêtes AJAX pour le filtrage dynamique
     */
    private function handleAjaxRequest()
    {
        header('Content-Type: application/json; charset=utf-8');

        // Récupérer les paramètres
        $type = isset($_GET['type']) ? $_GET['type'] : null;
        $theme = isset($_GET['theme']) ? $_GET['theme'] : null;
        $limit = isset($_GET['limit']) ? (int)$_GET['limit'] : 20;
        $page = isset($_GET['page']) ? max(1, (int)$_GET['page']) : 1;
        $offset = ($page - 1) * $limit;

        // Valider le type
        if ($type !== null && !in_array($type, ['book', 'film'], true)) {
            $type = null;
        }

        // Récupérer le nombre total de ressources (pour la pagination)
        $totalCount = $this->model->countLatest($type, $theme);
        $totalPages = $totalCount > 0 ? ceil($totalCount / $limit) : 1;
        
        // S'assurer que la page demandée n'est pas supérieure au nombre total de pages
        if ($page > $totalPages) {
            $page = $totalPages;
            $offset = ($page - 1) * $limit;
        }
        
        // Récupérer les ressources filtrées
        $resources = $this->model->getLatest($limit, $offset, $type, $theme);

        // Retourner les résultats en JSON
        echo json_encode([
            'success' => true,
            'resources' => $resources,
            'count' => count($resources),
            'totalCount' => $totalCount,
            'totalPages' => $totalPages,
            'currentPage' => $page,
            'limit' => $limit
        ], JSON_UNESCAPED_UNICODE);

        exit;
    }
}

