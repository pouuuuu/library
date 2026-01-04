*<?php

require_once __DIR__ . '/Model.php';

/**
 * Modèle pour gérer les opérations CRUD sur les ressources (livres et films)
 */
class ResourceModel
{
    private $pdo;

    public function __construct()
    {
        global $pdo;
        require_once __DIR__ . "/../sqlconnect.php";
        $this->pdo = $pdo;
    }

    /**
     * Récupère tous les livres
     * @return array Tableau de livres
     */
    public function getAllBooks()
    {
        $sql = "
            SELECT 
                l.idLivre AS id,
                l.titre AS title,
                l.couverture AS poster,
                l.isbn,
                l.idEditeur,
                l.anneePublication AS publishYear,
                l.prix AS price,
                l.nbPages,
                l.edition,
                l.idLangue,
                l.description,
                lang.nomLangue AS language,
                e.nomEditeur AS editor
            FROM Livres l
            LEFT JOIN Langues lang ON l.idLangue = lang.idLangue
            LEFT JOIN Editeurs e ON l.idEditeur = e.idEditeur
            ORDER BY l.titre
        ";
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Récupère les livres avec pagination
     * @param int $page Numéro de page (commence à 1)
     * @param int $perPage Nombre d'éléments par page
     * @return array ['items' => array, 'total' => int, 'page' => int, 'perPage' => int, 'totalPages' => int]
     */
    public function getBooksPaginated($page = 1, $perPage = 5, $search = '')
    {
        $page = max(1, (int)$page);
        $perPage = max(1, min(100, (int)$perPage)); // Limiter entre 1 et 100
        $offset = ($page - 1) * $perPage;
        $searchTerm = trim($search);

        // Construire la clause WHERE pour la recherche
        $whereClause = '';
        $params = [];
        if (!empty($searchTerm)) {
            $whereClause = "WHERE LOWER(l.titre) LIKE LOWER(:search) OR LOWER(l.isbn) LIKE LOWER(:search)";
            $params[':search'] = '%' . $searchTerm . '%';
        }

        // Compter le total
        $countSql = "SELECT COUNT(*) FROM Livres l " . $whereClause;
        $countStmt = $this->pdo->prepare($countSql);
        foreach ($params as $key => $value) {
            $countStmt->bindValue($key, $value);
        }
        $countStmt->execute();
        $total = (int)$countStmt->fetchColumn();

        // Récupérer les livres
        $sql = "
            SELECT 
                l.idLivre AS id,
                l.titre AS title,
                l.couverture AS poster,
                l.isbn,
                l.idEditeur,
                l.anneePublication AS publishYear,
                l.prix AS price,
                l.nbPages,
                l.edition,
                l.idLangue,
                l.description,
                lang.nomLangue AS language,
                e.nomEditeur AS editor
            FROM Livres l
            LEFT JOIN Langues lang ON l.idLangue = lang.idLangue
            LEFT JOIN Editeurs e ON l.idEditeur = e.idEditeur
            " . $whereClause . "
            ORDER BY l.titre
            LIMIT :limit OFFSET :offset
        ";
        $stmt = $this->pdo->prepare($sql);
        foreach ($params as $key => $value) {
            $stmt->bindValue($key, $value);
        }
        $stmt->bindValue(':limit', $perPage, PDO::PARAM_INT);
        $stmt->bindValue(':offset', $offset, PDO::PARAM_INT);
        $stmt->execute();
        $items = $stmt->fetchAll(PDO::FETCH_ASSOC);

        $totalPages = (int)ceil($total / $perPage);

        return [
            'items' => $items,
            'total' => $total,
            'page' => $page,
            'perPage' => $perPage,
            'totalPages' => $totalPages,
            'search' => $searchTerm
        ];
    }

    /**
     * Récupère tous les films
     * @return array Tableau de films
     */
    public function getAllFilms()
    {
        $sql = "
            SELECT 
                f.idFilm AS id,
                f.titre AS title,
                f.poster,
                f.synopsis,
                f.anneeProduction AS productionYear,
                f.dateSortie AS releaseDate,
                f.bandeAnnonce AS trailer,
                f.duree AS duration,
                f.idTypeFilm AS typeId
            FROM Films f
            ORDER BY f.titre
        ";
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Récupère les films avec pagination
     * @param int $page Numéro de page (commence à 1)
     * @param int $perPage Nombre d'éléments par page
     * @return array ['items' => array, 'total' => int, 'page' => int, 'perPage' => int, 'totalPages' => int]
     */
    public function getFilmsPaginated($page = 1, $perPage = 5, $search = '')
    {
        $page = max(1, (int)$page);
        $perPage = max(1, min(100, (int)$perPage)); // Limiter entre 1 et 100
        $offset = ($page - 1) * $perPage;
        $searchTerm = trim($search);

        // Construire la clause WHERE pour la recherche
        $whereClause = '';
        $params = [];
        if (!empty($searchTerm)) {
            $whereClause = "WHERE LOWER(f.titre) LIKE LOWER(:search)";
            $params[':search'] = '%' . $searchTerm . '%';
        }

        // Compter le total
        $countSql = "SELECT COUNT(*) FROM Films f " . $whereClause;
        $countStmt = $this->pdo->prepare($countSql);
        foreach ($params as $key => $value) {
            $countStmt->bindValue($key, $value);
        }
        $countStmt->execute();
        $total = (int)$countStmt->fetchColumn();

        // Récupérer les films
        $sql = "
            SELECT 
                f.idFilm AS id,
                f.titre AS title,
                f.poster,
                f.synopsis,
                f.anneeProduction AS productionYear,
                f.dateSortie AS releaseDate,
                f.bandeAnnonce AS trailer,
                f.duree AS duration,
                f.idTypeFilm AS typeId
            FROM Films f
            " . $whereClause . "
            ORDER BY f.titre
            LIMIT :limit OFFSET :offset
        ";
        $stmt = $this->pdo->prepare($sql);
        foreach ($params as $key => $value) {
            $stmt->bindValue($key, $value);
        }
        $stmt->bindValue(':limit', $perPage, PDO::PARAM_INT);
        $stmt->bindValue(':offset', $offset, PDO::PARAM_INT);
        $stmt->execute();
        $items = $stmt->fetchAll(PDO::FETCH_ASSOC);

        $totalPages = (int)ceil($total / $perPage);

        return [
            'items' => $items,
            'total' => $total,
            'page' => $page,
            'perPage' => $perPage,
            'totalPages' => $totalPages,
            'search' => $searchTerm
        ];
    }

    /**
     * Récupère un livre par son ID
     * @param int $id
     * @return array|null
     */
    public function getBookById($id)
    {
        $sql = "
            SELECT 
                l.*,
                lang.nomLangue AS language,
                e.nomEditeur AS editor
            FROM Livres l
            LEFT JOIN Langues lang ON l.idLangue = lang.idLangue
            LEFT JOIN Editeurs e ON l.idEditeur = e.idEditeur
            WHERE l.idLivre = :id
        ";
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute(['id' => $id]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    /**
     * Récupère un film par son ID
     * @param int $id
     * @return array|null
     */
    public function getFilmById($id)
    {
        $sql = "SELECT * FROM Films WHERE idFilm = :id";
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute(['id' => $id]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    /**
     * Crée un nouveau livre
     * @param array $data Données du livre
     * @return int ID du livre créé
     */
    public function createBook($data)
    {
        $sql = "
            INSERT INTO Livres (
                titre, isbn, idEditeur, anneePublication, prix, nbPages, 
                edition, idLangue, couverture, description
            ) VALUES (
                :titre, :isbn, :idEditeur, :anneePublication, :prix, :nbPages,
                :edition, :idLangue, :couverture, :description
            )
        ";
        
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([
            'titre' => $data['titre'] ?? null,
            'isbn' => $data['isbn'] ?? null,
            'idEditeur' => !empty($data['idEditeur']) ? (int)$data['idEditeur'] : null,
            'anneePublication' => !empty($data['anneePublication']) ? (int)$data['anneePublication'] : null,
            'prix' => !empty($data['prix']) ? (float)$data['prix'] : null,
            'nbPages' => !empty($data['nbPages']) ? (int)$data['nbPages'] : null,
            'edition' => $data['edition'] ?? null,
            'idLangue' => !empty($data['idLangue']) ? (int)$data['idLangue'] : null,
            'couverture' => $data['couverture'] ?? null,
            'description' => $data['description'] ?? null
        ]);
        
        return (int)$this->pdo->lastInsertId();
    }

    /**
     * Crée un nouveau film
     * @param array $data Données du film
     * @return int ID du film créé
     */
    public function createFilm($data)
    {
        $sql = "
            INSERT INTO Films (
                titre, synopsis, anneeProduction, dateSortie, bandeAnnonce,
                duree, idTypeFilm, poster
            ) VALUES (
                :titre, :synopsis, :anneeProduction, :dateSortie, :bandeAnnonce,
                :duree, :idTypeFilm, :poster
            )
        ";
        
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([
            'titre' => $data['titre'] ?? null,
            'synopsis' => $data['synopsis'] ?? null,
            'anneeProduction' => !empty($data['anneeProduction']) ? (int)$data['anneeProduction'] : null,
            'dateSortie' => $data['dateSortie'] ?? null,
            'bandeAnnonce' => $data['bandeAnnonce'] ?? null,
            'duree' => !empty($data['duree']) ? (int)$data['duree'] : null,
            'idTypeFilm' => !empty($data['idTypeFilm']) ? (int)$data['idTypeFilm'] : null,
            'poster' => $data['poster'] ?? null
        ]);
        
        return (int)$this->pdo->lastInsertId();
    }

    /**
     * Met à jour un livre
     * @param int $id ID du livre
     * @param array $data Données à mettre à jour
     * @return bool
     */
    public function updateBook($id, $data)
    {
        $sql = "
            UPDATE Livres SET
                titre = :titre,
                isbn = :isbn,
                idEditeur = :idEditeur,
                anneePublication = :anneePublication,
                prix = :prix,
                nbPages = :nbPages,
                edition = :edition,
                idLangue = :idLangue,
                couverture = :couverture,
                description = :description
            WHERE idLivre = :id
        ";
        
        $stmt = $this->pdo->prepare($sql);
        return $stmt->execute([
            'id' => (int)$id,
            'titre' => $data['titre'] ?? null,
            'isbn' => $data['isbn'] ?? null,
            'idEditeur' => !empty($data['idEditeur']) ? (int)$data['idEditeur'] : null,
            'anneePublication' => !empty($data['anneePublication']) ? (int)$data['anneePublication'] : null,
            'prix' => !empty($data['prix']) ? (float)$data['prix'] : null,
            'nbPages' => !empty($data['nbPages']) ? (int)$data['nbPages'] : null,
            'edition' => $data['edition'] ?? null,
            'idLangue' => !empty($data['idLangue']) ? (int)$data['idLangue'] : null,
            'couverture' => $data['couverture'] ?? null,
            'description' => $data['description'] ?? null
        ]);
    }

    /**
     * Met à jour un film
     * @param int $id ID du film
     * @param array $data Données à mettre à jour
     * @return bool
     */
    public function updateFilm($id, $data)
    {
        $sql = "
            UPDATE Films SET
                titre = :titre,
                synopsis = :synopsis,
                anneeProduction = :anneeProduction,
                dateSortie = :dateSortie,
                bandeAnnonce = :bandeAnnonce,
                duree = :duree,
                idTypeFilm = :idTypeFilm,
                poster = :poster
            WHERE idFilm = :id
        ";
        
        $stmt = $this->pdo->prepare($sql);
        return $stmt->execute([
            'id' => (int)$id,
            'titre' => $data['titre'] ?? null,
            'synopsis' => $data['synopsis'] ?? null,
            'anneeProduction' => !empty($data['anneeProduction']) ? (int)$data['anneeProduction'] : null,
            'dateSortie' => $data['dateSortie'] ?? null,
            'bandeAnnonce' => $data['bandeAnnonce'] ?? null,
            'duree' => !empty($data['duree']) ? (int)$data['duree'] : null,
            'idTypeFilm' => !empty($data['idTypeFilm']) ? (int)$data['idTypeFilm'] : null,
            'poster' => $data['poster'] ?? null
        ]);
    }

    /**
     * Supprime un livre
     * @param int $id ID du livre
     * @return bool
     */
    public function deleteBook($id)
    {
        // Supprimer d'abord les relations avec les auteurs
        $sqlAuthors = "DELETE FROM AuteursLivres WHERE idLivre = :id";
        $stmt = $this->pdo->prepare($sqlAuthors);
        $stmt->execute(['id' => (int)$id]);
        
        // Supprimer le livre
        $sql = "DELETE FROM Livres WHERE idLivre = :id";
        $stmt = $this->pdo->prepare($sql);
        return $stmt->execute(['id' => (int)$id]);
    }

    /**
     * Supprime un film
     * @param int $id ID du film
     * @return bool
     */
    public function deleteFilm($id)
    {
        // Supprimer d'abord les relations avec les langues
        $sqlLanguages = "DELETE FROM LanguesFilms WHERE idFilm = :id";
        $stmt = $this->pdo->prepare($sqlLanguages);
        $stmt->execute(['id' => (int)$id]);
        
        // Supprimer le film
        $sql = "DELETE FROM Films WHERE idFilm = :id";
        $stmt = $this->pdo->prepare($sql);
        return $stmt->execute(['id' => (int)$id]);
    }

    /**
     * Récupère toutes les langues disponibles
     * @return array
     */
    public function getAllLanguages()
    {
        $sql = "SELECT idLangue, nomLangue FROM Langues ORDER BY nomLangue";
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Récupère tous les éditeurs disponibles
     * @return array
     */
    public function getAllEditors()
    {
        $sql = "SELECT idEditeur, nomEditeur FROM Editeurs ORDER BY nomEditeur";
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Récupère tous les types de films disponibles
     * @return array
     */
    public function getAllFilmTypes()
    {
        // Vérifier si la table existe
        try {
            $sql = "SELECT idTypeFilm, nomTypeFilm FROM TypesFilms ORDER BY nomTypeFilm";
            $stmt = $this->pdo->prepare($sql);
            $stmt->execute();
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            // Si la table n'existe pas, retourner un tableau vide
            return [];
        }
    }

    /**
     * Incrémente le compteur d'emprunts pour un livre
     * @param int $bookId ID du livre
     * @return bool true si la mise à jour a réussi
     */
    public function incrementBookNbEmprunts($bookId)
    {
        $sql = "
            UPDATE Livres 
            SET nbEmprunts = nbEmprunts + 1 
            WHERE idLivre = :id
        ";
        $stmt = $this->pdo->prepare($sql);
        return $stmt->execute(['id' => (int)$bookId]);
    }

    /**
     * Incrémente le compteur d'emprunts pour un film
     * @param int $filmId ID du film
     * @return bool true si la mise à jour a réussi
     */
    public function incrementFilmNbEmprunts($filmId)
    {
        $sql = "
            UPDATE Films 
            SET nbEmprunts = nbEmprunts + 1 
            WHERE idFilm = :id
        ";
        $stmt = $this->pdo->prepare($sql);
        return $stmt->execute(['id' => (int)$filmId]);
    }
}

