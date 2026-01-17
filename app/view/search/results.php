<?php
$publicBase = defined('PUBLIC_BASE_URL') ? PUBLIC_BASE_URL : '/public';
$indexUrl = defined('APP_INDEX_URL') ? APP_INDEX_URL : 'index.php';
?>
<!DOCTYPE html>
<html lang="fr">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Résultats de recherche - SAÉ E-Library</title>
    <link rel="stylesheet" href="<?= htmlspecialchars($publicBase) ?>/css/style.css">
    <link rel="stylesheet" href="<?= htmlspecialchars($publicBase) ?>/css/nav.css">
    <link rel="stylesheet" href="<?= htmlspecialchars($publicBase) ?>/css/home.css">
    <!-- Toastify.js CSS -->
    <link rel="stylesheet" type="text/css" href="https://cdn.jsdelivr.net/npm/toastify-js/src/toastify.min.css">
    <script src="<?= htmlspecialchars($publicBase) ?>/js/nav.js"></script>
    <style>
        .search-results {
            max-width: 1200px;
            margin: 2rem auto;
            padding: 0 1rem;
        }
        .search-header {
            margin-bottom: 2rem;
        }
        .search-header h1 {
            margin-bottom: 1rem;
        }
        .search-query {
            font-size: 1.2rem;
            color: #666;
            margin-bottom: 1rem;
        }
        .results-count {
            color: #888;
            margin-bottom: 1.5rem;
        }
        .results-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(200px, 1fr));
            gap: 1.5rem;
            margin-bottom: 2rem;
        }
        .result-item {
            background: white;
            border-radius: 8px;
            overflow: hidden;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
            transition: transform 0.2s, box-shadow 0.2s;
        }
        .result-item:hover {
            transform: translateY(-4px);
            box-shadow: 0 4px 8px rgba(0,0,0,0.15);
        }
        .result-item a {
            display: block;
            text-decoration: none;
            color: inherit;
        }
        .result-poster {
            width: 100%;
            aspect-ratio: 2/3;
            object-fit: cover;
            background: #f0f0f0;
        }
        .result-info {
            padding: 1rem;
        }
        .result-title {
            font-weight: bold;
            margin-bottom: 0.5rem;
            color: #333;
        }
        .result-meta {
            font-size: 0.9rem;
            color: #666;
        }
        .result-type {
            display: inline-block;
            padding: 0.25rem 0.5rem;
            background: #e0e0e0;
            border-radius: 4px;
            font-size: 0.8rem;
            margin-bottom: 0.5rem;
        }
        .result-type.book {
            background: #e3f2fd;
            color: #1976d2;
        }
        .result-type.film {
            background: #fff3e0;
            color: #f57c00;
        }
        .no-results {
            text-align: center;
            padding: 3rem;
            color: #666;
        }
        .no-results h2 {
            margin-bottom: 1rem;
        }
        .advanced-search-link {
            display: inline-block;
            margin-top: 1rem;
            color: #1976d2;
            text-decoration: none;
        }
        .advanced-search-link:hover {
            text-decoration: underline;
        }
    </style>
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
        <div class="search-results">
            <div class="search-header">
                <h1>Résultats de recherche</h1>
                <?php if (!empty($query ?? '')): ?>
                <div class="search-query">
                    Recherche : "<strong><?= htmlspecialchars($query) ?></strong>"
                </div>
                <?php endif; ?>
                <div class="results-count">
                    <?= count($results ?? []) ?> résultat(s) trouvé(s)
                </div>
            </div>

            <?php if (empty($results ?? [])): ?>
                <div class="no-results">
                    <h2>Aucun résultat trouvé</h2>
                    <p>Essayez de modifier vos critères de recherche ou utilisez la recherche avancée.</p>
                    <a href="<?= htmlspecialchars($indexUrl) ?>?action=search" class="advanced-search-link">Recherche avancée</a>
                </div>
            <?php else: ?>
                <div class="results-grid">
                    <?php foreach (($results ?? []) as $result): ?>
                        <div class="result-item">
                            <a href="<?= htmlspecialchars($result['type'] === 'book' ? $indexUrl . '?book=' . $result['id'] : $indexUrl . '?film=' . $result['id']) ?>">
                                <?php if ($result['poster']): ?>
                                    <img src="<?= htmlspecialchars($result['poster']) ?>" alt="<?= htmlspecialchars($result['title']) ?>" class="result-poster">
                                <?php else: ?>
                                    <div class="result-poster" style="display: flex; align-items: center; justify-content: center; color: #999;">
                                        Pas d'image
                                    </div>
                                <?php endif; ?>
                                <div class="result-info">
                                    <span class="result-type <?= htmlspecialchars($result['type']) ?>">
                                        <?= $result['type'] === 'book' ? '📘 Livre' : '🎬 Film' ?>
                                    </span>
                                    <div class="result-title"><?= htmlspecialchars($result['title']) ?></div>
                                    <div class="result-meta">
                                        <?php if ($result['type'] === 'book'): ?>
                                            <?php if (!empty($result['authors'])): ?>
                                                <div>Auteurs : <?= htmlspecialchars(implode(', ', $result['authors'])) ?></div>
                                            <?php endif; ?>
                                            <?php if ($result['publishYear']): ?>
                                                <div>Année : <?= htmlspecialchars($result['publishYear']) ?></div>
                                            <?php endif; ?>
                                            <?php if ($result['language']): ?>
                                                <div>Langue : <?= htmlspecialchars($result['language']) ?></div>
                                            <?php endif; ?>
                                        <?php else: ?>
                                            <?php if ($result['productionYear']): ?>
                                                <div>Année : <?= htmlspecialchars($result['productionYear']) ?></div>
                                            <?php endif; ?>
                                            <?php if ($result['duration']): ?>
                                                <div>Durée : <?= htmlspecialchars($result['duration']) ?> min</div>
                                            <?php endif; ?>
                                        <?php endif; ?>
                                    </div>
                                </div>
                            </a>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>

            <div style="text-align: center; margin-top: 2rem;">
                <a href="<?= htmlspecialchars($indexUrl) ?>?action=search" class="advanced-search-link">Recherche avancée</a>
            </div>
        </div>
    </main>

    <footer>
    </footer>
    <?= renderFlashMessagesScript() ?>
    <!-- Toastify.js JS -->
    <script type="text/javascript" src="https://cdn.jsdelivr.net/npm/toastify-js"></script>
    <script src="<?= htmlspecialchars($publicBase) ?>/js/flash-messages.js"></script>
    <script src="<?= htmlspecialchars($publicBase) ?>/js/search.js"></script>
</body>

</html>

