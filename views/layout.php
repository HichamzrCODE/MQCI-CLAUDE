<?php
require_once __DIR__ . '/../includes/permissions.php';
require_once __DIR__ . '/../models/Settings.php';

global $db;
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

$action = $_GET['action'] ?? 'home';
$prefix = explode('/', (string)$action, 2)[0];

$isActive = function ($prefixes) use ($action): string {
    foreach ((array)$prefixes as $p) {
        $p = (string)$p;
        if ($p === '') continue;
        if ($action === $p) return 'active';
        if (str_starts_with($action, $p . '/')) return 'active';
    }
    return '';
};

$isSectionActive = function ($prefixes) use ($action): string {
    foreach ((array)$prefixes as $p) {
        $p = (string)$p;
        if ($p === '') continue;
        if ($action === $p) return 'section-active';
        if (str_starts_with($action, $p . '/')) return 'section-active';
    }
    return '';
};

$labelByPrefix = [
    'home'                   => 'Accueil',
    'dashboard'              => 'Tableau de bord',
    'clients'                => 'Clients',
    'devis'                  => 'Devis',
    'livraisons'             => 'Livraisons',
    'factures'               => 'Factures clients',
    'credit'                 => 'Crédits clients',
    'versements'             => 'Versements clients',
    'versements_fournisseurs'=> 'Versements fournisseurs',
    'factures_fournisseurs'  => 'Factures fournisseurs',
    'fournisseurs'           => 'Fournisseurs',
    'articles'               => 'Articles',
    'categories'             => 'Catégories',
    'depots'                 => 'Dépôts',
    'stock_movements'        => 'Mouvements de stock',
    'receptions_fournisseur' => 'Réceptions fournisseur',
    'transferts_stock'       => 'Transferts dépôt',
    'client_releve'          => 'Suivi clients',
    'fournisseur_releve'     => 'Suivi fournisseurs',
    'releve'                 => 'Relevés clients',
    'fs'                     => 'Relevés fournisseurs',
    'voiture'                => 'Véhicules',
    'users'                  => 'Utilisateurs',
    'sauvegarde'             => 'Sauvegardes',
    'settings'               => 'Paramètres',
    'tresorerie'             => 'Trésorerie',
    'login'                  => 'Connexion',
    'logout'                 => 'Déconnexion',
];

$sectionByPrefix = [
    'home'                   => 'Menu principal',
    'dashboard'              => 'Suivi',
    'clients'                => 'Ventes',
    'devis'                  => 'Ventes',
    'livraisons'             => 'Ventes',
    'factures'               => 'Ventes',
    'credit'                 => 'Ventes',
    'versements'             => 'Ventes',
    'versements_fournisseurs'=> 'Achats',
    'factures_fournisseurs'  => 'Achats',
    'fournisseurs'           => 'Menu principal',
    'articles'               => 'Stock',
    'categories'             => 'Stock',
    'depots'                 => 'Stock',
    'stock_movements'        => 'Stock',
    'receptions_fournisseur' => 'Stock',
    'transferts_stock'       => 'Stock',
    'client_releve'          => 'Suivi',
    'fournisseur_releve'     => 'Suivi',
    'releve'                 => 'Suivi',
    'fs'                     => 'Suivi',
    'voiture'                => 'Logistique',
    'users'                  => 'Paramètres',
    'sauvegarde'             => 'Maintenance',
    'settings'               => 'Paramètres',
    'tresorerie'             => 'Paramètres',
];

$breadcrumbSection = $sectionByPrefix[$prefix] ?? 'Menu';
$breadcrumbPage    = $labelByPrefix[$prefix]    ?? $prefix;
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= htmlspecialchars($pageTitle ?? $appName) ?></title>
    <link rel="icon" type="image/x-icon" href="/maqci/public/img/logo.ico">
    <link rel="stylesheet" href="/maqci/public/css/bootstrap.min.css">
    <link rel="stylesheet" href="/maqci/public/css/autocomplete.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        * { margin:0; padding:0; box-sizing:border-box; }
        :root {
            --primary:#2563eb; --primary-dark:#1e40af; --primary-light:#dbeafe;
            --sidebar-bg:#ffffff; --sidebar-border:#e5e7eb;
            --text-primary:#1f2937; --text-secondary:#6b7280;
            --bg-light:#f9fafb; --border-color:#e5e7eb;
        }
        body { font-family:-apple-system,BlinkMacSystemFont,'Segoe UI',Roboto,'Helvetica Neue',Arial,sans-serif; background:var(--bg-light); color:var(--text-primary); line-height:1.6; }

        /* NAVBAR */
        .navbar { background:#fff; border-bottom:2px solid var(--border-color); padding:1rem 0; box-shadow:0 1px 3px rgba(0,0,0,.05); position:sticky; top:0; z-index:1020; }
        .navbar-brand { font-weight:700; font-size:22px; color:var(--primary)!important; display:flex; align-items:center; gap:10px; }
        .navbar-brand i { font-size:28px; }
        .navbar-nav .nav-link { color:var(--text-secondary)!important; font-weight:500; padding:8px 16px!important; transition:all .3s; border-radius:6px; margin:0 4px; }
        .navbar-nav .nav-link:hover { color:var(--primary)!important; background:var(--primary-light); }
        .user-info { display:flex; align-items:center; gap:12px; padding:8px 16px; background:var(--bg-light); border-radius:8px; }
        .user-info i { color:var(--primary); font-size:18px; }
        .user-info span { font-weight:600; color:var(--text-primary); }

        /* SIDEBAR */
        .sidebar { position:fixed; left:0; top:60px; width:260px; height:calc(100vh - 60px); background:var(--sidebar-bg); border-right:1px solid var(--sidebar-border); overflow-y:auto; padding:20px 0; z-index:1010; }
        .sidebar-title { padding:10px 14px; margin:8px 12px; border-radius:10px; background:#f3f4f6; border:1px solid var(--border-color); font-size:12px; font-weight:800; text-transform:uppercase; color:var(--text-secondary); letter-spacing:.5px; }
        .sidebar-section.section-active .sidebar-title { background:var(--primary-light); border-color:#bfdbfe; color:var(--primary); }
        .sidebar-section { margin:16px 10px; padding:8px 6px 12px; border-radius:12px; background:#fff; }
        .sidebar-section.section-active { background:#eff6ff; border:1px solid #bfdbfe; }
        .sidebar .nav-link { color:var(--text-secondary)!important; padding:5px 20px; margin:1px 8px; border-radius:8px; transition:all .2s; display:flex; align-items:center; gap:12px; font-weight:500; border-left:3px solid transparent; }
        .sidebar .nav-link i { width:20px; text-align:center; font-size:16px; }
        .sidebar .nav-link:hover { background:var(--primary-light); color:var(--primary)!important; border-left-color:var(--primary); transform:translateX(4px); }
        .sidebar .nav-link.active { background:var(--primary-light); color:var(--primary)!important; border-left-color:var(--primary); font-weight:600; }

        /* MAIN */
        .main-wrapper { display:flex; }
        .main-content { width:100%; margin-left:260px; padding:30px; min-height:calc(100vh - 60px); background:var(--bg-light); }
        .breadcrumb-pill { display:inline-flex; align-items:center; gap:10px; background:#fff; border:1px solid var(--border-color); border-radius:999px; padding:10px 14px; font-weight:700; color:var(--text-secondary); margin-bottom:16px; }
        .breadcrumb-pill .section { color:var(--primary); }
        .breadcrumb-pill .sep { opacity:.5; }

        @media(max-width:992px) {
            .sidebar { transform:translateX(-100%); transition:transform .3s; width:240px; box-shadow:2px 0 8px rgba(0,0,0,.1); }
            .sidebar.show { transform:translateX(0); }
            .main-content { margin-left:0; }
            .navbar-toggler { display:block!important; }
        }
        @media print {
            .navbar,.sidebar,.navbar-toggler,.btn,nav { display:none!important; }
            .main-content { margin-left:0; padding:0; }
        }
        ::-webkit-scrollbar { width:8px; height:8px; }
        ::-webkit-scrollbar-track { background:var(--bg-light); }
        ::-webkit-scrollbar-thumb { background:#cbd5e1; border-radius:4px; }
        ::-webkit-scrollbar-thumb:hover { background:#94a3b8; }
    </style>
</head>
<body>

<nav class="navbar navbar-expand-lg">
    <div class="container-fluid">
        <a class="navbar-brand" href="index.php?action=home">
            <?php if ($logoUrl): ?>
                <img src="<?= htmlspecialchars($logoUrl) ?>" alt="Logo" style="height:32px;width:auto;margin-right:10px;">
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

<aside class="sidebar" id="sidebar">
<?php
// Définition de toutes les sections dans l'ordre par défaut
$sections = [

    'principal' => [
        'label'    => '🏠 Menu principal',
        'prefixes' => ['home','clients','fournisseurs','articles'],
        'html'     => function() use ($isActive) { ?>
            <nav class="nav flex-column">
                <a class="nav-link <?= $isActive('home') ?>" href="index.php?action=home"><i class="fas fa-home"></i><span>Accueil</span></a>
                <?php if (hasPermission('clients','view')): ?><a class="nav-link <?= $isActive('clients') ?>" href="index.php?action=clients"><i class="fas fa-users"></i><span>Clients</span></a><?php endif; ?>
                <?php if (hasPermission('fournisseurs','view')): ?><a class="nav-link <?= $isActive('fournisseurs') ?>" href="index.php?action=fournisseurs"><i class="fas fa-truck"></i><span>Fournisseurs</span></a><?php endif; ?>
                <?php if (hasPermission('articles','view')): ?><a class="nav-link <?= $isActive('articles') ?>" href="index.php?action=articles"><i class="fas fa-boxes"></i><span>Articles</span></a><?php endif; ?>
            </nav>
        <?php }
    ],

    'ventes' => [
        'label'    => '💼 Ventes',
        'prefixes' => ['devis','livraisons','factures','credit','versements'],
        'html'     => function() use ($isActive) { ?>
            <nav class="nav flex-column">
                <?php if (hasPermission('devis','view')): ?><a class="nav-link <?= $isActive('devis') ?>" href="index.php?action=devis"><i class="fas fa-file-invoice-dollar"></i><span>Devis</span></a><?php endif; ?>
                <?php if (hasPermission('livraisons','view')): ?><a class="nav-link <?= $isActive('livraisons') ?>" href="index.php?action=livraisons"><i class="fas fa-truck-fast"></i><span>Livraisons</span></a><?php endif; ?>
                <?php if (hasPermission('factures','view')): ?><a class="nav-link <?= $isActive('factures') ?>" href="index.php?action=factures"><i class="fas fa-file-invoice"></i><span>Factures</span></a><?php endif; ?>
                <?php if (hasPermission('versements','view')): ?><a class="nav-link <?= $isActive('versements') ?>" href="index.php?action=versements"><i class="fas fa-money-bill-wave"></i><span>Versements</span></a><?php endif; ?>
            </nav>
        <?php }
    ],

    'achats' => [
        'label'    => '🛒 Achats',
        'prefixes' => ['factures_fournisseurs','versements_fournisseurs'],
        'html'     => function() use ($isActive) { ?>
            <nav class="nav flex-column">
                <a class="nav-link <?= $isActive('factures_fournisseurs') ?>" href="index.php?action=factures_fournisseurs"><i class="fas fa-file-invoice"></i><span>Factures fournisseurs</span></a>
                <?php if (hasPermission('versements_fournisseurs','view')): ?><a class="nav-link <?= $isActive('versements_fournisseurs') ?>" href="index.php?action=versements_fournisseurs"><i class="fas fa-money-check-dollar"></i><span>Versements fournisseurs</span></a><?php endif; ?>
            </nav>
        <?php }
    ],

    'stock' => [
        'label'    => '📦 Stock',
        'prefixes' => ['depots','categories','stock_movements','transferts_stock','receptions_fournisseur'],
        'html'     => function() use ($isActive) { ?>
            <nav class="nav flex-column">
                <a class="nav-link <?= $isActive('depots') ?>" href="index.php?action=depots"><i class="fas fa-warehouse"></i><span>Dépôts</span></a>
                <?php if (hasPermission('categories','view')): ?><a class="nav-link <?= $isActive('categories') ?>" href="index.php?action=categories"><i class="fas fa-tags"></i><span>Catégories</span></a><?php endif; ?>
                <?php if (hasPermission('stock_movements','view')): ?><a class="nav-link <?= $isActive('stock_movements') ?>" href="index.php?action=stock_movements"><i class="fas fa-history"></i><span>Mouvements de stock</span></a><?php endif; ?>
                <a class="nav-link <?= $isActive('transferts_stock') ?>" href="index.php?action=transferts_stock"><i class="fas fa-exchange-alt"></i><span>Transferts dépôt</span></a>
                <?php if (hasPermission('stock_movements','view')): ?><a class="nav-link <?= $isActive('stock_alerts') ?>" href="index.php?action=stock_movements/alerts"><i class="fas fa-bell"></i><span>Alertes stock</span></a><?php endif; ?>
            </nav>
        <?php }
    ],

    'suivi' => [
        'label'    => '📈 Suivi',
        'prefixes' => ['client_releve','fournisseur_releve','releve','fs','dashboard'],
        'html'     => function() use ($isActive) { ?>
            <nav class="nav flex-column">
                <a class="nav-link <?= $isActive('client_releve') ?>" href="index.php?action=client_releve/index"><i class="fas fa-users"></i><span>Suivi clients</span></a>
                <a class="nav-link <?= $isActive('fournisseur_releve') ?>" href="index.php?action=fournisseur_releve/index"><i class="fas fa-truck"></i><span>Suivi fournisseurs</span></a>
                <a class="nav-link <?= $isActive('releve') ?>" href="index.php?action=releve"><i class="fas fa-list-check"></i><span>Relevés clients</span></a>
                <a class="nav-link <?= $isActive('fs') ?>" href="index.php?action=fs"><i class="fas fa-receipt"></i><span>Relevés fournisseurs</span></a>
                <a class="nav-link <?= $isActive('dashboard') ?>" href="index.php?action=dashboard"><i class="fas fa-chart-line"></i><span>Tableau de bord</span></a>
            </nav>
        <?php }
    ],

    'logistique' => [
        'label'    => '🚚 Logistique',
        'prefixes' => ['voiture'],
        'html'     => function() use ($isActive) { ?>
            <nav class="nav flex-column">
                <a class="nav-link <?= $isActive('voiture') ?>" href="index.php?action=voiture"><i class="fas fa-car"></i><span>Véhicules</span></a>
            </nav>
        <?php }
    ],

    'maintenance' => [
        'label'    => '🧰 Maintenance',
        'admin'    => true,
        'prefixes' => ['sauvegarde'],
        'html'     => function() use ($isActive) { ?>
            <nav class="nav flex-column">
                <a class="nav-link <?= $isActive('sauvegarde') ?>" href="index.php?action=sauvegarde"><i class="fas fa-database"></i><span>Sauvegardes</span></a>
            </nav>
        <?php }
    ],

    'parametres' => [
        'label'    => '⚙️ Paramètres',
        'admin'    => true,
        'prefixes' => ['users','settings','tresorerie'],
        'html'     => function() use ($isActive) { ?>
            <nav class="nav flex-column">
                <a class="nav-link <?= $isActive('users') ?>" href="index.php?action=users"><i class="fas fa-users-cog"></i><span>Utilisateurs</span></a>
                <a class="nav-link <?= $isActive('settings') ?>" href="index.php?action=settings"><i class="fas fa-cog"></i><span>Paramètres</span></a>
                <a class="nav-link <?= $isActive('tresorerie') ?>" href="index.php?action=tresorerie"><i class="fas fa-cash-register"></i><span>Trésorerie</span></a>
            </nav>
        <?php }
    ],
];

// Trouver la section active
$activeKey = null;
foreach ($sections as $key => $section) {
    foreach ($section['prefixes'] as $p) {
        if ($action === $p || str_starts_with($action, $p . '/')) {
            $activeKey = $key;
            break 2;
        }
    }
}

// Réordonner : section active en premier
if ($activeKey && isset($sections[$activeKey])) {
    $active  = [$activeKey => $sections[$activeKey]];
    $rest    = array_filter($sections, fn($k) => $k !== $activeKey, ARRAY_FILTER_USE_KEY);
    $sections = $active + $rest;
}

// Afficher
foreach ($sections as $key => $section):
    $isAdmin = $section['admin'] ?? false;
    if ($isAdmin && ($_SESSION['role'] ?? '') !== 'admin') continue;
    $sectionActive = $activeKey === $key ? 'section-active' : '';
?>
    <div class="sidebar-section <?= $sectionActive ?>">
        <div class="sidebar-title"><?= $section['label'] ?></div>
        <?php ($section['html'])(); ?>
    </div>
<?php endforeach; ?>
</aside>
<div class="main-wrapper">
    <main class="main-content">
        <?php if (isset($_SESSION['user_id']) && $action !== 'login'): ?>
        <div class="breadcrumb-pill">
            <span class="section"><?= htmlspecialchars($breadcrumbSection) ?></span>
            <span class="sep">›</span>
            <span class="page"><?= htmlspecialchars($breadcrumbPage) ?></span>
        </div>
        <?php endif; ?>
