<?php
require_once __DIR__ . '/../includes/permissions.php';
require_once __DIR__ . '/../includes/CsrfMiddleware.php';
require_once __DIR__ . '/../models/User.php';

class UsersController {
    private User $userModel;
    private PDO  $db;

    public function __construct(PDO $db) {
        $this->db        = $db;
        $this->userModel = new User($db);
    }

    private function checkAdmin(): void {
        if (!isset($_SESSION['user_id'])) {
            header('Location: index.php?action=login');
            exit();
        }
        if ($_SESSION['role'] !== 'admin') {
            http_response_code(403);
            die("Accès refusé.");
        }
    }

    public function index(): array {
        $this->checkAdmin();

        $users = $this->userModel->getAll();
        $message = $_GET['message'] ?? null;
        $error   = $_GET['error']   ?? null;

        return [
            'view' => 'users/index',
            'data' => [
                'users'      => $users,
                'message'    => $message,
                'error'      => $error,
                'csrf_field' => CsrfMiddleware::field(),
            ]
        ];
    }

    public function create(array $data): array {
        $this->checkAdmin();

        $error   = null;
        $success = null;

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            try {
                CsrfMiddleware::verify();

                $username        = trim($data['username']         ?? '');
                $password        = trim($data['password']         ?? '');
                $password_confirm = trim($data['password_confirm'] ?? '');
                $nom             = trim($data['nom']              ?? '');
                $prenom          = trim($data['prenom']           ?? '');
                $telephone       = trim($data['telephone']        ?? '');
                $succursale      = trim($data['succursale']       ?? '');
                $role            = $data['role']   ?? 'user';
                $status          = $data['status'] ?? 'actif';

                if (empty($username) || empty($password) || empty($nom) || empty($prenom) || empty($telephone)) {
                    $error = "Tous les champs obligatoires doivent être remplis.";
                } elseif (strlen($username) < 3) {
                    $error = "Le nom d'utilisateur doit contenir au moins 3 caractères.";
                } elseif (strlen($password) < 6) {
                    $error = "Le mot de passe doit contenir au moins 6 caractères.";
                } elseif ($password !== $password_confirm) {
                    $error = "Les mots de passe ne correspondent pas.";
                } elseif ($this->userModel->usernameExists($username)) {
                    $error = "Ce nom d'utilisateur existe déjà.";
                } elseif (!in_array($role, ['user', 'manager', 'admin'], true)) {
                    $error = "Rôle invalide.";
                } elseif (!in_array($status, ['actif', 'inactif'], true)) {
                    $error = "Statut invalide.";
                } else {
                    $userId = $this->userModel->create([
                        'username'   => $username,
                        'password'   => $password,
                        'nom'        => $nom,
                        'prenom'     => $prenom,
                        'telephone'  => $telephone,
                        'succursale' => $succursale,
                        'role'       => $role,
                        'status'     => $status,
                    ]);
                    $success = "Utilisateur créé avec succès !";
                    $this->log("Création utilisateur #$userId ($username)");
                }

            } catch (Exception $e) {
                $error = $e->getMessage();
            }
        }

        return [
            'view' => 'users/create',
            'data' => [
                'error'      => $error,
                'success'    => $success,
                'csrf_field' => CsrfMiddleware::field(),
            ]
        ];
    }

    public function edit(int $id, array $data): array {
        $this->checkAdmin();

        $user = $this->userModel->findById($id);
        if (!$user) {
            http_response_code(404);
            die("Utilisateur non trouvé.");
        }

        $error   = null;
        $success = null;

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            try {
                CsrfMiddleware::verify();

                $nom        = trim($data['nom']        ?? '');
                $prenom     = trim($data['prenom']     ?? '');
                $telephone  = trim($data['telephone']  ?? '');
                $succursale = trim($data['succursale'] ?? '');
                $role       = $data['role']   ?? 'user';
                $status     = $data['status'] ?? 'actif';
                $password   = trim($data['password']   ?? '');

                if (empty($nom) || empty($prenom) || empty($telephone)) {
                    $error = "Nom, prénom et téléphone sont obligatoires.";
                } elseif ($password && strlen($password) < 6) {
                    $error = "Le mot de passe doit contenir au moins 6 caractères.";
                } elseif (!in_array($role, ['user', 'manager', 'admin'], true)) {
                    $error = "Rôle invalide.";
                } elseif (!in_array($status, ['actif', 'inactif'], true)) {
                    $error = "Statut invalide.";
                } else {
                    $updateData = compact('nom', 'prenom', 'telephone', 'succursale', 'role', 'status');
                    if ($password) $updateData['password'] = $password;

                    $this->userModel->update($id, $updateData);
                    $success = "Utilisateur mis à jour avec succès !";
                    $this->log("Modification utilisateur #$id");
                    $user = $this->userModel->findById($id);
                }

            } catch (Exception $e) {
                $error = $e->getMessage();
            }
        }

        return [
            'view' => 'users/edit',
            'data' => [
                'user'       => $user,
                'error'      => $error,
                'success'    => $success,
                'csrf_field' => CsrfMiddleware::field(),
            ]
        ];
    }

    public function delete(int $id): void {
        $this->checkAdmin();

        if ($id === (int)$_SESSION['user_id']) {
            header('Location: index.php?action=users&error=Vous ne pouvez pas supprimer votre propre compte');
            exit();
        }

        if ($this->userModel->exists($id)) {
            $user = $this->userModel->findById($id);
            $this->userModel->delete($id);
            $this->log("Suppression utilisateur #$id (" . $user['username'] . ")");
            header('Location: index.php?action=users&message=Utilisateur supprimé avec succès');
        } else {
            header('Location: index.php?action=users&error=Utilisateur non trouvé');
        }
        exit();
    }

    public function disconnect(int $id): void {
        $this->checkAdmin();

        if ($id === (int)$_SESSION['user_id']) {
            header('Location: index.php?action=users&error=Vous ne pouvez pas vous déconnecter vous-même');
            exit();
        }

        if ($this->userModel->exists($id)) {
            $user = $this->userModel->findById($id);
            $this->userModel->clearSessionToken($id);
            $this->log("Déconnexion forcée #$id (" . $user['username'] . ")");
            header('Location: index.php?action=users&message=Utilisateur déconnecté avec succès');
        } else {
            header('Location: index.php?action=users&error=Utilisateur non trouvé');
        }
        exit();
    }

    private function log(string $action): void {
        $logDir  = __DIR__ . '/../logs';
        $logFile = $logDir . '/admin.log';

        if (!is_dir($logDir)) {
            mkdir($logDir, 0755, true);
        }

        $line = sprintf(
            "[%s] Admin: %s — %s\n",
            date('Y-m-d H:i:s'),
            $_SESSION['username'] ?? 'UNKNOWN',
            $action
        );

        file_put_contents($logFile, $line, FILE_APPEND | LOCK_EX);
    }
}
