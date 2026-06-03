<<<<<<< HEAD
<?php
function getPDO() {
    static $pdo = null;
    if ($pdo === null) {
        $dbConfig = require __DIR__ . '/../config/database.php';
        $pdo = new PDO(
            "mysql:host={$dbConfig['host']};dbname={$dbConfig['database']};charset={$dbConfig['charset']}",
            $dbConfig['username'],
            $dbConfig['password'],
            $dbConfig['options']
        );
    }
    return $pdo;
}

function hasPermission($page, $action) {
    if (!isset($_SESSION['role'])) return false;
    if ($_SESSION['role'] === 'admin') return true;

    // Droits par défaut du user lambda
    if ($_SESSION['role'] === 'user') {
        if ($page === 'articles' && in_array($action, ['view', 'create', 'edit'])) return true;
        if ($page === 'fournisseurs' && $action === 'view') return true;
        if ($page === 'clients' && in_array($action, ['view', 'create'])) return true;
        if ($page === 'devis' && in_array($action, ['view', 'create', 'edit'])) return true;
        if ($page === 'voiture' && $action === 'view') return true;
        // CREDIT, RELEVE, FS : jamais pour user
        return false;
    }

    // Manager : permissions fines via table + droits fournisseurs en dur
    if ($_SESSION['role'] === 'manager') {
        // Permissions personnalisées en base
        $pdo = getPDO();
        $stmt = $pdo->prepare("SELECT COUNT(*) FROM permissions WHERE user_id=? AND page=? AND action=?");
        $stmt->execute([$_SESSION['user_id'], $page, $action]);
        if ($stmt->fetchColumn() > 0) return true;
        // Droit en dur sur fournisseurs (voir, créer, modifier)
        if ($page === 'fournisseurs' && in_array($action, ['view', 'create', 'edit'])) return true;
        if ($page === 'articles' && in_array($action, ['view', 'create', 'edit'])) return true;
        if ($page === 'clients' && in_array($action, ['view', 'create', 'edit'])) return true;
        if ($page === 'devis' && in_array($action, ['view', 'create', 'edit'])) return true;
        if ($page === 'dashboard' && $action === 'view') return true; 
        if ($page === 'voiture' && in_array($action, ['view', 'create', 'edit'])) return true;
        if ($page === 'credit' && $action === 'view') return true;
        // DROITS EN DUR POUR CREDIT, RELEVE, FS
        if (in_array($page, ['credit', 'releve', 'fs']) && $action === 'view') return true;
        if ($page === 'fs' && in_array($action, ['view', 'create', 'edit'])) return true;
        if ($page === 'releve' && in_array($action, ['view', 'create', 'edit'])) return true;
        return false;
    }

    return false;
}
=======
<?php
function getPDO() {
    static $pdo = null;
    if ($pdo === null) {
        $dbConfig = require __DIR__ . '/../config/database.php';
        $pdo = new PDO(
            "mysql:host={$dbConfig['host']};dbname={$dbConfig['database']};charset={$dbConfig['charset']}",
            $dbConfig['username'],
            $dbConfig['password'],
            $dbConfig['options']
        );
    }
    return $pdo;
}

function hasPermission($page, $action) {
    if (!isset($_SESSION['role'])) return false;
    if ($_SESSION['role'] === 'admin') return true;

    // Droits par défaut du user lambda
    if ($_SESSION['role'] === 'user') {
        if ($page === 'articles' && in_array($action, ['view', 'create', 'edit'])) return true;
        if ($page === 'fournisseurs' && $action === 'view') return true;
        if ($page === 'clients' && in_array($action, ['view', 'create'])) return true;
        if ($page === 'devis' && in_array($action, ['view', 'create', 'edit'])) return true;
        if ($page === 'voiture' && $action === 'view') return true;
        // CREDIT, RELEVE, FS : jamais pour user
        return false;
    }

    // Manager : permissions fines via table + droits fournisseurs en dur
    if ($_SESSION['role'] === 'manager') {
        // Permissions personnalisées en base
        $pdo = getPDO();
        $stmt = $pdo->prepare("SELECT COUNT(*) FROM permissions WHERE user_id=? AND page=? AND action=?");
        $stmt->execute([$_SESSION['user_id'], $page, $action]);
        if ($stmt->fetchColumn() > 0) return true;
        // Droit en dur sur fournisseurs (voir, créer, modifier)
        if ($page === 'fournisseurs' && in_array($action, ['view', 'create', 'edit'])) return true;
        if ($page === 'articles' && in_array($action, ['view', 'create', 'edit'])) return true;
        if ($page === 'clients' && in_array($action, ['view', 'create', 'edit'])) return true;
        if ($page === 'devis' && in_array($action, ['view', 'create', 'edit'])) return true;
        if ($page === 'dashboard' && $action === 'view') return true; 
        if ($page === 'voiture' && in_array($action, ['view', 'create', 'edit'])) return true;
        if ($page === 'credit' && $action === 'view') return true;
        // DROITS EN DUR POUR CREDIT, RELEVE, FS
        if (in_array($page, ['credit', 'releve', 'fs']) && $action === 'view') return true;
        if ($page === 'fs' && in_array($action, ['view', 'create', 'edit'])) return true;
        if ($page === 'releve' && in_array($action, ['view', 'create', 'edit'])) return true;
        return false;
    }

    return false;
}
>>>>>>> 4f8dbbb6b83eb9c6f755d57287033c7da885a3b1
?>