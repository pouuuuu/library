<?php

require_once __DIR__ . '/resources/Film.php';
require_once __DIR__ . '/resources/Book.php';

class Model
{
    private $pdo;

    public function __construct()
    {
        global $pdo;
        require_once __DIR__ . "/../sqlconnect.php";
        $this->pdo = $pdo;
    }

    public function getSomeResources($offset, $limit): array
    {
        // moitié-moitié films / livres
        $limitFilms = (int)ceil($limit / 2);
        $limitBooks = (int)floor($limit / 2);

        // --- Films ---
        $sqlFilms = "
        SELECT 
            idFilm AS id,
            titre AS title,
            poster,
            dateSortie AS releaseDate,
            duree AS duration,
            anneeProduction AS productionYear,
            'film' AS resource_type
        FROM Films
        WHERE poster IS NOT NULL AND poster != ''
        ORDER BY RAND()
        LIMIT :limitFilms
    ";
        $stmt = $this->pdo->prepare($sqlFilms);
        $stmt->bindValue(':limitFilms', $limitFilms, PDO::PARAM_INT);
        $stmt->execute();
        $films = $stmt->fetchAll(PDO::FETCH_ASSOC);

        // --- Livres ---
        $sqlBooks = "
        SELECT 
            l.idLivre AS id,
            l.titre AS title,
            l.couverture AS poster,
            l.anneePublication AS publishYear,
            lang.nomLangue AS language,
            'book' AS resource_type
        FROM Livres l
        LEFT JOIN Langues lang ON l.idLangue = lang.idLangue
        WHERE l.couverture IS NOT NULL AND l.couverture != ''
        ORDER BY RAND()
        LIMIT :limitBooks
    ";
        $stmt = $this->pdo->prepare($sqlBooks);
        $stmt->bindValue(':limitBooks', $limitBooks, PDO::PARAM_INT);
        $stmt->execute();
        $books = $stmt->fetchAll(PDO::FETCH_ASSOC);

        // --- Fusionner & mélanger ---
        $rows = array_merge($films, $books);
        shuffle($rows);

        $resources = [];

        foreach ($rows as $row) {
            if ($row['resource_type'] === 'film') {
                $resources[] = [
                    'id' => $row['id'],
                    'type' => 'film',
                    'title' => $row['title'],
                    'poster' => $row['poster'],
                    'duration' => $row['duration'] ?? null,
                    'releaseDate' => $row['releaseDate'] ?? null,
                    'productionYear' => $row['productionYear'] ?? null,
                    'languages' => []
                ];
            } else {
                $resources[] = [
                    'id' => $row['id'],
                    'type' => 'book',
                    'title' => $row['title'],
                    'poster' => $row['poster'],
                    'authors' => [],
                    'publishYear' => $row['publishYear'] ?? null,
                    'language' => $row['language'] ?? null
                ];
            }
        }

        return $resources;
    }

    public function getCarouselResources(int $nbResources): array
    {
        $nbFilms = (int)ceil($nbResources / 2);
        $nbBooks = (int)floor($nbResources / 2);

        $resources = array_merge($this->getCarouselFilms($nbFilms), $this->getCarouselBooks($nbBooks));
        for ($i = 0; $i < count($resources); $i++) {
            $resources[$i]['genres'] = isset($resources[$i]['genres']) ? array_slice(explode(",", $resources[$i]['genres']), 0, 2) : null;
            $resources[$i]['themes'] = isset($resources[$i]['themes']) ? array_slice(explode(",", $resources[$i]['themes']), 0, 2) : null;
        }

        shuffle($resources);

        return $resources;
    }

    private function getCarouselFilms(int $nbFilms): array
    {
        $sqlFilms = "
        SELECT
            'film' AS resource_type,
            f.idFilm AS id,
            f.titre AS title,
            f.poster,
            f.dateSortie AS releaseDate,
            f.duree AS duration,
            f.anneeProduction AS year,
            f.nbEmprunts AS nbBorrowings,
            
    		tf.nomTypeFilm AS type,
    		
    		GROUP_CONCAT(DISTINCT l.nomLangue ORDER BY l.nomLangue SEPARATOR ',') AS languages,
    		GROUP_CONCAT(DISTINCT g.nomGenre ORDER BY g.nomGenre SEPARATOR ',') AS genres,
    		GROUP_CONCAT(DISTINCT t.nomTheme ORDER BY t.nomTheme SEPARATOR ',') AS themes
        FROM Films f
        LEFT JOIN TypeFilm tf ON tf.idTypeFilm = f.idTypeFilm

		LEFT JOIN Films_Langues fl ON fl.idFilm = f.idFilm
		LEFT JOIN Langues l ON l.idLangue = fl.idLangue
		
		LEFT JOIN Films_Genres fg ON fg.idFilm = f.idFilm
		LEFT JOIN Genres g ON g.idGenre = fg.idGenre
		
		LEFT JOIN Films_Themes ft ON ft.idFilm = f.idFilm
		LEFT JOIN Thèmes t ON t.idTheme = ft.idTheme
        
        WHERE f.poster IS NOT NULL AND f.poster != ''
        GROUP BY f.idFilm
        ORDER BY RAND()
        LIMIT :nbFilms
    	";

        $stmt = $this->pdo->prepare($sqlFilms);
        $stmt->bindParam(':nbFilms', $nbFilms, PDO::PARAM_INT);
        $stmt->execute();
        $films = $stmt->fetchAll(PDO::FETCH_ASSOC);

        for ($i = 0; $i < count($films); $i++) {
            $films[$i]['duration'] = isset($films[$i]['duration']) ? $this->minutesToTime($films[$i]['duration']) : null;
        }

        return $films;
    }

    private function getCarouselBooks(int $nbBooks): array
    {
        $sqlBooks = "
        SELECT
            'book' AS resource_type,
            l.idLivre AS id,
            l.titre AS title,
            l.couverture AS poster,
            l.anneePublication AS year,
            l.nbEmprunts AS nbBorrowings,
            l.nbPages,
            l.edition,
            
    		tl.nomTypeLivre AS type,
    		
    		la.nomLangue AS language,
    		
    		GROUP_CONCAT(DISTINCT g.nomGenre ORDER BY g.nomGenre SEPARATOR ',') AS genres,
    		GROUP_CONCAT(DISTINCT t.nomTheme ORDER BY t.nomTheme SEPARATOR ',') AS themes
        FROM Livres l
        LEFT JOIN TypeLivre tl ON tl.idTypeLivre = l.idTypeLivre

		LEFT JOIN Langues la ON la.idLangue = l.idLangue
		
		LEFT JOIN Livres_Genres lg ON lg.idLivre = l.idLivre
		LEFT JOIN Genres g ON g.idGenre = lg.idGenre
		
		LEFT JOIN Livres_Themes lt ON lt.idLivre = l.idLivre
		LEFT JOIN Thèmes t ON t.idTheme = lt.idTheme
        
        WHERE l.couverture IS NOT NULL AND l.couverture != ''
        GROUP BY l.idLivre
        ORDER BY RAND()
        LIMIT :nbBooks
    	";

        $stmt = $this->pdo->prepare($sqlBooks);
        $stmt->bindParam(':nbBooks', $nbBooks, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    private function minutesToTime(int $minutes): string
    {
        if ($minutes < 0) {
            return '0h';
        }

        $hours = intdiv($minutes, 60);
        $mins = $minutes % 60;

        if ($hours === 0) {
            return $mins . 'min';
        }

        if ($mins === 0) {
            return $hours . 'h';
        }

        return $hours . 'h' . str_pad($mins, 2, '0', STR_PAD_LEFT);
    }

    public function getPopularResources(int $nbResources): array
    {
        $sql = "
        SELECT * FROM (
        SELECT
            'film' AS resource_type,
            f.idFilm AS id,
            f.titre AS title,
            f.poster AS poster,
            f.nbEmprunts AS nbBorrowings,
            tf.nomTypeFilm AS type,
            AVG(a.note) AS rating
        FROM Films f
            LEFT JOIN TypeFilm tf ON tf.idTypeFilm = f.idTypeFilm
            LEFT JOIN Avis a ON a.idFilm = f.idFilm
        
        WHERE f.poster IS NOT NULL
        AND f.poster != ''
        GROUP BY f.idFilm
        
        UNION ALL
        
        SELECT 'book' AS resource_type,
             l.idLivre AS id,
             l.titre AS title,
             l.couverture AS poster,
             l.nbEmprunts AS nbBorrowings,
             tl.nomTypeLivre AS type,
             AVG(a.note) AS rating
        FROM Livres l
            LEFT JOIN TypeLivre tl ON tl.idTypeLivre = l.idTypeLivre
            LEFT JOIN Avis a ON a.idLivre = l.idLivre
        
        WHERE l.couverture IS NOT NULL
        AND l.couverture != ''
        GROUP BY l.idLivre
        
        ORDER BY rating DESC, nbBorrowings DESC
        LIMIT :nbResources) as Resources
    ";

        $stmt = $this->pdo->prepare($sql);
        $stmt->bindValue(':nbResources', $nbResources, PDO::PARAM_INT);
        $stmt->execute();

        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function getAllBooks()
    {
        $sql = "SELECT * FROM `Livres`";
        $rows = $this->pdo->query($sql);

        /*$books = array();
        foreach ($rows as $row) {
            $books[] = new Book();
        }*/
    }

    public function getSomeFilms($offset, $limit)
    {
        $sql = "SELECT * FROM Films LIMIT :offset, :limit";
        $stmt = $this->pdo->prepare($sql);


        $stmt->bindValue(':offset', (int)$offset, PDO::PARAM_INT);
        $stmt->bindValue(':limit', (int)$limit, PDO::PARAM_INT);

        $stmt->execute();
        $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);

        $films = [];
        foreach ($rows as $row) {
            $films[] = new Film(
                $row['idFilm'],
                $row['titre'],
                [],
                [],
                $row['poster'],
                null,
                $row['synopsis'],
                $row['anneeProduction'],
                $row['dateSortie'],
                $row['bandeAnnonce'],
                $row['duree'],
                $row['idTypeFilm'],
                [],
                [],
                [],
                null
            );
        }
        return $films;
    }


    public function getFilms()
    {
        $sql = "SELECT * FROM Films";
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute();
        $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);

        $films = [];
        foreach ($rows as $row) {
            $films[] = new Film(
                $row['idFilm'],
                $row['titre'],
                [],
                [],
                $row['poster'],
                null,
                $row['synopsis'],
                $row['anneeProduction'],
                $row['dateSortie'],
                $row['bandeAnnonce'],
                $row['duree'],
                $row['idTypeFilm'],
                [],
                [],
                [],
                null
            );
        }
        return $films;
    }

    /**
     * Récupère un film par son ID avec toutes ses données
     * @param int $idFilm
     * @return Film|null Retourne un objet Film ou null si non trouvé
     */
    public function getFilmById($idFilm)
    {
        $sql = "SELECT * FROM Films WHERE idFilm = :id";
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute(['id' => $idFilm]);
        $row = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$row) {
            return null;
        }

        // Créer un objet Film avec les données de la base
        return new Film(
            $row['idFilm'],
            $row['titre'],
            [], // genres - à compléter si nécessaire
            [], // themes - à compléter si nécessaire
            $row['poster'] ?? null,
            $row['dateSortie'] ?? null,
            $row['synopsis'] ?? null,
            $row['anneeProduction'] ?? null,
            $row['dateSortie'] ?? null,
            $row['bandeAnnonce'] ?? null,
            $row['duree'] ?? null,
            $row['idTypeFilm'] ?? null,
            [], // languages - à compléter si nécessaire
            [], // productionCountries - à compléter si nécessaire
            [], // plotCountries - à compléter si nécessaire
            null // proposedBy
        );
    }

    /**
     * Récupère un livre par son ID avec toutes ses données
     * @param int $idLivre
     * @return Book|null Retourne un objet Book ou null si non trouvé
     */
    public function getBookById($idLivre)
    {
        $sql = "
            SELECT 
                l.*,
                lang.nomLangue AS langue,
                e.nomEditeur AS editeur
            FROM Livres l
            LEFT JOIN Langues lang ON l.idLangue = lang.idLangue
            LEFT JOIN Editeurs e ON l.idEditeur = e.idEditeur
            WHERE l.idLivre = :id
        ";
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute(['id' => $idLivre]);
        $row = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$row) {
            return null;
        }

        // Créer un objet Book avec les données de la base
        return new Book(
            $row['idLivre'],
            $row['titre'],
            [], // genres - à compléter si nécessaire
            [], // themes - à compléter si nécessaire
            $row['couverture'] ?? null,
            $row['date_ajout'] ?? null,
            $row['description'] ?? null,
            $row['isbn'] ?? null,
            $row['editeur'] ?? null, // nomEditeur récupéré via la jointure
            $row['anneePublication'] ?? null,
            $row['prix'] ?? null,
            $row['nbPages'] ?? null,
            $row['edition'] ?? null,
            null, // type - correspond à idTypeLivre, à récupérer via jointure si nécessaire
            $row['langue'] ?? null, // nomLangue récupéré via la jointure
            [] // authors - à compléter si nécessaire
        );
    }

    /**
     * Recherche des ressources (livres et films) avec filtres
     * @param string $query Terme de recherche
     * @param array $filters Filtres de recherche (type, language, year_min, year_max, author)
     * @return array Tableau de ressources trouvées
     */
    public function searchResources($query, $filters = [])
    {
        $results = [];
        $searchTerm = '%' . $query . '%';

        // Recherche dans les livres
        if (empty($filters['type']) || $filters['type'] === 'book') {
            $sqlBooks = "
                SELECT DISTINCT
                    l.idLivre AS id,
                    l.titre AS title,
                    l.couverture AS poster,
                    l.anneePublication AS publishYear,
                    lang.nomLangue AS language,
                    GROUP_CONCAT(DISTINCT a.nom SEPARATOR ', ') AS authors
                FROM Livres l
                LEFT JOIN Langues lang ON l.idLangue = lang.idLangue
                LEFT JOIN Livres_Auteurs al ON l.idLivre = al.idLivre
                LEFT JOIN Personnes a ON al.idPersonne = a.idPersonne
                WHERE 1=1
            ";

            $params = [];

            // Filtre par titre ou auteur (insensible à la casse)
            // Vérifier que l'auteur n'est pas NULL avant de comparer
            if (!empty($query)) {
                $sqlBooks .= " AND (LOWER(l.titre) LIKE LOWER(:query) OR (a.nomAuteur IS NOT NULL AND LOWER(a.nomAuteur) LIKE LOWER(:query)))";
                $params[':query'] = $searchTerm;
            }

            // Filtre par langue
            if (!empty($filters['language'])) {
                $sqlBooks .= " AND lang.nomLangue = :language";
                $params[':language'] = $filters['language'];
            }

            // Filtre par année
            if (!empty($filters['year_min'])) {
                $sqlBooks .= " AND l.anneePublication >= :year_min";
                $params[':year_min'] = $filters['year_min'];
            }
            if (!empty($filters['year_max'])) {
                $sqlBooks .= " AND l.anneePublication <= :year_max";
                $params[':year_max'] = $filters['year_max'];
            }

            // Filtre par auteur spécifique (insensible à la casse)
            if (!empty($filters['author'])) {
                $sqlBooks .= " AND LOWER(a.nomAuteur) LIKE LOWER(:author)";
                $params[':author'] = '%' . $filters['author'] . '%';
            }

            $sqlBooks .= " GROUP BY l.idLivre, l.titre, l.couverture, l.anneePublication, lang.nomLangue";
            $sqlBooks .= " ORDER BY l.titre LIMIT 50";

            $stmt = $this->pdo->prepare($sqlBooks);
            foreach ($params as $key => $value) {
                $stmt->bindValue($key, $value);
            }
            $stmt->execute();
            $books = $stmt->fetchAll(PDO::FETCH_ASSOC);

            foreach ($books as $book) {
                $results[] = [
                    'id' => $book['id'],
                    'type' => 'book',
                    'title' => $book['title'],
                    'poster' => $book['poster'],
                    'publishYear' => $book['publishYear'],
                    'language' => $book['language'],
                    'authors' => !empty($book['authors']) ? explode(', ', $book['authors']) : []
                ];
            }
        }

        // Recherche dans les films
        if (empty($filters['type']) || $filters['type'] === 'film') {
            $sqlFilms = "
                SELECT DISTINCT
                    f.idFilm AS id,
                    f.titre AS title,
                    f.poster,
                    f.anneeProduction AS productionYear,
                    f.dateSortie AS releaseDate,
                    f.duree AS duration
                FROM Films f
                LEFT JOIN Films_Langues lf ON f.idFilm = lf.idFilm
                LEFT JOIN Langues lang ON lf.idLangue = lang.idLangue
                WHERE 1=1
            ";

            $params = [];

            // Filtre par titre (insensible à la casse)
            if (!empty($query)) {
                $sqlFilms .= " AND LOWER(f.titre) LIKE LOWER(:query)";
                $params[':query'] = $searchTerm;
            }

            // Filtre par langue
            if (!empty($filters['language'])) {
                $sqlFilms .= " AND lang.nomLangue = :language";
                $params[':language'] = $filters['language'];
            }

            // Filtre par année (production)
            if (!empty($filters['year_min'])) {
                $sqlFilms .= " AND f.anneeProduction >= :year_min";
                $params[':year_min'] = $filters['year_min'];
            }
            if (!empty($filters['year_max'])) {
                $sqlFilms .= " AND f.anneeProduction <= :year_max";
                $params[':year_max'] = $filters['year_max'];
            }

            $sqlFilms .= " GROUP BY f.idFilm, f.titre, f.poster, f.anneeProduction, f.dateSortie, f.duree";
            $sqlFilms .= " ORDER BY f.titre LIMIT 50";

            $stmt = $this->pdo->prepare($sqlFilms);
            foreach ($params as $key => $value) {
                $stmt->bindValue($key, $value);
            }
            $stmt->execute();
            $films = $stmt->fetchAll(PDO::FETCH_ASSOC);

            foreach ($films as $film) {
                $results[] = [
                    'id' => $film['id'],
                    'type' => 'film',
                    'title' => $film['title'],
                    'poster' => $film['poster'],
                    'productionYear' => $film['productionYear'],
                    'releaseDate' => $film['releaseDate'],
                    'duration' => $film['duration']
                ];
            }
        }

        return $results;
    }

    /**
     * Recherche autocomplete pour suggestions en temps réel
     * @param string $query Terme de recherche
     * @param int $limit Nombre maximum de résultats
     * @return array Tableau de suggestions
     */
    public function autocompleteSearch($query, $limit = 10)
    {
        $results = [];
        $searchTerm = '%' . $query . '%';
        // Augmenter la limite pour les livres pour s'assurer qu'on en trouve
        $bookLimit = (int)ceil($limit / 2);
        $filmLimit = (int)floor($limit / 2);

        // Recherche dans les livres (titre et auteurs)
        // Séparer la recherche par titre et par auteur pour éviter les problèmes de GROUP BY
        // D'abord chercher par titre uniquement - requête simple sans jointure
        $sqlBooks = "
            SELECT 
                l.idLivre AS id,
                l.titre AS title,
                l.couverture AS poster
            FROM Livres l
            WHERE LOWER(l.titre) LIKE LOWER(:query)
            ORDER BY l.titre
            LIMIT :limit
        ";

        try {
            $stmt = $this->pdo->prepare($sqlBooks);
            $stmt->bindValue(':query', $searchTerm, PDO::PARAM_STR);
            $stmt->bindValue(':limit', $bookLimit, PDO::PARAM_INT);
            $stmt->execute();
            $booksByTitle = $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            error_log('Erreur SQL dans autocompleteSearch (livres par titre): ' . $e->getMessage());
            error_log('Requête: ' . $sqlBooks);
            error_log('Paramètres: query=' . $searchTerm . ', limit=' . $bookLimit);
            $booksByTitle = [];
        }

        // Ensuite chercher par auteur (si on n'a pas encore assez de résultats)
        $booksByAuthor = [];
        if (count($booksByTitle) < $bookLimit) {
            $remainingLimit = $bookLimit - count($booksByTitle);
            $bookIds = array_column($booksByTitle, 'id');

            if (!empty($bookIds)) {
                $placeholders = implode(',', array_fill(0, count($bookIds), '?'));
                $sqlBooksAuthor = "
                    SELECT DISTINCT
                        l.idLivre AS id,
                        l.titre AS title,
                        'book' AS type,
                        l.couverture AS poster
                    FROM Livres l
                    INNER JOIN AuteursLivres al ON l.idLivre = al.idLivre
                    INNER JOIN Auteurs a ON al.idAuteur = a.idAuteur
                    WHERE LOWER(a.nomAuteur) LIKE LOWER(?)
                      AND l.idLivre NOT IN ($placeholders)
                    ORDER BY l.titre
                    LIMIT ?
                ";

                $params = array_merge([$searchTerm], $bookIds, [$remainingLimit]);
            } else {
                $sqlBooksAuthor = "
                    SELECT DISTINCT
                        l.idLivre AS id,
                        l.titre AS title,
                        'book' AS type,
                        l.couverture AS poster
                    FROM Livres l
                    INNER JOIN Livres_Auteurs al ON l.idLivre = al.idLivre
                    INNER JOIN Personnes a ON al.idPersonne = a.idPersonne
                    WHERE LOWER(a.nom) LIKE LOWER(?)
                    ORDER BY l.titre
                    LIMIT ?
                ";

                $params = [$searchTerm, $remainingLimit];
            }

            try {
                $stmt = $this->pdo->prepare($sqlBooksAuthor);
                foreach ($params as $index => $value) {
                    $stmt->bindValue($index + 1, $value, is_int($value) ? PDO::PARAM_INT : PDO::PARAM_STR);
                }
                $stmt->execute();
                $booksByAuthor = $stmt->fetchAll(PDO::FETCH_ASSOC);
            } catch (PDOException $e) {
                error_log('Erreur SQL dans autocompleteSearch (livres par auteur): ' . $e->getMessage());
                $booksByAuthor = [];
            }
        }

        // Fusionner les résultats
        $books = array_merge($booksByTitle, $booksByAuthor);

        // Récupérer les auteurs pour chaque livre et construire les résultats
        foreach ($books as &$book) {
            // Ajouter le type
            $book['type'] = 'book';

            // Récupérer les auteurs
            $sqlAuthors = "
                SELECT a.nom
                FROM Livres_Auteurs al
                INNER JOIN Personnes a ON al.idPersonne = a.idPersonne
                WHERE al.idLivre = :idLivre
            ";
            try {
                $stmt = $this->pdo->prepare($sqlAuthors);
                $stmt->execute(['idLivre' => $book['id']]);
                $authors = $stmt->fetchAll(PDO::FETCH_COLUMN);
                $book['authors'] = $authors;
            } catch (PDOException $e) {
                $book['authors'] = [];
            }

            // Ajouter au tableau de résultats
            $results[] = [
                'id' => $book['id'],
                'type' => 'book',
                'title' => $book['title'],
                'poster' => $book['poster'],
                'authors' => $book['authors'],
                'url' => APP_INDEX_URL . '?book=' . $book['id']
            ];
        }
        unset($book);

        // Recherche dans les films (titre)
        // Utiliser LOWER() pour rendre la recherche insensible à la casse
        $sqlFilms = "
            SELECT DISTINCT
                f.idFilm AS id,
                f.titre AS title,
                'film' AS type,
                f.poster
            FROM Films f
            WHERE LOWER(f.titre) LIKE LOWER(:query)
            ORDER BY f.titre
            LIMIT :limit
        ";

        try {
            $stmt = $this->pdo->prepare($sqlFilms);
            $stmt->bindValue(':query', $searchTerm);
            $stmt->bindValue(':limit', $filmLimit, PDO::PARAM_INT);
            $stmt->execute();
            $films = $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            // En cas d'erreur SQL, retourner un tableau vide
            error_log('Erreur SQL dans autocompleteSearch (films): ' . $e->getMessage());
            $films = [];
        }

        foreach ($films as $film) {
            $results[] = [
                'id' => $film['id'],
                'type' => 'film',
                'title' => $film['title'],
                'poster' => $film['poster'],
                'url' => APP_INDEX_URL . '?film=' . $film['id']
            ];
        }

        // Limiter le nombre total de résultats
        return array_slice($results, 0, $limit);
    }

    /**
     * Récupère la liste des langues disponibles
     * @return array Tableau des langues
     */
    public function getAvailableLanguages()
    {
        $sql = "SELECT DISTINCT nomLangue FROM Langues ORDER BY nomLangue";
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute();
        $languages = $stmt->fetchAll(PDO::FETCH_COLUMN);
        return $languages;
    }

    /**
     * Récupère les dernières ressources ajoutées (livres et films)
     * @param int $limit Nombre maximum de ressources à retourner
     * @param int $offset Offset pour la pagination
     * @param string|null $type Type de ressource ('book', 'film' ou null pour les deux)
     * @param string|null $theme Nom du thème pour filtrer (optionnel)
     * @return array Tableau de ressources avec leurs informations
     */
    public function getLatest($limit = 20, $offset = 0, $type = null, $theme = null)
    {
        $results = [];
        $limit = (int)$limit;
        $offset = (int)$offset;

        // Récupérer les livres
        if ($type === null || $type === 'book') {
            $sqlBooks = "
                SELECT DISTINCT
                    l.idLivre AS id,
                    l.titre AS title,
                    l.couverture AS poster,
                    l.date_ajout AS dateAdded,
                    l.anneePublication AS publishYear,
                    lang.nomLangue AS language,
                    GROUP_CONCAT(DISTINCT CONCAT(p.nom, ' ', p.prenom) SEPARATOR ', ') AS authors
                FROM Livres l
                LEFT JOIN Langues lang ON l.idLangue = lang.idLangue
                LEFT JOIN Livres_Auteurs la ON l.idLivre = la.idLivre
                LEFT JOIN Personnes p ON la.idPersonne = p.idPersonne
                WHERE 1=1
            ";

            $params = [];

            // Filtre par thème si spécifié
            if (!empty($theme)) {
                // Essayer d'abord avec les thèmes, sinon avec les genres
                $sqlBooks .= "
                    AND (
                        EXISTS (
                            SELECT 1 FROM Livres_Themes lt
                            INNER JOIN `Thèmes` t ON lt.idTheme = t.idTheme
                            WHERE lt.idLivre = l.idLivre
                            AND t.nomTheme = :theme
                        )
                        OR EXISTS (
                            SELECT 1 FROM Livres_Genres lg
                            INNER JOIN Genres g ON lg.idGenre = g.idGenre
                            WHERE lg.idLivre = l.idLivre
                            AND g.nomGenre = :theme
                        )
                    )
                ";
                $params[':theme'] = $theme;
            }

            $sqlBooks .= " GROUP BY l.idLivre, l.titre, l.couverture, l.date_ajout, l.anneePublication, lang.nomLangue";
            // Trier par date_ajout si disponible, sinon par idLivre (les plus récents en dernier)
            $sqlBooks .= " ORDER BY CASE WHEN l.date_ajout IS NOT NULL THEN l.date_ajout ELSE '1970-01-01' END DESC, l.idLivre DESC";
            // Récupérer assez de résultats pour couvrir la pagination (offset + limit)
            // On récupère plus que nécessaire pour pouvoir trier après fusion avec les films
            $sqlBooks .= " LIMIT :limitBooks";

            try {
                $stmt = $this->pdo->prepare($sqlBooks);
                foreach ($params as $key => $value) {
                    $stmt->bindValue($key, $value);
                }
                // Pour la pagination, on récupère assez de résultats pour couvrir offset + limit
                // On récupère au moins offset + limit de chaque type pour être sûr d'avoir assez après fusion
                // On ajoute un buffer pour gérer les cas où un type domine l'autre
                $fetchLimit = max($offset + $limit + 100, 200);
                $stmt->bindValue(':limitBooks', $fetchLimit, PDO::PARAM_INT);
                $stmt->execute();
                $books = $stmt->fetchAll(PDO::FETCH_ASSOC);

                foreach ($books as $book) {
                    $results[] = [
                        'id' => $book['id'],
                        'type' => 'book',
                        'title' => $book['title'],
                        'poster' => $book['poster'],
                        'dateAdded' => $book['dateAdded'],
                        'publishYear' => $book['publishYear'],
                        'language' => $book['language'],
                        'authors' => !empty($book['authors']) ? explode(', ', $book['authors']) : []
                    ];
                }
            } catch (PDOException $e) {
                error_log('Erreur SQL dans getLatest (livres): ' . $e->getMessage());
            }
        }

        // Récupérer les films
        if ($type === null || $type === 'film') {
            // Pour les films, on utilise dateSortie comme date d'ajout si date_ajout n'existe pas
            $sqlFilms = "
                SELECT DISTINCT
                    f.idFilm AS id,
                    f.titre AS title,
                    f.poster,
                    COALESCE(f.date_ajout, f.dateSortie) AS dateAdded,
                    f.anneeProduction AS productionYear,
                    f.dateSortie AS releaseDate,
                    f.duree AS duration
                FROM Films f
                WHERE 1=1
            ";

            $params = [];

            // Filtre par thème si spécifié
            if (!empty($theme)) {
                // Essayer d'abord avec les thèmes, sinon avec les genres
                $sqlFilms .= "
                    AND (
                        EXISTS (
                            SELECT 1 FROM Films_Themes ft
                            INNER JOIN `Thèmes` t ON ft.idTheme = t.idTheme
                            WHERE ft.idFilm = f.idFilm
                            AND t.nomTheme = :theme
                        )
                        OR EXISTS (
                            SELECT 1 FROM Films_Genres fg
                            INNER JOIN Genres g ON fg.idGenre = g.idGenre
                            WHERE fg.idFilm = f.idFilm
                            AND g.nomGenre = :theme
                        )
                    )
                ";
                $params[':theme'] = $theme;
            }

            // Trier par dateAdded si disponible, sinon par idFilm (les plus récents en dernier)
            $sqlFilms .= " ORDER BY CASE WHEN COALESCE(f.date_ajout, f.dateSortie) IS NOT NULL THEN COALESCE(f.date_ajout, f.dateSortie) ELSE '1970-01-01' END DESC, f.idFilm DESC";
            // Récupérer assez de résultats pour couvrir la pagination (offset + limit)
            // On récupère plus que nécessaire pour pouvoir trier après fusion avec les livres
            $sqlFilms .= " LIMIT :limitFilms";

            try {
                $stmt = $this->pdo->prepare($sqlFilms);
                foreach ($params as $key => $value) {
                    $stmt->bindValue($key, $value);
                }
                // Pour la pagination, on récupère assez de résultats pour couvrir offset + limit
                // On récupère au moins offset + limit de chaque type pour être sûr d'avoir assez après fusion
                // On ajoute un buffer pour gérer les cas où un type domine l'autre
                $fetchLimit = max($offset + $limit + 100, 200);
                $stmt->bindValue(':limitFilms', $fetchLimit, PDO::PARAM_INT);
                $stmt->execute();
                $films = $stmt->fetchAll(PDO::FETCH_ASSOC);

                foreach ($films as $film) {
                    $results[] = [
                        'id' => $film['id'],
                        'type' => 'film',
                        'title' => $film['title'],
                        'poster' => $film['poster'],
                        'dateAdded' => $film['dateAdded'],
                        'productionYear' => $film['productionYear'],
                        'releaseDate' => $film['releaseDate'],
                        'duration' => $film['duration']
                    ];
                }
            } catch (PDOException $e) {
                error_log('Erreur SQL dans getLatest (films): ' . $e->getMessage());
            }
        }

        // Trier par date d'ajout décroissante (les plus récents en premier)
        // Gérer les dates NULL et les formats de date différents
        usort($results, function ($a, $b) {
            $dateA = $a['dateAdded'] ?? null;
            $dateB = $b['dateAdded'] ?? null;

            // Si les deux dates sont NULL, trier par ID décroissant
            if ($dateA === null && $dateB === null) {
                return ($b['id'] ?? 0) - ($a['id'] ?? 0);
            }

            // Si une seule date est NULL, mettre celle avec date en premier
            if ($dateA === null) return 1;
            if ($dateB === null) return -1;

            // Convertir en timestamp pour comparer correctement
            $timestampA = is_string($dateA) ? strtotime($dateA) : (is_numeric($dateA) ? $dateA : 0);
            $timestampB = is_string($dateB) ? strtotime($dateB) : (is_numeric($dateB) ? $dateB : 0);

            // Si les timestamps sont égaux, trier par ID
            if ($timestampA === $timestampB) {
                return ($b['id'] ?? 0) - ($a['id'] ?? 0);
            }

            // Ordre décroissant (plus récent en premier)
            return $timestampB - $timestampA;
        });

        // Appliquer la pagination après le tri
        return array_slice($results, $offset, $limit);
    }

    /**
     * Compte le nombre total de ressources avec les filtres appliqués
     * @param string|null $type Type de ressource ('book', 'film' ou null pour les deux)
     * @param string|null $theme Nom du thème pour filtrer (optionnel)
     * @return int Nombre total de ressources
     */
    public function countLatest($type = null, $theme = null)
    {
        $totalCount = 0;

        // Compter les livres
        if ($type === null || $type === 'book') {
            $sqlBooks = "
                SELECT COUNT(DISTINCT l.idLivre) AS count
                FROM Livres l
                WHERE 1=1
            ";

            $params = [];

            // Filtre par thème si spécifié
            if (!empty($theme)) {
                $sqlBooks .= "
                    AND (
                        EXISTS (
                            SELECT 1 FROM Livres_Themes lt
                            INNER JOIN `Thèmes` t ON lt.idTheme = t.idTheme
                            WHERE lt.idLivre = l.idLivre
                            AND t.nomTheme = :theme
                        )
                        OR EXISTS (
                            SELECT 1 FROM Livres_Genres lg
                            INNER JOIN Genres g ON lg.idGenre = g.idGenre
                            WHERE lg.idLivre = l.idLivre
                            AND g.nomGenre = :theme
                        )
                    )
                ";
                $params[':theme'] = $theme;
            }

            try {
                $stmt = $this->pdo->prepare($sqlBooks);
                foreach ($params as $key => $value) {
                    $stmt->bindValue($key, $value);
                }
                $stmt->execute();
                $result = $stmt->fetch(PDO::FETCH_ASSOC);
                $totalCount += (int)$result['count'];
            } catch (PDOException $e) {
                error_log('Erreur SQL dans countLatest (livres): ' . $e->getMessage());
            }
        }

        // Compter les films
        if ($type === null || $type === 'film') {
            $sqlFilms = "
                SELECT COUNT(DISTINCT f.idFilm) AS count
                FROM Films f
                WHERE 1=1
            ";

            $params = [];

            // Filtre par thème si spécifié
            if (!empty($theme)) {
                $sqlFilms .= "
                    AND (
                        EXISTS (
                            SELECT 1 FROM Films_Themes ft
                            INNER JOIN `Thèmes` t ON ft.idTheme = t.idTheme
                            WHERE ft.idFilm = f.idFilm
                            AND t.nomTheme = :theme
                        )
                        OR EXISTS (
                            SELECT 1 FROM Films_Genres fg
                            INNER JOIN Genres g ON fg.idGenre = g.idGenre
                            WHERE fg.idFilm = f.idFilm
                            AND g.nomGenre = :theme
                        )
                    )
                ";
                $params[':theme'] = $theme;
            }

            try {
                $stmt = $this->pdo->prepare($sqlFilms);
                foreach ($params as $key => $value) {
                    $stmt->bindValue($key, $value);
                }
                $stmt->execute();
                $result = $stmt->fetch(PDO::FETCH_ASSOC);
                $totalCount += (int)$result['count'];
            } catch (PDOException $e) {
                error_log('Erreur SQL dans countLatest (films): ' . $e->getMessage());
            }
        }

        return $totalCount;
    }

    /**
     * Récupère les ressources les plus récentes par thème
     * @param string $theme Nom du thème
     * @param int $limit Nombre maximum de ressources à retourner
     * @param int $offset Offset pour la pagination
     * @param string|null $type Type de ressource ('book', 'film' ou null pour les deux)
     * @return array Tableau de ressources
     */
    public function getLatestByTheme($theme, $limit = 20, $offset = 0, $type = null)
    {
        return $this->getLatest($limit, $offset, $type, $theme);
    }

    /**
     * Récupère la liste des thèmes disponibles
     * Essaie d'abord avec les tables Themes, sinon essaie avec les genres
     * @return array Tableau des thèmes avec leur nom
     */
    public function getAvailableThemes()
    {
        $themes = [];

        // Essayer de récupérer les thèmes des livres
        try {
            $sqlBooks = "
                SELECT DISTINCT t.idTheme, t.nomTheme
                FROM `Thèmes` t
                INNER JOIN Livres_Themes lt ON t.idTheme = lt.idTheme
                ORDER BY t.nomTheme
            ";
            $stmt = $this->pdo->prepare($sqlBooks);
            $stmt->execute();
            $bookThemes = $stmt->fetchAll(PDO::FETCH_ASSOC);
            foreach ($bookThemes as $theme) {
                $themes[$theme['nomTheme']] = $theme['nomTheme'];
            }
        } catch (PDOException $e) {
            // Si les tables Thèmes n'existent pas, essayer avec les genres
            error_log('Tables Thèmes non trouvées, essai avec Genres: ' . $e->getMessage());

            try {
                $sqlGenres = "
                    SELECT DISTINCT g.nomGenre
                    FROM Genres g
                    INNER JOIN Livres_Genres lg ON g.idGenre = lg.idGenre
                    ORDER BY g.nomGenre
                ";
                $stmt = $this->pdo->prepare($sqlGenres);
                $stmt->execute();
                $bookGenres = $stmt->fetchAll(PDO::FETCH_COLUMN);
                foreach ($bookGenres as $genre) {
                    $themes[$genre] = $genre;
                }
            } catch (PDOException $e2) {
                error_log('Erreur SQL dans getAvailableThemes (genres livres): ' . $e2->getMessage());
            }
        }

        // Essayer de récupérer les thèmes des films
        try {
            $sqlFilms = "
                SELECT DISTINCT t.idTheme, t.nomTheme
                FROM `Thèmes` t
                INNER JOIN Films_Themes ft ON t.idTheme = ft.idTheme
                ORDER BY t.nomTheme
            ";
            $stmt = $this->pdo->prepare($sqlFilms);
            $stmt->execute();
            $filmThemes = $stmt->fetchAll(PDO::FETCH_ASSOC);
            foreach ($filmThemes as $theme) {
                $themes[$theme['nomTheme']] = $theme['nomTheme'];
            }
        } catch (PDOException $e) {
            // Si les tables Thèmes n'existent pas, essayer avec les genres
            try {
                $sqlGenres = "
                    SELECT DISTINCT g.nomGenre
                    FROM Genres g
                    INNER JOIN Films_Genres fg ON g.idGenre = fg.idGenre
                    ORDER BY g.nomGenre
                ";
                $stmt = $this->pdo->prepare($sqlGenres);
                $stmt->execute();
                $filmGenres = $stmt->fetchAll(PDO::FETCH_COLUMN);
                foreach ($filmGenres as $genre) {
                    $themes[$genre] = $genre;
                }
            } catch (PDOException $e2) {
                error_log('Erreur SQL dans getAvailableThemes (genres films): ' . $e2->getMessage());
            }
        }

        // Trier et retourner les thèmes/genres uniques
        sort($themes);
        return array_values($themes);
    }

    /**
     * Récupère le TOP des livres
     */
    public function getTopBooks($limit = 20, $offset = 0, $sort = 'rating')
    {
        // Par défaut : tri par note
        $orderBy = "rating DESC, comment_count DESC";

        // Si tri par commentaires demandé
        if ($sort === 'comments') {
            $orderBy = "comment_count DESC, rating DESC";
        }

        $sql = "
            SELECT 
                l.idLivre AS id,
                l.titre AS title,
                l.couverture AS poster,
                COALESCE((SELECT AVG(note) FROM Avis WHERE idLivre = l.idLivre), 0) as rating,
                (SELECT COUNT(*) FROM Avis WHERE idLivre = l.idLivre) as comment_count
            FROM Livres l
            WHERE l.couverture IS NOT NULL AND l.couverture != ''
            ORDER BY $orderBy
            LIMIT :limit OFFSET :offset
        ";

        $stmt = $this->pdo->prepare($sql);
        $stmt->bindValue(':limit', (int)$limit, PDO::PARAM_INT);
        $stmt->bindValue(':offset', (int)$offset, PDO::PARAM_INT);
        $stmt->execute();

        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    // Assurez-vous d'avoir aussi cette fonction à la fin
    public function countTopBooks()
    {
        return (int)$this->pdo->query("SELECT COUNT(*) FROM Livres WHERE couverture IS NOT NULL AND couverture != ''")->fetchColumn();
    }
}
