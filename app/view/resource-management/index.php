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
	<title>SAÉ - E-Library - Gestion des ressources</title>
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
				<?php if (($user['role'] ?? '') === 'admin'): ?>
					<li><a href="<?= htmlspecialchars($indexUrl) ?>?route=admin">Administration</a></li>
				<?php endif; ?>
				<li><a href="<?= htmlspecialchars($indexUrl) ?>?route=resources" class="active">Gestion des ressources</a></li>
			</ul>
			<ul>
				<li style="margin-right:10px; color:#fff;">Connecté en tant que <?= htmlspecialchars($user['username']) ?> (<?= htmlspecialchars($roleLabel) ?>)</li>
				<li><a href="<?= htmlspecialchars($indexUrl) ?>?route=auth/logout" class="button main-button">Se déconnecter</a></li>
			</ul>
		</nav>
	</header>
	<main class="admin">
		<h1>Gestion des ressources</h1>
		<p>Ajoutez, modifiez ou supprimez des livres et des films.</p>

		<!-- Section Gestion des Livres -->
		<section class="card">
			<h2>Gestion des Livres</h2>
			
			<h3>Ajouter un livre</h3>
			<form method="post" action="<?= htmlspecialchars($indexUrl) ?>?route=resources" class="add-form">
				<input type="hidden" name="action" value="create_book">
				
				<div>
					<label for="book_titre">Titre *</label>
					<input type="text" id="book_titre" name="titre" required>
				</div>

				<div>
					<label for="book_isbn">ISBN</label>
					<input type="text" id="book_isbn" name="isbn">
				</div>

				<div>
					<label for="book_idEditeur">Éditeur</label>
					<select id="book_idEditeur" name="idEditeur">
						<option value="">-- Aucun --</option>
						<?php foreach ($editors as $editor): ?>
							<option value="<?= (int)$editor['idEditeur'] ?>"><?= htmlspecialchars($editor['nomEditeur']) ?></option>
						<?php endforeach; ?>
					</select>
				</div>

				<div>
					<label for="book_anneePublication">Année de publication</label>
					<input type="number" id="book_anneePublication" name="anneePublication" min="1000" max="9999">
				</div>

				<div>
					<label for="book_prix">Prix</label>
					<input type="number" id="book_prix" name="prix" step="0.01" min="0">
				</div>

				<div>
					<label for="book_nbPages">Nombre de pages</label>
					<input type="number" id="book_nbPages" name="nbPages" min="1">
				</div>

				<div>
					<label for="book_edition">Édition</label>
					<input type="text" id="book_edition" name="edition">
				</div>

				<div>
					<label for="book_idLangue">Langue</label>
					<select id="book_idLangue" name="idLangue">
						<option value="">-- Aucune --</option>
						<?php foreach ($languages as $lang): ?>
							<option value="<?= (int)$lang['idLangue'] ?>"><?= htmlspecialchars($lang['nomLangue']) ?></option>
						<?php endforeach; ?>
					</select>
				</div>

				<div>
					<label for="book_couverture">URL de la couverture</label>
					<input type="url" id="book_couverture" name="couverture">
				</div>

				<div style="grid-column: 1 / -1;">
					<label for="book_description">Description</label>
					<textarea id="book_description" name="description" rows="4"></textarea>
				</div>

				<div style="grid-column: 1 / -1;">
					<button type="submit" class="button main-button">Créer le livre</button>
				</div>
			</form>

			<h3>Livres existants (<?= number_format($booksData['total'], 0, ',', ' ') ?> au total)</h3>
			
			<!-- Recherche de livres -->
			<form method="get" action="<?= htmlspecialchars($indexUrl) ?>?route=resources" class="search-form">
				<input type="hidden" name="route" value="resources">
				<input type="hidden" name="books_page" value="1">
				<input type="hidden" name="films_page" value="<?= $filmsData['page'] ?>">
				<input type="hidden" name="per_page" value="<?= $booksData['perPage'] ?>">
				<input type="hidden" name="films_search" value="<?= htmlspecialchars($filmsData['search'] ?? '') ?>">
				<input type="text" name="books_search" placeholder="Rechercher un livre (titre, ISBN)..." value="<?= htmlspecialchars($booksData['search'] ?? '') ?>">
				<button type="submit" class="button secondary-button">Rechercher</button>
				<?php if (!empty($booksData['search'])): ?>
					<a href="<?= htmlspecialchars($indexUrl) ?>?route=resources&books_page=1&films_page=<?= $filmsData['page'] ?>&per_page=<?= $booksData['perPage'] ?>&films_search=<?= htmlspecialchars($filmsData['search'] ?? '') ?>" class="button secondary-button">Effacer</a>
				<?php endif; ?>
			</form>
			
			<?php if (empty($booksData['items'])): ?>
				<p>Aucun livre trouvé<?= !empty($booksData['search']) ? ' pour "' . htmlspecialchars($booksData['search']) . '"' : '' ?>.</p>
			<?php else: ?>
				<table>
					<thead>
						<tr>
							<th>ID</th>
							<th>Titre</th>
							<th>ISBN</th>
							<th>Année</th>
							<th>Éditeur</th>
							<th>Actions</th>
						</tr>
					</thead>
					<tbody>
						<?php foreach ($booksData['items'] as $book): ?>
							<tr id="book-row-<?= (int)$book['id'] ?>">
								<td><?= (int)$book['id'] ?></td>
								<td><?= htmlspecialchars($book['title']) ?></td>
								<td><?= htmlspecialchars($book['isbn'] ?? '') ?></td>
								<td><?= $book['publishYear'] ?? '' ?></td>
								<td><?= htmlspecialchars($book['editor'] ?? '') ?></td>
								<td>
									<div class="action-buttons">
										<button onclick="toggleEditBook(<?= (int)$book['id'] ?>)" class="button secondary-button">Modifier</button>
										<form method="post" action="<?= htmlspecialchars($indexUrl) ?>?route=resources" class="inline-form" onsubmit="return confirm('Êtes-vous sûr de vouloir supprimer ce livre ?');">
											<input type="hidden" name="action" value="delete_book">
											<input type="hidden" name="book_id" value="<?= (int)$book['id'] ?>">
											<button type="submit" class="button secondary-button" style="background-color: rgba(220, 53, 69, 0.2); border-color: #dc3545; color: #ff6b7a;">Supprimer</button>
										</form>
									</div>
								</td>
							</tr>
							<tr id="book-edit-<?= (int)$book['id'] ?>" style="display: none;">
								<td colspan="6" style="max-width: 100%; overflow: visible;">
									<form method="post" action="<?= htmlspecialchars($indexUrl) ?>?route=resources" class="edit-form" style="width: 100%; max-width: 100%; box-sizing: border-box;">
										<input type="hidden" name="action" value="update_book">
										<input type="hidden" name="book_id" value="<?= (int)$book['id'] ?>">
										
										<div>
											<label for="edit_book_titre_<?= (int)$book['id'] ?>">Titre *</label>
											<input type="text" id="edit_book_titre_<?= (int)$book['id'] ?>" name="titre" value="<?= htmlspecialchars($book['title']) ?>" required>
										</div>
										
										<div>
											<label for="edit_book_isbn_<?= (int)$book['id'] ?>">ISBN</label>
											<input type="text" id="edit_book_isbn_<?= (int)$book['id'] ?>" name="isbn" value="<?= htmlspecialchars($book['isbn'] ?? '') ?>">
										</div>
										
										<div>
											<label for="edit_book_idEditeur_<?= (int)$book['id'] ?>">Éditeur</label>
											<select id="edit_book_idEditeur_<?= (int)$book['id'] ?>" name="idEditeur">
												<option value="">-- Aucun --</option>
												<?php foreach ($editors as $editor): ?>
													<option value="<?= (int)$editor['idEditeur'] ?>" <?= ($book['idEditeur'] ?? null) == $editor['idEditeur'] ? 'selected' : '' ?>><?= htmlspecialchars($editor['nomEditeur']) ?></option>
												<?php endforeach; ?>
											</select>
										</div>
										
										<div>
											<label for="edit_book_anneePublication_<?= (int)$book['id'] ?>">Année de publication</label>
											<input type="number" id="edit_book_anneePublication_<?= (int)$book['id'] ?>" name="anneePublication" value="<?= $book['publishYear'] ?? '' ?>" min="1000" max="9999">
										</div>
										
										<div>
											<label for="edit_book_prix_<?= (int)$book['id'] ?>">Prix</label>
											<input type="number" id="edit_book_prix_<?= (int)$book['id'] ?>" name="prix" value="<?= $book['price'] ?? '' ?>" step="0.01" min="0">
										</div>
										
										<div>
											<label for="edit_book_nbPages_<?= (int)$book['id'] ?>">Nombre de pages</label>
											<input type="number" id="edit_book_nbPages_<?= (int)$book['id'] ?>" name="nbPages" value="<?= $book['nbPages'] ?? '' ?>" min="1">
										</div>
										
										<div>
											<label for="edit_book_edition_<?= (int)$book['id'] ?>">Édition</label>
											<input type="text" id="edit_book_edition_<?= (int)$book['id'] ?>" name="edition" value="<?= htmlspecialchars($book['edition'] ?? '') ?>">
										</div>
										
										<div>
											<label for="edit_book_idLangue_<?= (int)$book['id'] ?>">Langue</label>
											<select id="edit_book_idLangue_<?= (int)$book['id'] ?>" name="idLangue">
												<option value="">-- Aucune --</option>
												<?php foreach ($languages as $lang): ?>
													<option value="<?= (int)$lang['idLangue'] ?>" <?= ($book['idLangue'] ?? null) == $lang['idLangue'] ? 'selected' : '' ?>><?= htmlspecialchars($lang['nomLangue']) ?></option>
												<?php endforeach; ?>
											</select>
										</div>
										
										<div>
											<label for="edit_book_couverture_<?= (int)$book['id'] ?>">URL de la couverture</label>
											<input type="url" id="edit_book_couverture_<?= (int)$book['id'] ?>" name="couverture" value="<?= htmlspecialchars($book['poster'] ?? '') ?>">
										</div>
										
										<div style="grid-column: 1 / -1;">
											<label for="edit_book_description_<?= (int)$book['id'] ?>">Description</label>
											<textarea id="edit_book_description_<?= (int)$book['id'] ?>" name="description" rows="4"><?= htmlspecialchars($book['description'] ?? '') ?></textarea>
										</div>
										
										<div class="form-actions">
											<button type="submit" class="button main-button">Mettre à jour</button>
											<button type="button" onclick="toggleEditBook(<?= (int)$book['id'] ?>)" class="button secondary-button">Annuler</button>
										</div>
									</form>
								</td>
							</tr>
						<?php endforeach; ?>
					</tbody>
				</table>
				
				<!-- Pagination pour les livres -->
				<?php if ($booksData['totalPages'] > 1): ?>
					<div class="pagination">
						<?php if ($booksData['page'] > 1): ?>
							<a href="<?= htmlspecialchars($indexUrl) ?>?route=resources&books_page=<?= $booksData['page'] - 1 ?>&films_page=<?= $filmsData['page'] ?>&per_page=<?= $booksData['perPage'] ?>&books_search=<?= urlencode($booksData['search'] ?? '') ?>&films_search=<?= urlencode($filmsData['search'] ?? '') ?>" class="button secondary-button">← Précédent</a>
						<?php endif; ?>
						
						<span>Page <?= $booksData['page'] ?> sur <?= $booksData['totalPages'] ?></span>
						
						<?php if ($booksData['page'] < $booksData['totalPages']): ?>
							<a href="<?= htmlspecialchars($indexUrl) ?>?route=resources&books_page=<?= $booksData['page'] + 1 ?>&films_page=<?= $filmsData['page'] ?>&per_page=<?= $booksData['perPage'] ?>&books_search=<?= urlencode($booksData['search'] ?? '') ?>&films_search=<?= urlencode($filmsData['search'] ?? '') ?>" class="button secondary-button">Suivant →</a>
						<?php endif; ?>
					</div>
				<?php endif; ?>
			<?php endif; ?>
		</section>

		<!-- Section Gestion des Films -->
		<section class="card">
			<h2>Gestion des Films</h2>
			
			<h3>Ajouter un film</h3>
			<form method="post" action="<?= htmlspecialchars($indexUrl) ?>?route=resources" class="add-form">
				<input type="hidden" name="action" value="create_film">
				
				<div>
					<label for="film_titre">Titre *</label>
					<input type="text" id="film_titre" name="titre" required>
				</div>

				<div>
					<label for="film_anneeProduction">Année de production</label>
					<input type="number" id="film_anneeProduction" name="anneeProduction" min="1000" max="9999">
				</div>

				<div>
					<label for="film_dateSortie">Date de sortie</label>
					<input type="date" id="film_dateSortie" name="dateSortie">
				</div>

				<div>
					<label for="film_duree">Durée (en minutes)</label>
					<input type="number" id="film_duree" name="duree" min="1">
				</div>

				<?php if (!empty($filmTypes)): ?>
				<div>
					<label for="film_idTypeFilm">Type de film</label>
					<select id="film_idTypeFilm" name="idTypeFilm">
						<option value="">-- Aucun --</option>
						<?php foreach ($filmTypes as $type): ?>
							<option value="<?= (int)$type['idTypeFilm'] ?>"><?= htmlspecialchars($type['nomTypeFilm']) ?></option>
						<?php endforeach; ?>
					</select>
				</div>
				<?php endif; ?>

				<div>
					<label for="film_bandeAnnonce">URL de la bande-annonce</label>
					<input type="url" id="film_bandeAnnonce" name="bandeAnnonce">
				</div>

				<div>
					<label for="film_poster">URL de l'affiche</label>
					<input type="url" id="film_poster" name="poster">
				</div>

				<div style="grid-column: 1 / -1;">
					<label for="film_synopsis">Synopsis</label>
					<textarea id="film_synopsis" name="synopsis" rows="4"></textarea>
				</div>

				<div style="grid-column: 1 / -1;">
					<button type="submit" class="button main-button">Créer le film</button>
				</div>
			</form>

			<h3>Films existants (<?= number_format($filmsData['total'], 0, ',', ' ') ?> au total)</h3>
			
			<!-- Recherche de films -->
			<form method="get" action="<?= htmlspecialchars($indexUrl) ?>?route=resources" class="search-form">
				<input type="hidden" name="route" value="resources">
				<input type="hidden" name="books_page" value="<?= $booksData['page'] ?>">
				<input type="hidden" name="films_page" value="1">
				<input type="hidden" name="per_page" value="<?= $filmsData['perPage'] ?>">
				<input type="hidden" name="books_search" value="<?= htmlspecialchars($booksData['search'] ?? '') ?>">
				<input type="text" name="films_search" placeholder="Rechercher un film (titre)..." value="<?= htmlspecialchars($filmsData['search'] ?? '') ?>">
				<button type="submit" class="button secondary-button">Rechercher</button>
				<?php if (!empty($filmsData['search'])): ?>
					<a href="<?= htmlspecialchars($indexUrl) ?>?route=resources&books_page=<?= $booksData['page'] ?>&films_page=1&per_page=<?= $filmsData['perPage'] ?>&books_search=<?= htmlspecialchars($booksData['search'] ?? '') ?>" class="button secondary-button">Effacer</a>
				<?php endif; ?>
			</form>
			
			<?php if (empty($filmsData['items'])): ?>
				<p>Aucun film trouvé<?= !empty($filmsData['search']) ? ' pour "' . htmlspecialchars($filmsData['search']) . '"' : '' ?>.</p>
			<?php else: ?>
				<table>
					<thead>
						<tr>
							<th>ID</th>
							<th>Titre</th>
							<th>Année</th>
							<th>Durée</th>
							<th>Actions</th>
						</tr>
					</thead>
					<tbody>
						<?php foreach ($filmsData['items'] as $film): ?>
							<tr id="film-row-<?= (int)$film['id'] ?>">
								<td><?= (int)$film['id'] ?></td>
								<td><?= htmlspecialchars($film['title']) ?></td>
								<td><?= $film['productionYear'] ?? '' ?></td>
								<td><?= $film['duration'] ? $film['duration'] . ' min' : '' ?></td>
								<td>
									<div class="action-buttons">
										<button onclick="toggleEditFilm(<?= (int)$film['id'] ?>)" class="button secondary-button">Modifier</button>
										<form method="post" action="<?= htmlspecialchars($indexUrl) ?>?route=resources" class="inline-form" onsubmit="return confirm('Êtes-vous sûr de vouloir supprimer ce film ?');">
											<input type="hidden" name="action" value="delete_film">
											<input type="hidden" name="film_id" value="<?= (int)$film['id'] ?>">
											<button type="submit" class="button secondary-button" style="background-color: rgba(220, 53, 69, 0.2); border-color: #dc3545; color: #ff6b7a;">Supprimer</button>
										</form>
									</div>
								</td>
							</tr>
							<tr id="film-edit-<?= (int)$film['id'] ?>" style="display: none;">
								<td colspan="5" style="max-width: 100%; overflow: visible;">
									<form method="post" action="<?= htmlspecialchars($indexUrl) ?>?route=resources" class="edit-form" style="width: 100%; max-width: 100%; box-sizing: border-box;">
										<input type="hidden" name="action" value="update_film">
										<input type="hidden" name="film_id" value="<?= (int)$film['id'] ?>">
										
										<div>
											<label for="edit_film_titre_<?= (int)$film['id'] ?>">Titre *</label>
											<input type="text" id="edit_film_titre_<?= (int)$film['id'] ?>" name="titre" value="<?= htmlspecialchars($film['title']) ?>" required>
										</div>
										
										<div>
											<label for="edit_film_anneeProduction_<?= (int)$film['id'] ?>">Année de production</label>
											<input type="number" id="edit_film_anneeProduction_<?= (int)$film['id'] ?>" name="anneeProduction" value="<?= $film['productionYear'] ?? '' ?>" min="1000" max="9999">
										</div>
										
										<div>
											<label for="edit_film_dateSortie_<?= (int)$film['id'] ?>">Date de sortie</label>
											<input type="date" id="edit_film_dateSortie_<?= (int)$film['id'] ?>" name="dateSortie" value="<?= $film['releaseDate'] ? date('Y-m-d', strtotime($film['releaseDate'])) : '' ?>">
										</div>
										
										<div>
											<label for="edit_film_duree_<?= (int)$film['id'] ?>">Durée (en minutes)</label>
											<input type="number" id="edit_film_duree_<?= (int)$film['id'] ?>" name="duree" value="<?= $film['duration'] ?? '' ?>" min="1">
										</div>
										
										<?php if (!empty($filmTypes)): ?>
										<div>
											<label for="edit_film_idTypeFilm_<?= (int)$film['id'] ?>">Type de film</label>
											<select id="edit_film_idTypeFilm_<?= (int)$film['id'] ?>" name="idTypeFilm">
												<option value="">-- Aucun --</option>
												<?php foreach ($filmTypes as $type): ?>
													<option value="<?= (int)$type['idTypeFilm'] ?>" <?= ($film['typeId'] ?? null) == $type['idTypeFilm'] ? 'selected' : '' ?>><?= htmlspecialchars($type['nomTypeFilm']) ?></option>
												<?php endforeach; ?>
											</select>
										</div>
										<?php endif; ?>
										
										<div>
											<label for="edit_film_bandeAnnonce_<?= (int)$film['id'] ?>">URL de la bande-annonce</label>
											<input type="url" id="edit_film_bandeAnnonce_<?= (int)$film['id'] ?>" name="bandeAnnonce" value="<?= htmlspecialchars($film['trailer'] ?? '') ?>">
										</div>
										
										<div>
											<label for="edit_film_poster_<?= (int)$film['id'] ?>">URL de l'affiche</label>
											<input type="url" id="edit_film_poster_<?= (int)$film['id'] ?>" name="poster" value="<?= htmlspecialchars($film['poster'] ?? '') ?>">
										</div>
										
										<div style="grid-column: 1 / -1;">
											<label for="edit_film_synopsis_<?= (int)$film['id'] ?>">Synopsis</label>
											<textarea id="edit_film_synopsis_<?= (int)$film['id'] ?>" name="synopsis" rows="4"><?= htmlspecialchars($film['synopsis'] ?? '') ?></textarea>
										</div>
										
										<div class="form-actions">
											<button type="submit" class="button main-button">Mettre à jour</button>
											<button type="button" onclick="toggleEditFilm(<?= (int)$film['id'] ?>)" class="button secondary-button">Annuler</button>
										</div>
									</form>
								</td>
							</tr>
						<?php endforeach; ?>
					</tbody>
				</table>
				
				<!-- Pagination pour les films -->
				<?php if ($filmsData['totalPages'] > 1): ?>
					<div class="pagination">
						<?php if ($filmsData['page'] > 1): ?>
							<a href="<?= htmlspecialchars($indexUrl) ?>?route=resources&books_page=<?= $booksData['page'] ?>&films_page=<?= $filmsData['page'] - 1 ?>&per_page=<?= $filmsData['perPage'] ?>&books_search=<?= urlencode($booksData['search'] ?? '') ?>&films_search=<?= urlencode($filmsData['search'] ?? '') ?>" class="button secondary-button">← Précédent</a>
						<?php endif; ?>
						
						<span>Page <?= $filmsData['page'] ?> sur <?= $filmsData['totalPages'] ?></span>
						
						<?php if ($filmsData['page'] < $filmsData['totalPages']): ?>
							<a href="<?= htmlspecialchars($indexUrl) ?>?route=resources&books_page=<?= $booksData['page'] ?>&films_page=<?= $filmsData['page'] + 1 ?>&per_page=<?= $filmsData['perPage'] ?>&books_search=<?= urlencode($booksData['search'] ?? '') ?>&films_search=<?= urlencode($filmsData['search'] ?? '') ?>" class="button secondary-button">Suivant →</a>
						<?php endif; ?>
					</div>
				<?php endif; ?>
			<?php endif; ?>
		</section>
	</main>
<?= renderFlashMessagesScript() ?>
<!-- Toastify.js JS -->
<script type="text/javascript" src="https://cdn.jsdelivr.net/npm/toastify-js"></script>
<script src="<?= htmlspecialchars($publicBase) ?>/js/flash-messages.js"></script>
<script>
function toggleEditBook(bookId) {
	const row = document.getElementById('book-row-' + bookId);
	const editRow = document.getElementById('book-edit-' + bookId);
	
	if (editRow.style.display === 'none') {
		editRow.style.display = 'table-row';
		row.style.display = 'none';
		editRow.scrollIntoView({ behavior: 'smooth', block: 'nearest' });
	} else {
		editRow.style.display = 'none';
		row.style.display = 'table-row';
	}
}

function toggleEditFilm(filmId) {
	const row = document.getElementById('film-row-' + filmId);
	const editRow = document.getElementById('film-edit-' + filmId);
	
	if (editRow.style.display === 'none') {
		editRow.style.display = 'table-row';
		row.style.display = 'none';
		editRow.scrollIntoView({ behavior: 'smooth', block: 'nearest' });
	} else {
		editRow.style.display = 'none';
		row.style.display = 'table-row';
	}
}
</script>
</body>

</html>

