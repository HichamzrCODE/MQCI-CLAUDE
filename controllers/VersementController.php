<?php
require_once __DIR__ . '/../includes/permissions.php';
require_once __DIR__ . '/../includes/CsrfMiddleware.php';
require_once __DIR__ . '/../models/Versement.php';
require_once __DIR__ . '/../models/client.php';
require_once __DIR__ . '/../models/Tresorerie.php';

class VersementController {
    private PDO $db;
    private Versement $model;
    private Client $clientModel;

    public function __construct(PDO $db) {
        $this->db = $db;
        $this->model = new Versement($db);
        $this->clientModel = new Client($db);
    }

  public function index(): array {
    if (!hasPermission('versements', 'view')) die("Accès refusé.");
    $items = $this->model->getAll(300);
    return [
        'view' => 'versements/index',
        'data' => [
            'versements' => $items,
            'csrf_field' => CsrfMiddleware::field()
        ]
    ];
}

    public function create(array $data): array {
        if (!hasPermission('versements', 'create')) die("Accès refusé.");

        $error = null;

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            try {
                CsrfMiddleware::verify();

                $userId = (int)($_SESSION['user_id'] ?? 0);
                if (!$userId) throw new RuntimeException("Utilisateur non authentifié.");

                $id = $this->model->create($data, $userId);
                header("Location: index.php?action=versements/edit&id=" . $id);
                exit();
            } catch (Throwable $e) {
                $error = $e->getMessage();
            }
        }

        // ✅ Toujours charger les listes (même en GET, et même si POST a échoué)
        $tres = new Tresorerie($this->db);
        $caisses = $tres->listActifs('caisses');
        $banques = $tres->listActifs('banques');
        $mobiles = $tres->listActifs('mobile');

        return [
            'view' => 'versements/create',
            'data' => [
                'error' => $error,
                'caisses' => $caisses,
                'banques' => $banques,
                'mobiles' => $mobiles,
                'csrf_field' => CsrfMiddleware::field(),
            ]
        ];
    }

    public function edit(int $id, array $data): array {
        if (!hasPermission('versements', 'edit')) die("Accès refusé.");

        $versement = $this->model->findById($id);
        if (!$versement) return ['view' => 'error', 'data' => ['message' => 'Versement introuvable.']];

        $error = null;
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            try {
                CsrfMiddleware::verify();

                $userId = (int)($_SESSION['user_id'] ?? 0);
                if (!$userId) throw new RuntimeException("Utilisateur non authentifié.");

                $this->model->update($id, $data, $userId);
                header("Location: index.php?action=versements/edit&id=" . $id);
                exit();
            } catch (Throwable $e) {
                $error = $e->getMessage();
            }
        }

        return [
  'view' => 'versements/edit',
  'data' => [
    'versement' => $this->model->findById($id),
    'error' => $error,
    'csrf_field' => CsrfMiddleware::field()
  ]
];
    }

    public function cancel(int $id): void {
        if (!hasPermission('versements', 'delete')) die("Accès refusé.");
        $userId = (int)($_SESSION['user_id'] ?? 0);
        if (!$userId) die("Utilisateur non authentifié.");
        $this->model->cancel($id, $userId);
        header("Location: index.php?action=versements");
        exit();
    }


 public function delete(int $id): void {
    if (!isset($_SESSION['user_id']) || ($_SESSION['role'] ?? '') !== 'admin') die("Accès refusé.");

    // (optionnel) CSRF si tu passes en POST
    // CsrfMiddleware::verify();

    $this->model->delete($id);

    header("Location: index.php?action=versements");
    exit();
}


public function hide(int $id): void {
    if (!isset($_SESSION['user_id']) || ($_SESSION['role'] ?? '') !== 'admin') die("Accès refusé.");

    // ✅ recommandé: POST + CSRF
    CsrfMiddleware::verify();

    $userId = (int)($_SESSION['user_id'] ?? 0);
    if (!$userId) die("Utilisateur non authentifié.");

    $this->model->hide($id, $userId);

    header("Location: index.php?action=versements");
    exit();
}

public function print(int $id): array {
    if (!hasPermission('versements', 'view')) die("Accès refusé.");

    $versement = $this->model->findById($id);
    if (!$versement) {
        return ['view' => 'error', 'data' => ['message' => 'Versement introuvable.']];
    }

    return [
        'view' => 'versements/print',
        'data' => [
            'versement' => $versement
        ]
    ];
}
}