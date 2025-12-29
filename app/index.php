<?php
session_start();

$scriptName = str_replace('\\', '/', $_SERVER['SCRIPT_NAME'] ?? '');
$scriptDir = rtrim(dirname($scriptName), '/');
$appBase = preg_replace('#/app$#', '', $scriptDir);
if ($appBase === '.' || $appBase === false) {
	$appBase = '';
}
$basePrefix = $appBase ?? '';
$basePrefix = $basePrefix === '/' ? '' : $basePrefix;
if ($basePrefix !== '' && $basePrefix[0] !== '/') {
	$basePrefix = '/' . $basePrefix;
}
define('APP_BASE_URL', $basePrefix === '' ? '' : $basePrefix);
define('APP_INDEX_URL', $basePrefix . '/app/index.php');
define('PUBLIC_BASE_URL', $basePrefix . '/public');

ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// Helpers simples
function redirect(string $route): void {
	header('Location: ' . APP_INDEX_URL . '?route=' . $route);
	exit;
}

// Charger helpers
require_once __DIR__ . '/helpers/flash.php';
require_once __DIR__ . '/helpers/auth.php';

// Charger dépendances minimales
require_once __DIR__ . '/controller/Controller.php';
require_once __DIR__ . '/controller/AuthController.php';
require_once __DIR__ . '/controller/DashboardController.php';
require_once __DIR__ . '/controller/AdminController.php';
require_once __DIR__ . '/controller/ResourceController.php';
require_once __DIR__ . '/controller/ResourceManagementController.php';
require_once __DIR__ . '/controller/TopController.php';
require_once __DIR__ . '/controller/NouveautesController.php';
require_once __DIR__ . '/controller/SearchController.php';
require_once __DIR__ . '/controller/AvisController.php';
require_once __DIR__ . '/controller/EmpruntController.php';
require_once __DIR__ . '/model/AuthModel.php';
require_once __DIR__ . '/model/Model.php';

// Vérifier si on demande un détail de livre ou film (priorité sur la route)
if (isset($_GET['book'])) {
	$resourceController = new ResourceController();
	$resourceController->showBook();
	exit;
}

if (isset($_GET['film'])) {
	$resourceController = new ResourceController();
	$resourceController->showFilm();
	exit;
}

// Vérifier les actions de recherche (priorité sur la route)
if (isset($_GET['action'])) {
	$action = $_GET['action'];
	if ($action === 'search') {
		$searchController = new SearchController();
		$searchController->search();
		exit;
	} elseif ($action === 'autocomplete') {
		$searchController = new SearchController();
		$searchController->autocomplete();
		exit;
	}
}

// Parsing de la route
$route = $_GET['route'] ?? 'home';

// Routing table
switch ($route) {
	case 'auth/login':
		$auth = new AuthController();
		$auth->login();
		break;

	case 'auth/signup':
		$auth = new AuthController();
		$auth->signup();
		break;

	case 'auth/logout':
		$auth = new AuthController();
		$auth->logout();
		break;

	case 'dashboard':
		$dashboard = new DashboardController();
		$dashboard->index();
		break;

	case 'admin':
		$admin = new AdminController();
		if ($_SERVER['REQUEST_METHOD'] === 'POST') {
			$admin->handlePost();
		} else {
			$admin->index();
		}
		break;

	case 'resources':
		$resourceManagement = new ResourceManagementController();
		if ($_SERVER['REQUEST_METHOD'] === 'POST') {
			$resourceManagement->handlePost();
		} else {
			$resourceManagement->index();
		}
		break;

	case 'top':
		$top = new TopController();
		$top->index();
		break;

	case 'nouveautes':
		$nouveautes = new NouveautesController();
		$nouveautes->index();
		break;

	case 'avis/create':
		$avis = new AvisController();
		$avis->create();
		break;

	case 'avis/update':
		$avis = new AvisController();
		$avis->update();
		break;

	case 'avis/delete':
		$avis = new AvisController();
		$avis->delete();
		break;

	case 'emprunt/emprunter':
		$emprunt = new EmpruntController();
		$emprunt->emprunter();
		break;

	case 'emprunt/supprimer':
		$emprunt = new EmpruntController();
		$emprunt->supprimer();
		break;

	case 'home':
	default:
		$controller = new Controller();
		$controller->invoke();
		break;
}