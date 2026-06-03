<?php
require_once __DIR__ . '/../includes/permissions.php';
require_once __DIR__ . '/../includes/Validator.php';
require_once __DIR__ . '/../includes/AuditLogger.php';
require_once __DIR__ . '/../includes/CsrfMiddleware.php';
require_once __DIR__ . '/../models/TransfertStock.php';
require_once __DIR__ . '/../models/MouvementStock.php';
require_once __DIR__ . '/../models/StockManager.php';

class TransfertStockController {

    private TransfertStock  $transfertModel;
    private MouvementStock  $mouvementModel;
    private StockManager    $stockManager;
    private AuditLogger     $auditLogger;
    private PDO             $db;

    public function __construct(PDO $db) {
        $this->transfertModel = new TransfertStock($db);
        $this->mouvementModel = new MouvementStock($db);
        $this->stockManager   = new StockManager($db);
        $this->auditLogger    = new AuditLogger($db);
        $this->db             = $db;
    }

    // ================================================================
    // INDEX
    // ================================================================

    public function index(): array {
        if (!hasPermission('transferts_stock', 'view')) {
            die("Accès refusé.");
        }

        $filters = [
            'depot_source_id'      => $_GET['depot_source_id'] ?? '',
            'depot_destination_id' => $_GET['depot_destination_id'] ?? '',
            'statut'               => $_GET['statut'] ?? '',
            'date_debut'           => $_GET['date_debut'] ?? '',
            'date_fin'             => $_GET['date_fin'] ?? '',
        ];

        $transferts  = $this->transfertModel->getAll($filters);
        $totalCount  = $this->transfertModel->getTotalCount();
        $depots      = $this->stockManager->getAllDepots();

        return [
            'view' => 'transferts_stock/index',
            'data' => [
                'transferts' => $transferts,
                'totalCount' => $totalCount,
                'depots'     => $depots,
                'filters'    => $filters,
            ],
        ];
    }

    // ================================================================
    // CREATE
    // ================================================================

    public function create(array $data): array {
        if (!hasPermission('transferts_stock', 'create')) {
            die("Accès refusé.");
        }

        $error  = null;
        $depots = $this->stockManager->getAllDepots();

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            CsrfMiddleware::verify();

            $v = new Validator($data);
            $v->required('depot_source_id', 'Dépôt source')
              ->required('depot_destination_id', 'Dépôt destination')
              ->required('date_transfert', 'Date de transfert');

            $userId = $_SESSION['user_id'] ?? null;
            if (!$userId) {
                $error = "Utilisateur non authentifié.";
            } elseif ((int)($data['depot_source_id'] ?? 0) === (int)($data['depot_destination_id'] ?? 0)) {
                $error = "Le dépôt source et le dépôt destination doivent être différents.";
            } elseif ($v->fails()) {
                $error = $v->getFirstError();
            } else {
                try {
                    $transfertId = $this->transfertModel->create($data, (int)$userId);
                    $this->auditLogger->log('transferts_stock', $transfertId, 'CREATE', (int)$userId, null, $data);
                    header('Location: index.php?action=transferts_stock/show&id=' . $transfertId);
                    exit();
                } catch (PDOException $e) {
                    $error = "Erreur lors de la création : " . $e->getMessage();
                    error_log($error);
                }
            }
        }

        return [
            'view' => 'transferts_stock/create',
            'data' => [
                'error'      => $error,
                'depots'     => $depots,
                'csrf_field' => CsrfMiddleware::field(),
            ],
        ];
    }

    // ================================================================
    // SHOW
    // ================================================================

    public function show(int $id): array {
        if (!hasPermission('transferts_stock', 'view')) {
            die("Accès refusé.");
        }

        $transfert = $this->transfertModel->findById($id);
        if (!$transfert) {
            return ['view' => 'error', 'data' => ['message' => "Transfert introuvable."]];
        }

        $lignes = $this->transfertModel->getLignes($id);

        return [
            'view' => 'transferts_stock/show',
            'data' => [
                'transfert'  => $transfert,
                'lignes'     => $lignes,
                'csrf_field' => CsrfMiddleware::field(),
            ],
        ];
    }

    // ================================================================
    // AJOUTER UNE LIGNE (AJAX)
    // ================================================================

    public function addLigne(): void {
        if (!hasPermission('transferts_stock', 'edit')) {
            http_response_code(403);
            echo json_encode(['success' => false, 'message' => 'Accès refusé.']);
            exit();
        }

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            echo json_encode(['success' => false, 'message' => 'Méthode invalide.']);
            exit();
        }

        CsrfMiddleware::verify();

        $transfertId = (int)($_POST['transfert_id'] ?? 0);
        $articleId   = (int)($_POST['article_id'] ?? 0);
        $quantite    = (int)($_POST['quantite'] ?? 0);

        if (!$transfertId || !$articleId || $quantite <= 0) {
            header('Content-Type: application/json');
            echo json_encode(['success' => false, 'message' => 'Données invalides.']);
            exit();
        }

        $transfert = $this->transfertModel->findById($transfertId);
        if (!$transfert || $transfert['statut'] !== 'brouillon') {
            header('Content-Type: application/json');
            echo json_encode(['success' => false, 'message' => 'Transfert non modifiable.']);
            exit();
        }

        // Vérifier le stock disponible
        $stockDispo = $this->mouvementModel->getStockDisponible($articleId, (int)$transfert['depot_source_id']);
        if ($stockDispo < $quantite) {
            header('Content-Type: application/json');
            echo json_encode([
                'success' => false,
                'message' => "Stock insuffisant : disponible {$stockDispo}, demandé {$quantite}.",
            ]);
            exit();
        }

        try {
            $this->transfertModel->addLigne($transfertId, $articleId, $quantite);
            header('Content-Type: application/json');
            echo json_encode(['success' => true]);
        } catch (PDOException $e) {
            header('Content-Type: application/json');
            echo json_encode(['success' => false, 'message' => $e->getMessage()]);
        }
        exit();
    }

    // ================================================================
    // SUPPRIMER UNE LIGNE
    // ================================================================

    public function removeLigne(): void {
        if (!hasPermission('transferts_stock', 'edit')) {
            die("Accès refusé.");
        }

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            CsrfMiddleware::verify();
        }

        $ligneId     = (int)($_POST['ligne_id'] ?? $_GET['id'] ?? 0);
        $transfertId = (int)($_POST['transfert_id'] ?? $_GET['transfert_id'] ?? 0);

        if ($ligneId) {
            $this->transfertModel->removeLigne($ligneId);
        }

        if ($transfertId) {
            header('Location: index.php?action=transferts_stock/show&id=' . $transfertId);
        } else {
            header('Location: index.php?action=transferts_stock');
        }
        exit();
    }

    // ================================================================
    // VALIDER
    // ================================================================

    public function valider(): void {
        if (!hasPermission('transferts_stock', 'edit')) {
            die("Accès refusé.");
        }

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            header('Location: index.php?action=transferts_stock');
            exit();
        }

        CsrfMiddleware::verify();

        $id     = (int)($_POST['transfert_id'] ?? 0);
        $userId = (int)($_SESSION['user_id'] ?? 0);

        if (!$id) {
            header('Location: index.php?action=transferts_stock&error=id_invalide');
            exit();
        }

        try {
            $this->transfertModel->valider($id, $userId, $this->mouvementModel);
            $this->auditLogger->log('transferts_stock', $id, 'VALIDATE', $userId);
            header('Location: index.php?action=transferts_stock/show&id=' . $id . '&success=valide');
        } catch (RuntimeException $e) {
            header('Location: index.php?action=transferts_stock/show&id=' . $id . '&error=' . urlencode($e->getMessage()));
        } catch (PDOException $e) {
            error_log("Erreur validation transfert : " . $e->getMessage());
            header('Location: index.php?action=transferts_stock/show&id=' . $id . '&error=' . urlencode("Erreur base de données."));
        }
        exit();
    }

    // ================================================================
    // SUPPRIMER LE TRANSFERT
    // ================================================================

    public function delete(): void {
        if (!hasPermission('transferts_stock', 'delete')) {
            die("Accès refusé.");
        }

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            CsrfMiddleware::verify();
        }

        $id     = (int)($_POST['transfert_id'] ?? $_GET['id'] ?? 0);
        $userId = (int)($_SESSION['user_id'] ?? 0);

        if ($id) {
            try {
                $this->transfertModel->delete($id);
                $this->auditLogger->log('transferts_stock', $id, 'DELETE', $userId);
            } catch (PDOException $e) {
                error_log("Erreur suppression transfert : " . $e->getMessage());
            }
        }

        header('Location: index.php?action=transferts_stock');
        exit();
    }

    // ================================================================
    // RECHERCHE ARTICLES (AJAX pour modal)
    // ================================================================

    public function searchArticles(): void {
        if (!hasPermission('transferts_stock', 'view')) {
            http_response_code(403);
            echo json_encode([]);
            exit();
        }

        $term    = trim($_GET['term'] ?? '');
        $depotId = (int)($_GET['depot_id'] ?? 0);

        header('Content-Type: application/json');

        if ($term === '' && !$depotId) {
            echo json_encode([]);
            exit();
        }

        try {
            $like   = '%' . $term . '%';
            if ($depotId) {
                $stmt = $this->db->prepare(
                    "SELECT a.id_articles, a.nom_art, a.sku,
                            COALESCE(sp.quantite, 0) AS stock_depot
                     FROM articles a
                     LEFT JOIN stock_par_depot sp ON sp.article_id = a.id_articles
                         AND sp.depot_id = ?
                     WHERE a.deleted_at IS NULL
                       AND (a.nom_art LIKE ? OR a.sku LIKE ?)
                       AND COALESCE(sp.quantite, 0) > 0
                     ORDER BY a.nom_art ASC
                     LIMIT 30"
                );
                $stmt->execute([$depotId, $like, $like]);
            } else {
                $stmt = $this->db->prepare(
                    "SELECT a.id_articles, a.nom_art, a.sku, 0 AS stock_depot
                     FROM articles a
                     WHERE a.deleted_at IS NULL
                       AND (a.nom_art LIKE ? OR a.sku LIKE ?)
                     ORDER BY a.nom_art ASC
                     LIMIT 30"
                );
                $stmt->execute([$like, $like]);
            }
            echo json_encode($stmt->fetchAll(PDO::FETCH_ASSOC));
        } catch (PDOException $e) {
            echo json_encode([]);
        }
        exit();
    }
}