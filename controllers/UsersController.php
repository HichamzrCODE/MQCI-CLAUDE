<<<<<<< HEAD
<?php
class UsersController {
    private $db;
    public function __construct(PDO $db) {
        $this->db = $db;
    }

    public function index() {
        $users = $this->db->query("SELECT * FROM users")->fetchAll(PDO::FETCH_ASSOC);
        return ['view' => 'users/index', 'data' => ['users' => $users]];
    }

    public function create($data) {
        $error = $success = '';
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $username   = trim($data['username'] ?? '');
            $nom        = trim($data['nom'] ?? '');
            $prenom     = trim($data['prenom'] ?? '');
            $telephone  = trim($data['telephone'] ?? '');
            $succursale = trim($data['succursale'] ?? '');
            $role       = $data['role'] ?? 'user';
            $password   = $data['password'] ?? '';

            if (!$username || !$password || !$nom || !$prenom || !$telephone || !$succursale || !in_array($role, ['user', 'manager'])) {
                $error = "Tous les champs sont obligatoires.";
            } else {
                $stmt = $this->db->prepare("SELECT COUNT(*) FROM users WHERE username=?");
                $stmt->execute([$username]);
                if ($stmt->fetchColumn() > 0) {
                    $error = "Ce nom d'utilisateur existe déjà.";
                } else {
                    $hash = password_hash($password, PASSWORD_BCRYPT);
                    $stmt = $this->db->prepare("INSERT INTO users (username, password, role, nom, prenom, telephone, succursale, created_at) VALUES (?, ?, ?, ?, ?, ?, ?, NOW())");
                    $stmt->execute([$username, $hash, $role, $nom, $prenom, $telephone, $succursale]);
                    $success = "Utilisateur créé avec succès.";
                }
            }
        }
        return ['view' => 'users/create', 'data' => compact('error', 'success')];
    }

    public function edit($id, $data) {
        $error = $success = '';
        $stmt = $this->db->prepare("SELECT * FROM users WHERE id_users = ?");
        $stmt->execute([$id]);
        $user = $stmt->fetch(PDO::FETCH_ASSOC);
        if (!$user) die('Utilisateur introuvable.');

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $nom        = trim($data['nom'] ?? '');
            $prenom     = trim($data['prenom'] ?? '');
            $telephone  = trim($data['telephone'] ?? '');
            $succursale = trim($data['succursale'] ?? '');
            $role       = $data['role'] ?? 'user';
            $password   = $data['password'] ?? '';

            if (!$nom || !$prenom || !$telephone || !$succursale || !in_array($role, ['user', 'manager'])) {
                $error = "Tous les champs sont obligatoires.";
            } else {
                if ($password) {
                    $hash = password_hash($password, PASSWORD_BCRYPT);
                    $this->db->prepare("UPDATE users SET nom=?, prenom=?, telephone=?, succursale=?, role=?, password=? WHERE id_users=?")
                        ->execute([$nom, $prenom, $telephone, $succursale, $role, $hash, $id]);
                } else {
                    $this->db->prepare("UPDATE users SET nom=?, prenom=?, telephone=?, succursale=?, role=? WHERE id_users=?")
                        ->execute([$nom, $prenom, $telephone, $succursale, $role, $id]);
                }
                $success = "Utilisateur modifié.";
                // Recharger l'utilisateur
                $stmt->execute([$id]);
                $user = $stmt->fetch(PDO::FETCH_ASSOC);
            }
        }
        return ['view' => 'users/edit', 'data' => compact('user', 'error', 'success')];
    }

    public function delete($id) {
        $this->db->prepare("DELETE FROM users WHERE id_users=?")->execute([$id]);
        header('Location: index.php?action=users');
        exit();
    }

    public function disconnect($id) {
    $this->db->prepare("UPDATE users SET session_token = NULL WHERE id_users = ?")->execute([$id]);
    header('Location: index.php?action=users');
    exit();
}

    
=======
<?php
class UsersController {
    private $db;
    public function __construct(PDO $db) {
        $this->db = $db;
    }

    public function index() {
        $users = $this->db->query("SELECT * FROM users")->fetchAll(PDO::FETCH_ASSOC);
        return ['view' => 'users/index', 'data' => ['users' => $users]];
    }

    public function create($data) {
        $error = $success = '';
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $username   = trim($data['username'] ?? '');
            $nom        = trim($data['nom'] ?? '');
            $prenom     = trim($data['prenom'] ?? '');
            $telephone  = trim($data['telephone'] ?? '');
            $succursale = trim($data['succursale'] ?? '');
            $role       = $data['role'] ?? 'user';
            $password   = $data['password'] ?? '';

            if (!$username || !$password || !$nom || !$prenom || !$telephone || !$succursale || !in_array($role, ['user', 'manager'])) {
                $error = "Tous les champs sont obligatoires.";
            } else {
                $stmt = $this->db->prepare("SELECT COUNT(*) FROM users WHERE username=?");
                $stmt->execute([$username]);
                if ($stmt->fetchColumn() > 0) {
                    $error = "Ce nom d'utilisateur existe déjà.";
                } else {
                    $hash = password_hash($password, PASSWORD_BCRYPT);
                    $stmt = $this->db->prepare("INSERT INTO users (username, password, role, nom, prenom, telephone, succursale, created_at) VALUES (?, ?, ?, ?, ?, ?, ?, NOW())");
                    $stmt->execute([$username, $hash, $role, $nom, $prenom, $telephone, $succursale]);
                    $success = "Utilisateur créé avec succès.";
                }
            }
        }
        return ['view' => 'users/create', 'data' => compact('error', 'success')];
    }

    public function edit($id, $data) {
        $error = $success = '';
        $stmt = $this->db->prepare("SELECT * FROM users WHERE id_users = ?");
        $stmt->execute([$id]);
        $user = $stmt->fetch(PDO::FETCH_ASSOC);
        if (!$user) die('Utilisateur introuvable.');

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $nom        = trim($data['nom'] ?? '');
            $prenom     = trim($data['prenom'] ?? '');
            $telephone  = trim($data['telephone'] ?? '');
            $succursale = trim($data['succursale'] ?? '');
            $role       = $data['role'] ?? 'user';
            $password   = $data['password'] ?? '';

            if (!$nom || !$prenom || !$telephone || !$succursale || !in_array($role, ['user', 'manager'])) {
                $error = "Tous les champs sont obligatoires.";
            } else {
                if ($password) {
                    $hash = password_hash($password, PASSWORD_BCRYPT);
                    $this->db->prepare("UPDATE users SET nom=?, prenom=?, telephone=?, succursale=?, role=?, password=? WHERE id_users=?")
                        ->execute([$nom, $prenom, $telephone, $succursale, $role, $hash, $id]);
                } else {
                    $this->db->prepare("UPDATE users SET nom=?, prenom=?, telephone=?, succursale=?, role=? WHERE id_users=?")
                        ->execute([$nom, $prenom, $telephone, $succursale, $role, $id]);
                }
                $success = "Utilisateur modifié.";
                // Recharger l'utilisateur
                $stmt->execute([$id]);
                $user = $stmt->fetch(PDO::FETCH_ASSOC);
            }
        }
        return ['view' => 'users/edit', 'data' => compact('user', 'error', 'success')];
    }

    public function delete($id) {
        $this->db->prepare("DELETE FROM users WHERE id_users=?")->execute([$id]);
        header('Location: index.php?action=users');
        exit();
    }

    public function disconnect($id) {
    $this->db->prepare("UPDATE users SET session_token = NULL WHERE id_users = ?")->execute([$id]);
    header('Location: index.php?action=users');
    exit();
}

    
>>>>>>> 4f8dbbb6b83eb9c6f755d57287033c7da885a3b1
}