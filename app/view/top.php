<?php
$publicBase = defined('PUBLIC_BASE_URL') ? PUBLIC_BASE_URL : '/public';
$indexUrl = defined('APP_INDEX_URL') ? APP_INDEX_URL : 'index.php';

$resources = $resources ?? [];
$page = $page ?? 1;
$sort = $sort ?? 'rating';
$totalPages = $totalPages ?? 1;
$startRank = $startRank ?? 1;
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>TOP Livres - SAÉ E-Library</title>
    <link rel="stylesheet" href="<?= htmlspecialchars($publicBase) ?>/css/style.css">
    <link rel="stylesheet" href="<?= htmlspecialchars($publicBase) ?>/css/nav.css">
    <link rel="stylesheet" href="<?= htmlspecialchars($publicBase) ?>/css/top.css">
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
<main class="ranking-container">
    <div class="ranking-header" style="text-align: center; border: none;">
        <h1 style="margin-bottom: 0.5rem;">🏆 Classement des Livres</h1>
    </div>

    <div class="top-filters">
        <a href="#" class="filter-btn <?= $sort === 'rating' ? 'active' : '' ?>" data-sort="rating">
            ⭐ Les mieux notés
        </a>
        <a href="#" class="filter-btn <?= $sort === 'comments' ? 'active' : '' ?>" data-sort="comments">
            💬 Les plus commentés
        </a>
        <a href="#" class="filter-btn <?= $sort === 'popular' ? 'active' : '' ?>" data-sort="popular">
            🔥 Les plus populaires
        </a>
    </div>

    <div id="loading-message" style="display:none; text-align:center;">Chargement...</div>

    <div class="ranking-list" id="ranking-list">
        <?php if (empty($resources)): ?>
            <p style="text-align: center; color: #888;">Aucun livre trouvé.</p>
        <?php else: ?>
            <?php foreach ($resources as $index => $book): ?>
                <?php $rank = $startRank + $index; ?>
                <a href="<?= htmlspecialchars($indexUrl) ?>?book=<?= $book['id'] ?>" class="ranking-item rank-<?= $rank ?>">
                    <div class="rank-number">#<?= $rank ?></div>
                    <img src="<?= htmlspecialchars($book['poster'] ?? '/public/img/default-cover.png') ?>"
                         alt="<?= htmlspecialchars($book['title']) ?>"
                         class="rank-poster"
                         onerror="this.src='<?= htmlspecialchars($publicBase) ?>/img/default-cover.png'">
                    <div class="rank-info">
                        <h3 class="rank-title"><?= htmlspecialchars($book['title']) ?></h3>
                    </div>
                    <div class="rank-score">
                        <?php if ($sort === 'popular'): ?>
                            <span style="font-size:1.2rem; font-weight:bold; color:#e17055;">
                                    <?= number_format($book['borrow_count']) ?>
                                </span> <small>Emprunts</small>
                        <?php elseif ($sort === 'comments'): ?>
                            <span style="font-size:1.2rem; font-weight:bold; color:#6c5ce7;">
                                    <?= number_format($book['comment_count']) ?>
                                </span> <small>Avis</small>
                        <?php else: ?>
                            <span style="font-size:1.2rem; font-weight:bold; color:#FFD700;">
                                    ★ <?= number_format($book['rating'], 1) ?>
                                </span> <small>Moyenne</small>
                        <?php endif; ?>
                    </div>
                </a>
            <?php endforeach; ?>
        <?php endif; ?>
    </div>

    <div class="pagination-container">
        <div class="pagination" id="pagination">
            <?php if ($totalPages > 1): ?>
                <span class="pagination-button disabled">← Précédent</span>
                <span style="margin: 0 10px;">Page <?= $page ?> / <?= $totalPages ?></span>
                <a href="#" class="pagination-button" data-page="<?= $page + 1 ?>">Suivant →</a>
            <?php endif; ?>
        </div>
    </div>
</main>

<script id="auth-status" type="application/json">
        <?php echo json_encode(['isLoggedIn' => isLoggedIn(), 'loginUrl' => htmlspecialchars($indexUrl).'?route=auth/login']); ?>
    </script>
<?= renderFlashMessagesScript() ?>
<script type="text/javascript" src="https://cdn.jsdelivr.net/npm/toastify-js"></script>
<script src="<?= htmlspecialchars($publicBase) ?>/js/flash-messages.js"></script>
<script src="<?= htmlspecialchars($publicBase) ?>/js/search.js"></script>
<script src="<?= htmlspecialchars($publicBase) ?>/js/top.js"></script>
</body>
</html>