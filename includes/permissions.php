<?php

/**
 * Connexion PDO singleton + système de permissions par rôle
 *
 * Amélioration :
 * - getPDO() réutilise la connexion $db injectée dans index.php
 *   si elle est disponible, sinon crée une nouvelle connexion.
 * - hasPermission() utilise uniquement les rôles — plus de
 *   whitelist de noms d'utilisateurs hardcodés.
 */

/**
 * Retourne une instance PDO partagée.
 * Si la variable globale $db existe (créée dans index.php),
 * on la réutilise — pas de double connexion.
 */
function getPDO(): PDO
{
    global $db;

    if ($db instanceof PDO) {
        return $db;
    }

    // Fallback : créer une nouvelle connexion (cas appel hors index.php)
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
 * Vérifie si l'utilisateur connecté a la permission d'effectuer
 * une action sur une page donnée.
 *
 * Rôles :
 *  - admin   : accès total
 *  - manager : permissions en base + fallback en dur
 *  - user    : droits limités en dur
 *
 * @param string $page   Nom de la page (ex: 'articles')
 * @param string $action Action (ex: 'view', 'create', 'edit', 'delete')
 */
function hasPermission(string $page, string $action): bool
{
    if (!isset($_SESSION['role'])) {
        return false;
    }

    // Admin = accès complet
    if ($_SESSION['role'] === 'admin') {
        return true;
    }

    $page   = trim(strtolower($page));
    $action = trim(strtolower($action));

    // ─── RÔLE : user ────────────────────────────────────────────────────────
    if ($_SESSION['role'] === 'user') {
        $userPermissions = [
            'articles'               => ['view', 'create', 'edit'],
            'fournisseurs'           => ['view'],
            'clients'                => ['view', 'create'],
            'devis'                  => ['view', 'create', 'edit'],
            'transferts_stock'       => ['view', 'create', 'edit'],
            'receptions_fournisseur' => ['view', 'create', 'edit'],
            'voiture'                => ['view'],
            'livraisons'             => ['view'],
            'factures'               => ['view'],
            'versements'             => ['view'],
            'factures_fournisseurs'  => ['view'],
            'versements_fournisseurs'=> ['view'],
            'credit'                 => ['view'],
            'releve'                 => ['view'],
            'fs'                     => ['view'],
            'stock_movements'        => ['view'],
            'categories'             => ['view'],
            'depots'                 => ['view'],
        ];

        return isset($userPermissions[$page])
            && in_array($action, $userPermissions[$page], true);
    }

    // ─── RÔLE : manager ─────────────────────────────────────────────────────
    if ($_SESSION['role'] === 'manager') {

        // 1) Permissions personnalisées en base (priorité)
        $pdo  = getPDO();
        $stmt = $pdo->prepare(
            'SELECT COUNT(*) FROM permissions WHERE user_id = ? AND page = ? AND action = ?'
        );
        $stmt->execute([$_SESSION['user_id'], $page, $action]);

        if ((int) $stmt->fetchColumn() > 0) {
            return true;
        }

        // 2) Droits par défaut du manager
        $managerPermissions = [
            'dashboard'              => ['view'],
            'fournisseurs'           => ['view', 'create', 'edit'],
            'articles'               => ['view', 'create', 'edit'],
            'clients'                => ['view', 'create', 'edit'],
            'categories'             => ['view', 'create', 'edit'],
            'depots'                 => ['view', 'create', 'edit'],
            'devis'                  => ['view', 'create', 'edit'],
            'livraisons'             => ['view', 'create', 'edit'],
            'factures'               => ['view', 'create', 'edit'],
            'versements'             => ['view', 'create', 'edit'],
            'factures_fournisseurs'  => ['view', 'create', 'edit'],
            'versements_fournisseurs'=> ['view', 'create', 'edit'],
            'transferts_stock'       => ['view', 'create', 'edit'],
            'receptions_fournisseur' => ['view', 'create', 'edit'],
            'stock_movements'        => ['view', 'create', 'edit'],
            'credit'                 => ['view'],
            'releve'                 => ['view', 'create', 'edit'],
            'fs'                     => ['view', 'create', 'edit'],
            'voiture'                => ['view', 'create', 'edit'],
            'tresorerie'             => ['view'],
            'settings'               => ['view'],
        ];

        return isset($managerPermissions[$page])
            && in_array($action, $managerPermissions[$page], true);
    }

    return false;
}
