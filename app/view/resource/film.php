<?php
$publicBase = defined('PUBLIC_BASE_URL') ? PUBLIC_BASE_URL : '/public';
$indexUrl = defined('APP_INDEX_URL') ? APP_INDEX_URL : 'index.php';
?>
<!DOCTYPE html>
<html lang="fr">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= htmlspecialchars($film->getTitle()) ?> - SAÉ E-Library</title>
    <link rel="stylesheet" href="<?= htmlspecialchars($publicBase) ?>/css/style.css">
    <link rel="stylesheet" href="<?= htmlspecialchars($publicBase) ?>/css/nav.css">
    <link rel="stylesheet" href="<?= htmlspecialchars($publicBase) ?>/css/resource.css">
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

    <main class="resource-detail">
        <div class="resource-header">
            <a href="<?= htmlspecialchars($indexUrl) ?>?route=home" class="back-link">← Retour au catalogue</a>
            
            <div class="resource-content">
                <div class="resource-poster">
                    <?php if ($film->getPoster()): ?>
                        <img src="<?= htmlspecialchars($film->getPoster()) ?>" alt="<?= htmlspecialchars($film->getTitle()) ?>">
                    <?php else: ?>
                        <div class="no-poster">Pas d'image disponible</div>
                    <?php endif; ?>
                </div>

                <div class="resource-info">
                    <h1><?= htmlspecialchars($film->getTitle()) ?></h1>
                    <span class="badge">🎬 Film</span>

                    <!-- Bouton d'emprunt -->
                    <?php if (!empty($_SESSION['user'])): ?>
                    <div class="emprunt-section" style="margin: 20px 0;">
                        <?php if ($isEmprunte): ?>
                            <button class="button secondary-button" disabled style="opacity: 0.6; cursor: not-allowed;">
                                ✓ Déjà emprunté
                            </button>
                        <?php else: ?>
                            <form method="POST" action="<?= htmlspecialchars($indexUrl) ?>?route=emprunt/emprunter" style="display: inline;">
                                <input type="hidden" name="idFilm" value="<?= htmlspecialchars($idFilmForView ?? $film->getId()) ?>">
                                <input type="hidden" name="type" value="film">
                                <button type="submit" class="button main-button">
                                    🎬 Emprunter ce film
                                </button>
                            </form>
                        <?php endif; ?>
                    </div>
                    <?php endif; ?>

                    <div class="resource-details">
                        <?php if ($film->getReleaseDate()): ?>
                        <p><strong>Date de sortie :</strong> <?= htmlspecialchars($film->getReleaseDate()) ?></p>
                        <?php endif; ?>

                        <?php if ($film->getProductionYear()): ?>
                        <p><strong>Année de production :</strong> <?= htmlspecialchars($film->getProductionYear()) ?></p>
                        <?php endif; ?>

                        <?php if ($film->getDuration()): ?>
                        <p><strong>Durée :</strong> <?= htmlspecialchars($film->getDuration()) ?> minutes</p>
                        <?php endif; ?>

                        <?php if ($film->getSynopsis()): ?>
                        <div class="synopsis">
                            <h2>Synopsis</h2>
                            <p><?= nl2br(htmlspecialchars($film->getSynopsis())) ?></p>
                        </div>
                        <?php endif; ?>

                        <?php if ($film->getTrailer()): ?>
                        <div class="trailer">
                            <h2>Bande-annonce</h2>
                            <a href="<?= htmlspecialchars($film->getTrailer()) ?>" target="_blank" class="button main-button">
                                Voir la bande-annonce
                            </a>
                        </div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>

        <!-- Section Avis -->
        <section class="reviews-section">
            <h2>Avis des utilisateurs</h2>
            
            <!-- Note moyenne -->
            <?php if ($rating['count'] > 0): ?>
            <div class="average-rating">
                <p><strong>Note moyenne :</strong> 
                    <span class="stars">
                        <?php 
                        $avgNote = round($rating['average']);
                        for ($i = 1; $i <= 5; $i++): 
                        ?>
                            <span class="star <?= $i <= $avgNote ? 'filled' : '' ?>">★</span>
                        <?php endfor; ?>
                    </span>
                    <span class="rating-text"><?= number_format($rating['average'], 1) ?>/5 (<?= $rating['count'] ?> avis)</span>
                </p>
            </div>
            <?php else: ?>
            <p class="no-reviews">Aucun avis pour le moment. Soyez le premier à laisser un avis !</p>
            <?php endif; ?>

            <!-- Formulaire d'avis (pour utilisateurs connectés) -->
            <?php if (!empty($_SESSION['user'])): ?>
            <div class="review-form-container">
                <h3><?= $userAvis ? 'Modifier votre avis' : 'Laisser un avis' ?></h3>
                <form method="POST" action="<?= htmlspecialchars($indexUrl) ?>?route=avis/<?= $userAvis ? 'update' : 'create' ?>" class="review-form">
                    <input type="hidden" name="idFilm" value="<?= htmlspecialchars($idFilmForView ?? $film->getId()) ?>">
                    <?php if ($userAvis): ?>
                    <input type="hidden" name="idAvis" value="<?= htmlspecialchars($userAvis['idAvis']) ?>">
                    <?php endif; ?>
                    
                    <div class="form-group">
                        <label for="note">Note (1 à 5 étoiles) :</label>
                        <div class="star-rating-input">
                            <?php for ($i = 5; $i >= 1; $i--): ?>
                            <input type="radio" name="note" id="note<?= $i ?>" value="<?= $i ?>" 
                                   <?= $userAvis && (int)$userAvis['note'] === $i ? 'checked' : '' ?> required>
                            <label for="note<?= $i ?>" class="star-label">★</label>
                            <?php endfor; ?>
                        </div>
                    </div>

                    <div class="form-group">
                        <label for="objet">Titre de l'avis (optionnel) :</label>
                        <input type="text" name="objet" id="objet" 
                               value="<?= $userAvis ? htmlspecialchars($userAvis['objet'] ?? '') : '' ?>" 
                               maxlength="100" placeholder="Ex: Excellent film !">
                    </div>

                    <div class="form-group">
                        <label for="text">Votre commentaire :</label>
                        <textarea name="text" id="text" rows="5" required 
                                  placeholder="Partagez votre avis sur ce film..."><?= $userAvis ? htmlspecialchars($userAvis['text'] ?? '') : '' ?></textarea>
                    </div>

                    <div class="form-actions">
                        <button type="submit" class="button main-button">
                            <?= $userAvis ? 'Modifier mon avis' : 'Publier mon avis' ?>
                        </button>
                        <?php if ($userAvis): ?>
                        <button type="button" class="button secondary-button" onclick="confirmDelete()">
                            Supprimer mon avis
                        </button>
                        <?php endif; ?>
                    </div>
                </form>

                <?php if ($userAvis): ?>
                <form method="POST" action="<?= htmlspecialchars($indexUrl) ?>?route=avis/delete" id="delete-form" style="display: none;">
                    <input type="hidden" name="idAvis" value="<?= htmlspecialchars($userAvis['idAvis']) ?>">
                    <input type="hidden" name="idFilm" value="<?= htmlspecialchars($idFilmForView ?? $film->getId()) ?>">
                </form>
                <?php endif; ?>
            </div>
            <?php endif; ?>

            <!-- Liste des avis -->
            <?php if (!empty($avis)): ?>
            <div class="reviews-list">
                <h3>Tous les avis <span class="review-count">(<?= count($avis) ?>)</span></h3>
                <?php foreach ($avis as $unAvis): ?>
                <article class="review-item">
                    <header class="review-header">
                        <div class="review-author">
                            <strong><?= htmlspecialchars($unAvis['pseudo']) ?></strong>
                        </div>
                        <div class="review-rating" aria-label="Note : <?= (int)$unAvis['note'] ?>/5">
                            <?php 
                            $note = (int)$unAvis['note'];
                            for ($i = 1; $i <= 5; $i++): 
                            ?>
                                <span class="star <?= $i <= $note ? 'filled' : '' ?>" aria-hidden="true">★</span>
                            <?php endfor; ?>
                            <span class="rating-value" aria-label="<?= $note ?> sur 5"><?= $note ?>/5</span>
                        </div>
                        <time class="review-date" datetime="<?= date('Y-m-d\TH:i:s', strtotime($unAvis['timestamp'])) ?>">
                            <?= date('d/m/Y à H:i', strtotime($unAvis['timestamp'])) ?>
                        </time>
                    </header>
                    <?php if (!empty($unAvis['objet'])): ?>
                    <h4 class="review-title"><?= htmlspecialchars($unAvis['objet']) ?></h4>
                    <?php endif; ?>
                    <div class="review-text"><?= nl2br(htmlspecialchars($unAvis['text'])) ?></div>
                </article>
                <?php endforeach; ?>
            </div>
            <?php endif; ?>
        </section>
    </main>

    <footer>
    </footer>
<?= renderFlashMessagesScript() ?>
<!-- Toastify.js JS -->
<script type="text/javascript" src="https://cdn.jsdelivr.net/npm/toastify-js"></script>
<script src="<?= htmlspecialchars($publicBase) ?>/js/flash-messages.js"></script>
<script src="<?= htmlspecialchars($publicBase) ?>/js/search.js"></script>
<script>
// Confirmation de suppression d'avis
function confirmDelete() {
    if (confirm('Êtes-vous sûr de vouloir supprimer votre avis ?')) {
        document.getElementById('delete-form').submit();
    }
}
</script>
</body>

</html>

