<?php
class AuthModel {
	private const ROLE_USER = 'user';
	private const ROLE_LIBRARIAN = 'bibliothecaire';
	private const ROLE_ADMIN = 'admin';

	private const ALLOWED_ROLES = [
		self::ROLE_USER,
		self::ROLE_LIBRARIAN,
		self::ROLE_ADMIN,
	];

	private $pdo;

	public function __construct() {
		global $pdo;
		require_once __DIR__ . "/../sqlconnect.php";
		$this->pdo = $pdo;
	}

	public function login($username, $email, $password) {
		$username = $username !== null ? trim($username) : '';
		$email = $email !== null ? trim($email) : '';
		$password = $password ?? '';

		if ($username === '' && $email === '') {
			throw new Exception("Email ou nom d'utilisateur requis");
		}

		if ($username === '') {
			$sql = "SELECT * FROM Utilisateurs WHERE email = :email";
			$stmt = $this->pdo->prepare($sql);
			$stmt->execute(array("email" => $email));
		} else {
			$sql = "SELECT * FROM Utilisateurs WHERE username = :username";
			$stmt = $this->pdo->prepare($sql);
			$stmt->execute(array("username" => $username));
		}
		$user = $stmt->fetch(PDO::FETCH_ASSOC);

		if ($user && password_verify($password, $user['password_hash'])) {
			return $user;
		}
		return false;
	}

	public function signup($username, $email, $password) {
		return $this->createUser($username, $email, $password, self::ROLE_USER);
	}

	public function createUser($username, $email, $password, $role) {
		$username = trim((string) $username);
		$email = trim((string) $email);
		$role = trim((string) $role);

		if ($username === '' || $email === '' || $password === '') {
			throw new InvalidArgumentException("Les champs nom d'utilisateur, email et mot de passe sont requis.");
		}

		if (!in_array($role, self::ALLOWED_ROLES, true)) {
			throw new InvalidArgumentException("Rôle invalide fourni.");
		}

		$sql = "SELECT COUNT(*) FROM Utilisateurs WHERE username = :username OR email = :email";
		$stmt = $this->pdo->prepare($sql);
		$stmt->execute(array("username" => $username, "email" => $email));

		if ($stmt->fetchColumn() > 0) {
			throw new RuntimeException("Nom d'utilisateur ou email déjà utilisé.");
		}

		$hashedPassword = password_hash($password, PASSWORD_DEFAULT);
		$sql = "INSERT INTO Utilisateurs (username, email, password_hash, role) VALUES (:username, :email, :hashed_password, :role)";
		$stmt = $this->pdo->prepare($sql);

		$stmt->execute(array(
			"username" => $username,
			"email" => $email,
			"hashed_password" => $hashedPassword,
			"role" => $role
		));

		return (int) $this->pdo->lastInsertId();
	}

	public function getAllUsers(): array {
		$sql = "SELECT idUtilisateur, username, email, role FROM Utilisateurs ORDER BY username";
		$stmt = $this->pdo->query($sql);
		return $stmt->fetchAll(PDO::FETCH_ASSOC) ?: [];
	}

	public function updateUserRole(int $userId, string $role): bool {
		if (!in_array($role, self::ALLOWED_ROLES, true)) {
			throw new InvalidArgumentException("Rôle invalide fourni.");
		}

		$sql = "UPDATE Utilisateurs SET role = :role WHERE idUtilisateur = :id";
		$stmt = $this->pdo->prepare($sql);
		return $stmt->execute(array("role" => $role, "id" => $userId));
	}

	public static function getAllowedRoles(): array {
		return self::ALLOWED_ROLES;
	}
}