<?php
$publicBase = defined('PUBLIC_BASE_URL') ? PUBLIC_BASE_URL : '/public';
$indexUrl = defined('APP_INDEX_URL') ? APP_INDEX_URL : 'index.php';
?>
<!DOCTYPE html>
<html lang="fr">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Recherche avancée - SAÉ E-Library</title>
    <link rel="stylesheet" href="<?= htmlspecialchars($publicBase) ?>/css/style.css">
    <link rel="stylesheet" href="<?= htmlspecialchars($publicBase) ?>/css/nav.css">
    <!-- Toastify.js CSS -->
    <link rel="stylesheet" type="text/css" href="https://cdn.jsdelivr.net/npm/toastify-js/src/toastify.min.css">
    <script src="<?= htmlspecialchars($publicBase) ?>/js/nav.js"></script>
    <style>
        .advanced-search {
            max-width: 800px;
            margin: 2rem auto;
            padding: 0 1rem;
        }
        .search-form {
            background: white;
            padding: 2rem;
            border-radius: 8px;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
        }
        .form-group {
            margin-bottom: 1.5rem;
        }
        .form-group label {
            display: block;
            margin-bottom: 0.5rem;
            font-weight: bold;
            color: #333;
        }
        .form-group input,
        .form-group select {
            width: 100%;
            padding: 0.75rem;
            border: 1px solid #ddd;
            border-radius: 4px;
            font-size: 1rem;
        }
        .form-row {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 1rem;
        }
        .form-actions {
            display: flex;
            gap: 1rem;
            margin-top: 2rem;
        }
        .form-actions button {
            flex: 1;
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
        <div class="advanced-search">
            <h1>Recherche avancée</h1>
            
            <form method="GET" action="<?= htmlspecialchars($indexUrl) ?>" class="search-form">
                <input type="hidden" name="action" value="search">
                
                <div class="form-group">
                    <label for="q">Recherche par titre ou auteur</label>
                    <input type="text" id="q" name="q" placeholder="Tapez votre recherche..." value="<?= htmlspecialchars($_GET['q'] ?? '') ?>">
                </div>

                <div class="form-group">
                    <label for="type">Type de ressource</label>
                    <select id="type" name="type">
                        <option value="">Tous</option>
                        <option value="book" <?= (isset($_GET['type']) && $_GET['type'] === 'book') ? 'selected' : '' ?>>Livre</option>
                        <option value="film" <?= (isset($_GET['type']) && $_GET['type'] === 'film') ? 'selected' : '' ?>>Film</option>
                    </select>
                </div>

                <div class="form-group">
                    <label for="language">Langue</label>
                    <select id="language" name="language">
                        <option value="">Toutes les langues</option>
                        <?php foreach ($languages as $lang): ?>
                            <option value="<?= htmlspecialchars($lang) ?>" <?= (isset($_GET['language']) && $_GET['language'] === $lang) ? 'selected' : '' ?>>
                                <?= htmlspecialchars($lang) ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <div class="form-group">
                    <label for="author">Auteur / Réalisateur</label>
                    <input type="text" id="author" name="author" placeholder="Nom de l'auteur ou réalisateur..." value="<?= htmlspecialchars($_GET['author'] ?? '') ?>">
                </div>

                <div class="form-row">
                    <div class="form-group">
                        <label for="year_min">Année minimum</label>
                        <input type="number" id="year_min" name="year_min" placeholder="Ex: 2000" value="<?= htmlspecialchars($_GET['year_min'] ?? '') ?>" min="1900" max="<?= date('Y') ?>">
                    </div>
                    <div class="form-group">
                        <label for="year_max">Année maximum</label>
                        <input type="number" id="year_max" name="year_max" placeholder="Ex: 2024" value="<?= htmlspecialchars($_GET['year_max'] ?? '') ?>" min="1900" max="<?= date('Y') ?>">
                    </div>
                </div>

                <div class="form-actions">
                    <button type="submit" class="button main-button">Rechercher</button>
                    <a href="<?= htmlspecialchars($indexUrl) ?>?route=home" class="button secondary-button">Annuler</a>
                </div>
            </form>
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

