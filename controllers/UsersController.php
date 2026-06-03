<?php
require_once __DIR__ . '/../includes/permissions.php';
require_once __DIR__ . '/../includes/Validator.php';
require_once __DIR__ . '/../includes/CsrfMiddleware.php';
require_once __DIR__ . '/../models/User.php';

class UsersController {
    private User $userModel;
    private PDO $db;

    public function __construct(PDO $db) {
        $this->db = $db;
        $this->userModel = new User($db);
    }

    /**
     * Vérifier accès admin
     */
    private function checkAdmin(): void {
        if (!isset($_SESSION['user_id'])) {
            header('Location: index.php?action=login');
            exit();
        }
        if ($_SESSION['role'] !== 'admin') {
            http_response_code(403);
            die("Accès refusé. Vous devez être admin.");
        }
    }

    /**
     * Lister tous les utilisateurs
     */
    public function index(): array {
        $this->checkAdmin();
        
        try {
            $users = $this->userModel->getAll();
        } catch (PDOException $e) {
            $users = [];
            $error = "Erreur lors du chargement des utilisateurs.";
        }
        
        return [
            'view' => 'users/index',
            'data' => [
                'users' => $users,
                'error' => $error ?? null,
                'csrf_field' => CsrfMiddleware::field()
            ]
        ];
    }

    /**
     * Créer un utilisateur
     */
    public function create($data): array {
        $this->checkAdmin();
        
        $error = null;
        $success = null;

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            try {
                CsrfMiddleware::verify();
            } catch (Exception $e) {
                return [
                    'view' => 'users/create',
                    'data' => [
                        'error' => $e->getMessage(),
                        'csrf_field' => CsrfMiddleware::field()
                    ]
                ];
            }

            // Validation
            $username = trim($data['username'] ?? '');
            $password = trim($data['password'] ?? '');
            $password_confirm = trim($data['password_confirm'] ?? '');
            $nom = trim($data['nom'] ?? '');
            $prenom = trim($data['prenom'] ?? '');
            $telephone = trim($data['telephone'] ?? '');
            $succursale = trim($data['succursale'] ?? '');
            $role = $data['role'] ?? 'user';
            $status = $data['status'] ?? 'actif';

            // Vérifications
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
            } elseif (!in_array($role, ['user', 'manager', 'admin'])) {
                $error = "Rôle invalide.";
            } elseif (!in_array($status, ['actif', 'inactif'])) {
                $error = "Statut invalide.";
            } else {
                try {
                    $userId = $this->userModel->create([
                        'username' => $username,
                        'password' => $password,
                        'nom' => $nom,
                        'prenom' => $prenom,
                        'telephone' => $telephone,
                        'succursale' => $succursale,
                        'role' => $role,
                        'status' => $status
                    ]);
                    $success = "Utilisateur créé avec succès ! ✅";
                    
                    // Log l'action
                    $this->logAction("Création utilisateur ID: $userId ($username)");
                } catch (PDOException $e) {
                    $error = "Erreur lors de la création : " . $e->getMessage();
                }
            }
        }

        return [
            'view' => 'users/create',
            'data' => [
                'error' => $error,
                'success' => $success,
                'csrf_field' => CsrfMiddleware::field()
            ]
        ];
    }

    /**
     * Éditer un utilisateur
     */
    public function edit(int $id, $data): array {
        $this->checkAdmin();
        
        $user = $this->userModel->findById($id);
        if (!$user) {
            http_response_code(404);
            die("Utilisateur non trouvé.");
        }

        $error = null;
        $success = null;

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            try {
                CsrfMiddleware::verify();
            } catch (Exception $e) {
                return [
                    'view' => 'users/edit',
                    'data' => [
                        'user' => $user,
                        'error' => $e->getMessage(),
                        'csrf_field' => CsrfMiddleware::field()
                    ]
                ];
            }

            $nom = trim($data['nom'] ?? '');
            $prenom = trim($data['prenom'] ?? '');
            $telephone = trim($data['telephone'] ?? '');
            $succursale = trim($data['succursale'] ?? '');
            $role = $data['role'] ?? 'user';
            $status = $data['status'] ?? 'actif';
            $password = trim($data['password'] ?? '');

            if (empty($nom) || empty($prenom) || empty($telephone) || empty($succursale)) {
                $error = "Tous les champs sont obligatoires.";
            } elseif ($password && strlen($password) < 6) {
                $error = "Le mot de passe doit contenir au moins 6 caractères.";
            } elseif (!in_array($role, ['user', 'manager', 'admin'])) {
                $error = "Rôle invalide.";
            } elseif (!in_array($status, ['actif', 'inactif'])) {
                $error = "Statut invalide.";
            } else {
                try {
                    $updateData = [
                        'nom' => $nom,
                        'prenom' => $prenom,
                        'telephone' => $telephone,
                        'succursale' => $succursale,
                        'role' => $role,
                        'status' => $status
                    ];
                    if (!empty($password)) {
                        $updateData['password'] = $password;
                    }
                    
                    $this->userModel->update($id, $updateData);
                    $success = "Utilisateur mis à jour avec succès ! ✅";
                    
                    // Log l'action
                    $this->logAction("Modification utilisateur ID: $id");
                    
                    $user = $this->userModel->findById($id);
                } catch (PDOException $e) {
                    $error = "Erreur lors de la mise à jour : " . $e->getMessage();
                }
            }
        }

        return [
            'view' => 'users/edit',
            'data' => [
                'user' => $user,
                'error' => $error,
                'success' => $success,
                'csrf_field' => CsrfMiddleware::field()
            ]
        ];
    }

    /**
     * Supprimer un utilisateur
     */
    public function delete(int $id): void {
        $this->checkAdmin();
        
        // Vérification de sécurité
        if ($id === $_SESSION['user_id']) {
            header('Location: index.php?action=users&error=Vous ne pouvez pas supprimer votre propre compte');
            exit();
        }

        if ($this->userModel->exists($id)) {
            try {
                $user = $this->userModel->findById($id);
                $this->userModel->delete($id);
                
                // Log l'action
                $this->logAction("Suppression utilisateur ID: $id (" . $user['username'] . ")");
                
                header('Location: index.php?action=users&message=Utilisateur supprimé avec succès');
            } catch (PDOException $e) {
                header('Location: index.php?action=users&error=Erreur lors de la suppression');
            }
        } else {
            header('Location: index.php?action=users&error=Utilisateur non trouvé');
        }
        exit();
    }

    /**
     * Déconnecter un utilisateur
     */
    public function disconnect(int $id): void {
        $this->checkAdmin();
        
        // Vérification de sécurité
        if ($id === $_SESSION['user_id']) {
            header('Location: index.php?action=users&error=Vous ne pouvez pas déconnecter votre propre session');
            exit();
        }

        try {
            if ($this->userModel->exists($id)) {
                $user = $this->userModel->findById($id);
                $this->userModel->clearSessionToken($id);
                
                // Log l'action
                $this->logAction("Déconnexion forcée utilisateur ID: $id (" . $user['username'] . ")");
                
                header('Location: index.php?action=users&message=Utilisateur déconnecté avec succès');
            } else {
                header('Location: index.php?action=users&error=Utilisateur non trouvé');
            }
        } catch (PDOException $e) {
            header('Location: index.php?action=users&error=Erreur lors de la déconnexion');
        }
        exit();
    }

    /**
     * Logger une action administrative
     */
    private function logAction(string $action): void {
        // Vous pouvez implémenter un vrai système de logs ici
        // Pour l'instant, on peut juste écrire dans un fichier de log
        $logFile = __DIR__ . '/../logs/admin.log';
        if (!is_dir(dirname($logFile))) {
            mkdir(dirname($logFile), 0755, true);
        }
        
        $timestamp = date('Y-m-d H:i:s');
        $adminUsername = $_SESSION['username'] ?? 'UNKNOWN';
        $logMessage = "[$timestamp] Admin: $adminUsername - $action\n";
        
        file_put_contents($logFile, $logMessage, FILE_APPEND);
    }
}