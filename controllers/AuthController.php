<?php
require_once __DIR__ . '/../includes/permissions.php';
require_once __DIR__ . '/../includes/CsrfMiddleware.php';
require_once __DIR__ . '/../models/User.php';

class AuthController {
    private User $userModel;
    private PDO $db;

    public function __construct(PDO $db) {
        $this->db = $db;
        $this->userModel = new User($db);
    }

    /**
     * Afficher et traiter la page de login
     */
    public function login(array $data): array {
    // Si déjà connecté, rediriger
    if (isset($_SESSION['user_id'])) {
        header('Location: index.php?action=home');
        exit();
    }

    // ✅ S'assurer que le CSRF token existe (au cas où)
    if (empty($_SESSION['csrf_token'])) {
        CsrfMiddleware::regenerate();
    }

    $error = null;
    $success = null;

    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        try {
            CsrfMiddleware::verify();
        } catch (Exception $e) {
            return [
                'view' => 'auth/login',
                'data' => [
                    'error' => $e->getMessage(),
                    'csrf_field' => CsrfMiddleware::field()
                ]
            ];
        }

        $username = trim($data['username'] ?? '');
        $password = trim($data['password'] ?? '');

        // Validation
        if (empty($username)) {
            $error = "Veuillez entrer votre nom d'utilisateur.";
        } elseif (empty($password)) {
            $error = "Veuillez entrer votre mot de passe.";
        } elseif (strlen($username) < 3) {
            $error = "Le nom d'utilisateur est invalide.";
        } else {
            // Chercher l'utilisateur
            $user = $this->userModel->findByUsername($username);

            if ($user && password_verify($password, $user['password'])) {
                // ✅ Authentification réussie
                $this->createSession($user);
                
                // ✅ Regénérer le token CSRF après connexion
                CsrfMiddleware::regenerate();
                
                header('Location: index.php?action=home');
                exit();
            } else {
                // Sécurité : délai pour éviter brute force
                sleep(1);
                $error = "Nom d'utilisateur ou mot de passe incorrect.";
            }
        }
    }

    // Message de succès (après inscription par exemple)
    if (isset($_GET['message']) && $_GET['message'] === 'registered') {
        $success = "Inscription réussie ! Veuillez vous connecter.";
    }

    return [
        'view' => 'auth/login',
        'data' => [
            'error' => $error,
            'success' => $success,
            'csrf_field' => CsrfMiddleware::field()
        ]
    ];
}

    /**
     * Créer une session utilisateur avec session_token en BD
     */
    private function createSession(array $user): void {
        // ✅ Générer un token pour la BD
        $dbToken = bin2hex(random_bytes(32));
        
        // ✅ Créer la session PHP
        $_SESSION['user_id'] = (int)$user['id_users'];
        $_SESSION['username'] = $user['username'];
        $_SESSION['role'] = $user['role'];
        $_SESSION['session_token'] = $dbToken;
        $_SESSION['nom'] = $user['nom'];
        $_SESSION['prenom'] = $user['prenom'];

        // ✅ Sauvegarder en base
        try {
            $this->userModel->setSessionToken($user['id_users'], $dbToken);
        } catch (PDOException $e) {
            error_log("Erreur setSessionToken: " . $e->getMessage());
        }
    }

    /**
     * Déconnexion
     * ✅ CLEF : Bien gérer session_token ET CSRF token
     */
public function logout(): void {
    $userId = $_SESSION['user_id'] ?? null;

    // ✅ ÉTAPE 1 : Effacer le session_token en BD
    if ($userId) {
        try {
            $this->userModel->clearSessionToken($userId);
        } catch (PDOException $e) {
            error_log("Erreur clearSessionToken: " . $e->getMessage());
        }
    }

    // ✅ ÉTAPE 2 : Détruire complètement la session
    session_destroy();

    // ✅ ÉTAPE 3 : Rediriger DIRECTEMENT sans toucher à la session
    // Le navigateur supprimera le cookie de session automatiquement
    header('Location: index.php?action=login');
    exit();
}

    /**
     * Page de register (inscription)
     */
    public function register(array $data): array {
        // Si déjà connecté, rediriger
        if (isset($_SESSION['user_id'])) {
            header('Location: index.php?action=home');
            exit();
        }

        $error = null;
        $success = null;

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            try {
                CsrfMiddleware::verify();
            } catch (Exception $e) {
                return [
                    'view' => 'auth/register',
                    'data' => [
                        'error' => $e->getMessage(),
                        'csrf_field' => CsrfMiddleware::field()
                    ]
                ];
            }

            $username = trim($data['username'] ?? '');
            $password = trim($data['password'] ?? '');
            $password_confirm = trim($data['password_confirm'] ?? '');
            $nom = trim($data['nom'] ?? '');
            $prenom = trim($data['prenom'] ?? '');
            $telephone = trim($data['telephone'] ?? '');
            $succursale = trim($data['succursale'] ?? '');

            // Validation
            if (empty($username) || empty($password) || empty($nom) || empty($prenom) || 
                empty($telephone)) {
                $error = "Tous les champs sont obligatoires.";
            } elseif (strlen($username) < 3) {
                $error = "Le nom d'utilisateur doit contenir au moins 3 caractères.";
            } elseif (strlen($password) < 6) {
                $error = "Le mot de passe doit contenir au moins 6 caractères.";
            } elseif ($password !== $password_confirm) {
                $error = "Les mots de passe ne correspondent pas.";
            } elseif ($this->userModel->usernameExists($username)) {
                $error = "Ce nom d'utilisateur existe déjà.";
            } else {
                try {
                    $this->userModel->create([
                        'username' => $username,
                        'password' => $password,
                        'nom' => $nom,
                        'prenom' => $prenom,
                        'telephone' => $telephone,
                        'succursale' => $succursale,
                        'role' => 'user',
                        'status' => 'actif'
                    ]);

                    // Rediriger vers login avec message
                    header('Location: index.php?action=login&message=registered');
                    exit();
                } catch (PDOException $e) {
                    $error = "Erreur lors de l'inscription. Veuillez réessayer.";
                }
            }
        }

        return [
            'view' => 'auth/register',
            'data' => [
                'error' => $error,
                'success' => $success,
                'csrf_field' => CsrfMiddleware::field()
            ]
        ];
    }
}
?>