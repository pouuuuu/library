<?php
require_once __DIR__ . "/../model/Model.php";
require_once __DIR__ . "/../helpers/auth.php";

class TopController
{
    private $model;

    public function __construct()
    {
        $this->model = new Model();
    }

    public function index()
    {
        requireLogin();

        $isAjax = isset($_SERVER['HTTP_X_REQUESTED_WITH']) &&
            strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest';

        if (isset($_GET['action']) && $_GET['action'] === 'list' && $isAjax) {
            $this->handleAjaxRequest();
            return;
        }

        $page = isset($_GET['page']) ? max(1, (int)$_GET['page']) : 1;

        $sort = isset($_GET['sort']) && in_array($_GET['sort'], ['rating', 'comments']) ? $_GET['sort'] : 'rating';

        $limit = 20;
        $offset = ($page - 1) * $limit;

        $resources = $this->model->getTopBooks($limit, $offset, $sort);
        $totalCount = $this->model->countTopBooks();
        $totalPages = ceil($totalCount / $limit);

        $startRank = $offset + 1;

        require __DIR__ . '/../view/top.php';
    }

    private function handleAjaxRequest()
    {
        header('Content-Type: application/json; charset=utf-8');

        $page = isset($_GET['page']) ? max(1, (int)$_GET['page']) : 1;

        $sort = isset($_GET['sort']) && in_array($_GET['sort'], ['rating', 'comments']) ? $_GET['sort'] : 'rating';

        $limit = isset($_GET['limit']) ? (int)$_GET['limit'] : 20;
        $offset = ($page - 1) * $limit;

        try {
            $resources = $this->model->getTopBooks($limit, $offset, $sort);
            $totalCount = $this->model->countTopBooks();
            $totalPages = ceil($totalCount / $limit);

            echo json_encode([
                'success' => true,
                'resources' => $resources,
                'totalCount' => $totalCount,
                'totalPages' => $totalPages,
                'currentPage' => $page,
                'startRank' => $offset + 1,
                'sort' => $sort
            ], JSON_UNESCAPED_UNICODE);
        } catch (Exception $e) {
            echo json_encode(['success' => false, 'error' => $e->getMessage()]);
        }
        exit;
    }
}