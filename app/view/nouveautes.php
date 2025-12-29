<?php
$publicBase = defined('PUBLIC_BASE_URL') ? PUBLIC_BASE_URL : '/public';
$indexUrl = defined('APP_INDEX_URL') ? APP_INDEX_URL : 'index.php';

// Variables passées par le controller
$resources = $resources ?? [];
$themes = $themes ?? [];
$currentType = $_GET['type'] ?? '';
$currentTheme = $_GET['theme'] ?? '';
$page = isset($_GET['page']) ? max(1, (int)$_GET['page']) : 1;
$totalPages = $totalPages ?? 1;
$totalCount = $totalCount ?? 0;
$limit = isset($_GET['limit']) ? (int)$_GET['limit'] : 20;
?>
<!DOCTYPE html>
<html lang="fr">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Nouveautés - SAÉ E-Library</title>
    <link rel="stylesheet" href="<?= htmlspecialchars($publicBase) ?>/css/style.css">
    <link rel="stylesheet" href="<?= htmlspecialchars($publicBase) ?>/css/nav.css">
    <link rel="stylesheet" href="<?= htmlspecialchars($publicBase) ?>/css/home.css">
    <link rel="stylesheet" href="<?= htmlspecialchars($publicBase) ?>/css/nouveautes.css">
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
        <section id="bot">
            <h1>Nouveautés - Ressources récentes</h1>
            
            <!-- Filtres -->
            <div class="filters-container">
                <div class="filter-group">
                    <label class="filter-label">Type :</label>
                    <div class="filter-chips">
                        <button class="filter-chip <?= $currentType === '' ? 'active' : '' ?>" 
                                data-type="" 
                                data-filter="type">
                            Tous
                        </button>
                        <button class="filter-chip <?= $currentType === 'book' ? 'active' : '' ?>" 
                                data-type="book" 
                                data-filter="type">
                            📘 Livres
                        </button>
                        <button class="filter-chip <?= $currentType === 'film' ? 'active' : '' ?>" 
                                data-type="film" 
                                data-filter="type">
                            🎬 Films
                        </button>
                    </div>
                </div>

                <?php if (!empty($themes)): ?>
                <div class="filter-group">
                    <label class="filter-label">Thème :</label>
                    <div class="filter-chips">
                        <button class="filter-chip <?= $currentTheme === '' ? 'active' : '' ?>" 
                                data-theme="" 
                                data-filter="theme">
                            Tous les thèmes
                        </button>
                        <?php foreach ($themes as $theme): ?>
                        <button class="filter-chip <?= $currentTheme === $theme ? 'active' : '' ?>" 
                                data-theme="<?= htmlspecialchars($theme) ?>" 
                                data-filter="theme">
                            <?= htmlspecialchars($theme) ?>
                        </button>
                        <?php endforeach; ?>
                    </div>
                </div>
                <?php endif; ?>
            </div>

            <!-- Message de chargement -->
            <div id="loading-message" class="loading-message hidden">
                <p>Chargement des ressources...</p>
            </div>

            <!-- Message si aucun résultat -->
            <div id="no-results" class="no-results hidden">
                <p>Aucune ressource trouvée avec ces filtres.</p>
            </div>

            <!-- Grille de ressources -->
            <div class="catalog" id="catalog">
                <?php if (empty($resources)): ?>
                    <p class="no-results">Aucune nouveauté disponible pour le moment.</p>
                <?php else: ?>
                    <?php foreach ($resources as $resource): ?>
                        <a href="<?= htmlspecialchars($indexUrl) ?>?<?= $resource['type'] === 'book' ? 'book' : 'film' ?>=<?= (int)$resource['id'] ?>" 
                           class="resource-card" 
                           data-type="<?= htmlspecialchars($resource['type']) ?>">
                            <div class="resource-image-container">
                                <img src="<?= htmlspecialchars($resource['poster'] ?? '/public/img/default-cover.png') ?>" 
                                     alt="<?= htmlspecialchars($resource['title']) ?>"
                                     onerror="this.src='<?= htmlspecialchars($publicBase) ?>/img/default-cover.png'">
                                <span class="badge badge-new">✨ Nouveau</span>
                                <span class="badge badge-type <?= $resource['type'] === 'book' ? 'badge-book' : 'badge-film' ?>">
                                    <?= $resource['type'] === 'book' ? '📘 Livre' : '🎬 Film' ?>
                                </span>
                            </div>
                            <div class="resource-info">
                                <h2><?= htmlspecialchars($resource['title']) ?></h2>
                                <?php if ($resource['type'] === 'book'): ?>
                                    <?php if (!empty($resource['authors'])): ?>
                                        <p class="resource-meta">Auteurs : <?= htmlspecialchars(implode(', ', $resource['authors'])) ?></p>
                                    <?php endif; ?>
                                    <?php if (!empty($resource['publishYear'])): ?>
                                        <p class="resource-meta">Année : <?= htmlspecialchars($resource['publishYear']) ?></p>
                                    <?php endif; ?>
                                    <?php if (!empty($resource['language'])): ?>
                                        <p class="resource-meta">Langue : <?= htmlspecialchars($resource['language']) ?></p>
                                    <?php endif; ?>
                                <?php else: ?>
                                    <?php if (!empty($resource['productionYear'])): ?>
                                        <p class="resource-meta">Année : <?= htmlspecialchars($resource['productionYear']) ?></p>
                                    <?php endif; ?>
                                    <?php if (!empty($resource['duration'])): ?>
                                        <p class="resource-meta">Durée : <?= htmlspecialchars($resource['duration']) ?> min</p>
                                    <?php endif; ?>
                                <?php endif; ?>
                                <?php if (!empty($resource['dateAdded'])): ?>
                                    <?php 
                                    $dateAdded = strtotime($resource['dateAdded']);
                                    if ($dateAdded !== false): 
                                    ?>
                                        <p class="resource-date">Ajouté le <?= date('d/m/Y', $dateAdded) ?></p>
                                    <?php endif; ?>
                                <?php endif; ?>
                            </div>
                        </a>
                    <?php endforeach; ?>
                <?php endif; ?>
            </div>

            <!-- Pagination -->
            <?php if ($totalPages > 1): ?>
            <div class="pagination-container">
                <div class="pagination-info">
                    Page <?= $page ?> sur <?= $totalPages ?> (<?= $totalCount ?> ressources)
                </div>
                <div class="pagination">
                    <?php if ($page > 1): ?>
                        <a href="<?= htmlspecialchars($indexUrl) ?>?route=nouveautes<?= $currentType ? '&type=' . urlencode($currentType) : '' ?><?= $currentTheme ? '&theme=' . urlencode($currentTheme) : '' ?>&page=<?= $page - 1 ?>" 
                           class="pagination-button">
                            ← Précédent
                        </a>
                    <?php else: ?>
                        <span class="pagination-button disabled">← Précédent</span>
                    <?php endif; ?>

                    <?php
                    // Afficher les numéros de page
                    $startPage = max(1, $page - 2);
                    $endPage = min($totalPages, $page + 2);
                    
                    if ($startPage > 1): ?>
                        <a href="<?= htmlspecialchars($indexUrl) ?>?route=nouveautes<?= $currentType ? '&type=' . urlencode($currentType) : '' ?><?= $currentTheme ? '&theme=' . urlencode($currentTheme) : '' ?>&page=1" 
                           class="pagination-number">1</a>
                        <?php if ($startPage > 2): ?>
                            <span class="pagination-ellipsis">...</span>
                        <?php endif; ?>
                    <?php endif; ?>

                    <?php for ($i = $startPage; $i <= $endPage; $i++): ?>
                        <?php if ($i == $page): ?>
                            <span class="pagination-number active"><?= $i ?></span>
                        <?php else: ?>
                            <a href="<?= htmlspecialchars($indexUrl) ?>?route=nouveautes<?= $currentType ? '&type=' . urlencode($currentType) : '' ?><?= $currentTheme ? '&theme=' . urlencode($currentTheme) : '' ?>&page=<?= $i ?>" 
                               class="pagination-number"><?= $i ?></a>
                        <?php endif; ?>
                    <?php endfor; ?>

                    <?php if ($endPage < $totalPages): ?>
                        <?php if ($endPage < $totalPages - 1): ?>
                            <span class="pagination-ellipsis">...</span>
                        <?php endif; ?>
                        <a href="<?= htmlspecialchars($indexUrl) ?>?route=nouveautes<?= $currentType ? '&type=' . urlencode($currentType) : '' ?><?= $currentTheme ? '&theme=' . urlencode($currentTheme) : '' ?>&page=<?= $totalPages ?>" 
                           class="pagination-number"><?= $totalPages ?></a>
                    <?php endif; ?>

                    <?php if ($page < $totalPages): ?>
                        <a href="<?= htmlspecialchars($indexUrl) ?>?route=nouveautes<?= $currentType ? '&type=' . urlencode($currentType) : '' ?><?= $currentTheme ? '&theme=' . urlencode($currentTheme) : '' ?>&page=<?= $page + 1 ?>" 
                           class="pagination-button">
                            Suivant →
                        </a>
                    <?php else: ?>
                        <span class="pagination-button disabled">Suivant →</span>
                    <?php endif; ?>
                </div>
            </div>
            <?php endif; ?>
        </section>
    </main>
    <footer>
    </footer>
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
<?= renderFlashMessagesScript() ?>
<!-- Toastify.js JS -->
<script type="text/javascript" src="https://cdn.jsdelivr.net/npm/toastify-js"></script>
<script src="<?= htmlspecialchars($publicBase) ?>/js/flash-messages.js"></script>
<script src="<?= htmlspecialchars($publicBase) ?>/js/search.js"></script>
<script src="<?= htmlspecialchars($publicBase) ?>/js/nouveautes.js"></script>
</body>

</html>
