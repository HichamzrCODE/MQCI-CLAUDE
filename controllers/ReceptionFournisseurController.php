<?php
require_once __DIR__ . '/../includes/permissions.php';
require_once __DIR__ . '/../includes/Validator.php';
require_once __DIR__ . '/../includes/AuditLogger.php';
require_once __DIR__ . '/../includes/CsrfMiddleware.php';
require_once __DIR__ . '/../models/ReceptionFournisseur.php';
require_once __DIR__ . '/../models/MouvementStock.php';
require_once __DIR__ . '/../models/StockManager.php';

class ReceptionFournisseurController {

    private ReceptionFournisseur $receptionModel;
    private MouvementStock       $mouvementModel;
    private StockManager         $stockManager;
    private AuditLogger          $auditLogger;
    private PDO                  $db;

    public function __construct(PDO $db) {
        $this->receptionModel = new ReceptionFournisseur($db);
        $this->mouvementModel = new MouvementStock($db);
        $this->stockManager   = new StockManager($db);
        $this->auditLogger    = new AuditLogger($db);
        $this->db             = $db;
    }

    // ================================================================
    // INDEX
    // ================================================================

    public function index(): array {
        if (!hasPermission('receptions_fournisseur', 'view')) {
            die("Accès refusé.");
        }

        $filters = [
            'fournisseur_id' => $_GET['fournisseur_id'] ?? '',
            'depot_id'       => $_GET['depot_id'] ?? '',
            'statut'         => $_GET['statut'] ?? '',
            'date_debut'     => $_GET['date_debut'] ?? '',
            'date_fin'       => $_GET['date_fin'] ?? '',
        ];

        $receptions  = $this->receptionModel->getAll($filters);
        $totalCount  = $this->receptionModel->getTotalCount();
        $depots      = $this->stockManager->getAllDepots();
        $fournisseurs = $this->getFournisseurs();

        return [
            'view' => 'receptions_fournisseur/index',
            'data' => [
                'receptions'   => $receptions,
                'totalCount'   => $totalCount,
                'depots'       => $depots,
                'fournisseurs' => $fournisseurs,
                'filters'      => $filters,
            ],
        ];
    }

    // ================================================================
    // CREATE
    // ================================================================

    public function create(array $data): array {
        if (!hasPermission('receptions_fournisseur', 'create')) {
            die("Accès refusé.");
        }

        $error        = null;
        $depots       = $this->stockManager->getAllDepots();
        $fournisseurs = $this->getFournisseurs();

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            CsrfMiddleware::verify();

            $v = new Validator($data);
            $v->required('fournisseur_id', 'Fournisseur')
              ->required('depot_id', 'Dépôt de réception')
              ->required('date_reception', 'Date de réception');

            $userId = $_SESSION['user_id'] ?? null;
            if (!$userId) {
                $error = "Utilisateur non authentifié.";
            } elseif ($v->fails()) {
                $error = $v->getFirstError();
            } else {
                try {
                    $receptionId = $this->receptionModel->create($data, (int)$userId);
                    $this->auditLogger->log('receptions_fournisseur', $receptionId, 'CREATE', (int)$userId, null, $data);
                    header('Location: index.php?action=receptions_fournisseur/show&id=' . $receptionId);
                    exit();
                } catch (PDOException $e) {
                    $error = "Erreur lors de la création : " . $e->getMessage();
                    error_log($error);
                }
            }
        }

        return [
            'view' => 'receptions_fournisseur/create',
            'data' => [
                'error'        => $error,
                'depots'       => $depots,
                'fournisseurs' => $fournisseurs,
                'csrf_field'   => CsrfMiddleware::field(),
            ],
        ];
    }

    // ================================================================
    // SHOW
    // ================================================================

    public function show(int $id): array {
        if (!hasPermission('receptions_fournisseur', 'view')) {
            die("Accès refusé.");
        }

        $reception = $this->receptionModel->findById($id);
        if (!$reception) {
            return ['view' => 'error', 'data' => ['message' => "Réception introuvable."]];
        }

        $lignes = $this->receptionModel->getLignes($id);

        return [
            'view' => 'receptions_fournisseur/show',
            'data' => [
                'reception'  => $reception,
                'lignes'     => $lignes,
                'csrf_field' => CsrfMiddleware::field(),
            ],
        ];
    }

    // ================================================================
    // AJOUTER UNE LIGNE (AJAX)
    // ================================================================

    public function addLigne(): void {
        if (!hasPermission('receptions_fournisseur', 'edit')) {
            http_response_code(403);
            echo json_encode(['success' => false, 'message' => 'Accès refusé.']);
            exit();
        }

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            echo json_encode(['success' => false, 'message' => 'Méthode invalide.']);
            exit();
        }

        CsrfMiddleware::verify();

        $receptionId  = (int)($_POST['reception_id'] ?? 0);
        $articleId    = (int)($_POST['article_id'] ?? 0);
        $qteCmdee     = (int)($_POST['quantite_commandee'] ?? 0);
        $prixUnitaire = !empty($_POST['prix_unitaire']) ? (float)$_POST['prix_unitaire'] : null;

        if (!$receptionId || !$articleId || $qteCmdee <= 0) {
            header('Content-Type: application/json');
            echo json_encode(['success' => false, 'message' => 'Données invalides.']);
            exit();
        }

        $reception = $this->receptionModel->findById($receptionId);
        if (!$reception || $reception['statut'] !== 'brouillon') {
            header('Content-Type: application/json');
            echo json_encode(['success' => false, 'message' => 'Réception non modifiable.']);
            exit();
        }

        try {
            $this->receptionModel->addLigne($receptionId, $articleId, $qteCmdee, $prixUnitaire);
            header('Content-Type: application/json');
            echo json_encode(['success' => true]);
        } catch (PDOException $e) {
            header('Content-Type: application/json');
            echo json_encode(['success' => false, 'message' => $e->getMessage()]);
        }
        exit();
    }

    // ================================================================
    // METTRE À JOUR LA QUANTITÉ REÇUE
    // ================================================================

    public function updateLigne(): void {
        if (!hasPermission('receptions_fournisseur', 'edit')) {
            die("Accès refusé.");
        }

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            header('Location: index.php?action=receptions_fournisseur');
            exit();
        }

        CsrfMiddleware::verify();

        $ligneId     = (int)($_POST['ligne_id'] ?? 0);
        $qteRecue    = (int)($_POST['quantite_recue'] ?? 0);
        $receptionId = (int)($_POST['reception_id'] ?? 0);

        if ($ligneId && $qteRecue >= 0) {
            try {
                $this->receptionModel->updateLigne($ligneId, $qteRecue);
            } catch (PDOException $e) {
                error_log("Erreur updateLigne : " . $e->getMessage());
            }
        }

        header('Location: index.php?action=receptions_fournisseur/show&id=' . $receptionId);
        exit();
    }

    // ================================================================
    // SUPPRIMER UNE LIGNE
    // ================================================================

    public function removeLigne(): void {
        if (!hasPermission('receptions_fournisseur', 'edit')) {
            die("Accès refusé.");
        }

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            CsrfMiddleware::verify();
        }

        $ligneId     = (int)($_POST['ligne_id'] ?? $_GET['id'] ?? 0);
        $receptionId = (int)($_POST['reception_id'] ?? $_GET['reception_id'] ?? 0);

        if ($ligneId) {
            $this->receptionModel->removeLigne($ligneId);
        }

        if ($receptionId) {
            header('Location: index.php?action=receptions_fournisseur/show&id=' . $receptionId);
        } else {
            header('Location: index.php?action=receptions_fournisseur');
        }
        exit();
    }

    // ================================================================
    // VALIDER
    // ================================================================

    public function valider(): void {
        if (!hasPermission('receptions_fournisseur', 'edit')) {
            die("Accès refusé.");
        }

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            header('Location: index.php?action=receptions_fournisseur');
            exit();
        }

        CsrfMiddleware::verify();

        $id     = (int)($_POST['reception_id'] ?? 0);
        $userId = (int)($_SESSION['user_id'] ?? 0);

        if (!$id) {
            header('Location: index.php?action=receptions_fournisseur&error=id_invalide');
            exit();
        }

        try {
            $this->receptionModel->valider($id, $userId, $this->mouvementModel);
            $this->auditLogger->log('receptions_fournisseur', $id, 'VALIDATE', $userId);
            header('Location: index.php?action=receptions_fournisseur/show&id=' . $id . '&success=validee');
        } catch (RuntimeException $e) {
            header('Location: index.php?action=receptions_fournisseur/show&id=' . $id . '&error=' . urlencode($e->getMessage()));
        } catch (PDOException $e) {
            error_log("Erreur validation réception : " . $e->getMessage());
            header('Location: index.php?action=receptions_fournisseur/show&id=' . $id . '&error=' . urlencode("Erreur base de données."));
        }
        exit();
    }

    // ================================================================
    // SUPPRIMER LA RÉCEPTION
    // ================================================================

    public function delete(): void {
        if (!hasPermission('receptions_fournisseur', 'delete')) {
            die("Accès refusé.");
        }

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            CsrfMiddleware::verify();
        }

        $id     = (int)($_POST['reception_id'] ?? $_GET['id'] ?? 0);
        $userId = (int)($_SESSION['user_id'] ?? 0);

        if ($id) {
            try {
                $this->receptionModel->delete($id);
                $this->auditLogger->log('receptions_fournisseur', $id, 'DELETE', $userId);
            } catch (PDOException $e) {
                error_log("Erreur suppression réception : " . $e->getMessage());
            }
        }

        header('Location: index.php?action=receptions_fournisseur');
        exit();
    }

    // ================================================================
    // RECHERCHE ARTICLES (AJAX pour modal)
    // ================================================================

    public function searchArticles(): void {
        if (!hasPermission('receptions_fournisseur', 'view')) {
            http_response_code(403);
            echo json_encode([]);
            exit();
        }

        $term = trim($_GET['term'] ?? '');
        header('Content-Type: application/json');

        try {
            $like = '%' . $term . '%';
            $stmt = $this->db->prepare(
                "SELECT a.id_articles, a.nom_art, a.sku, a.pr
                 FROM articles a
                 WHERE a.deleted_at IS NULL
                   AND (a.nom_art LIKE ? OR a.sku LIKE ?)
                 ORDER BY a.nom_art ASC
                 LIMIT 30"
            );
            $stmt->execute([$like, $like]);
            echo json_encode($stmt->fetchAll(PDO::FETCH_ASSOC));
        } catch (PDOException $e) {
            echo json_encode([]);
        }
        exit();
    }

    // ================================================================
    // HELPERS
    // ================================================================

    private function getFournisseurs(): array {
        try {
            $stmt = $this->db->query(
                "SELECT id_fournisseurs, nom_fournisseurs FROM fournisseurs ORDER BY nom_fournisseurs ASC"
            );
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            return [];
        }
    }

    /**
 * API - Récupère les articles filtrés par fournisseur
 */
public function getArticlesByFournisseur(): void {
    header('Content-Type: application/json');
    
    $fournisseurId = intval($_GET['fournisseur_id'] ?? 0);
    $search = trim($_GET['search'] ?? '');
    
    if (!$fournisseurId) {
        echo json_encode([]);
        exit();
    }

    try {
        $query = "SELECT a.id_articles, a.nom_art, a.sku, a.pr
                  FROM articles a
                  WHERE a.fournisseur_id = ? AND a.deleted_at IS NULL";
        $params = [$fournisseurId];
        
        if ($search) {
            $query .= " AND (a.nom_art LIKE ? OR a.sku LIKE ?)";
            $params[] = '%' . $search . '%';
            $params[] = '%' . $search . '%';
        }
        
        $query .= " ORDER BY a.nom_art ASC LIMIT 30";
        
        $stmt = $this->db->prepare($query);
        $stmt->execute($params);
        $articles = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        echo json_encode($articles);
    } catch (PDOException $e) {
        echo json_encode([]);
    }
    exit();
}
}