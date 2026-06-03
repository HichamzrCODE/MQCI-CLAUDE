<<<<<<< HEAD
<?php
require_once __DIR__ . '/../../includes/permissions.php';
require_once __DIR__ . '/../../includes/db.php'; // ton fichier de connexion PDO

session_start();
if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
    die("Accès réservé à l'administrateur.");
}

$error = $success = "";
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username   = trim($_POST['username'] ?? '');
    $nom        = trim($_POST['nom'] ?? '');
    $prenom     = trim($_POST['prenom'] ?? '');
    $telephone  = trim($_POST['telephone'] ?? '');
    $succursale = trim($_POST['succursale'] ?? '');
    $role       = $_POST['role'] ?? 'user';
    $password   = $_POST['password'] ?? '';

    if (!$username || !$password || !$nom || !$prenom || !$telephone || !$succursale || !in_array($role, ['user', 'manager'])) {
        $error = "Tous les champs sont obligatoires.";
    } else {
        $pdo = getPDO();
        // Vérifie si username existe déjà
        $stmt = $pdo->prepare("SELECT COUNT(*) FROM users WHERE username=?");
        $stmt->execute([$username]);
        if ($stmt->fetchColumn() > 0) {
            $error = "Ce nom d'utilisateur existe déjà.";
        } else {
            $hash = password_hash($password, PASSWORD_BCRYPT);
            $stmt = $pdo->prepare("INSERT INTO users 
                (username, password, role, nom, prenom, telephone, succursale, created_at) 
                VALUES (?, ?, ?, ?, ?, ?, ?, NOW())");
            $stmt->execute([$username, $hash, $role, $nom, $prenom, $telephone, $succursale]);
            $success = "Utilisateur créé avec succès.";
        }
    }
}
?>

<?php include '../../views/layout.php'; ?>
<div class="container mt-4">
    <h2 style="margin-top: 30px;">Créer un utilisateur</h2>
    <?php if ($error): ?>
        <div class="alert alert-danger"><?= htmlspecialchars($error) ?></div>
    <?php endif; ?>
    <?php if ($success): ?>
        <div class="alert alert-success"><?= htmlspecialchars($success) ?></div>
    <?php endif; ?>
    <form method="post" class="mt-3">
        <div class="form-row">
            <div class="form-group col-md-4">
                <label>Nom d'utilisateur</label>
                <input type="text" name="username" class="form-control" required>
            </div>
            <div class="form-group col-md-4">
                <label>Mot de passe</label>
                <input type="password" name="password" class="form-control" required>
            </div>
            <div class="form-group col-md-4">
                <label>Rôle</label>
                <select name="role" class="form-control" required>
                    <option value="user">Utilisateur</option>
                    <option value="manager">Manager</option>
                </select>
            </div>
        </div>
        <div class="form-row">
            <div class="form-group col-md-3">
                <label>Nom</label>
                <input type="text" name="nom" class="form-control" required>
            </div>
            <div class="form-group col-md-3">
                <label>Prénom</label>
                <input type="text" name="prenom" class="form-control" required>
            </div>
            <div class="form-group col-md-3">
                <label>Téléphone</label>
                <input type="text" name="telephone" class="form-control" required>
            </div>
            <div class="form-group col-md-3">
                <label>Succursale</label>
                <input type="text" name="succursale" class="form-control" required>
            </div>
        </div>
        <button type="submit" class="btn btn-primary">Créer l'utilisateur</button>
    </form>
=======
<?php
require_once __DIR__ . '/../../includes/permissions.php';
require_once __DIR__ . '/../../includes/db.php'; // ton fichier de connexion PDO

session_start();
if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
    die("Accès réservé à l'administrateur.");
}

$error = $success = "";
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username   = trim($_POST['username'] ?? '');
    $nom        = trim($_POST['nom'] ?? '');
    $prenom     = trim($_POST['prenom'] ?? '');
    $telephone  = trim($_POST['telephone'] ?? '');
    $succursale = trim($_POST['succursale'] ?? '');
    $role       = $_POST['role'] ?? 'user';
    $password   = $_POST['password'] ?? '';

    if (!$username || !$password || !$nom || !$prenom || !$telephone || !$succursale || !in_array($role, ['user', 'manager'])) {
        $error = "Tous les champs sont obligatoires.";
    } else {
        $pdo = getPDO();
        // Vérifie si username existe déjà
        $stmt = $pdo->prepare("SELECT COUNT(*) FROM users WHERE username=?");
        $stmt->execute([$username]);
        if ($stmt->fetchColumn() > 0) {
            $error = "Ce nom d'utilisateur existe déjà.";
        } else {
            $hash = password_hash($password, PASSWORD_BCRYPT);
            $stmt = $pdo->prepare("INSERT INTO users 
                (username, password, role, nom, prenom, telephone, succursale, created_at) 
                VALUES (?, ?, ?, ?, ?, ?, ?, NOW())");
            $stmt->execute([$username, $hash, $role, $nom, $prenom, $telephone, $succursale]);
            $success = "Utilisateur créé avec succès.";
        }
    }
}
?>

<?php include '../../views/layout.php'; ?>
<div class="container mt-4">
    <h2 style="margin-top: 30px;">Créer un utilisateur</h2>
    <?php if ($error): ?>
        <div class="alert alert-danger"><?= htmlspecialchars($error) ?></div>
    <?php endif; ?>
    <?php if ($success): ?>
        <div class="alert alert-success"><?= htmlspecialchars($success) ?></div>
    <?php endif; ?>
    <form method="post" class="mt-3">
        <div class="form-row">
            <div class="form-group col-md-4">
                <label>Nom d'utilisateur</label>
                <input type="text" name="username" class="form-control" required>
            </div>
            <div class="form-group col-md-4">
                <label>Mot de passe</label>
                <input type="password" name="password" class="form-control" required>
            </div>
            <div class="form-group col-md-4">
                <label>Rôle</label>
                <select name="role" class="form-control" required>
                    <option value="user">Utilisateur</option>
                    <option value="manager">Manager</option>
                </select>
            </div>
        </div>
        <div class="form-row">
            <div class="form-group col-md-3">
                <label>Nom</label>
                <input type="text" name="nom" class="form-control" required>
            </div>
            <div class="form-group col-md-3">
                <label>Prénom</label>
                <input type="text" name="prenom" class="form-control" required>
            </div>
            <div class="form-group col-md-3">
                <label>Téléphone</label>
                <input type="text" name="telephone" class="form-control" required>
            </div>
            <div class="form-group col-md-3">
                <label>Succursale</label>
                <input type="text" name="succursale" class="form-control" required>
            </div>
        </div>
        <button type="submit" class="btn btn-primary">Créer l'utilisateur</button>
    </form>
>>>>>>> 4f8dbbb6b83eb9c6f755d57287033c7da885a3b1
</div>