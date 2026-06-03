<?php

function getPDO(): PDO {
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

/**
 * hasPermission($page, $action)
 * - admin : tout
 * - user  : droits limités en dur
 * - manager : permissions en base OU fallback droits en dur
 */
function hasPermission(string $page, string $action): bool {
    if (!isset($_SESSION['role'])) return false;

    // admin = full access
    if ($_SESSION['role'] === 'admin') return true;

    $page = trim(strtolower($page));
    $action = trim(strtolower($action));

    // ===== ROLE: user =====
    if ($_SESSION['role'] === 'user') {
        $userPermissions = [
            'articles' => ['view', 'create', 'edit'],
            'fournisseurs' => ['view'],
            'clients' => ['view', 'create'],
            'devis' => ['view', 'create', 'edit'],
            'transferts_stock' => ['view', 'create', 'edit'],
            'receptions_fournisseur' => ['view', 'create', 'edit'],
            'voiture' => ['view'],
            'livraisons' => ['view'],

            // Ventes
            'factures' => ['view'],
            'versements' => ['view'],

            // Achats
            'factures_fournisseurs' => ['view'],
            'versements_fournisseurs' => ['view'],

            // Suivi
            'credit' => ['view'],
            'releve' => ['view'],
            'fs' => ['view'],

            // Stock
            'stock_movements' => ['view'],
            'categories' => ['view'],
            'depots' => ['view'],
        ];

        return isset($userPermissions[$page]) && in_array($action, $userPermissions[$page], true);
    }

    // ===== ROLE: manager =====
    if ($_SESSION['role'] === 'manager') {

        // 1) Permissions personnalisées en base (priorité)
        $pdo = getPDO();
        $stmt = $pdo->prepare("SELECT COUNT(*) FROM permissions WHERE user_id=? AND page=? AND action=?");
        $stmt->execute([$_SESSION['user_id'], $page, $action]);
        if ((int)$stmt->fetchColumn() > 0) return true;

        // 2) Fallback "en dur" (par défaut pour manager)
        $managerPermissions = [
            'dashboard' => ['view'],

            // Référentiels
            'fournisseurs' => ['view', 'create', 'edit'],
            'articles' => ['view', 'create', 'edit'],
            'clients' => ['view', 'create', 'edit'],
            'categories' => ['view', 'create', 'edit'],
            'depots' => ['view', 'create', 'edit'],

            // Ventes
            'devis' => ['view', 'create', 'edit'],
            'livraisons' => ['view', 'create', 'edit'],
            'factures' => ['view', 'create', 'edit'],
            'versements' => ['view', 'create', 'edit'],

            // Achats
            'factures_fournisseurs' => ['view', 'create', 'edit'],
            'versements_fournisseurs' => ['view', 'create', 'edit'],

            // Stock
            'transferts_stock' => ['view', 'create', 'edit'],
            'receptions_fournisseur' => ['view', 'create', 'edit'],
            'stock_movements' => ['view', 'create', 'edit'],

            // Suivi
            'credit' => ['view'],
            'releve' => ['view', 'create', 'edit'],
            'fs' => ['view', 'create', 'edit'],

            // Logistique
            'voiture' => ['view', 'create', 'edit'],

            // Admin fonctionnel limité
            'tresorerie' => ['view'],
            'settings' => ['view'],
        ];

        return isset($managerPermissions[$page]) && in_array($action, $managerPermissions[$page], true);
    }

    return false;
}