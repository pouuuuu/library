<?php

require_once __DIR__ . '/../model/AuthModel.php';

class AdminController
{
	private $authModel;

	public function __construct()
	{
		$this->authModel = new AuthModel();
	}

	private function requireAdmin(): void
	{
		if (empty($_SESSION['user'])) {
			header('Location: ' . APP_INDEX_URL . '?route=auth/login');
			exit;
		}

		if (($_SESSION['user']['role'] ?? '') !== 'admin') {
			header('Location: ' . APP_INDEX_URL . '?route=dashboard');
			exit;
		}
	}

	public function index(): void
	{
		$this->requireAdmin();

		$allRoles = AuthModel::getAllowedRoles();
		try {
			$users = $this->authModel->getAllUsers();
		} catch (Throwable $throwable) {
			setFlashError("Impossible de récupérer les utilisateurs: " . $throwable->getMessage());
			$users = [];
		}
		$allowedRoles = array_filter($allRoles, static function ($role) {
			return $role !== 'user';
		});

		require __DIR__ . '/../view/admin/index.php';
	}

	public function handlePost(): void
	{
		$this->requireAdmin();

		$action = $_POST['action'] ?? '';

		try {
			if ($action === 'create_user') {
				$this->handleCreateUser();
				setFlashSuccess("Utilisateur créé avec succès.");
			} elseif ($action === 'update_role') {
				$this->handleUpdateRole();
				setFlashSuccess("Rôle mis à jour.");
			} else {
				throw new InvalidArgumentException("Action non reconnue.");
			}
		} catch (Throwable $throwable) {
			setFlashError($throwable->getMessage());
		}

		header('Location: ' . APP_INDEX_URL . '?route=admin');
		exit;
	}

	private function handleCreateUser(): void
	{
		$username = trim((string) ($_POST['new_username'] ?? ''));
		$email = trim((string) ($_POST['new_email'] ?? ''));
		$password = (string) ($_POST['new_password'] ?? '');
		$role = trim((string) ($_POST['new_role'] ?? ''));

		if (!in_array($role, ['bibliothecaire', 'admin'], true)) {
			throw new InvalidArgumentException("Le rôle choisi n'est pas autorisé.");
		}

		$this->authModel->createUser($username, $email, $password, $role);
	}

	private function handleUpdateRole(): void
	{
		$userId = (int) ($_POST['user_id'] ?? 0);
		$role = trim((string) ($_POST['role'] ?? ''));

		if ($userId <= 0) {
			throw new InvalidArgumentException("Identifiant utilisateur invalide.");
		}

		if (!in_array($role, AuthModel::getAllowedRoles(), true)) {
			throw new InvalidArgumentException("Le rôle choisi n'est pas autorisé.");
		}

		$currentUserId = $_SESSION['user']['id'] ?? null;
		if ($currentUserId === $userId && $role !== 'admin') {
			throw new RuntimeException("Un administrateur ne peut pas se rétrograder lui-même.");
		}

		$this->authModel->updateUserRole($userId, $role);

		if ($currentUserId === $userId) {
			$_SESSION['user']['role'] = $role;
		}
	}
}

