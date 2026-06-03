<?php
require_once __DIR__ . '/../includes/permissions.php';
require_once __DIR__ . '/../includes/Validator.php';
require_once __DIR__ . '/../includes/CsrfMiddleware.php';
require_once __DIR__ . '/../models/Depot.php';
require_once __DIR__ . '/../models/Article.php';

class DepotController {
    private $depotModel;
    private $articleModel;
    private $db;

    public function __construct(PDO $db) {
        $this->depotModel = new Depot($db);
        $this->articleModel = new Article($db);
        $this->db = $db;
    }

    // ================================================================
    // INDEX - LISTE DES DÉPÔTS
    // ================================================================

    public function index(array $getData = []): array {
        if (!hasPermission('depots', 'view')) {
            die("Accès refusé.");
        }

        $depots = $this->depotModel->getAll();
        $totalDepots = $this->depotModel->getTotalCount();

        return [
            'view' => 'depots/index',
            'data' => [
                'depots' => $depots,
                'totalDepots' => $totalDepots
            ]
        ];
    }

    // ================================================================
    // CRÉATION
    // ================================================================

    public function create(array $data): array {
        if (!hasPermission('depots', 'create')) {
            die("Accès refusé.");
        }

        $error = null;
        $responsables = $this->getResponsables();

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            CsrfMiddleware::verify();

            $v = new Validator($data);
            $v->required('nom', "Nom du dépôt")
              ->maxLength('nom', 255, "Nom du dépôt")
              ->required('adresse', "Adresse")
              ->required('ville', "Ville")
              ->nonNegativeNumber('responsable_id', 'Responsable')
              ->email('email', 'Email')
              ->inList('statut', ['actif', 'inactif'], 'Statut');

            $userId = $_SESSION['user_id'] ?? null;
            if (!$userId) {
                $error = "Utilisateur non authentifié.";
            } elseif ($v->fails()) {
                $error = $v->getFirstError();
            } else {
                try {
                    $depotData = [
                        'nom'              => trim($data['nom']),
                        'adresse'          => trim($data['adresse']),
                        'ville'            => trim($data['ville']),
                        'responsable_id'   => ($data['responsable_id'] ?? '') !== '' ? (int)$data['responsable_id'] : null,
                        'telephone'        => trim($data['telephone'] ?? ''),
                        'email'            => trim($data['email'] ?? ''),
                        'statut'           => $data['statut'] ?? 'actif'
                    ];
                    $this->depotModel->create($depotData, $userId);
                    header('Location: index.php?action=depots');
                    exit();
                } catch (PDOException $e) {
                    $error = "Erreur lors de la création : " . $e->getMessage();
                    error_log($error);
                }
            }
        }

        return [
            'view' => 'depots/create',
            'data' => [
                'error' => $error,
                'responsables' => $responsables,
                'csrf_field' => CsrfMiddleware::field()
            ]
        ];
    }

    // ================================================================
    // ÉDITION
    // ================================================================

    public function edit(int $id, array $data = []): array {
        if (!hasPermission('depots', 'edit')) {
            die("Accès refusé.");
        }

        $error = null;
        $depot = $this->depotModel->findById($id);
        $responsables = $this->getResponsables();

        if (!$depot) {
            return ['view' => 'error', 'data' => ['message' => "Dépôt non trouvé."]];
        }

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            CsrfMiddleware::verify();

            $v = new Validator($data);
            $v->required('nom', "Nom du dépôt")
              ->maxLength('nom', 255, "Nom du dépôt")
              ->required('adresse', "Adresse")
              ->required('ville', "Ville")
              ->nonNegativeNumber('responsable_id', 'Responsable')
              ->email('email', 'Email')
              ->inList('statut', ['actif', 'inactif'], 'Statut');

            if ($v->fails()) {
                $error = $v->getFirstError();
            } else {
                try {
                    $userId = $_SESSION['user_id'] ?? null;
                    $depotData = [
                        'nom'              => trim($data['nom']),
                        'adresse'          => trim($data['adresse']),
                        'ville'            => trim($data['ville']),
                        'responsable_id'   => ($data['responsable_id'] ?? '') !== '' ? (int)$data['responsable_id'] : null,
                        'telephone'        => trim($data['telephone'] ?? ''),
                        'email'            => trim($data['email'] ?? ''),
                        'statut'           => $data['statut'] ?? 'actif'
                    ];
                    $this->depotModel->update($id, $depotData, $userId);
                    header('Location: index.php?action=depots');
                    exit();
                } catch (PDOException $e) {
                    $error = "Erreur lors de la mise à jour : " . $e->getMessage();
                    error_log($error);
                }
            }
        }

        return [
            'view' => 'depots/edit',
            'data' => [
                'depot' => $depot,
                'error' => $error,
                'responsables' => $responsables,
                'csrf_field' => CsrfMiddleware::field()
            ]
        ];
    }

    // ================================================================
    // SUPPRESSION
    // ================================================================

    public function delete(int $id): void {
        if (!hasPermission('depots', 'delete')) {
            die("Accès refusé.");
        }

        $depot = $this->depotModel->findById($id);
        if (!$depot) {
            echo "Dépôt non trouvé.";
            return;
        }

        if (!$this->depotModel->delete($id)) {
            header('Location: index.php?action=depots&error=impossible_supprimer');
            exit();
        }

        header('Location: index.php?action=depots');
        exit();
    }

    // ================================================================
    // AFFICHAGE DÉTAILLÉ
    // ================================================================

    public function show(int $id): array {
        if (!hasPermission('depots', 'view')) {
            die("Accès refusé.");
        }

        $depot = $this->depotModel->findById($id);
        if (!$depot) {
            return ['view' => 'error', 'data' => ['message' => "Dépôt non trouvé."]];
        }

        // Récupérer tous les stocks dans ce dépôt
        $stocks = $this->depotModel->getAllStocksInDepot($id);

        return [
            'view' => 'depots/show',
            'data' => [
                'depot' => $depot,
                'stocks' => $stocks
            ]
        ];
    }

    // ================================================================
    // RECHERCHE AJAX
    // ================================================================

    public function search(array $data): void {
        $term = trim($data['term'] ?? '');
        header('Content-Type: application/json');

        if ($term === '') {
            $depots = $this->depotModel->getAll();
        } else {
            $depots = $this->depotModel->search($term);
        }

        foreach ($depots as &$depot) {
            $depot['editable'] = hasPermission('depots', 'edit');
            $depot['deletable'] = hasPermission('depots', 'delete');
        }

        echo json_encode($depots);
        exit();
    }

    // ================================================================
    // HELPERS
    // ================================================================

    private function getResponsables(): array {
        try {
            $stmt = $this->db->query("SELECT id_users, username FROM users WHERE statut = 'actif' ORDER BY username ASC");
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            error_log("Erreur récupération responsables : " . $e->getMessage());
            return [];
        }
    }
}