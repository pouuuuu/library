<?php
try {
	$pdo = new PDO("mysql:host=linserv-info-01.campus.unice.fr; dbname=pm304770_e-library", "pm304770", "pm304770");
} catch (PDOException $e) {
	echo("Erreur connexion" . $e->getMessage());
	exit();
}
