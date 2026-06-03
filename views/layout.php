<<<<<<< HEAD
<?php 

require_once __DIR__ . '\..\includes\permissions.php';
// Vérification session unique
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

?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>SODISAPP</title>
    <link rel="icon" type="image/x-icon" href="/S6/public/img/images.ico">
    <!-- Bootstrap CSS local -->
    <link rel="stylesheet" href="/S6/public/css/bootstrap.min.css">
    <link rel="stylesheet" href="/s6/public/css/autocomplete.css">
    <style>
        .cell-facture { max-width: 120px; white-space: normal; word-break: break-all; }
        .cell-montant, .cell-versement, .cell-total { white-space: nowrap; }
        #right-sidebar { position: fixed; top: 56px; right: 0; width: 100px; height: 100%; background: #f8f9fa; border-left: 1px solid #dee2e6; display: flex; flex-direction: column; align-items: stretch; padding-top: 30px; z-index: 100; }
        #right-sidebar .nav-link { margin: 8px 10px; padding: 15px 0; border-radius: 5px; text-align: center; font-weight: bold; color: #000; background: #e9ecef; border: none; transition: background 0.2s, color 0.2s; }
        #right-sidebar .nav-link:hover { background: #007bff; color: #fff; cursor: pointer; }
        .main-content { margin-right: 130px; padding: 20px; }
        @media print {
            body * { visibility: hidden !important; }
            .container, .container * { visibility: visible !important; }
            .container { position: absolute !important; left: 0; top: 0; width: 100% !important; z-index: 1000; background: #fff !important; }
            .btn, .mb-3, .float-right, .ml-2, .mb-3 a, .mb-3 button { display: none !important; }
            #right-sidebar, .sidebar, #sidebar, nav, .vertical-menu, .menu-lateral, .navbar { display: none !important; }
            @media print { body.bl-print .prix-unitaire, body.bl-print .total-ligne, body.bl-print .footer .total-general {display: none !important;}
                           body.bl-print td.prix-unitaire, body.bl-print td.total-ligne {display: none !important;}
                           body.bl-print th[style*="width:20%"]:not(.prix-unitaire):not(.total-ligne) {width: 30% !important; }
                           body.bl-print th[style*="width:40%"] {width: 70% !important;}
            }
        }
        .sidebar-backup-link {
            margin-top: auto;
            margin-bottom: 16px;
            background: #ffeeba;
            color: #856404;
            font-weight: bold;
            border: 1px solid #ffeeba;
        }
        .sidebar-backup-link:hover {
            background: #ffc107;
            color: #212529;
        }
    </style>
</head>
<body>
    <nav class="navbar navbar-expand-lg navbar-light bg-light fixed-top shadow-sm">
        <a class="navbar-brand font-weight-bold text-primary" href="index.php?action=home">MQCI</a>
        <button class="navbar-toggler" type="button" data-toggle="collapse" data-target="#navbarNav" aria-controls="navbarNav" aria-expanded="false" aria-label="Toggle navigation">
            <span class="navbar-toggler-icon"></span>
        </button>
        <div class="collapse navbar-collapse" id="navbarNav">
            <ul class="navbar-nav">
                <?php if (isset($_SESSION['user_id'])): ?>
                    <?php if (hasPermission('clients', 'view')): ?>
                        <li class="nav-item"><a class="nav-link" href="index.php?action=clients">Clients</a></li>
                    <?php endif; ?>
                    <?php if (hasPermission('fournisseurs', 'view')): ?>
                        <li class="nav-item"><a class="nav-link" href="index.php?action=fournisseurs">Fournisseurs</a></li>
                    <?php endif; ?>
                    <?php if (hasPermission('articles', 'view')): ?>
                        <li class="nav-item"><a class="nav-link" href="index.php?action=articles">Articles</a></li>
                    <?php endif; ?>
                    <?php if (hasPermission('devis', 'view')): ?>
                        <li class="nav-item"><a class="nav-link" href="index.php?action=devis">Devis</a></li>
                    <?php endif; ?>
                    <?php if (hasPermission('dashboard', 'view')): ?>
                        <li class="nav-item"><a class="nav-link" href="index.php?action=dashboard">Tableau de bord</a></li>
                    <?php endif; ?>
                    <?php else: ?>
                    <li class="nav-item"><a class="nav-link" href="index.php?action=login">Connexion</a></li>
                <?php endif; ?>
            </ul>
             <ul class="navbar-nav ml-auto">
                <?php if (isset($_SESSION['user_id']) && $_SESSION['role'] === 'admin'): ?>
                    <li class="nav-item"><a class="nav-link" href="index.php?action=users">Utilisateurs</a></li>
                <?php endif; ?>
                <?php if (isset($_SESSION['user_id'])): ?>
                    <li class="nav-item"><a class="nav-link" href="index.php?action=logout">Déconnexion</a></li>
                <?php endif; ?>
            </ul>
        </div>
    </nav>
    <?php if (isset($_SESSION['user_id'])): ?>
        <div id="right-sidebar">
            <?php if (hasPermission('credit', 'view')): ?>
                <a class="nav-link" href="index.php?action=credit">Crédit</a>
            <?php endif; ?>
            <?php if (hasPermission('releve', 'view')): ?>
                <a class="nav-link" href="index.php?action=releve">Relevé</a>
            <?php endif; ?>
            <?php if (hasPermission('fs', 'view')): ?>
                <a class="nav-link" href="index.php?action=fs">f/s</a>
            <?php endif; ?>
            <?php if (hasPermission('voiture', 'view')): ?>
                <a class="nav-link" href="index.php?action=voiture">Voiture</a>
            <?php endif; ?>
            <?php if (isset($_SESSION['role']) && $_SESSION['role'] === 'admin'): ?>
                <a class="nav-link sidebar-backup-link" href="index.php?action=sauvegarde" title="Sauvegarder ou télécharger les sauvegardes">
                    Sauvegarde
                </a>
            <?php endif; ?>
        </div>
    <?php endif; ?>
    <div class="main-content">
        <?php // Ici s'affichera le contenu des vues PHP ?>
    </div>
    <!-- jQuery d'abord ! -->
    <script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
    <script src="/S6/public/js/popper.min.js"></script>
    <script src="/S6/public/js/bootstrap.min.js"></script>
    <script src="/s6/public/js/script.js"></script>
</body>
=======
<?php 

require_once __DIR__ . '\..\includes\permissions.php';
// Vérification session unique
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

?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>SODISAPP</title>
    <link rel="icon" type="image/x-icon" href="/S6/public/img/images.ico">
    <!-- Bootstrap CSS local -->
    <link rel="stylesheet" href="/S6/public/css/bootstrap.min.css">
    <link rel="stylesheet" href="/s6/public/css/autocomplete.css">
    <style>
        .cell-facture { max-width: 120px; white-space: normal; word-break: break-all; }
        .cell-montant, .cell-versement, .cell-total { white-space: nowrap; }
        #right-sidebar { position: fixed; top: 56px; right: 0; width: 100px; height: 100%; background: #f8f9fa; border-left: 1px solid #dee2e6; display: flex; flex-direction: column; align-items: stretch; padding-top: 30px; z-index: 100; }
        #right-sidebar .nav-link { margin: 8px 10px; padding: 15px 0; border-radius: 5px; text-align: center; font-weight: bold; color: #000; background: #e9ecef; border: none; transition: background 0.2s, color 0.2s; }
        #right-sidebar .nav-link:hover { background: #007bff; color: #fff; cursor: pointer; }
        .main-content { margin-right: 130px; padding: 20px; }
        @media print {
            body * { visibility: hidden !important; }
            .container, .container * { visibility: visible !important; }
            .container { position: absolute !important; left: 0; top: 0; width: 100% !important; z-index: 1000; background: #fff !important; }
            .btn, .mb-3, .float-right, .ml-2, .mb-3 a, .mb-3 button { display: none !important; }
            #right-sidebar, .sidebar, #sidebar, nav, .vertical-menu, .menu-lateral, .navbar { display: none !important; }
            @media print { body.bl-print .prix-unitaire, body.bl-print .total-ligne, body.bl-print .footer .total-general {display: none !important;}
                           body.bl-print td.prix-unitaire, body.bl-print td.total-ligne {display: none !important;}
                           body.bl-print th[style*="width:20%"]:not(.prix-unitaire):not(.total-ligne) {width: 30% !important; }
                           body.bl-print th[style*="width:40%"] {width: 70% !important;}
            }
        }
        .sidebar-backup-link {
            margin-top: auto;
            margin-bottom: 16px;
            background: #ffeeba;
            color: #856404;
            font-weight: bold;
            border: 1px solid #ffeeba;
        }
        .sidebar-backup-link:hover {
            background: #ffc107;
            color: #212529;
        }
    </style>
</head>
<body>
    <nav class="navbar navbar-expand-lg navbar-light bg-light fixed-top shadow-sm">
        <a class="navbar-brand font-weight-bold text-primary" href="index.php?action=home">MQCI</a>
        <button class="navbar-toggler" type="button" data-toggle="collapse" data-target="#navbarNav" aria-controls="navbarNav" aria-expanded="false" aria-label="Toggle navigation">
            <span class="navbar-toggler-icon"></span>
        </button>
        <div class="collapse navbar-collapse" id="navbarNav">
            <ul class="navbar-nav">
                <?php if (isset($_SESSION['user_id'])): ?>
                    <?php if (hasPermission('clients', 'view')): ?>
                        <li class="nav-item"><a class="nav-link" href="index.php?action=clients">Clients</a></li>
                    <?php endif; ?>
                    <?php if (hasPermission('fournisseurs', 'view')): ?>
                        <li class="nav-item"><a class="nav-link" href="index.php?action=fournisseurs">Fournisseurs</a></li>
                    <?php endif; ?>
                    <?php if (hasPermission('articles', 'view')): ?>
                        <li class="nav-item"><a class="nav-link" href="index.php?action=articles">Articles</a></li>
                    <?php endif; ?>
                    <?php if (hasPermission('devis', 'view')): ?>
                        <li class="nav-item"><a class="nav-link" href="index.php?action=devis">Devis</a></li>
                    <?php endif; ?>
                    <?php if (hasPermission('dashboard', 'view')): ?>
                        <li class="nav-item"><a class="nav-link" href="index.php?action=dashboard">Tableau de bord</a></li>
                    <?php endif; ?>
                    <?php else: ?>
                    <li class="nav-item"><a class="nav-link" href="index.php?action=login">Connexion</a></li>
                <?php endif; ?>
            </ul>
             <ul class="navbar-nav ml-auto">
                <?php if (isset($_SESSION['user_id']) && $_SESSION['role'] === 'admin'): ?>
                    <li class="nav-item"><a class="nav-link" href="index.php?action=users">Utilisateurs</a></li>
                <?php endif; ?>
                <?php if (isset($_SESSION['user_id'])): ?>
                    <li class="nav-item"><a class="nav-link" href="index.php?action=logout">Déconnexion</a></li>
                <?php endif; ?>
            </ul>
        </div>
    </nav>
    <?php if (isset($_SESSION['user_id'])): ?>
        <div id="right-sidebar">
            <?php if (hasPermission('credit', 'view')): ?>
                <a class="nav-link" href="index.php?action=credit">Crédit</a>
            <?php endif; ?>
            <?php if (hasPermission('releve', 'view')): ?>
                <a class="nav-link" href="index.php?action=releve">Relevé</a>
            <?php endif; ?>
            <?php if (hasPermission('fs', 'view')): ?>
                <a class="nav-link" href="index.php?action=fs">f/s</a>
            <?php endif; ?>
            <?php if (hasPermission('voiture', 'view')): ?>
                <a class="nav-link" href="index.php?action=voiture">Voiture</a>
            <?php endif; ?>
            <?php if (isset($_SESSION['role']) && $_SESSION['role'] === 'admin'): ?>
                <a class="nav-link sidebar-backup-link" href="index.php?action=sauvegarde" title="Sauvegarder ou télécharger les sauvegardes">
                    Sauvegarde
                </a>
            <?php endif; ?>
        </div>
    <?php endif; ?>
    <div class="main-content">
        <?php // Ici s'affichera le contenu des vues PHP ?>
    </div>
    <!-- jQuery d'abord ! -->
    <script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
    <script src="/S6/public/js/popper.min.js"></script>
    <script src="/S6/public/js/bootstrap.min.js"></script>
    <script src="/s6/public/js/script.js"></script>
</body>
>>>>>>> 4f8dbbb6b83eb9c6f755d57287033c7da885a3b1
</html>