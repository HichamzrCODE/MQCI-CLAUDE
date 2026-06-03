<?php
require_once __DIR__ . '/../includes/permissions.php';
require_once __DIR__ . '/../includes/Validator.php';
require_once __DIR__ . '/../includes/CsrfMiddleware.php';
require_once __DIR__ . '/../models/MouvementStock.php';
require_once __DIR__ . '/../models/SeuilStock.php';
require_once __DIR__ . '/../models/StockManager.php';
require_once __DIR__ . '/../models/article.php';

class StockMovementController {

    private MouvementStock $mouvementModel;
    private SeuilStock     $seuilModel;
    private StockManager   $stockManager;
    private Article        $articleModel;
    private PDO            $db;

    public function __construct(PDO $db) {
        $this->db             = $db;
        $this->mouvementModel = new MouvementStock($db);
        $this->seuilModel     = new SeuilStock($db);
        $this->stockManager   = new StockManager($db);
        $this->articleModel   = new Article($db);
    }

    /**
     * Liste tous les mouvements avec filtres.
     */
    public function index(array $filters = []): array {
        if (!hasPermission('stock_movements', 'view')) {
            die("Accès refusé.");
        }

        $allowed = ['article_id', 'depot_id', 'type_mouvement', 'date_debut', 'date_fin'];
        $f = [];
        foreach ($allowed as $key) {
            if (!empty($filters[$key])) {
                $f[$key] = $filters[$key];
            }
        }

        $page    = max(1, (int)($filters['page'] ?? 1));
        $limit   = 50;
        $offset  = ($page - 1) * $limit;
        $total   = $this->mouvementModel->count($f);
        $mouvements = $this->mouvementModel->getAll($f, $limit, $offset);

        $articles = $this->articleModel->getAll();
        $depots   = $this->stockManager->getAllDepots();

        return [
            'view' => 'stock_movements/index',
            'data' => [
                'mouvements' => $mouvements,
                'total'      => $total,
                'page'       => $page,
                'limit'      => $limit,
                'filters'    => $f,
                'articles'   => $articles,
                'depots'     => $depots,
            ],
        ];
    }

    /**
     * Formulaire et traitement de création d'un mouvement.
     */
public function create(array $data = []): array {
    if (!hasPermission('stock_movements', 'create')) {
        die("Accès refusé.");
    }

    $error    = null;
    $success  = null;
    $articles = $this->articleModel->getAll();
    $depots   = $this->stockManager->getDepotsActifs();

    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        CsrfMiddleware::verify();

        $data['type_mouvement'] = 'ajustement';

        $v = new Validator($data);
        $v->required('article_id', 'Article')
          ->required('depot_id', 'Dépôt')
          ->required('quantite', 'Quantité')
          ->nonNegativeNumber('quantite', 'Quantité');

        $userId = $_SESSION['user_id'] ?? null;
        $isAdmin = (($_SESSION['role'] ?? '') === 'admin');

        if (!$isAdmin) {
            $error = "Seul un administrateur peut faire un ajustement manuel.";
        } elseif (!$userId) {
            $error = "Utilisateur non authentifié.";
        } elseif ($v->fails()) {
            $error = $v->getFirstError();
        } else {
            try {
                $id = $this->mouvementModel->create([
                    'article_id'     => (int)$data['article_id'],
                    'depot_id'       => (int)$data['depot_id'],
                    'type_mouvement' => 'ajustement',
                    'quantite'       => (int)$data['quantite'],
                    'reference'      => trim($data['reference'] ?? ''),
                    'description'    => trim($data['description'] ?? 'Ajustement manuel de stock'),
                    'document_type'  => null,
                    'document_id'    => null,
                    'prix_unitaire'  => null,
                ], (int)$userId);

                header('Location: index.php?action=stock_movements/show&id=' . $id);
                exit();
            } catch (\InvalidArgumentException $e) {
                $error = $e->getMessage();
            } catch (PDOException $e) {
                $error = "Erreur lors de l'enregistrement : " . $e->getMessage();
                error_log($error);
            }
        }
    }

    return [
        'view' => 'stock_movements/create',
        'data' => [
            'error'      => $error,
            'articles'   => $articles,
            'depots'     => $depots,
            'csrf_field' => CsrfMiddleware::field(),
        ],
    ];
}

    /**
     * Détails d'un mouvement.
     */
    public function show(int $id): array {
        if (!hasPermission('stock_movements', 'view')) {
            die("Accès refusé.");
        }

        $mouvement = $this->mouvementModel->findById($id);
        if (!$mouvement) {
            return ['view' => 'error', 'data' => ['message' => "Mouvement non trouvé."]];
        }

        return [
            'view' => 'stock_movements/show',
            'data' => ['mouvement' => $mouvement],
        ];
    }

    /**
     * Tableau des alertes de stock minimal.
     */
    public function getAlerts(): array {
        if (!hasPermission('stock_movements', 'view')) {
            die("Accès refusé.");
        }

        $alertes = $this->seuilModel->getAlertes();

        return [
            'view' => 'stock_movements/alerts',
            'data' => ['alertes' => $alertes],
        ];
    }

    /**
     * Historique des mouvements par article et/ou dépôt.
     */
    public function getHistorique(array $filters = []): array {
        if (!hasPermission('stock_movements', 'view')) {
            die("Accès refusé.");
        }

        $articleId = !empty($filters['article_id']) ? (int)$filters['article_id'] : null;
        $depotId   = !empty($filters['depot_id'])   ? (int)$filters['depot_id']   : null;

        $historique = [];
        $article    = null;
        $depot      = null;

        if ($articleId) {
            $article    = $this->articleModel->findById($articleId);
            $historique = $this->mouvementModel->getHistorique($articleId, $depotId);
            if ($depotId) {
                $depot = $this->stockManager->findDepotById($depotId);
            }
        }

        $articles = $this->articleModel->getAll();
        $depots   = $this->stockManager->getAllDepots();

        return [
            'view' => 'stock_movements/historique',
            'data' => [
                'historique' => $historique,
                'article'    => $article,
                'depot'      => $depot,
                'articles'   => $articles,
                'depots'     => $depots,
                'filters'    => [
                    'article_id' => $articleId,
                    'depot_id'   => $depotId,
                ],
            ],
        ];
    }

    /**
     * Gestion des seuils (affichage + création/modification).
     */
    public function seuils(array $data = []): array {
        if (!hasPermission('stock_movements', 'view')) {
            die("Accès refusé.");
        }

        $error   = null;
        $success = null;

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            if (!hasPermission('stock_movements', 'create')) {
                die("Accès refusé.");
            }
            CsrfMiddleware::verify();

            $v = new Validator($data);
            $v->required('article_id', 'Article')
              ->required('depot_id', 'Dépôt')
              ->nonNegativeNumber('stock_minimal', 'Stock minimal');

            if ($v->fails()) {
                $error = $v->getFirstError();
            } else {
                try {
                    $stockMax = ($data['stock_maximal'] ?? '') !== '' ? (int)$data['stock_maximal'] : null;
                    $this->seuilModel->upsert(
                        (int)$data['article_id'],
                        (int)$data['depot_id'],
                        (int)($data['stock_minimal'] ?? 0),
                        $stockMax
                    );
                    $success = "Seuil enregistré avec succès.";
                } catch (PDOException $e) {
                    $error = "Erreur : " . $e->getMessage();
                    error_log($error);
                }
            }
        }

        $seuils   = $this->seuilModel->getAll();
        $articles = $this->articleModel->getAll();
        $depots   = $this->stockManager->getDepotsActifs();

        return [
            'view' => 'stock_movements/seuils',
            'data' => [
                'seuils'     => $seuils,
                'articles'   => $articles,
                'depots'     => $depots,
                'error'      => $error,
                'success'    => $success,
                'csrf_field' => CsrfMiddleware::field(),
            ],
        ];
    }
}