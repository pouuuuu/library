<?php
require_once __DIR__ . "/../model/AuthModel.php";
require_once __DIR__ . "/../model/User.php";
class AuthController
{

    private $authModel;

    public function __construct()
    {
        $this->authModel = new AuthModel();
    }

    public function login()
    {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            // Nettoyer les anciens messages d'erreur d'authentification AVANT le traitement
            // pour éviter qu'ils s'affichent après une connexion réussie
            if (isset($_SESSION['flash_messages'])) {
                $_SESSION['flash_messages'] = array_filter($_SESSION['flash_messages'], function($msg) {
                    return $msg['message'] !== "Vous devez être connecté pour accéder à cette page";
                });
                $_SESSION['flash_messages'] = array_values($_SESSION['flash_messages']);
            }

            $username = isset($_POST['username']) ? trim($_POST['username']) : '';
            $email = isset($_POST['email']) ? trim($_POST['email']) : '';
            $password = $_POST['password'] ?? '';

			try {
				$user = $this->authModel->login($username, $email, $password);
			} catch (Throwable $throwable) {
				setFlashError($throwable->getMessage());
				require __DIR__ . "/../view/auth/login.php";
				return;
			}

            if ($user) {
                // Récupérer l'URL de redirection AVANT de modifier la session
                $redirectParam = $_POST['redirect'] ?? $_GET['redirect'] ?? null;
                $redirectUrl = $redirectParam ?? $_SESSION['redirect_after_login'] ?? APP_INDEX_URL . '?route=home';
                
                // Nettoyer COMPLÈTEMENT tous les messages flash existants
                // (y compris le message "Vous devez être connecté" qui pourrait persister)
                $_SESSION['flash_messages'] = [];
                
                // Nettoyer la redirection sauvegardée
                unset($_SESSION['redirect_after_login']);
                
                // Définir l'utilisateur dans la session
                $userId = $user['idUser'] ?? $user['id'] ?? $user['idUtilisateur'] ?? null;
                $_SESSION['user'] = [
                    'id' => $userId,
                    'username' => $user['username'],
                    'email' => $user['email'],
                    'role' => $user['role'] ?? 'user'
                ];
                
                // Ajouter le message de succès
                setFlashSuccess("Vous êtes bien connecté");
                
                // Régénérer l'ID de session pour la sécurité (après avoir défini toutes les données)
                // Utiliser false pour garder les données de session
                session_regenerate_id(false);
                
                // Vérifier que la session est bien définie après régénération
                // Si ce n'est pas le cas, la redéfinir (sécurité supplémentaire)
                if (!isset($_SESSION['user']) || !isset($_SESSION['user']['id'])) {
                    $_SESSION['user'] = [
                        'id' => $userId,
                        'username' => $user['username'],
                        'email' => $user['email'],
                        'role' => $user['role'] ?? 'user'
                    ];
                }
                
                // S'assurer qu'aucun output n'a été envoyé avant la redirection
                if (ob_get_level() > 0) {
                    ob_clean();
                }
                
                // Rediriger vers l'URL demandée avec un code HTTP 302
                // La session sera automatiquement sauvegardée par PHP à la fin du script
                header('Location: ' . $redirectUrl, true, 302);
                exit;
            } else {
                setFlashError("Identifiants incorrects.");
                require __DIR__ . "/../view/auth/login.php";
            }
        } else {
            // En GET, nettoyer aussi les anciens messages d'erreur d'authentification
            if (isset($_SESSION['flash_messages'])) {
                $_SESSION['flash_messages'] = array_filter($_SESSION['flash_messages'], function($msg) {
                    return $msg['message'] !== "Vous devez être connecté pour accéder à cette page";
                });
                $_SESSION['flash_messages'] = array_values($_SESSION['flash_messages']);
            }
            require __DIR__ . "/../view/auth/login.php";
        }
    }
    public function signup() {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $username = isset($_POST['username']) ? trim($_POST['username']) : '';
            $email = isset($_POST['email']) ? trim($_POST['email']) : '';
            $password = $_POST['password'] ?? '';

			try {
				$userId = $this->authModel->signup($username, $email, $password);
				
				// Récupérer l'utilisateur créé pour la connexion automatique
				$user = $this->authModel->login($username, '', $password);
				
				if ($user) {
					// Nettoyer tous les messages flash
					$_SESSION['flash_messages'] = [];
					
					// Connecter automatiquement l'utilisateur
					$_SESSION['user'] = [
						'id' => $user['idUser'] ?? $user['id'] ?? $userId,
						'username' => $user['username'],
						'email' => $user['email'],
						'role' => $user['role'] ?? 'user'
					];
					
					setFlashSuccess("Inscription réussie ! Vous êtes maintenant connecté.");
					
					// Régénérer l'ID de session pour la sécurité (après avoir défini toutes les données)
					session_regenerate_id(false);
					
					// S'assurer qu'aucun output n'a été envoyé avant la redirection
					if (ob_get_level() > 0) {
						ob_clean();
					}
					
					// Rediriger vers la page d'accueil
					// La session sera automatiquement sauvegardée par PHP à la fin du script
					header('Location: ' . APP_INDEX_URL . '?route=home');
					exit;
				} else {
					// Si la connexion automatique échoue, rediriger vers login
					setFlashSuccess("Inscription réussie ! Vous pouvez maintenant vous connecter.");
					header('Location: ' . APP_INDEX_URL . '?route=auth/login');
					exit;
				}
			} catch (InvalidArgumentException $invalidArgumentException) {
				setFlashError($invalidArgumentException->getMessage());
				require __DIR__ . "/../view/auth/signup.php";
			} catch (RuntimeException $runtimeException) {
				setFlashError($runtimeException->getMessage());
				require __DIR__ . "/../view/auth/signup.php";
			} catch (Throwable $throwable) {
				setFlashError("Une erreur inattendue est survenue.");
				require __DIR__ . "/../view/auth/signup.php";
			}
        } else {
            require __DIR__ . "/../view/auth/signup.php";
        }
    }

    public function logout() {
        // Ajouter le message de déconnexion AVANT de nettoyer la session
        setFlashInfo("Déconnexion réussie");
        
        // Sauvegarder les flash messages (incluant le message de déconnexion)
        $flashMessages = $_SESSION['flash_messages'] ?? [];
        
        // Nettoyer toutes les données de session
        $_SESSION = [];
        
        // Restaurer uniquement les flash messages
        $_SESSION['flash_messages'] = $flashMessages;
        
        // Régénérer l'ID de session pour la sécurité (sans supprimer les données)
        session_regenerate_id(false);
        
        // Rediriger vers la page d'accueil au lieu de la page de connexion
        header('Location: ' . APP_INDEX_URL . '?route=home');
        exit;
    }
}

