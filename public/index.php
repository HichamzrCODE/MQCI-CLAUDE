<?php

/**
 * MQCI 2.0 — Point d'entrée unique
 *
 * Améliorations :
 * - Chargement .env via phpdotenv (credentials hors du code)
 * - Connexion PDO unique, partagée via $db globale
 * - Vérifications de permission via hasPermission() sur toutes les routes
 * - Plus de whitelist de noms d'utilisateurs hardcodés
 * - Helper requireRole() et requirePermission() pour éviter les répétitions
 * - Suppression de test_backup.php référencé (à effacer du dossier)
 */

// ─── 1. Chargement des dépendances Composer (.env inclus) ───────────────────
require_once __DIR__ . '/../vendor/autoload.php';

$dotenv = Dotenv\Dotenv::createImmutable(dirname(__DIR__));
$dotenv->load();

// ─── 2. Démarrage de la session ──────────────────────────────────────────────
require_once __DIR__ . '/../includes/licence_check.php';
session_start();

require_once __DIR__ . '/../includes/CsrfMiddleware.php';

if (empty($_SESSION['csrf_token'])) {
    CsrfMiddleware::regenerate();
}

// ─── 3. Autoloading des classes métier ──────────────────────────────────────
spl_autoload_register(function (string $classname): void {
    foreach (['../models/', '../controllers/', '../includes/'] as $path) {
        $file = __DIR__ . '/' . $path . $classname . '.php';
        if (file_exists($file)) {
            require_once $file;
            return;
        }
    }
});

// Configuration de la base de données
$dbConfig = require '../config/database.php';

try {
    $db = new PDO(
        "mysql:host={$dbConfig['host']};dbname={$dbConfig['database']};charset={$dbConfig['charset']}",
        $dbConfig['username'],
        $dbConfig['password'],
        $dbConfig['options']
    );
} catch (PDOException $e) {
    die("Erreur de connexion à la base de données : " . $e->getMessage());
}

// Rafraîchir l'activité de l'utilisateur connecté
if (isset($_SESSION['user_id'])) {
    $stmtActivity = $db->prepare("UPDATE users SET last_login = NOW() WHERE id_users = ?");
    $stmtActivity->execute([$_SESSION['user_id']]);
}

// Routage principal
$action = $_GET['action'] ?? 'home';

// Liste des actions accessibles sans connexion
$publicActions = ['home', 'login', 'register'];

if (!isset($_SESSION['user_id']) && !in_array($action, $publicActions)) {
    header('Location: index.php?action=login');
    exit();
}


switch ($action) {
    // ... reste du code

        // --- UTILISATEURS (ADMIN SEULEMENT) ---
    case 'users':
        if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') die("Accès refusé.");
        $controller = new UsersController($db);
        $viewData = $controller->index();
        break;
    case 'users/create':
        if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') die("Accès refusé.");
        $controller = new UsersController($db);
        $viewData = $controller->create($_POST);
        break;
    case 'users/edit':
        if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') die("Accès refusé.");
        $id = $_GET['id'] ?? null;
        if ($id === null || !is_numeric($id)) die("ID utilisateur invalide.");
        $controller = new UsersController($db);
        $viewData = $controller->edit((int)$id, $_POST);
        break;
    case 'users/delete':
        if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') die("Accès refusé.");
        $id = $_GET['id'] ?? null;
        if ($id === null || !is_numeric($id)) die("ID utilisateur invalide.");
        $controller = new UsersController($db);
        $controller->delete((int)$id);
        exit();
        break;
    case 'users/disconnect':
            if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') die("Accès refusé.");
       $id = $_GET['id'] ?? null;
        if ($id === null || !is_numeric($id)) die("ID utilisateur invalide.");
        $controller = new UsersController($db);
        $controller->disconnect((int)$id);
        exit();
        break;

// VERSEMENT
case 'versements':
case 'versements/index':
    $controller = new VersementController($db);
    $viewData = $controller->index();
    break;

case 'versements/create':
    $controller = new VersementController($db);
    $viewData = $controller->create($_POST);
    break;

case 'versements/edit':
    $id = $_GET['id'] ?? null;
    if ($id === null || !is_numeric($id)) die("ID versement invalide.");
    $controller = new VersementController($db);
    $viewData = $controller->edit((int)$id, $_POST);
    break;

case 'versements/cancel':
    $id = $_GET['id'] ?? null;
    if ($id === null || !is_numeric($id)) die("ID versement invalide.");
    $controller = new VersementController($db);
    $controller->cancel((int)$id);
    exit();

    case 'versements/delete':
    if (!isset($_SESSION['user_id']) || ($_SESSION['role'] ?? '') !== 'admin') die("Accès refusé.");
    $id = $_GET['id'] ?? null;
    if ($id === null || !is_numeric($id)) die("ID versement invalide.");
    $controller = new VersementController($db);
    $controller->delete((int)$id);
    exit();


    case 'versements/hide':
    if (!isset($_SESSION['user_id']) || ($_SESSION['role'] ?? '') !== 'admin') die("Accès refusé.");
    $id = $_GET['id'] ?? null;
    if ($id === null || !is_numeric($id)) die("ID versement invalide.");
    $controller = new VersementController($db);
    $controller->hide((int)$id);
    exit();

    case 'versements/print':
    $id = $_GET['id'] ?? null;
    if ($id === null || !is_numeric($id)) die("ID versement invalide.");
    $controller = new VersementController($db);
    $viewData = $controller->print((int)$id);
    break;

// VERSEMENTS FOURNISSEURS
case 'versements_fournisseurs':
case 'versements_fournisseurs/index':
    $controller = new VersementFournisseurController($db);
    $viewData = $controller->index();
    break;

case 'versements_fournisseurs/create':
    $controller = new VersementFournisseurController($db);
    $viewData = $controller->create($_POST);
    break;

case 'versements_fournisseurs/edit':
    $id = $_GET['id'] ?? null;
    if ($id === null || !is_numeric($id)) die("ID versement fournisseur invalide.");
    $controller = new VersementFournisseurController($db);
    $viewData = $controller->edit((int)$id, $_POST);
    break;

case 'versements_fournisseurs/cancel':
    $id = $_GET['id'] ?? null;
    if ($id === null || !is_numeric($id)) die("ID versement fournisseur invalide.");
    $controller = new VersementFournisseurController($db);
    $controller->cancel((int)$id);
    exit();

case 'versements_fournisseurs/delete':
    if (!isset($_SESSION['user_id']) || ($_SESSION['role'] ?? '') !== 'admin') die("Accès refusé.");
    $id = $_GET['id'] ?? null;
    if ($id === null || !is_numeric($id)) die("ID versement fournisseur invalide.");
    $controller = new VersementFournisseurController($db);
    $controller->delete((int)$id);
    exit();

case 'versements_fournisseurs/hide':
    if (!isset($_SESSION['user_id']) || ($_SESSION['role'] ?? '') !== 'admin') die("Accès refusé.");
    $id = $_GET['id'] ?? null;
    if ($id === null || !is_numeric($id)) die("ID versement fournisseur invalide.");
    $controller = new VersementFournisseurController($db);
    $controller->hide((int)$id);
    exit();

case 'versements_fournisseurs/print':
    $id = $_GET['id'] ?? null;
    if ($id === null || !is_numeric($id)) die("ID versement fournisseur invalide.");
    $controller = new VersementFournisseurController($db);
    $viewData = $controller->print((int)$id);
    break;

        // --- SETTINGS ---
   case 'settings':
    $controller = new SettingsController($db);
    $viewData = $controller->index();
    break;

case 'settings/devis':
    $controller = new SettingsController($db);
    $viewData = $controller->devis();
    break;

// --- TRESORERIE (ADMIN) ---
case 'tresorerie':
case 'tresorerie/index':
    if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') die("Accès refusé.");
    $controller = new TresorerieController($db);
    $viewData = $controller->index();
    break;

case 'tresorerie/create':
    if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') die("Accès refusé.");
    $controller = new TresorerieController($db);
    $viewData = $controller->create($_POST);
    break;

case 'tresorerie/delete':
    if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') die("Accès refusé.");
    $controller = new TresorerieController($db);
    $controller->delete($_GET);
    exit();


 // --- TRANSFERTS DE STOCK ---
    case 'transferts_stock':
        $controller = new TransfertStockController($db);
        $viewData = $controller->index();
        break;
    case 'transferts_stock/create':
        $controller = new TransfertStockController($db);
        $viewData = $controller->create($_POST);
        break;
    case 'transferts_stock/show':
        $id = $_GET['id'] ?? null;
        if ($id === null || !is_numeric($id)) die("ID de transfert invalide.");
        $controller = new TransfertStockController($db);
        $viewData = $controller->show((int)$id);
        break;
    case 'transferts_stock/addLigne':
        $controller = new TransfertStockController($db);
        $controller->addLigne();
        exit();
    case 'transferts_stock/removeLigne':
        $controller = new TransfertStockController($db);
        $controller->removeLigne();
        exit();
    case 'transferts_stock/valider':
        $controller = new TransfertStockController($db);
        $controller->valider();
        exit();
    case 'transferts_stock/delete':
        $controller = new TransfertStockController($db);
        $controller->delete();
        exit();
    case 'transferts_stock/searchArticles':
        $controller = new TransfertStockController($db);
        $controller->searchArticles();
        exit();

    // --- RÉCEPTIONS FOURNISSEUR ---
    case 'receptions_fournisseur':
        $controller = new ReceptionFournisseurController($db);
        $viewData = $controller->index();
        break;
    case 'receptions_fournisseur/create':
        $controller = new ReceptionFournisseurController($db);
        $viewData = $controller->create($_POST);
        break;
    case 'receptions_fournisseur/show':
        $id = $_GET['id'] ?? null;
        if ($id === null || !is_numeric($id)) die("ID de réception invalide.");
        $controller = new ReceptionFournisseurController($db);
        $viewData = $controller->show((int)$id);
        break;
    case 'receptions_fournisseur/addLigne':
        $controller = new ReceptionFournisseurController($db);
        $controller->addLigne();
        exit();
    case 'receptions_fournisseur/updateLigne':
        $controller = new ReceptionFournisseurController($db);
        $controller->updateLigne();
        exit();
    case 'receptions_fournisseur/removeLigne':
        $controller = new ReceptionFournisseurController($db);
        $controller->removeLigne();
        exit();
    case 'receptions_fournisseur/valider':
        $controller = new ReceptionFournisseurController($db);
        $controller->valider();
        exit();
    case 'receptions_fournisseur/delete':
        $controller = new ReceptionFournisseurController($db);
        $controller->delete();
        exit();
    case 'receptions_fournisseur/searchArticles':
        $controller = new ReceptionFournisseurController($db);
        $controller->searchArticles();
        exit();

case 'factures_fournisseurs':
    $controller = new FactureFournisseurController($db);
    $viewData = $controller->index();
    break;

case 'factures_fournisseurs/create':
    $controller = new FactureFournisseurController($db);
    $viewData = $controller->create($_POST);
    break;

case 'factures_fournisseurs/edit':
    $controller = new FactureFournisseurController($db);
    $viewData = $controller->edit((int)($_GET['id'] ?? 0));
    break;

case 'factures_fournisseurs/update':
    $controller = new FactureFournisseurController($db);
    $viewData = $controller->update((int)($_GET['id'] ?? 0), $_POST);
    break;

case 'factures_fournisseurs/show':
    $controller = new FactureFournisseurController($db);
    $viewData = $controller->show((int)($_GET['id'] ?? 0));
    break;

case 'factures_fournisseurs/validate':
    $controller = new FactureFournisseurController($db);
    $controller->validate((int)($_GET['id'] ?? 0));
    exit();

case 'factures_fournisseurs/delete':
    $controller = new FactureFournisseurController($db);
    $controller->delete((int)($_GET['id'] ?? 0));
    exit();


    // --- ACCUEIL & AUTHENTIFICATION ---
    case 'home':
        $controller = new HomeController($db);
        $viewData = $controller->index();
        break;
    case 'login':
        $controller = new AuthController($db);
        $viewData = $controller->login($_POST);
        break;
    case 'logout':
        $controller = new AuthController($db);
        $controller->logout();
        exit();

    // --- CLIENTS ---
    case 'clients':
        $controller = new ClientController($db);
        $viewData = $controller->index();
        break;
    case 'clients/create':
        $controller = new ClientController($db);
        $viewData = $controller->create($_POST);
        break;
    case 'clients/edit':
        $id = $_GET['id'] ?? null;
        if ($id === null || !is_numeric($id)) die("ID de client invalide.");
        $controller = new ClientController($db);
        $viewData = $controller->edit((int)$id, $_POST);
        break;
    case 'clients/delete':
        $id = $_GET['id'] ?? null;
        if ($id === null || !is_numeric($id)) die("ID de client invalide.");
        $controller = new ClientController($db);
        $controller->delete((int)$id);
        exit();
    case 'clients/show':
        $id = $_GET['id'] ?? null;
        if ($id === null || !is_numeric($id)) die("ID de client invalide.");
        $controller = new ClientController($db);
        $viewData = $controller->show((int)$id);
        break;
    case 'clients/search':
        $term = $_GET['term'] ?? '';
        $controller = new ClientController($db);
        $controller->search(['term' => $term]);
             // La méthode doit faire echo json_encode et exit
         exit();
         break;

    // --- FOURNISSEURS ---
    case 'fournisseurs':
        $controller = new FournisseurController($db);
        $viewData = $controller->index();
        break;
    case 'fournisseurs/create':
        $controller = new FournisseurController($db);
        $viewData = $controller->create($_POST);
        break;
    case 'fournisseurs/edit':
        $id = $_GET['id'] ?? null;
        if ($id === null || !is_numeric($id)) die("ID de Fournisseur invalide.");
        $controller = new FournisseurController($db);
        $viewData = $controller->edit((int)$id, $_POST);
        break;
    case 'fournisseurs/delete':
        $id = $_GET['id'] ?? null;
        if ($id === null || !is_numeric($id)) die("ID de Fournisseur invalide.");
        $controller = new FournisseurController($db);
        $controller->delete((int)$id);
        exit();
    case 'fournisseurs/show':
        $id = $_GET['id'] ?? null;
        if ($id === null || !is_numeric($id)) die("ID de Fournisseur invalide.");
        $controller = new FournisseurController($db);
        $viewData = $controller->show((int)$id);
        break;
    case 'fournisseurs/search':
        $term = $_GET['term'] ?? '';
        $controller = new FournisseurController($db);
        $controller->search(['term' => $term]);
         exit();


    // --- ARTICLES ---
    case 'articles':
    case 'articles/index':
        $controller = new ArticleController($db);
        $viewData = $controller->index($_GET);
        break;
    case 'articles/create':
        $controller = new ArticleController($db);
        $viewData = $controller->create($_POST);
        break;
    case 'articles/edit':
        $id = $_GET['id'] ?? null;
        if ($id === null || !is_numeric($id)) die("ID de l'article est invalide.");
        $controller = new ArticleController($db);
        $viewData = $controller->edit((int)$id, $_POST);
        break;
    case 'articles/delete':
        $id = $_GET['id'] ?? null;
        if ($id === null || !is_numeric($id)) die("ID de l'article est invalide.");
        $controller = new ArticleController($db);
        $controller->delete((int)$id);
        exit();
        break;
    case 'articles/search':
       $term = $_GET['term'] ?? '';
       $controller = new ArticleController($db);
       $controller->search(['term' => $term]);
       exit();
       break;
  case 'articles/show':
        $id = $_GET['id'] ?? null;
        if ($id === null || !is_numeric($id)) die("ID de l'article est invalide.");
        $controller = new ArticleController($db);
        $viewData = $controller->show((int)$id);
        break;
    case 'articles/historique-prix':
        $id = $_GET['id'] ?? null;
        if ($id === null || !is_numeric($id)) die("ID de l'article est invalide.");
        $controller = new ArticleController($db);
        $controller->historiquePrix((int)$id);
        exit();
        break;
    case 'articles/stock-par-depot':
        $id = $_GET['id'] ?? null;
        if ($id === null || !is_numeric($id)) die("ID de l'article est invalide.");
        $controller = new ArticleController($db);
        $controller->stockParDepot((int)$id);
        exit();
        break;
    case 'articles/export':
        $controller = new ArticleController($db);
        $controller->export();
        exit();
        break;
    case 'articles/import':
        $controller = new ArticleController($db);
        $viewData = $controller->import($_POST);
        break;
    
    // --- DEPOTS ---
case 'depots':
case 'depots/index':
    $controller = new DepotController($db);
    $viewData = $controller->index();
    break;
case 'depots/create':
    $controller = new DepotController($db);
    $viewData = $controller->create($_POST);
    break;
case 'depots/edit':
    $id = $_GET['id'] ?? null;
    if ($id === null || !is_numeric($id)) die("ID de depot invalide.");
    $controller = new DepotController($db);
    $viewData = $controller->edit((int)$id, $_POST);
    break;
        case 'depots/show':
        $id = $_GET['id'] ?? null;
        if ($id === null || !is_numeric($id)) die("ID de depot invalide.");
        $controller = new DepotController($db);
        $viewData = $controller->show((int)$id);
        break;


    // --- DEVIS ---
    case 'devis':
        $controller = new DevisController($db);
        $viewData = $controller->index();
        break;
    case 'devis/create':
        $controller = new DevisController($db);
        $viewData = $controller->create($_POST);
        break;
    case 'devis/detail':
        $id = $_GET['id'] ?? null;
        if ($id === null || !is_numeric($id)) die("ID de devis invalide.");
        $controller = new DevisController($db);
        $viewData = $controller->detail((int)$id);
        break;
    case 'devis/edit':
        $id = $_GET['id'] ?? null;
        if ($id === null || !is_numeric($id)) die("ID de devis invalide.");
        $controller = new DevisController($db);
        $viewData = $controller->edit((int)$id, $_POST);
        break;
    case 'devis/delete':
        $id = $_GET['id'] ?? null;
        if ($id === null || !is_numeric($id)) die("ID de devis invalide.");
        $controller = new DevisController($db);
        $controller->delete((int)$id);
        exit();
    case 'devis/deleteLigne':
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $ligneId = $_POST['ligne_id'] ?? null;
            if ($ligneId === null || !is_numeric($ligneId)) {
                header('Content-Type: application/json');
                echo json_encode(['success' => false, 'message' => 'ID de ligne invalide.']);
                exit();
            }
            $controller = new DevisController($db);
            $controller->deleteLigne((int)$ligneId);
            exit();
        } else {
            header('Content-Type: application/json');
            echo json_encode(['success' => false, 'message' => 'Méthode de requête incorrecte.']);
            exit();
        }
        break;
    case 'devis/searchArticles':
        $term = $_GET['term'] ?? '';
        $controller = new DevisController($db);
        $articles = $controller->searchArticles($term);
        header('Content-Type: application/json');
        echo json_encode($articles);
        exit();
    case 'devis/update':
        $id = $_GET['id'] ?? null;
        if ($id === null || !is_numeric($id)) die("ID de devis invalide.");
        $controller = new DevisController($db);
        $controller->update((int)$id, $_POST);
        exit();
        break;
    case 'devis/getClientArticlePrice':
        $clientId = $_GET['client_id'] ?? null;
        $articleId = $_GET['article_id'] ?? null;
        if ($clientId === null || !is_numeric($clientId) || $articleId === null || !is_numeric($articleId)) {
            header('Content-Type: application/json');
            echo json_encode(['error' => 'ID client ou ID article invalide.']);
            exit();
        }
        $controller = new DevisController($db);
        $controller->getArticlePrice((int)$clientId, (int)$articleId);
        exit();
        break;
    case 'devis/search':
        $controller = new DevisController($db);
        $viewData = $controller->search($_POST);
        break;

    case 'devis/pdf_wkhtml':
    $id = $_GET['id'] ?? null;
    if ($id === null || !is_numeric($id)) die("ID de devis invalide.");
    $controller = new DevisController($db);
    $controller->pdfWkhtml((int)$id);
    exit();

    case 'devis/validate':
    if ($_SERVER['REQUEST_METHOD'] !== 'POST') die("Méthode non autorisée.");
    $id = $_GET['id'] ?? null;
    if ($id === null || !is_numeric($id)) die("ID de devis invalide.");
    $controller = new DevisController($db);
    $controller->validate((int)$id);
    exit();

case 'devis/duplicate':
    $id = $_GET['id'] ?? null;
    if ($id === null || !is_numeric($id)) die("ID de devis invalide.");
    $controller = new DevisController($db);
    $controller->duplicate((int)$id);
    exit();

    // --- CREDIT ---
    case 'credit':
        $controller = new CreditController($db);
        $viewData = $controller->index();
        break;
    case 'credit_releve_ajax':
        header('Content-Type: application/json');
        $controller = new CreditController($db);
        $data = $controller->releveClientAjax($_GET['client_id'] ?? 0);
        echo json_encode($data);
        exit();


// --- LIVRAISONS (BL) ---
case 'livraisons':
case 'livraisons/index':
    $controller = new LivraisonController($db);
    $viewData = $controller->index($_GET);
    break;

case 'livraisons/createFromDevis':
    $devisId = $_GET['devis_id'] ?? null;
    if ($devisId === null || !is_numeric($devisId)) die("ID devis invalide.");
    $controller = new LivraisonController($db);
    $controller->createFromDevis((int)$devisId);
    exit();

case 'livraisons/edit':
    $id = $_GET['id'] ?? null;
    if ($id === null || !is_numeric($id)) die("ID BL invalide.");
    $controller = new LivraisonController($db);
    $viewData = $controller->edit((int)$id, $_POST);
    break;

case 'livraisons/show':
    $id = $_GET['id'] ?? null;
    if ($id === null || !is_numeric($id)) die("ID BL invalide.");
    $controller = new LivraisonController($db);
    $viewData = $controller->show((int)$id);
    break;

case 'livraisons/validate':
    if ($_SERVER['REQUEST_METHOD'] !== 'POST') die("Méthode non autorisée.");
    $id = $_GET['id'] ?? null;
    if ($id === null || !is_numeric($id)) die("ID BL invalide.");
    $controller = new LivraisonController($db);
    $controller->validate((int)$id);
    exit();

// REGISTER
    case 'register':
    $controller = new AuthController($db);
    $viewData = $controller->register($_POST);
    break;

// --- FACTURES ---
case 'factures':
case 'factures/index':
    $controller = new FactureController($db);
    $viewData = $controller->index($_GET);
    break;

case 'factures/createFromBl':
    $blId = $_GET['bl_id'] ?? null;
    if ($blId === null || !is_numeric($blId)) die("ID BL invalide.");
    $controller = new FactureController($db);
    $controller->createFromBl((int)$blId);
    exit();

case 'factures/edit':
    $id = $_GET['id'] ?? null;
    if ($id === null || !is_numeric($id)) die("ID facture invalide.");
    $controller = new FactureController($db);
    $viewData = $controller->edit((int)$id, $_POST);
    break;

case 'factures/show':
    $id = $_GET['id'] ?? null;
    if ($id === null || !is_numeric($id)) die("ID facture invalide.");
    $controller = new FactureController($db);
    $viewData = $controller->show((int)$id);
    break;

case 'factures/validate':
    if ($_SERVER['REQUEST_METHOD'] !== 'POST') die("Méthode non autorisée.");
    $id = $_GET['id'] ?? null;
    if ($id === null || !is_numeric($id)) die("ID facture invalide.");
    $controller = new FactureController($db);
    $controller->validate((int)$id);
    exit();

    // --- RELEVES ---
    case 'releve':
        $controller = new ReleveController($db);
        $viewData = $controller->index();
        break;
    case 'releve/create':
        if (!in_array($_SESSION['username'], ['admin','Abass','Moustapha'])) die("Accès refusé");
        $controller = new ReleveController($db);
        $viewData = $controller->create($_POST);
        break;
    case 'releve/edit':
        if (!in_array($_SESSION['username'], ['admin','Abass','Moustapha'])) die("Accès refusé");
        $controller = new ReleveController($db);
        $viewData = $controller->edit($_GET['id'], $_POST);
        break;
    case 'releve/delete':
        if (!in_array($_SESSION['username'], ['admin','Abass','Moustapha'])) die("Accès refusé");
        $controller = new ReleveController($db);
        $controller->delete($_GET['id']);
        exit();
    case 'releve/ajax':
        header('Content-Type: application/json');
        $controller = new ReleveController($db);
        $controller->ajax();
        exit();
    case 'releve/show':
        $id = $_GET['id'] ?? null;
        if ($id === null || !is_numeric($id)) die("ID de relevé invalide.");
        $controller = new ReleveController($db);
        $viewData = $controller->show((int)$id);
        break;
    case 'releve/extrait':
        $id = $_GET['id'] ?? null;
        $date_debut = $_GET['date_debut'] ?? null;
        $date_fin = $_GET['date_fin'] ?? null;
        if ($id === null || !is_numeric($id)) die("ID de relevé invalide.");
        $controller = new ReleveController($db);
        $viewData = $controller->extrait((int)$id, $date_debut, $date_fin);
        break;
        
  // --- RELEVE CLIENT AUTOMATIQUE (lecture seule, depuis devis + versements) ---
    case 'client_releve/show':
        $id = $_GET['id'] ?? null;
        if ($id === null || !is_numeric($id)) die("ID de client invalide.");
        $date_debut = $_GET['date_debut'] ?? null;
        $date_fin   = $_GET['date_fin']   ?? null;
        $page       = (int)($_GET['page'] ?? 1);
        $controller = new ClientReleveController($db);
        $viewData   = $controller->show((int)$id, $date_debut, $date_fin, $page);
        break;

case 'fournisseur_releve/show':
    $id = $_GET['id'] ?? null;
    if ($id === null || !is_numeric($id)) die("ID de fournisseur invalide.");
    $date_debut = $_GET['date_debut'] ?? null;
    $date_fin   = $_GET['date_fin']   ?? null;
    $page       = (int)($_GET['page'] ?? 1);
    $controller = new FournisseurReleveController($db);
    $viewData   = $controller->show((int)$id, $date_debut, $date_fin, $page);
    break;

case 'fournisseur_releve':
case 'fournisseur_releve/index':
    $controller = new FournisseurReleveController($db);
    $viewData   = $controller->index();
    break;
    
case 'client_releve':
case 'client_releve/index':
    $controller = new ClientReleveController($db);
    $viewData   = $controller->index();
    break;

case 'fournisseur_releve':
case 'fournisseur_releve/index':
    $controller = new FournisseurReleveController($db);
    $viewData   = $controller->index();
    break;

    // --- VOITURE ---
    case 'voiture':
        $controller = new VoitureController($db);
        $viewData = $controller->index();
        break;
    case 'voiture/create':
        $controller = new VoitureController($db);
        $viewData = $controller->create($_POST);
        break;
    case 'voiture/edit':
        $id = $_GET['id'] ?? null;
        if ($id === null || !is_numeric($id)) die("ID de voiture invalide.");
        $controller = new VoitureController($db);
        $viewData = $controller->edit((int)$id, $_POST);
        break;
    case 'voiture/delete':
        $id = $_GET['id'] ?? null;
        if ($id === null || !is_numeric($id)) die("ID de voiture invalide.");
        $controller = new VoitureController($db);
        $controller->delete((int)$id);
        exit();
        break;

// TABLEAU DE BORD
    case 'dashboard':
        $controller = new DashboardController($db);
        $viewData = $controller->index();
        break;


        
// SAUVEGARDE
    case 'sauvegarde':
    if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') die("Accès refusé.");
    $controller = new SauvegardeController($db);
    $controller->index();
    exit();

case 'sauvegarde/backup':
    if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') die("Accès refusé.");
    if ($_SERVER['REQUEST_METHOD'] !== 'POST') die("Méthode non autorisée.");
    $controller = new SauvegardeController($db);
    $controller->backup();
    exit();

case 'sauvegarde/download':
    if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') die("Accès refusé.");
    $file = $_GET['file'] ?? '';
    $controller = new SauvegardeController($db);
    $controller->download($file);
    exit();


    // --- MOUVEMENTS DE STOCK ---
    case 'stock_movements':
    case 'stock_movements/index':
        $controller = new StockMovementController($db);
        $viewData = $controller->index($_GET);
        break;
    case 'stock_movements/create':
        $controller = new StockMovementController($db);
        $viewData = $controller->create($_POST);
        break;
    case 'stock_movements/show':
        $id = $_GET['id'] ?? null;
        if ($id === null || !is_numeric($id)) die("ID de mouvement invalide.");
        $controller = new StockMovementController($db);
        $viewData = $controller->show((int)$id);
        break;
    case 'stock_movements/alerts':
        $controller = new StockMovementController($db);
        $viewData = $controller->getAlerts();
        break;
    case 'stock_movements/historique':
        $controller = new StockMovementController($db);
        $viewData = $controller->getHistorique($_GET);
        break;
    case 'stock_movements/seuils':
        $controller = new StockMovementController($db);
        $viewData = $controller->seuils($_POST);
        break;
    // --- CATEGORIES ---
    case 'categories':
    case 'categories/index':
        $controller = new CategoryController($db);
        $viewData = $controller->index();
        break;

    case 'categories/create':
        $controller = new CategoryController($db);
        $viewData = $controller->create($_POST);
        break;

    case 'categories/edit':
        $id = $_GET['id'] ?? null;
        if ($id === null || !is_numeric($id)) die("ID catégorie invalide.");
        $controller = new CategoryController($db);
        $viewData = $controller->edit((int)$id, $_POST);
        break;

    case 'categories/delete':
        $id = $_GET['id'] ?? null;
        if ($id === null || !is_numeric($id)) die("ID catégorie invalide.");
        $controller = new CategoryController($db);
        $controller->delete((int)$id);
        exit();

    case 'categories/show':
        $id = $_GET['id'] ?? null;
        if ($id === null || !is_numeric($id)) die("ID catégorie invalide.");
        $controller = new CategoryController($db);
        $viewData = $controller->show((int)$id);
        break;

    case 'categories/search':
        $term = $_GET['term'] ?? '';
        $controller = new CategoryController($db);
        $controller->search(['term' => $term]); // doit echo json + exit
        exit();

    // --- ERREUR PAR DÉFAUT ---
    default:
        $viewData = ['view' => 'error', 'data' => ['message' => 'Page non trouvée']];
        break;
}

// Chargement de la vue
$view = $viewData['view'] ?? 'error';
$data = $viewData['data'] ?? [];

$viewPath = '../views/' . $view . '.php';
if (file_exists($viewPath)) {
    extract($data);
    require $viewPath;
} else {
    echo "Vue non trouvée : " . $viewPath;
}