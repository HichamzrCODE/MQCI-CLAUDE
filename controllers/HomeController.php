<<<<<<< HEAD
<?php
require_once __DIR__ . '\..\includes\permissions.php';
class HomeController {
    private $db;

    public function __construct(PDO $db) {
        $this->db = $db;
    }

    public function index(): array {
        // Vérification de l'authentification
        if (!isset($_SESSION['user_id'])) {
            header('Location: index.php?action=login');
            exit();
        }

        return ['view' => 'home/index', 'data' => ['username' => $_SESSION['username']]];
    }
=======
<?php
require_once __DIR__ . '\..\includes\permissions.php';
class HomeController {
    private $db;

    public function __construct(PDO $db) {
        $this->db = $db;
    }

    public function index(): array {
        // Vérification de l'authentification
        if (!isset($_SESSION['user_id'])) {
            header('Location: index.php?action=login');
            exit();
        }

        return ['view' => 'home/index', 'data' => ['username' => $_SESSION['username']]];
    }
>>>>>>> 4f8dbbb6b83eb9c6f755d57287033c7da885a3b1
}