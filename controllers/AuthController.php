<<<<<<< HEAD
<?php
require_once __DIR__ . '/../includes/permissions.php';
require_once __DIR__ . '/../models/User.php';

class AuthController {
    private $userModel;

    public function __construct(PDO $db) {
        $this->userModel = new User($db);
    }

   public function login(array $data): ?array {

    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        $username = trim($data['username'] ?? '');
        $password = trim($data['password'] ?? '');

        if (empty($username) || empty($password)) {
            return ['view' => 'auth/login', 'data' => ['error' => "Veuillez entrer votre nom d'utilisateur et votre mot de passe."]];
        }

        $user = $this->userModel->findByUsername($username);

        if ($user && password_verify($password, $user['password'])) {
            // Authentification réussie
            $token = bin2hex(random_bytes(16));
            $_SESSION['user_id'] = (int)$user['id_users'];
            $_SESSION['username'] = $user['username'];
            $_SESSION['role'] = $user['role'];
            $_SESSION['session_token'] = $token;

            // Met à jour le token en base
            $this->userModel->setSessionToken($user['id_users'], $token);

            header('Location: index.php?action=home');
            exit();
        } else {
            return ['view' => 'auth/login', 'data' => ['error' => "Nom d'utilisateur ou mot de passe incorrect."]];
        }
    }
    return ['view' => 'auth/login'];
}

public function logout(): void {
    session_start();
    if (isset($_SESSION['user_id'])) {
        $this->userModel->setSessionToken($_SESSION['user_id'], null);
    }
    session_destroy();
    header('Location: index.php?action=login');
    exit();
}



=======
<?php
require_once __DIR__ . '/../includes/permissions.php';
require_once __DIR__ . '/../models/User.php';

class AuthController {
    private $userModel;

    public function __construct(PDO $db) {
        $this->userModel = new User($db);
    }

   public function login(array $data): ?array {

    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        $username = trim($data['username'] ?? '');
        $password = trim($data['password'] ?? '');

        if (empty($username) || empty($password)) {
            return ['view' => 'auth/login', 'data' => ['error' => "Veuillez entrer votre nom d'utilisateur et votre mot de passe."]];
        }

        $user = $this->userModel->findByUsername($username);

        if ($user && password_verify($password, $user['password'])) {
            // Authentification réussie
            $token = bin2hex(random_bytes(16));
            $_SESSION['user_id'] = (int)$user['id_users'];
            $_SESSION['username'] = $user['username'];
            $_SESSION['role'] = $user['role'];
            $_SESSION['session_token'] = $token;

            // Met à jour le token en base
            $this->userModel->setSessionToken($user['id_users'], $token);

            header('Location: index.php?action=home');
            exit();
        } else {
            return ['view' => 'auth/login', 'data' => ['error' => "Nom d'utilisateur ou mot de passe incorrect."]];
        }
    }
    return ['view' => 'auth/login'];
}

public function logout(): void {
    session_start();
    if (isset($_SESSION['user_id'])) {
        $this->userModel->setSessionToken($_SESSION['user_id'], null);
    }
    session_destroy();
    header('Location: index.php?action=login');
    exit();
}



>>>>>>> 4f8dbbb6b83eb9c6f755d57287033c7da885a3b1
}