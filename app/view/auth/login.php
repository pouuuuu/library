<?php
$publicBase = defined('PUBLIC_BASE_URL') ? PUBLIC_BASE_URL : '/public';
$indexUrl = defined('APP_INDEX_URL') ? APP_INDEX_URL : 'index.php';
?>
<!DOCTYPE html>
<html lang="fr">
<head>
	<meta charset="UTF-8">
	<title>SAÉ - E-Library - Connexion</title>
	<link rel="stylesheet" href="<?= htmlspecialchars($publicBase) ?>/css/style.css">
	<link rel="stylesheet" href="<?= htmlspecialchars($publicBase) ?>/css/auth.css">
	<!-- Toastify.js CSS -->
	<link rel="stylesheet" type="text/css" href="https://cdn.jsdelivr.net/npm/toastify-js/src/toastify.min.css">
</head>
<body>
<header>
	<a href="<?= htmlspecialchars($indexUrl) ?>?route=home">
		<svg xmlns="http://www.w3.org/2000/svg" height="24px" viewBox="0 -960 960 960" width="24px">
			<path d="M400-240 160-480l240-240 56 58-142 142h486v80H314l142 142-56 58Z"/>
		</svg>
		Retour à l'accueil
	</a>
</header>
<main>
	<form action="" method="post">
		<h1>Se connecter</h1>
		
		<?php
		// Transmettre le paramètre redirect dans le formulaire si présent
		$redirectParam = $_GET['redirect'] ?? null;
		if ($redirectParam): ?>
			<input type="hidden" name="redirect" value="<?= htmlspecialchars($redirectParam) ?>">
		<?php endif; ?>

		<label for="email">Email</label>
		<input type="email" id="email" name="email" required>

		<label for="password">Mot de passe</label>
		<input type="password" id="password" name="password" required>

		<button type="submit" class="main-button">Se connecter</button>

		<p>ou</p>

		<a href="<?= htmlspecialchars($indexUrl) ?>?route=auth/signup">S'inscrire</a>
	</form>
</main>
<footer>

</footer>
<?= renderFlashMessagesScript() ?>
<!-- Toastify.js JS -->
<script type="text/javascript" src="https://cdn.jsdelivr.net/npm/toastify-js"></script>
<script src="<?= htmlspecialchars($publicBase) ?>/js/flash-messages.js"></script>
</body>
</html>