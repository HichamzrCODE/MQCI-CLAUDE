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
}