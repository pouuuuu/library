<?php
$publicBase = defined('PUBLIC_BASE_URL') ? PUBLIC_BASE_URL : '/public';
$indexUrl = defined('APP_INDEX_URL') ? APP_INDEX_URL : 'index.php';
$user = $_SESSION['user'];
$roleLabel = [
	'admin' => 'Administrateur',
	'bibliothecaire' => 'Bibliothécaire',
	'user' => 'Utilisateur',
][$user['role'] ?? 'user'] ?? 'Utilisateur';
?>
<!DOCTYPE html>
<html lang="fr">
<head>
	<meta charset="UTF-8">
	<title>SAÉ - E-Library - Dashboard</title>
	<link rel="stylesheet" href="<?= htmlspecialchars($publicBase) ?>/css/style.css">
	<link rel="stylesheet" href="<?= htmlspecialchars($publicBase) ?>/css/nav.css">
	<link rel="stylesheet" href="<?= htmlspecialchars($publicBase) ?>/css/dashboard.css">
	<!-- Toastify.js CSS -->
	<link rel="stylesheet" type="text/css" href="https://cdn.jsdelivr.net/npm/toastify-js/src/toastify.min.css">
</head>
<body>
<header>
	<nav>
		<a href="<?= htmlspecialchars($indexUrl) ?>?route=home">E-Library</a>
		<ul>
			<li><a href="<?= htmlspecialchars($indexUrl) ?>?route=home">Accueil</a></li>
			<li><a href="<?= htmlspecialchars($indexUrl) ?>?route=dashboard" class="active">Dashboard</a></li>
			<?php if ($capabilities['canAccessAdmin']): ?>
				<li><a href="<?= htmlspecialchars($indexUrl) ?>?route=admin">Administration</a></li>
			<?php endif; ?>
		</ul>
		<ul>
			<li style="margin-right:10px; color:#fff;">Connecté en tant que <?= htmlspecialchars($user['username']) ?> (<?= htmlspecialchars($roleLabel) ?>)</li>
			<li><a href="<?= htmlspecialchars($indexUrl) ?>?route=auth/logout" class="button main-button">Se déconnecter</a></li>
		</ul>
	</nav>
</header>
<main class="dashboard">
	<!-- Section Mes emprunts -->
	<section class="card emprunts-section">
		<h2>Mes emprunts</h2>
		<?php if (empty($emprunts)): ?>
			<p>Vous n'avez aucun emprunt pour le moment.</p>
			<a class="button secondary-button" href="<?= htmlspecialchars($indexUrl) ?>?route=home">Parcourir le catalogue</a>
		<?php else: ?>
			<p style="color: var(--secondary-text-color); margin-bottom: 1.5rem;">
				Vous avez <strong style="color: var(--text-color);"><?= count($emprunts) ?></strong> emprunt<?= count($emprunts) > 1 ? 's' : '' ?> en cours.
			</p>
			<div class="emprunts-list">
				<?php foreach ($emprunts as $emprunt): ?>
					<div class="emprunt-item">
						<?php if (!empty($emprunt['poster'])): ?>
							<img src="<?= htmlspecialchars($emprunt['poster']) ?>" 
								 alt="<?= htmlspecialchars($emprunt['titre']) ?>" 
								 class="emprunt-poster">
						<?php else: ?>
							<div class="emprunt-poster-placeholder">
								<?php if ($emprunt['type_ressource'] === 'livre'): ?>
									📘
								<?php else: ?>
									🎬
								<?php endif; ?>
							</div>
						<?php endif; ?>
						<div class="emprunt-content">
							<h3 class="emprunt-title">
								<?= htmlspecialchars($emprunt['titre']) ?>
							</h3>
							<div class="emprunt-meta">
								<div class="emprunt-meta-item">
									<strong>Type :</strong>
									<span class="emprunt-type-badge">
										<?php if ($emprunt['type_ressource'] === 'livre'): ?>
											📘 Livre
										<?php else: ?>
											🎬 Film
										<?php endif; ?>
									</span>
								</div>
								<div class="emprunt-meta-item">
									<strong>Date d'emprunt :</strong>
									<span><?= date('d/m/Y à H:i', strtotime($emprunt['timestamp'])) ?></span>
								</div>
							</div>
							<div class="emprunt-actions">
								<?php if ($emprunt['type_ressource'] === 'livre'): ?>
									<a href="<?= htmlspecialchars($indexUrl) ?>?book=<?= (int)$emprunt['idLivre'] ?>" 
									   class="button secondary-button">
										Voir le livre
									</a>
								<?php else: ?>
									<a href="<?= htmlspecialchars($indexUrl) ?>?film=<?= (int)$emprunt['idFilm'] ?>" 
									   class="button secondary-button">
										Voir le film
									</a>
								<?php endif; ?>
								<form method="POST" action="<?= htmlspecialchars($indexUrl) ?>?route=emprunt/supprimer" 
									  style="display: inline;" 
									  onsubmit="return confirm('Êtes-vous sûr de vouloir supprimer cet emprunt ?');">
									<?php if ($emprunt['type_ressource'] === 'livre'): ?>
										<input type="hidden" name="idLivre" value="<?= (int)$emprunt['idLivre'] ?>">
									<?php else: ?>
										<input type="hidden" name="idFilm" value="<?= (int)$emprunt['idFilm'] ?>">
									<?php endif; ?>
									<button type="submit" class="button button-danger">
										Supprimer
									</button>
								</form>
							</div>
						</div>
					</div>
				<?php endforeach; ?>
			</div>
		<?php endif; ?>
	</section>

	<?php if ($capabilities['canManageResources']): ?>
	<section class="card">
		<h2>Gestion des ressources</h2>
		<p>Accédez aux outils de gestion pour ajouter, modifier ou supprimer des ressources numériques.</p>
		<a class="button secondary-button" href="<?= htmlspecialchars($indexUrl) ?>?route=resources">Ouvrir la gestion</a>
	</section>
	<?php endif; ?>

	<?php if ($capabilities['canAccessAdmin']): ?>
	<section class="card">
		<h2>Administration</h2>
		<p>Créer et administrer les comptes utilisateurs, gérer les rôles et surveiller l'activité.</p>
		<a class="button main-button" href="<?= htmlspecialchars($indexUrl) ?>?route=admin">Accéder à l'administration</a>
	</section>
	<?php endif; ?>
</main>
<?= renderFlashMessagesScript() ?>
<!-- Toastify.js JS -->
<script type="text/javascript" src="https://cdn.jsdelivr.net/npm/toastify-js"></script>
<script src="<?= htmlspecialchars($publicBase) ?>/js/flash-messages.js"></script>
</body>
</html>

