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
    <title>TOP Livres - SAÉ E-Library</title>
    <link rel="stylesheet" href="<?= htmlspecialchars($publicBase) ?>/css/style.css">
    <link rel="stylesheet" href="<?= htmlspecialchars($publicBase) ?>/css/nav.css">
    <link rel="stylesheet" href="<?= htmlspecialchars($publicBase) ?>/css/top.css">
    <script src="<?= htmlspecialchars($publicBase) ?>/js/nav.js"></script>
    <style>
        .top-filters { display: flex; gap: 1rem; margin-bottom: 2rem; justify-content: center; }
        .filter-btn {
            background: rgba(255,255,255,0.05); border: 1px solid rgba(255,255,255,0.1);
            padding: 10px 20px; border-radius: 30px; color: #aaa; cursor: pointer;
            text-decoration: none; transition: all 0.3s; font-weight: 500;
        }
        .filter-btn:hover { background: rgba(255,255,255,0.1); color: #fff; }
        .filter-btn.active {
            background: var(--accent-color, #6c5ce7); color: white; border-color: var(--accent-color, #6c5ce7);
            box-shadow: 0 4px 15px rgba(108, 92, 231, 0.3);
        }
    </style>
</head>
<body>
<header>
    <nav>
        <a href="<?= htmlspecialchars($indexUrl) ?>?route=home">E-Library</a>
        <ul>
            <li><a href="<?= htmlspecialchars($indexUrl) ?>?route=home">Accueil</a></li>
            <li><a href="<?= htmlspecialchars($indexUrl) ?>?route=top" class="active">TOP</a></li>
            <li><a href="<?= htmlspecialchars($indexUrl) ?>?route=nouveautes">Nouveautés</a></li>
            <li><button class="search-button">🔍</button></li>
        </ul>
        <ul>
            <?php if (empty($_SESSION['user'])): ?>
                <li><a href="<?= htmlspecialchars($indexUrl) ?>?route=auth/login" class="button secondary-button">Connexion</a></li>
                <li><a href="<?= htmlspecialchars($indexUrl) ?>?route=auth/signup" class="button main-button">Inscription</a></li>
            <?php else: ?>
                <li>Bonjour, <?= htmlspecialchars($_SESSION['user']['username']) ?></li>
                <li><a href="<?= htmlspecialchars($indexUrl) ?>?route=dashboard" class="button secondary-button">Dashboard</a></li>
                <li><a href="<?= htmlspecialchars($indexUrl) ?>?route=auth/logout" class="button main-button">Déconnexion</a></li>
            <?php endif; ?>
        </ul>
    </nav>
    <div class="search-bar hidden">
        <label><input type="search" placeholder="Rechercher..." data-search-url="<?= htmlspecialchars($indexUrl) ?>"></label>
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
                        <?php if ($sort === 'comments'): ?>
                            <?= number_format($book['comment_count']) ?> <small>Avis</small>
                        <?php else: ?>
                            ★ <?= number_format($book['rating'], 1) ?> <small>Moyenne</small>
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
<script src="<?= htmlspecialchars($publicBase) ?>/js/search.js"></script>
<script src="<?= htmlspecialchars($publicBase) ?>/js/top.js"></script>
</body>
</html>