<?php
require_once __DIR__ . '/includes/db.php'; // adapte ce chemin à ton projet

session_start();

$pdo = getPDO(); // ta fonction de connexion PDO

// Vérifie s'il y a déjà des utilisateurs
$count = $pdo->query("SELECT COUNT(*) FROM users")->fetchColumn();

if ($count > 0) {
    echo "<div style='padding:2rem;font-family:sans-serif'>L'installation a déjà été effectuée.<br>Supprime ou renomme ce fichier install.php pour la sécurité.</div>";
    exit;
}

$error = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username   = trim($_POST['username'] ?? '');
    $password   = $_POST['password'] ?? '';
    $nom        = trim($_POST['nom'] ?? '');
    $prenom     = trim($_POST['prenom'] ?? '');
    $telephone  = trim($_POST['telephone'] ?? '');
    $succursale = trim($_POST['succursale'] ?? '');

    if (!$username || !$password || !$nom || !$prenom || !$telephone || !$succursale) {
        $error = "Tous les champs sont obligatoires.";
    } else {
        $hash = password_hash($password, PASSWORD_BCRYPT);
        $stmt = $pdo->prepare("INSERT INTO users (username, password, role, nom, prenom, telephone, succursale, created_at)
                               VALUES (?, ?, 'admin', ?, ?, ?, ?, NOW())");
        $stmt->execute([$username, $hash, $nom, $prenom, $telephone, $succursale]);
        echo "<div style='padding:2rem;font-family:sans-serif;color:green'>
                Compte administrateur créé avec succès.<br>
                <b>Pour la sécurité, supprime ou renomme ce fichier install.php.</b><br>
                <a href='index.php'>Aller à l'application</a>
              </div>";
        exit;
    }
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Installation - Création admin</title>
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
</head>
<body>
<div class="container mt-5" style="max-width:500px">
    <h2>Création du premier compte administrateur</h2>
    <?php if ($error): ?>
        <div class="alert alert-danger"><?= htmlspecialchars($error) ?></div>
    <?php endif; ?>
    <form method="post" autocomplete="off">
        <div class="form-group">
            <label>Nom d'utilisateur</label>
            <input type="text" name="username" class="form-control" required>
        </div>
        <div class="form-group">
            <label>Mot de passe</label>
            <input type="password" name="password" class="form-control" required autocomplete="new-password">
        </div>
        <div class="form-group">
            <label>Nom</label>
            <input type="text" name="nom" class="form-control" required>
        </div>
        <div class="form-group">
            <label>Prénom</label>
            <input type="text" name="prenom" class="form-control" required>
        </div>
        <div class="form-group">
            <label>Téléphone</label>
            <input type="text" name="telephone" class="form-control" required>
        </div>
        <div class="form-group">
            <label>Succursale</label>
            <input type="text" name="succursale" class="form-control" required>
        </div>
        <button type="submit" class="btn btn-primary btn-block">Créer l'admin</button>
    </form>
    <div class="alert alert-warning mt-3">
        Cette page ne sera plus accessible une fois un admin créé.<br>
        <b>Pense à supprimer ce fichier (install.php) après installation.</b>
    </div>
</div>
</body>
</html>