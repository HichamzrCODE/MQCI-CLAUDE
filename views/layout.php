<?php
require_once __DIR__ . '/../includes/permissions.php';
require_once __DIR__ . '/../models/Settings.php';

/**
 * IMPORTANT:
 * - $db doit exister avant ce layout (dans ton bootstrap / index.php).
 * - sinon Settings($db) plantera.
 */
$settingsModel = new Settings($db);
$appName = $settingsModel->get('app_name', 'MAQCI');
$appIcon = $settingsModel->get('app_icon', 'fa-cube');
$logoUrl = $settingsModel->getLogoUrl();

/** Vérification session unique */
if (!(isset($_GET['action']) && $_GET['action'] === 'login')) {
    if (isset($_SESSION['user_id'], $_SESSION['session_token'])) {
        require_once __DIR__ . '/../models/User.php';
        $db = getPDO();
        $userModel = new User($db);
        $token = $userModel->getSessionToken($_SESSION['user_id']);
        if ($token !== $_SESSION['session_token']) {
            session_destroy();
            header('Location: index.php?action=login&message=Déconnecté car une autre session est active');
            exit();
        }
    }
}

/** action courante */
$action = $_GET['action'] ?? 'home';
$prefix = explode('/', (string)$action, 2)[0];

/**
 * Active link helper:
 * - accepte soit "devis" soit "devis/create" soit un tableau
 * - si tu passes "devis", ça match "devis", "devis/create", "devis/edit", etc.
 */
$isActive = function ($prefixes) use ($action): string {
    $prefixes = (array)$prefixes;
    foreach ($prefixes as $p) {
        $p = (string)$p;
        if ($p === '') continue;
        if ($action === $p) return 'active';
        if (str_starts_with($action, $p . '/')) return 'active';
    }
    return '';
};

$isSectionActive = function ($prefixes) use ($action): string {
    $prefixes = (array)$prefixes;
    foreach ($prefixes as $p) {
        $p = (string)$p;
        if ($p === '') continue;
        if ($action === $p) return 'section-active';
        if (str_starts_with($action, $p . '/')) return 'section-active';
    }
    return '';
};

/** Fil d'Ariane */
$labelByPrefix = [
    'home' => 'Accueil',
    'dashboard' => 'Tableau de bord',

    'clients' => 'Clients',
    'devis' => 'Devis/Factures',
    'credit' => 'Crédits Clients',

    'articles' => 'Articles',
    'categories' => 'Catégories',
    'depots' => 'Dépôts',
    'stock_movements' => 'Mouvements Stock',
    'receptions_fournisseur' => 'Réception Fournisseur',
    'transferts_stock' => 'Transfert Dépôt',
    'stock_alerts' => 'Alertes Stock',

    'versements' => 'Versements',
    'versements_fournisseurs' => 'Versements Fournisseurs',

    'fournisseurs' => 'Fournisseurs',
    'fs' => 'Relevés Fournisseurs',
    'factures_fournisseurs' => 'Factures Fournisseurs',

    'voiture' => 'Véhicules',
    'releve' => 'Relevés',

    'users' => 'Utilisateurs',
    'sauvegarde' => 'Sauvegardes',
    'settings' => 'Paramètres',
    'tresorerie' => 'Trésorerie',

    'logout' => 'Déconnexion',
    'login' => 'Connexion',
];

$sectionByPrefix = [
    'home' => 'Menu Principal',
    'dashboard' => 'Menu Principal',

    'clients' => 'Ventes',
    'devis' => 'Ventes',
    'credit' => 'Ventes',

    'articles' => 'Stock',
    'categories' => 'Stock',
    'depots' => 'Stock',
    'stock_movements' => 'Stock',
    'receptions_fournisseur' => 'Stock',
    'transferts_stock' => 'Stock',
    'stock_alerts' => 'Stock',

    'versements' => 'Ventes',
    'versements_fournisseurs' => 'Achats',

    'fournisseurs' => 'Partenaires',
    'fs' => 'Partenaires',
    'factures_fournisseurs' => 'Achats',

    'voiture' => 'Logistique',
    'releve' => 'Logistique',

    'users' => 'Admin',
    'sauvegarde' => 'Admin',
    'settings' => 'Admin',
    'tresorerie' => 'Admin',
];

$breadcrumbSection = $sectionByPrefix[$prefix] ?? 'Menu';
$breadcrumbPage = $labelByPrefix[$prefix] ?? $prefix;
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= htmlspecialchars($pageTitle ?? $appName) ?></title>
    <link rel="icon" type="image/x-icon" href="/maqci/public/img/logo.ico">

    <!-- Bootstrap 4 CSS -->
    <link rel="stylesheet" href="/maqci/public/css/bootstrap.min.css">
    <link rel="stylesheet" href="/maqci/public/css/autocomplete.css">

    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">

    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }

        :root {
            --primary: #2563eb;
            --primary-dark: #1e40af;
            --primary-light: #dbeafe;
            --sidebar-bg: #ffffff;
            --sidebar-border: #e5e7eb;
            --text-primary: #1f2937;
            --text-secondary: #6b7280;
            --bg-light: #f9fafb;
            --border-color: #e5e7eb;
        }

        body {
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, 'Helvetica Neue', Arial, sans-serif;
            background-color: var(--bg-light);
            color: var(--text-primary);
            line-height: 1.6;
        }

        /* ============== NAVBAR ============== */
        .navbar {
            background: white;
            border-bottom: 2px solid var(--border-color);
            padding: 1rem 0;
            box-shadow: 0 1px 3px rgba(0, 0, 0, 0.05);
            position: sticky;
            top: 0;
            z-index: 1020;
        }

        .navbar-brand {
            font-weight: 700;
            font-size: 22px;
            color: var(--primary) !important;
            display: flex;
            align-items: center;
            gap: 10px;
        }

        .navbar-brand i { font-size: 28px; }

        .navbar-nav .nav-link {
            color: var(--text-secondary) !important;
            font-weight: 500;
            padding: 8px 16px !important;
            transition: all 0.3s ease;
            border-radius: 6px;
            margin: 0 4px;
        }

        .navbar-nav .nav-link:hover {
            color: var(--primary) !important;
            background: var(--primary-light);
        }

        .user-info {
            display: flex;
            align-items: center;
            gap: 12px;
            padding: 8px 16px;
            background: var(--bg-light);
            border-radius: 8px;
        }

        .user-info i { color: var(--primary); font-size: 18px; }
        .user-info span { font-weight: 600; color: var(--text-primary); }

        /* ============== SIDEBAR ============== */
        .sidebar {
            position: fixed;
            left: 0;
            top: 60px;
            width: 260px;
            height: calc(100vh - 60px);
            background: var(--sidebar-bg);
            border-right: 1px solid var(--sidebar-border);
            overflow-y: auto;
            padding: 20px 0;
            z-index: 1010;
        }

        /* ==== TITRES DE SECTION : fond différent pour mieux séparer ==== */
        .sidebar-title{
            padding: 10px 14px;
            margin: 8px 12px;
            border-radius: 10px;
            background: #f3f4f6;
            border: 1px solid var(--border-color);
            font-size: 12px;
            font-weight: 800;
            text-transform: uppercase;
            color: var(--text-secondary);
            letter-spacing: 0.5px;
        }

        /* Section active : titre plus visible */
        .sidebar-section.section-active .sidebar-title{
            background: var(--primary-light);
            border-color: #bfdbfe;
            color: var(--primary);
        }

        .sidebar-section{
            margin: 16px 10px;
            padding: 8px 6px 12px 6px;
            border-radius: 12px;
            background: #ffffff;
        }
        .sidebar-section.section-active{
            background: #eff6ff;
            border: 1px solid #bfdbfe;
        }

        .sidebar .nav-link {
            color: var(--text-secondary) !important;
            padding: 5px 20px;
            margin: 1px 8px;
            border-radius: 8px;
            transition: all 0.2s ease;
            display: flex;
            align-items: center;
            gap: 12px;
            font-weight: 500;
            border-left: 3px solid transparent;
        }

        .sidebar .nav-link i { width: 20px; text-align: center; font-size: 16px; }

        .sidebar .nav-link:hover {
            background-color: var(--primary-light);
            color: var(--primary) !important;
            border-left-color: var(--primary);
            transform: translateX(4px);
        }

        .sidebar .nav-link.active {
            background-color: var(--primary-light);
            color: var(--primary) !important;
            border-left-color: var(--primary);
            font-weight: 600;
        }

        /* ============== MAIN CONTENT ============== */
        .main-wrapper { display: flex; }

        .main-content {
            width: 100%;
            margin-left: 260px;
            padding: 30px;
            min-height: calc(100vh - 60px);
            background: var(--bg-light);
        }

        /* Fil d'ariane */
        .breadcrumb-pill{
            display: inline-flex;
            align-items: center;
            gap: 10px;
            background: white;
            border: 1px solid var(--border-color);
            border-radius: 999px;
            padding: 10px 14px;
            font-weight: 700;
            color: var(--text-secondary);
            margin-bottom: 16px;
        }
        .breadcrumb-pill .section{ color: var(--primary); }
        .breadcrumb-pill .sep{ opacity: .5; }

        /* ============== RESPONSIVE ============== */
        @media (max-width: 992px) {
            .sidebar {
                transform: translateX(-100%);
                transition: transform 0.3s ease;
                width: 240px;
                box-shadow: 2px 0 8px rgba(0, 0, 0, 0.1);
            }
            .sidebar.show { transform: translateX(0); }
            .main-content { margin-left: 0; }
            .navbar-toggler { display: block !important; }
        }

        @media print {
            .navbar, .sidebar, .navbar-toggler, .btn, nav { display: none !important; }
            .main-content { margin-left: 0; padding: 0; }
        }

        /* ============== SCROLLBAR ============== */
        ::-webkit-scrollbar { width: 8px; height: 8px; }
        ::-webkit-scrollbar-track { background: var(--bg-light); }
        ::-webkit-scrollbar-thumb { background: #cbd5e1; border-radius: 4px; }
        ::-webkit-scrollbar-thumb:hover { background: #94a3b8; }
    </style>
</head>
<body>

<!-- ============== NAVBAR ============== -->
<nav class="navbar navbar-expand-lg">
    <div class="container-fluid">
        <a class="navbar-brand" href="index.php?action=home">
            <?php if ($logoUrl): ?>
                <img src="<?= htmlspecialchars($logoUrl) ?>" alt="Logo" style="height: 32px; width: auto; margin-right: 10px;">
            <?php else: ?>
                <i class="fas <?= htmlspecialchars($appIcon) ?>"></i>
            <?php endif; ?>
            <span><?= htmlspecialchars($appName) ?></span>
        </a>

        <button class="navbar-toggler" type="button" id="sidebarToggle">
            <span class="navbar-toggler-icon"></span>
        </button>

        <div class="navbar-nav ml-auto">
            <div class="user-info">
                <i class="fas fa-circle-user"></i>
                <span><?= htmlspecialchars($_SESSION['username'] ?? 'Utilisateur') ?></span>
            </div>
            <a class="nav-link" href="index.php?action=logout" title="Déconnexion">
                <i class="fas fa-sign-out-alt"></i>
            </a>
        </div>
    </div>
</nav>

<!-- ============== SIDEBAR ============== -->
<aside class="sidebar" id="sidebar">

    <!-- MENU PRINCIPAL -->
    <div class="sidebar-section <?= $isSectionActive(['home','clients','fournisseurs','articles']) ?>">
        <div class="sidebar-title">🏠 Menu principal</div>
        <nav class="nav flex-column">
            <a class="nav-link <?= $isActive('home') ?>" href="index.php?action=home">
                <i class="fas fa-home"></i><span>Accueil</span>
            </a>

            <?php if (hasPermission('clients', 'view')): ?>
                <a class="nav-link <?= $isActive('clients') ?>" href="index.php?action=clients">
                    <i class="fas fa-users"></i><span>Clients</span>
                </a>
            <?php endif; ?>

            <?php if (hasPermission('fournisseurs', 'view')): ?>
                <a class="nav-link <?= $isActive('fournisseurs') ?>" href="index.php?action=fournisseurs">
                    <i class="fas fa-truck"></i><span>Fournisseurs</span>
                </a>
            <?php endif; ?>

            <?php if (hasPermission('articles', 'view')): ?>
                <a class="nav-link <?= $isActive('articles') ?>" href="index.php?action=articles">
                    <i class="fas fa-boxes"></i><span>Articles</span>
                </a>
            <?php endif; ?>
        </nav>
    </div>

    <!-- VENTES -->
    <div class="sidebar-section <?= $isSectionActive(['devis','livraisons','factures','credit','versements']) ?>">
        <div class="sidebar-title">💼 Ventes</div>
        <nav class="nav flex-column">
            <?php if (hasPermission('devis', 'view')): ?>
                <a class="nav-link <?= $isActive('devis') ?>" href="index.php?action=devis">
                    <i class="fas fa-file-invoice-dollar"></i><span>Devis</span>
                </a>
            <?php endif; ?>

            <?php if (hasPermission('livraisons', 'view')): ?>
                 <a class="nav-link <?= $isActive('livraisons') ?>" href="index.php?action=livraisons">
                      <i class="fas fa-truck-fast"></i>
                     <span>Livraisons</span>
                </a>
            <?php endif; ?>

            <?php if (hasPermission('factures', 'view')): ?>
                <a class="nav-link <?= $isActive('factures') ?>" href="index.php?action=factures">
                    <i class="fas fa-file-invoice"></i><span>Factures</span>
                </a>
            <?php endif; ?>

            <?php if (hasPermission('versements', 'view')): ?>
    <a class="nav-link <?= $isActive('versements') ?>" href="index.php?action=versements">
        <i class="fas fa-money-bill-wave"></i><span>Versements</span>
    </a>
<?php endif; ?>
        </nav>
    </div>

    <!-- ACHATS  -->
  <div class="sidebar-section <?= $isSectionActive(['factures_fournisseurs']) ?>">
    <div class="sidebar-title">🛒 Achats</div>
    <nav class="nav flex-column">
        <a class="nav-link <?= $isActive('factures_fournisseurs') ?>" href="index.php?action=factures_fournisseurs">
            <i class="fas fa-file-invoice"></i><span>FCT fournisseurs</span>
        </a>
<?php if (hasPermission('versements_fournisseurs', 'view')): ?>
            <a class="nav-link <?= $isActive('versements_fournisseurs') ?>" href="index.php?action=versements_fournisseurs">
                <i class="fas fa-money-check-dollar"></i><span>Versements fournisseurs</span>
            </a>
        <?php endif; ?>
        
    </nav>
</div>

    <!-- STOCK -->
    <div class="sidebar-section <?= $isSectionActive(['depots','categories','stock_movements','transferts_stock','stock_alerts']) ?>">
        <div class="sidebar-title">📦 Stock</div>
        <nav class="nav flex-column">
            <a class="nav-link <?= $isActive('depots') ?>" href="index.php?action=depots">
                <i class="fas fa-warehouse"></i><span>Dépôts</span>
            </a>

            <?php if (hasPermission('categories', 'view')): ?>
                <a class="nav-link <?= $isActive('categories') ?>" href="index.php?action=categories">
                    <i class="fas fa-tags"></i><span>Catégories</span>
                </a>
            <?php endif; ?>

            <?php if (hasPermission('stock_movements', 'view')): ?>
                <a class="nav-link <?= $isActive('stock_movements') ?>" href="index.php?action=stock_movements">
                    <i class="fas fa-history"></i><span>Mouvements de stock</span>
                </a>
            <?php endif; ?>

            <a class="nav-link <?= $isActive('transferts_stock') ?>" href="index.php?action=transferts_stock">
                <i class="fas fa-exchange-alt"></i><span>Transfert dépôt</span>
            </a>

            <?php if (hasPermission('stock_movements', 'view')): ?>
                <a class="nav-link <?= $isActive('stock_alerts') ?>" href="index.php?action=stock_movements/alerts">
                    <i class="fas fa-bell"></i><span>Alertes stock</span>
                </a>
            <?php endif; ?>
        </nav>
    </div>

    <!-- SUIVI -->
    <div class="sidebar-section <?= $isSectionActive(['fs','releve','dashboard']) ?>">
        <div class="sidebar-title">📈 Suivi</div>
        <nav class="nav flex-column">
            <a class="nav-link <?= $isActive('fs') ?>" href="index.php?action=fs">
                <i class="fas fa-receipt"></i><span>Relevé fournisseurs</span>
            </a>
            <a class="nav-link <?= $isActive('releve') ?>" href="index.php?action=releve">
                <i class="fas fa-list-check"></i><span>Relevé clients</span>
            </a>
            <a class="nav-link <?= $isActive('dashboard') ?>" href="index.php?action=dashboard">
                <i class="fas fa-chart-line"></i><span>Tableau de bord</span>
            </a>
        </nav>
    </div>

    <!-- LOGISTIQUE -->
    <div class="sidebar-section <?= $isSectionActive(['voiture']) ?>">
        <div class="sidebar-title">🚚 Logistique</div>
        <nav class="nav flex-column">
            <a class="nav-link <?= $isActive('voiture') ?>" href="index.php?action=voiture">
                <i class="fas fa-car"></i><span>Véhicules</span>
            </a>
        </nav>
    </div>

    <!-- MAINTENANCE -->
    <?php if (isset($_SESSION['role']) && $_SESSION['role'] === 'admin'): ?>
        <div class="sidebar-section <?= $isSectionActive(['sauvegarde']) ?>">
            <div class="sidebar-title">🧰 Maintenance</div>
            <nav class="nav flex-column">
                <a class="nav-link <?= $isActive('sauvegarde') ?>" href="index.php?action=sauvegarde">
                    <i class="fas fa-database"></i><span>Sauvegardes</span>
                </a>
            </nav>
        </div>
    <?php endif; ?>

    <!-- PARAMÈTRES -->
    <?php if (isset($_SESSION['role']) && $_SESSION['role'] === 'admin'): ?>
        <div class="sidebar-section <?= $isSectionActive(['users','settings']) ?>">
            <div class="sidebar-title">⚙️ Paramètres</div>
            <nav class="nav flex-column">
                <a class="nav-link <?= $isActive('users') ?>" href="index.php?action=users">
                    <i class="fas fa-users-cog"></i><span>Utilisateurs</span>
                </a>
                <a class="nav-link <?= $isActive('settings') ?>" href="index.php?action=settings">
                    <i class="fas fa-cog"></i><span>Paramètres</span>
                </a>

                <a class="nav-link <?= $isActive('tresorerie') ?>" href="index.php?action=tresorerie">
                    <i class="fas fa-cash-register"></i><span>Trésorerie</span>
                </a>
            </nav>
        </div>
    <?php endif; ?>

</aside>

<!-- ============== MAIN CONTENT ============== -->
<div class="main-wrapper">
    <main class="main-content">

        <?php if (isset($_SESSION['user_id']) && $action !== 'login'): ?>
            <div class="breadcrumb-pill">
                <span class="section"><?= htmlspecialchars($breadcrumbSection) ?></span>
                <span class="sep">></span>
                <span class="page"><?= htmlspecialchars($breadcrumbPage) ?></span>
            </div>
        <?php endif; ?>

        <!-- ✅ ICI: le contenu de la page (views) -->