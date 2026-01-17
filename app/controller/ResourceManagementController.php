<?php

require_once __DIR__ . '/../model/ResourceModel.php';

class ResourceManagementController
{
	private $resourceModel;

	public function __construct()
	{
		$this->resourceModel = new ResourceModel();
	}

	private function requireResourceManagement(): void
	{
		if (empty($_SESSION['user'])) {
			header('Location: ' . APP_INDEX_URL . '?route=auth/login');
			exit;
		}

		$role = $_SESSION['user']['role'] ?? 'user';
		if (!in_array($role, ['admin', 'bibliothecaire'], true)) {
			header('Location: ' . APP_INDEX_URL . '?route=dashboard');
			exit;
		}
	}

	public function index(): void
	{
		$this->requireResourceManagement();

		// Paramètres de pagination et recherche
		$booksPage = max(1, (int)($_GET['books_page'] ?? 1));
		$filmsPage = max(1, (int)($_GET['films_page'] ?? 1));
		$perPage = max(5, min(100, (int)($_GET['per_page'] ?? 5))); // Entre 5 et 100, défaut 5
		$booksSearch = trim((string)($_GET['books_search'] ?? ''));
		$filmsSearch = trim((string)($_GET['films_search'] ?? ''));

		// Récupérer les ressources avec pagination et recherche
		try {
			$booksData = $this->resourceModel->getBooksPaginated($booksPage, $perPage, $booksSearch);
			$filmsData = $this->resourceModel->getFilmsPaginated($filmsPage, $perPage, $filmsSearch);
			$languages = $this->resourceModel->getAllLanguages();
			$editors = $this->resourceModel->getAllEditors();
			
			// Gérer le cas où TypesFilms n'existe pas
			try {
				$filmTypes = $this->resourceModel->getAllFilmTypes();
			} catch (Throwable $e) {
				$filmTypes = [];
			}
		} catch (Throwable $throwable) {
			setFlashError("Impossible de récupérer les ressources: " . $throwable->getMessage());
			$booksData = ['items' => [], 'total' => 0, 'page' => 1, 'perPage' => $perPage, 'totalPages' => 0];
			$filmsData = ['items' => [], 'total' => 0, 'page' => 1, 'perPage' => $perPage, 'totalPages' => 0];
			$languages = [];
			$editors = [];
			$filmTypes = [];
		}

		require __DIR__ . '/../view/resource-management/index.php';
	}

	public function handlePost(): void
	{
		$this->requireResourceManagement();

		$action = $_POST['action'] ?? '';

		try {
			if ($action === 'create_book') {
				$this->handleCreateBook();
				setFlashSuccess("Livre créé avec succès.");
			} elseif ($action === 'update_book') {
				$this->handleUpdateBook();
				setFlashSuccess("Livre mis à jour.");
			} elseif ($action === 'delete_book') {
				$this->handleDeleteBook();
				setFlashSuccess("Livre supprimé avec succès.");
			} elseif ($action === 'create_film') {
				$this->handleCreateFilm();
				setFlashSuccess("Film créé avec succès.");
			} elseif ($action === 'update_film') {
				$this->handleUpdateFilm();
				setFlashSuccess("Film mis à jour.");
			} elseif ($action === 'delete_film') {
				$this->handleDeleteFilm();
				setFlashSuccess("Film supprimé avec succès.");
			} else {
				throw new InvalidArgumentException("Action non reconnue.");
			}
		} catch (Throwable $throwable) {
			setFlashError($throwable->getMessage());
		}

		header('Location: ' . APP_INDEX_URL . '?route=resources');
		exit;
	}

	private function handleCreateBook(): void
	{
		$data = [
			'titre' => trim((string) ($_POST['titre'] ?? '')),
			'isbn' => trim((string) ($_POST['isbn'] ?? '')),
			'idEditeur' => !empty($_POST['idEditeur']) ? (int)$_POST['idEditeur'] : null,
			'anneePublication' => !empty($_POST['anneePublication']) ? (int)$_POST['anneePublication'] : null,
			'prix' => !empty($_POST['prix']) ? (float)$_POST['prix'] : null,
			'nbPages' => !empty($_POST['nbPages']) ? (int)$_POST['nbPages'] : null,
			'edition' => trim((string) ($_POST['edition'] ?? '')),
			'idLangue' => !empty($_POST['idLangue']) ? (int)$_POST['idLangue'] : null,
			'couverture' => trim((string) ($_POST['couverture'] ?? '')),
			'description' => trim((string) ($_POST['description'] ?? ''))
		];

		if (empty($data['titre'])) {
			throw new InvalidArgumentException("Le titre est obligatoire.");
		}

		$this->resourceModel->createBook($data);
	}

	private function handleUpdateBook(): void
	{
		$id = (int) ($_POST['book_id'] ?? 0);
		if ($id <= 0) {
			throw new InvalidArgumentException("Identifiant livre invalide.");
		}

		$data = [
			'titre' => trim((string) ($_POST['titre'] ?? '')),
			'isbn' => trim((string) ($_POST['isbn'] ?? '')),
			'idEditeur' => !empty($_POST['idEditeur']) ? (int)$_POST['idEditeur'] : null,
			'anneePublication' => !empty($_POST['anneePublication']) ? (int)$_POST['anneePublication'] : null,
			'prix' => !empty($_POST['prix']) ? (float)$_POST['prix'] : null,
			'nbPages' => !empty($_POST['nbPages']) ? (int)$_POST['nbPages'] : null,
			'edition' => trim((string) ($_POST['edition'] ?? '')),
			'idLangue' => !empty($_POST['idLangue']) ? (int)$_POST['idLangue'] : null,
			'couverture' => trim((string) ($_POST['couverture'] ?? '')),
			'description' => trim((string) ($_POST['description'] ?? ''))
		];

		if (empty($data['titre'])) {
			throw new InvalidArgumentException("Le titre est obligatoire.");
		}

		$this->resourceModel->updateBook($id, $data);
	}

	private function handleDeleteBook(): void
	{
		$id = (int) ($_POST['book_id'] ?? 0);
		if ($id <= 0) {
			throw new InvalidArgumentException("Identifiant livre invalide.");
		}

		$this->resourceModel->deleteBook($id);
	}

	private function handleCreateFilm(): void
	{
		$data = [
			'titre' => trim((string) ($_POST['titre'] ?? '')),
			'synopsis' => trim((string) ($_POST['synopsis'] ?? '')),
			'anneeProduction' => !empty($_POST['anneeProduction']) ? (int)$_POST['anneeProduction'] : null,
			'dateSortie' => trim((string) ($_POST['dateSortie'] ?? '')),
			'bandeAnnonce' => trim((string) ($_POST['bandeAnnonce'] ?? '')),
			'duree' => !empty($_POST['duree']) ? (int)$_POST['duree'] : null,
			'idTypeFilm' => !empty($_POST['idTypeFilm']) ? (int)$_POST['idTypeFilm'] : null,
			'poster' => trim((string) ($_POST['poster'] ?? ''))
		];

		if (empty($data['titre'])) {
			throw new InvalidArgumentException("Le titre est obligatoire.");
		}

		$this->resourceModel->createFilm($data);
	}

	private function handleUpdateFilm(): void
	{
		$id = (int) ($_POST['film_id'] ?? 0);
		if ($id <= 0) {
			throw new InvalidArgumentException("Identifiant film invalide.");
		}

		$data = [
			'titre' => trim((string) ($_POST['titre'] ?? '')),
			'synopsis' => trim((string) ($_POST['synopsis'] ?? '')),
			'anneeProduction' => !empty($_POST['anneeProduction']) ? (int)$_POST['anneeProduction'] : null,
			'dateSortie' => trim((string) ($_POST['dateSortie'] ?? '')),
			'bandeAnnonce' => trim((string) ($_POST['bandeAnnonce'] ?? '')),
			'duree' => !empty($_POST['duree']) ? (int)$_POST['duree'] : null,
			'idTypeFilm' => !empty($_POST['idTypeFilm']) ? (int)$_POST['idTypeFilm'] : null,
			'poster' => trim((string) ($_POST['poster'] ?? ''))
		];

		if (empty($data['titre'])) {
			throw new InvalidArgumentException("Le titre est obligatoire.");
		}

		$this->resourceModel->updateFilm($id, $data);
	}

	private function handleDeleteFilm(): void
	{
		$id = (int) ($_POST['film_id'] ?? 0);
		if ($id <= 0) {
			throw new InvalidArgumentException("Identifiant film invalide.");
		}

		$this->resourceModel->deleteFilm($id);
	}
}

