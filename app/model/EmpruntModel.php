<?php

/**
 * Modèle pour gérer les opérations sur les emprunts
 * Gère les emprunts de livres et de films par les utilisateurs
 */
class EmpruntModel
{
    private $pdo;

    public function __construct()
    {
        global $pdo;
        require_once __DIR__ . "/../sqlconnect.php";
        $this->pdo = $pdo;
    }

    /**
     * Crée un nouvel emprunt
     * @param int $userId ID de l'utilisateur
     * @param int|null $bookId ID du livre (null si c'est un film)
     * @param int|null $filmId ID du film (null si c'est un livre)
     * @return int ID de l'emprunt créé
     * @throws Exception Si l'emprunt existe déjà ou si les paramètres sont invalides
     */
    public function createEmprunt($userId, $bookId = null, $filmId = null)
    {
        // Validation : soit livre soit film, mais pas les deux ni aucun
        if (($bookId === null && $filmId === null) || ($bookId !== null && $filmId !== null)) {
            throw new InvalidArgumentException("Il faut spécifier soit un livre, soit un film, mais pas les deux.");
        }

        // Vérifier que l'emprunt n'existe pas déjà
        if ($this->exists($userId, $bookId, $filmId)) {
            throw new Exception("Cette ressource est déjà empruntée par cet utilisateur.");
        }

        // Construire la requête SQL selon le type de ressource
        // Utiliser des paramètres liés pour NULL pour que PDO gère correctement les valeurs NULL
        if ($bookId !== null) {
            // C'est un livre : idLivre = valeur, idFilm = NULL
            $sql = "
                INSERT INTO Emprunts (idUtilisateur, idLivre, idFilm, timestamp)
                VALUES (:idUtilisateur, :idLivre, :idFilm, NOW())
            ";
            $stmt = $this->pdo->prepare($sql);
            $stmt->bindValue(':idUtilisateur', (int)$userId, PDO::PARAM_INT);
            $stmt->bindValue(':idLivre', (int)$bookId, PDO::PARAM_INT);
            $stmt->bindValue(':idFilm', null, PDO::PARAM_NULL);
        } else {
            // C'est un film : idLivre = NULL, idFilm = valeur
            $sql = "
                INSERT INTO Emprunts (idUtilisateur, idLivre, idFilm, timestamp)
                VALUES (:idUtilisateur, :idLivre, :idFilm, NOW())
            ";
            $stmt = $this->pdo->prepare($sql);
            $stmt->bindValue(':idUtilisateur', (int)$userId, PDO::PARAM_INT);
            $stmt->bindValue(':idLivre', null, PDO::PARAM_NULL);
            $stmt->bindValue(':idFilm', (int)$filmId, PDO::PARAM_INT);
        }

        $stmt->execute();

        return (int)$this->pdo->lastInsertId();
    }

    /**
     * Récupère tous les emprunts d'un utilisateur
     * @param int $userId ID de l'utilisateur
     * @return array Tableau d'emprunts avec les informations des ressources
     */
    public function getByUser($userId)
    {
        $sql = "
            SELECT 
                e.idUtilisateur,
                e.idLivre,
                e.idFilm,
                e.timestamp,
                -- Informations du livre si c'est un livre
                l.titre AS livre_titre,
                l.couverture AS livre_poster,
                -- Informations du film si c'est un film
                f.titre AS film_titre,
                f.poster AS film_poster,
                -- Déterminer le type de ressource
                CASE 
                    WHEN e.idLivre IS NOT NULL THEN 'livre'
                    WHEN e.idFilm IS NOT NULL THEN 'film'
                END AS type_ressource,
                -- Titre unifié
                COALESCE(l.titre, f.titre) AS titre,
                -- Poster unifié
                COALESCE(l.couverture, f.poster) AS poster
            FROM Emprunts e
            LEFT JOIN Livres l ON e.idLivre = l.idLivre
            LEFT JOIN Films f ON e.idFilm = f.idFilm
            WHERE e.idUtilisateur = :idUtilisateur
            AND (e.idLivre IS NOT NULL OR e.idFilm IS NOT NULL)
            ORDER BY e.timestamp DESC
        ";

        $stmt = $this->pdo->prepare($sql);
        $stmt->execute(['idUtilisateur' => (int)$userId]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Vérifie si un emprunt existe déjà pour un utilisateur et une ressource
     * @param int $userId ID de l'utilisateur
     * @param int|null $bookId ID du livre (null si c'est un film)
     * @param int|null $filmId ID du film (null si c'est un livre)
     * @return bool true si l'emprunt existe, false sinon
     */
    public function exists($userId, $bookId = null, $filmId = null)
    {
        // Construire la requête selon le type de ressource
        if ($bookId !== null) {
            $sql = "
                SELECT COUNT(*) 
                FROM Emprunts 
                WHERE idUtilisateur = :idUtilisateur 
                AND idLivre = :idLivre
                AND (idFilm IS NULL OR idFilm = 0)
            ";
            $params = [
                'idUtilisateur' => (int)$userId,
                'idLivre' => (int)$bookId
            ];
        } elseif ($filmId !== null) {
            $sql = "
                SELECT COUNT(*) 
                FROM Emprunts 
                WHERE idUtilisateur = :idUtilisateur 
                AND idFilm = :idFilm
                AND (idLivre IS NULL OR idLivre = 0)
            ";
            $params = [
                'idUtilisateur' => (int)$userId,
                'idFilm' => (int)$filmId
            ];
        } else {
            return false;
        }

        $stmt = $this->pdo->prepare($sql);
        $stmt->execute($params);
        $count = (int)$stmt->fetchColumn();

        return $count > 0;
    }

    /**
     * Vérifie si un utilisateur a déjà emprunté une ressource (par ID de ressource et type)
     * @param int $userId ID de l'utilisateur
     * @param int $resourceId ID de la ressource
     * @param string $type Type de ressource ('livre' ou 'film')
     * @return bool true si l'emprunt existe, false sinon
     */
    public function existsByResource($userId, $resourceId, $type)
    {
        if ($type === 'livre') {
            return $this->exists($userId, $resourceId, null);
        } elseif ($type === 'film') {
            return $this->exists($userId, null, $resourceId);
        }
        return false;
    }

    /**
     * Supprime un emprunt
     * @param int $userId ID de l'utilisateur (pour vérifier que c'est bien son emprunt)
     * @param int|null $bookId ID du livre (null si c'est un film)
     * @param int|null $filmId ID du film (null si c'est un livre)
     * @return bool true si la suppression a réussi
     * @throws Exception Si l'emprunt n'existe pas ou n'appartient pas à l'utilisateur
     */
    public function deleteEmprunt($userId, $bookId = null, $filmId = null)
    {
        // Validation : soit livre soit film, mais pas les deux ni aucun
        if (($bookId === null && $filmId === null) || ($bookId !== null && $filmId !== null)) {
            throw new InvalidArgumentException("Il faut spécifier soit un livre, soit un film, mais pas les deux.");
        }

        // Construire la requête selon le type de ressource
        if ($bookId !== null) {
            $sql = "
                DELETE FROM Emprunts 
                WHERE idUtilisateur = :idUtilisateur 
                AND idLivre = :idLivre
                AND (idFilm IS NULL OR idFilm = 0)
            ";
            $params = [
                'idUtilisateur' => (int)$userId,
                'idLivre' => (int)$bookId
            ];
        } else {
            $sql = "
                DELETE FROM Emprunts 
                WHERE idUtilisateur = :idUtilisateur 
                AND idFilm = :idFilm
                AND (idLivre IS NULL OR idLivre = 0)
            ";
            $params = [
                'idUtilisateur' => (int)$userId,
                'idFilm' => (int)$filmId
            ];
        }

        $stmt = $this->pdo->prepare($sql);
        $stmt->execute($params);
        
        // Vérifier si une ligne a été supprimée
        return $stmt->rowCount() > 0;
    }
}

