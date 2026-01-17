<?php

/**
 * Modèle pour gérer les opérations CRUD sur les avis
 * Permet de créer, lire, modifier et supprimer des avis sur les ressources (livres et films)
 */
class AvisModel
{
    private $pdo;

    public function __construct()
    {
        global $pdo;
        require_once __DIR__ . "/../sqlconnect.php";
        $this->pdo = $pdo;
    }

    /**
     * Récupère tous les avis d'une ressource (livre ou film)
     * @param int|null $idLivre ID du livre (null si c'est un film)
     * @param int|null $idFilm ID du film (null si c'est un livre)
     * @return array Tableau d'avis avec les informations de l'utilisateur
     */
    public function getAvisByResource(?int $idLivre = null, ?int $idFilm = null): array
    {
        if ($idLivre === null && $idFilm === null) {
            return [];
        }

        $sql = "
            SELECT 
                a.idAvis,
                a.idUtilisateur,
                a.objet,
                a.text,
                a.note,
                a.timestamp,
                a.idFilm,
                a.idLivre,
                u.username AS pseudo
            FROM Avis a
            INNER JOIN Utilisateurs u ON a.idUtilisateur = u.idUtilisateur
            WHERE ";

        $params = [];
        if ($idLivre !== null) {
            $sql .= "a.idLivre = :idLivre AND a.idFilm IS NULL";
            $params[':idLivre'] = $idLivre;
        } else {
            $sql .= "a.idFilm = :idFilm AND a.idLivre IS NULL";
            $params[':idFilm'] = $idFilm;
        }

        $sql .= " ORDER BY a.timestamp DESC";

        $stmt = $this->pdo->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Récupère l'avis d'un utilisateur pour une ressource spécifique
     * @param int $idUtilisateur ID de l'utilisateur
     * @param int|null $idLivre ID du livre (null si c'est un film)
     * @param int|null $idFilm ID du film (null si c'est un livre)
     * @return array|null L'avis ou null si aucun avis trouvé
     */
    public function getAvisByUserAndResource(int $idUtilisateur, ?int $idLivre = null, ?int $idFilm = null): ?array
    {
        if ($idLivre === null && $idFilm === null) {
            return null;
        }

        $sql = "
            SELECT 
                a.idAvis,
                a.idUtilisateur,
                a.objet,
                a.text,
                a.note,
                a.timestamp,
                a.idFilm,
                a.idLivre
            FROM Avis a
            WHERE a.idUtilisateur = :idUtilisateur ";

        $params = [':idUtilisateur' => $idUtilisateur];

        if ($idLivre !== null) {
            $sql .= "AND a.idLivre = :idLivre AND a.idFilm IS NULL";
            $params[':idLivre'] = $idLivre;
        } else {
            $sql .= "AND a.idFilm = :idFilm AND a.idLivre IS NULL";
            $params[':idFilm'] = $idFilm;
        }

        $stmt = $this->pdo->prepare($sql);
        $stmt->execute($params);
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        return $result ?: null;
    }

    /**
     * Crée un nouvel avis
     * @param int $idUtilisateur ID de l'utilisateur
     * @param int $note Note entre 1 et 5
     * @param string $text Texte de l'avis
     * @param string|null $objet Objet/titre de l'avis (optionnel)
     * @param int|null $idLivre ID du livre (null si c'est un film)
     * @param int|null $idFilm ID du film (null si c'est un livre)
     * @return int ID de l'avis créé
     * @throws Exception Si l'utilisateur a déjà un avis pour cette ressource
     */
    public function createAvis(
        int $idUtilisateur,
        int $note,
        string $text,
        ?string $objet = null,
        ?int $idLivre = null,
        ?int $idFilm = null
    ): int {
        // Vérifier qu'un avis n'existe pas déjà
        $existingAvis = $this->getAvisByUserAndResource($idUtilisateur, $idLivre, $idFilm);
        if ($existingAvis !== null) {
            throw new Exception("Vous avez déjà laissé un avis sur cette ressource.");
        }

        // Valider la note
        if ($note < 1 || $note > 5) {
            throw new InvalidArgumentException("La note doit être entre 1 et 5.");
        }

        // Valider qu'exactement un des deux IDs est défini
        if (($idLivre === null && $idFilm === null) || ($idLivre !== null && $idFilm !== null)) {
            throw new InvalidArgumentException("Un avis doit être associé à un livre OU un film, pas les deux.");
        }

        $sql = "
            INSERT INTO Avis (idUtilisateur, objet, text, note, idFilm, idLivre)
            VALUES (:idUtilisateur, :objet, :text, :note, :idFilm, :idLivre)
        ";

        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([
            ':idUtilisateur' => $idUtilisateur,
            ':objet' => $objet,
            ':text' => $text,
            ':note' => $note,
            ':idFilm' => $idFilm,
            ':idLivre' => $idLivre
        ]);

        return (int)$this->pdo->lastInsertId();
    }

    /**
     * Met à jour un avis existant
     * @param int $idAvis ID de l'avis à modifier
     * @param int $idUtilisateur ID de l'utilisateur (pour vérifier les permissions)
     * @param int $note Nouvelle note entre 1 et 5
     * @param string $text Nouveau texte de l'avis
     * @param string|null $objet Nouvel objet/titre de l'avis (optionnel)
     * @return bool True si la mise à jour a réussi
     * @throws Exception Si l'avis n'existe pas ou n'appartient pas à l'utilisateur
     */
    public function updateAvis(
        int $idAvis,
        int $idUtilisateur,
        int $note,
        string $text,
        ?string $objet = null
    ): bool {
        // Vérifier que l'avis existe et appartient à l'utilisateur
        $sqlCheck = "SELECT idUtilisateur FROM Avis WHERE idAvis = :idAvis";
        $stmtCheck = $this->pdo->prepare($sqlCheck);
        $stmtCheck->execute([':idAvis' => $idAvis]);
        $avis = $stmtCheck->fetch(PDO::FETCH_ASSOC);

        if (!$avis) {
            throw new Exception("Avis non trouvé.");
        }

        if ((int)$avis['idUtilisateur'] !== $idUtilisateur) {
            throw new Exception("Vous n'êtes pas autorisé à modifier cet avis.");
        }

        // Valider la note
        if ($note < 1 || $note > 5) {
            throw new InvalidArgumentException("La note doit être entre 1 et 5.");
        }

        $sql = "
            UPDATE Avis 
            SET objet = :objet, text = :text, note = :note
            WHERE idAvis = :idAvis AND idUtilisateur = :idUtilisateur
        ";

        $stmt = $this->pdo->prepare($sql);
        return $stmt->execute([
            ':idAvis' => $idAvis,
            ':idUtilisateur' => $idUtilisateur,
            ':objet' => $objet,
            ':text' => $text,
            ':note' => $note
        ]);
    }

    /**
     * Supprime un avis
     * @param int $idAvis ID de l'avis à supprimer
     * @param int $idUtilisateur ID de l'utilisateur (pour vérifier les permissions)
     * @return bool True si la suppression a réussi
     * @throws Exception Si l'avis n'existe pas ou n'appartient pas à l'utilisateur
     */
    public function deleteAvis(int $idAvis, int $idUtilisateur): bool
    {
        // Vérifier que l'avis existe et appartient à l'utilisateur
        $sqlCheck = "SELECT idUtilisateur FROM Avis WHERE idAvis = :idAvis";
        $stmtCheck = $this->pdo->prepare($sqlCheck);
        $stmtCheck->execute([':idAvis' => $idAvis]);
        $avis = $stmtCheck->fetch(PDO::FETCH_ASSOC);

        if (!$avis) {
            throw new Exception("Avis non trouvé.");
        }

        if ((int)$avis['idUtilisateur'] !== $idUtilisateur) {
            throw new Exception("Vous n'êtes pas autorisé à supprimer cet avis.");
        }

        $sql = "DELETE FROM Avis WHERE idAvis = :idAvis AND idUtilisateur = :idUtilisateur";
        $stmt = $this->pdo->prepare($sql);
        return $stmt->execute([
            ':idAvis' => $idAvis,
            ':idUtilisateur' => $idUtilisateur
        ]);
    }

    /**
     * Calcule la note moyenne d'une ressource
     * @param int|null $idLivre ID du livre (null si c'est un film)
     * @param int|null $idFilm ID du film (null si c'est un livre)
     * @return array ['average' => float, 'count' => int] Note moyenne et nombre d'avis
     */
    public function getAverageRating(?int $idLivre = null, ?int $idFilm = null): array
    {
        if ($idLivre === null && $idFilm === null) {
            return ['average' => 0.0, 'count' => 0];
        }

        $sql = "
            SELECT 
                AVG(note) AS average,
                COUNT(*) AS count
            FROM Avis
            WHERE ";

        $params = [];
        if ($idLivre !== null) {
            $sql .= "idLivre = :idLivre AND idFilm IS NULL";
            $params[':idLivre'] = $idLivre;
        } else {
            $sql .= "idFilm = :idFilm AND idLivre IS NULL";
            $params[':idFilm'] = $idFilm;
        }

        $stmt = $this->pdo->prepare($sql);
        $stmt->execute($params);
        $result = $stmt->fetch(PDO::FETCH_ASSOC);

        return [
            'average' => $result['average'] ? (float)round($result['average'], 2) : 0.0,
            'count' => (int)$result['count']
        ];
    }
}

