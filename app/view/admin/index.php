<?php
$publicBase = defined('PUBLIC_BASE_URL') ? PUBLIC_BASE_URL : '/public';
$indexUrl = defined('APP_INDEX_URL') ? APP_INDEX_URL : 'index.php';
?>
<!DOCTYPE html>
<html lang="fr">

<head>
	<meta charset="UTF-8">
	<title>SAÉ - E-Library - Administration</title>
	<link rel="stylesheet" href="<?= htmlspecialchars($publicBase) ?>/css/style.css">
	<link rel="stylesheet" href="<?= htmlspecialchars($publicBase) ?>/css/nav.css">
	<link rel="stylesheet" href="<?= htmlspecialchars($publicBase) ?>/css/admin.css">
	<!-- Toastify.js CSS -->
	<link rel="stylesheet" type="text/css" href="https://cdn.jsdelivr.net/npm/toastify-js/src/toastify.min.css">
</head>

<body>
	<header>
		<nav>
			<a href="<?= htmlspecialchars($indexUrl) ?>?route=home">E-Library</a>
			<ul>
				<li><a href="<?= htmlspecialchars($indexUrl) ?>?route=home">Accueil</a></li>
				<li><a href="<?= htmlspecialchars($indexUrl) ?>?route=dashboard">Dashboard</a></li>
				<li><a href="<?= htmlspecialchars($indexUrl) ?>?route=admin" class="active">Administration</a></li>
			</ul>
			<ul>
				<li id="session-admin">Administrateur : <?= htmlspecialchars($_SESSION['user']['username'] ?? '') ?></li>
				<li><a href="<?= htmlspecialchars($indexUrl) ?>?route=auth/logout" class="button main-button">Se déconnecter</a></li>
			</ul>
		</nav>
	</header>
	<main class="admin">
		<section class="card">
			<h2>Créer un utilisateur</h2>
			<form method="post" action="<?= htmlspecialchars($indexUrl) ?>?route=admin">
				<input type="hidden" name="action" value="create_user">
				<label for="new_username">Nom d'utilisateur</label>
				<input type="text" id="new_username" name="new_username" required>

				<label for="new_email">Email</label>
				<input type="email" id="new_email" name="new_email" required>

				<label for="new_password">Mot de passe</label>
				<input type="password" id="new_password" name="new_password" required>

				<label for="new_role">Rôle</label>
				<select id="new_role" name="new_role" required>
					<?php foreach ($allowedRoles as $role): ?>
						<option value="<?= htmlspecialchars($role) ?>"><?= ucfirst($role) ?></option>
					<?php endforeach; ?>
				</select>

				<button type="submit" class="button main-button">Créer</button>
			</form>
		</section>

		<section class="card">
			<h2>Utilisateurs existants</h2>
			<?php if (empty($users)): ?>
				<p>Aucun utilisateur pour le moment.</p>
			<?php else: ?>
				<table>
					<thead>
						<tr>
							<th>Nom d'utilisateur</th>
							<th>Email</th>
							<th>Rôle</th>
							<th>Modifier</th>
						</tr>
					</thead>
					<tbody>
						<?php foreach ($users as $user): ?>
							<tr>
								<td><?= htmlspecialchars($user['username']) ?></td>
								<td><?= htmlspecialchars($user['email']) ?></td>
								<td><?= htmlspecialchars($user['role']) ?></td>
								<td>
									<form method="post" action="<?= htmlspecialchars($indexUrl) ?>?route=admin" class="inline-form">
										<input type="hidden" name="action" value="update_role">
										<input type="hidden" name="user_id" value="<?= (int) $user['idUtilisateur'] ?>">
										<select name="role">
											<?php foreach ($allRoles as $role): ?>
												<option value="<?= htmlspecialchars($role) ?>" <?= $user['role'] === $role ? 'selected' : '' ?>><?= ucfirst($role) ?></option>
											<?php endforeach; ?>
										</select>
										<button type="submit" class="button secondary-button">Mettre à jour</button>
									</form>
								</td>
							</tr>
						<?php endforeach; ?>
					</tbody>
				</table>
			<?php endif; ?>
		</section>
	</main>
<?= renderFlashMessagesScript() ?>
<!-- Toastify.js JS -->
<script type="text/javascript" src="https://cdn.jsdelivr.net/npm/toastify-js"></script>
<script src="<?= htmlspecialchars($publicBase) ?>/js/flash-messages.js"></script>
</body>

</html>