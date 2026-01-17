<?php
require_once __DIR__ . "/../model/Model.php";

class Controller
{
    private $model;

    public function __construct()
    {
        $this->model = new Model();
    }

    public function invoke()
    {
        if (isset($_GET['action']) && $_GET['action'] === 'loadMore') {
            $offset = (int)($_GET['offset'] ?? 0);
            $limit  = (int)($_GET['limit'] ?? 0);

            $resources = $this->model->getSomeResources($offset, $limit);

            header('Content-Type: application/json; charset=utf-8');
            echo json_encode($resources, JSON_UNESCAPED_UNICODE);
            exit;
        }

		$carouselResources = $this->model->getCarouselResources(6);
		$popularResources = $this->model->getPopularResources(3);

        include __DIR__ . '/../view/home.php';
    }
}
