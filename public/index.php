<<<<<<< HEAD
<?php
require __DIR__.'/../includes/licence_check.php';
session_start();

// Autoloading des classes
spl_autoload_register(function ($classname) {
    $paths = ['../models/', '../controllers/'];
    foreach ($paths as $path) {
        $file = $path . $classname . '.php';
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
        $dbConfig['password']
    );
    $db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    die("Erreur de connexion à la base de données : " . $e->getMessage());
}


// Routage principal
$action = $_GET['action'] ?? 'home'; // Action par défaut

// Liste des actions accessibles sans connexion
$publicActions = ['home', 'login'];

if (!isset($_SESSION['user_id']) && !in_array($action, $publicActions)) {
    header('Location: index.php?action=login');
    exit();
}

switch ($action) {

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

// --- FS (RELEVES FOURNISSEUR) ---
case 'fs':
    $controller = new FsReleveController($db);
    $viewData = $controller->index();
    break;
case 'fs/create':
    $controller = new FsReleveController($db);
    $viewData = $controller->create($_POST);
    break;
case 'fs/edit':
    if (!isset($_GET['id']) || !is_numeric($_GET['id'])) die("ID de relevé fournisseur invalide.");
    $controller = new FsReleveController($db);
    $viewData = $controller->edit((int)$_GET['id'], $_POST);
    break;
case 'fs/delete':
    if (!isset($_GET['id']) || !is_numeric($_GET['id'])) die("ID de relevé fournisseur invalide.");
    $controller = new FsReleveController($db);
    $controller->delete((int)$_GET['id']);
    exit();
case 'fs/show':
    if (!isset($_GET['id']) || !is_numeric($_GET['id'])) die("ID de relevé fournisseur invalide.");
    $controller = new FsReleveController($db);
    $viewData = $controller->show((int)$_GET['id']);
    break;
case 'fs/extrait':
    $id = $_GET['id'] ?? null;
    $date_debut = $_GET['date_debut'] ?? null;
    $date_fin = $_GET['date_fin'] ?? null;
    if ($id === null || !is_numeric($id)) die("ID de relevé fournisseur invalide.");
    $controller = new FsReleveController($db);
    $viewData = $controller->extrait((int)$id, $date_debut, $date_fin);
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
=======
<?php
require __DIR__.'/../includes/licence_check.php';
session_start();

// Autoloading des classes
spl_autoload_register(function ($classname) {
    $paths = ['../models/', '../controllers/'];
    foreach ($paths as $path) {
        $file = $path . $classname . '.php';
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
        $dbConfig['password']
    );
    $db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    die("Erreur de connexion à la base de données : " . $e->getMessage());
}


// Routage principal
$action = $_GET['action'] ?? 'home'; // Action par défaut

// Liste des actions accessibles sans connexion
$publicActions = ['home', 'login'];

if (!isset($_SESSION['user_id']) && !in_array($action, $publicActions)) {
    header('Location: index.php?action=login');
    exit();
}

switch ($action) {

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

// --- FS (RELEVES FOURNISSEUR) ---
case 'fs':
    $controller = new FsReleveController($db);
    $viewData = $controller->index();
    break;
case 'fs/create':
    $controller = new FsReleveController($db);
    $viewData = $controller->create($_POST);
    break;
case 'fs/edit':
    if (!isset($_GET['id']) || !is_numeric($_GET['id'])) die("ID de relevé fournisseur invalide.");
    $controller = new FsReleveController($db);
    $viewData = $controller->edit((int)$_GET['id'], $_POST);
    break;
case 'fs/delete':
    if (!isset($_GET['id']) || !is_numeric($_GET['id'])) die("ID de relevé fournisseur invalide.");
    $controller = new FsReleveController($db);
    $controller->delete((int)$_GET['id']);
    exit();
case 'fs/show':
    if (!isset($_GET['id']) || !is_numeric($_GET['id'])) die("ID de relevé fournisseur invalide.");
    $controller = new FsReleveController($db);
    $viewData = $controller->show((int)$_GET['id']);
    break;
case 'fs/extrait':
    $id = $_GET['id'] ?? null;
    $date_debut = $_GET['date_debut'] ?? null;
    $date_fin = $_GET['date_fin'] ?? null;
    if ($id === null || !is_numeric($id)) die("ID de relevé fournisseur invalide.");
    $controller = new FsReleveController($db);
    $viewData = $controller->extrait((int)$id, $date_debut, $date_fin);
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
>>>>>>> 4f8dbbb6b83eb9c6f755d57287033c7da885a3b1
}