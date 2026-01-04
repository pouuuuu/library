<?php
$publicBase = defined('PUBLIC_BASE_URL') ? PUBLIC_BASE_URL : '/public';
$indexUrl = defined('APP_INDEX_URL') ? APP_INDEX_URL : 'index.php';
?>
<!DOCTYPE html>
<html lang="fr">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>SAÉ - E-Library</title>
    <link rel="stylesheet" href="<?= htmlspecialchars($publicBase) ?>/css/style.css">
    <link rel="stylesheet" href="<?= htmlspecialchars($publicBase) ?>/css/nav.css">
    <link rel="stylesheet" href="<?= htmlspecialchars($publicBase) ?>/css/home.css">
    <!-- Toastify.js CSS -->
    <link rel="stylesheet" type="text/css" href="https://cdn.jsdelivr.net/npm/toastify-js/src/toastify.min.css">
    <script src="<?= htmlspecialchars($publicBase) ?>/js/nav.js"></script>
</head>

<body>
    <header>
        <nav>
            <a href="<?= htmlspecialchars($indexUrl) ?>?route=home">E-Library</a>
            <ul>
                <li>
                    <a href="<?= htmlspecialchars($indexUrl) ?>?route=home">Accueil</a>
                </li>
                <li>
                    <a href="<?= htmlspecialchars($indexUrl) ?>?route=top">TOP</a>
                </li>
                <li>
                    <a href="<?= htmlspecialchars($indexUrl) ?>?route=nouveautes">Nouveautés</a>
                </li>
                <li>
                    <button class="search-button">
                        <svg xmlns="http://www.w3.org/2000/svg" height="24px" viewBox="0 -960 960 960" width="24px">
                            <path
                                d="M784-120 532-372q-30 24-69 38t-83 14q-109 0-184.5-75.5T120-580q0-109 75.5-184.5T380-840q109 0 184.5 75.5T640-580q0 44-14 83t-38 69l252 252-56 56ZM380-400q75 0 127.5-52.5T560-580q0-75-52.5-127.5T380-760q-75 0-127.5 52.5T200-580q0 75 52.5 127.5T380-400Z" />
                        </svg>
                    </button>
                </li>
            </ul>
            <ul>
                <?php if (empty($_SESSION['user'])): ?>
                <li><a href="<?= htmlspecialchars($indexUrl) ?>?route=auth/login" class="button secondary-button">Connexion</a></li>
                <li><a href="<?= htmlspecialchars($indexUrl) ?>?route=auth/signup" class="button main-button">Inscription</a></li>
                <?php else: ?>
                <li id="session-user">Bonjour, <?= htmlspecialchars($_SESSION['user']['username']) ?></li>
                <li><a href="<?= htmlspecialchars($indexUrl) ?>?route=dashboard" class="button secondary-button">Dashboard</a></li>
                <?php if (($_SESSION['user']['role'] ?? '') === 'admin'): ?>
                <li><a href="<?= htmlspecialchars($indexUrl) ?>?route=admin" class="button secondary-button">Administration</a></li>
                <?php endif; ?>
                <li><a href="<?= htmlspecialchars($indexUrl) ?>?route=auth/logout" class="button main-button">Se déconnecter</a></li>
                <?php endif; ?>
            </ul>
        </nav>
        <div class="search-bar hidden">
            <label>
                <input type="search" placeholder="Rechercher un film, un livre..." data-search-url="<?= htmlspecialchars($indexUrl) ?>">
            </label>
        </div>
    </header>
    <main>
        <section id="mid">
            <div id="carousel">
				<div>
					<?php if (isset($carouselResources)) {
						foreach ($carouselResources as $resource) { ?>
							<a class="resource" href="index.php?<?= htmlspecialchars($resource['resource_type']) ?>=<?= htmlspecialchars($resource['id']) ?>">
								<div class="poster">
									<img src="<?= htmlspecialchars($resource['poster']) ?>" alt="<?= htmlspecialchars($resource['resource_type'] ?? '') . " " . htmlspecialchars($resource['title']) ?>">
									<ul class="tags">
										<?php if ($resource['resource_type'] === 'film') { ?>
											<li>🎬 Film</li>
										<?php } elseif ($resource['resource_type'] === 'book') {?>
											<li>📘 Livre</li>
										<?php }
										if (isset($resource['type'])) { ?>
											<li><?= htmlspecialchars($resource['type']) ?></li>
										<?php } ?>
									</ul>
								</div>
								<div class="info">
									<h2 title="<?= htmlspecialchars($resource['title']) ?>"><?= htmlspecialchars($resource['title']) ?></h2>

									<?php if ($resource['resource_type'] === 'film') { ?>
										<?php if (!empty($resource['duration'])) { ?><p>Durée : <?= htmlspecialchars($resource['duration']) ?></p><?php } ?>
										<?php if (!empty($resource['releaseDate'])) { ?><p>Date de sortie : <?= htmlspecialchars($resource['releaseDate']) ?></p><?php } ?>
										<?php if (!empty($resource['languages'])) { ?><p>Langues : <?= implode(', ', explode(',', htmlspecialchars($resource['languages']))) ?></p><?php } ?>
									<?php } elseif ($resource['resource_type'] === 'book') { ?>
										<?php if (!empty($resource['nbPages'])) { ?><p>Nombre de pages : <?= htmlspecialchars($resource['nbPages']) ?></p><?php } ?>
										<?php if (!empty($resource['edition'])) { ?><p>Edition : <?= htmlspecialchars($resource['edition']) ?></p><?php } ?>
										<?php if (!empty($resource['language'])) { ?><p>Langue : <?= htmlspecialchars($resource['language']) ?></p><?php } ?>
									<?php } ?>

									<?php if (!empty($resource['year'])) { ?><p>Année : <?= htmlspecialchars($resource['year']) ?></p><?php } ?>
									<?php if (isset($resource['nbBorrowings'])) { ?><p>Nombre d'emprunts : <?= htmlspecialchars($resource['nbBorrowings']) ?></p><?php } ?>
									<ul class="tags">
										<?php
										if (isset($resource['genres'])) {
											foreach ($resource['genres'] as $genre) { ?>
												<li><?= htmlspecialchars($genre) ?></li>
											<?php }
										}
										if (isset($resource['themes'])) {
											foreach ($resource['themes'] as $theme) { ?>
												<li><?= htmlspecialchars($theme) ?></li>
											<?php }
										} ?>
									</ul>
								</div>
							</a>
						<?php }
					} ?>
				</div>
                <button class="button main-button" id="carousel-left-button">
                    <svg xmlns="http://www.w3.org/2000/svg" height="24px" viewBox="0 -960 960 960">
                        <path d="M640-80 240-480l400-400 71 71-329 329 329 329-71 71Z"/>
                    </svg>
                </button>
                <button class="button main-button" id="carousel-right-button">
                    <svg xmlns="http://www.w3.org/2000/svg" height="24px" viewBox="0 -960 960 960">
                        <path d="m321-80-71-71 329-329-329-329 71-71 400 400L321-80Z"/>
                    </svg>
                </button>
            </div>
            <aside id="popular">
                <h1>Populaires</h1>
				<?php if (isset($popularResources)) {
					foreach ($popularResources as $resource) { ?>
						<a class="resource" href="index.php?<?= htmlspecialchars($resource['resource_type']) ?>=<?= htmlspecialchars($resource['id']) ?>">
							<div class="poster">
								<img src="<?= htmlspecialchars($resource['poster']) ?>" alt="<?= htmlspecialchars($resource['resource_type'] ?? '') . " " . htmlspecialchars($resource['title']) ?>">
							</div>
							<div class="info">
								<h2 title="<?= htmlspecialchars($resource['title']) ?>"><?= htmlspecialchars($resource['title']) ?></h2>

								<?php if (isset($resource['nbBorrowings'])) { ?><p>Nombre d'emprunts : <?= htmlspecialchars($resource['nbBorrowings']) ?></p><?php } ?>
								<?php if (isset($resource['rating'])) { ?><p>Note : <?= htmlspecialchars($resource['rating']) ?></p><?php } ?>
								<ul class="tags">
									<?php if ($resource['resource_type'] === 'film') { ?>
										<li>🎬 Film</li>
									<?php } elseif ($resource['resource_type'] === 'book') {?>
										<li>📘 Livre</li>
									<?php }
									if (isset($resource['type'])) { ?>
										<li><?= htmlspecialchars($resource['type']) ?></li>
									<?php } ?>
								</ul>
							</div>
						</a>
				<?php }
				}?>
                <a id="view-more" href="<?= htmlspecialchars($indexUrl) ?>?route=top" class="button main-button">Voir Plus de Ressources</a>


            </aside>
        </section>
        <section id="bot">
            <h1>Catalogue</h1>
            <div class="catalog"></div>
            <button id="load-more" class="button main-button">Afficher plus</button>
        </section>

        <!-- Transmettre le statut de connexion à JavaScript -->
        <script id="auth-status" type="application/json">
            <?php
            // Vérifier explicitement le statut de connexion avec la fonction helper
            // pour être sûr d'utiliser la même logique partout
            $userIsLoggedIn = isLoggedIn();
            echo json_encode([
                'isLoggedIn' => (bool)$userIsLoggedIn, // Forcer en booléen explicite pour JSON
                'loginUrl' => htmlspecialchars($indexUrl) . '?route=auth/login'
            ], JSON_UNESCAPED_UNICODE | JSON_HEX_TAG);
            ?>
        </script>
        <script src="<?= htmlspecialchars($publicBase) ?>/js/modal.js"></script>
        <script src="<?= htmlspecialchars($publicBase) ?>/js/protect-resources.js"></script>
        <script src="<?= htmlspecialchars($publicBase) ?>/js/view-more.js"></script>
        <script src="<?= htmlspecialchars($publicBase) ?>/js/search.js"></script>
		<script src="<?= htmlspecialchars($publicBase) ?>/js/carousel.js"></script>


    </main>
    <footer>

    </footer>
<?= renderFlashMessagesScript() ?>
<!-- Toastify.js JS -->
<script type="text/javascript" src="https://cdn.jsdelivr.net/npm/toastify-js"></script>
<script src="<?= htmlspecialchars($publicBase) ?>/js/flash-messages.js"></script>
</body>

</html>