<?php
require_once __DIR__ . "/../model/Model.php";

/**
 * Controller pour la page TOP (ressources les plus populaires)
 * Page protégée : nécessite une connexion
 */
class TopController
{
    private $model;

    public function __construct()
    {
        $this->model = new Model();
    }

    /**
     * Affiche la page TOP
     */
    public function index()
    {
        // Vérifier que l'utilisateur est connecté
        requireLogin();
        
        // TODO: Récupérer les ressources les plus populaires depuis le modèle
        // Pour l'instant, on affiche une page simple
        require __DIR__ . '/../view/top.php';
    }
}

