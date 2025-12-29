<?php

require_once __DIR__ . '/../model/AuthModel.php';
require_once __DIR__ . '/../model/EmpruntModel.php';

class DashboardController
{
	private function requireAuth(): void
	{
		if (empty($_SESSION['user'])) {
			header('Location: ' . APP_INDEX_URL . '?route=auth/login');
			exit;
		}
	}

	public function index(): void
	{
		$this->requireAuth();

		$user = $_SESSION['user'];
		$role = $user['role'] ?? 'user';

		$capabilities = [
			'canAccessAdmin' => $role === 'admin',
			'canManageResources' => in_array($role, ['admin', 'bibliothecaire'], true),
			'canViewStandardFeatures' => true,
		];

		// Récupérer les emprunts de l'utilisateur
		$empruntModel = new EmpruntModel();
		$emprunts = [];
		if (!empty($user['id'])) {
			$emprunts = $empruntModel->getByUser((int)$user['id']);
		}

		require __DIR__ . '/../view/dashboard.php';
	}
}

