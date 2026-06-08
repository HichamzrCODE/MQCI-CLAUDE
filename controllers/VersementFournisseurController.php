<?php
require_once __DIR__ . '/../includes/permissions.php';
require_once __DIR__ . '/../includes/CsrfMiddleware.php';
require_once __DIR__ . '/../models/VersementFournisseur.php';
require_once __DIR__ . '/../models/fournisseur.php';
require_once __DIR__ . '/../models/Tresorerie.php';

class VersementFournisseurController
{
    private PDO                $db;
    private VersementFournisseur $model;
    private fournisseur        $fournisseurModel;

    const PER_PAGE = 15;

    public function __construct(PDO $db)
    {
        $this->db               = $db;
        $this->model            = new VersementFournisseur($db);
        $this->fournisseurModel = new fournisseur($db);
    }

    public function index(): array
    {
        if (!hasPermission('versements_fournisseurs', 'view')) die("Accès refusé.");

        $page        = max(1, (int)($_GET['page'] ?? 1));
        $per_page    = self::PER_PAGE;
        $all         = $this->model->getAll(9999);
        $total       = count($all);
        $total_pages = max(1, (int)ceil($total / $per_page));
        $page        = min($page, $total_pages);
        $versements  = array_slice($all, ($page - 1) * $per_page, $per_page);

        return [
            'view' => 'versements_fournisseurs/index',
            'data' => [
                'versements'  => $versements,
                'page'        => $page,
                'total_pages' => $total_pages,
                'total'       => $total,
                'per_page'    => $per_page,
                'csrf_field'  => CsrfMiddleware::field(),
            ]
        ];
    }

    public function create(array $data): array
    {
        if (!hasPermission('versements_fournisseurs', 'create')) die("Accès refusé.");

        $error = null;
        $mode  = $_GET['mode'] ?? '';

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            try {
                CsrfMiddleware::verify();
                $userId = (int)($_SESSION['user_id'] ?? 0);
                if (!$userId) throw new RuntimeException("Utilisateur non authentifié.");
                $montantSaisi = (float)str_replace(',', '.', preg_replace('/[\s\xA0]/u', '', (string)($data['montant'] ?? '0')));
                if ($montantSaisi <= 0) throw new RuntimeException("Montant invalide.");
                $id = $this->model->create($data, $userId);
                header("Location: index.php?action=versements_fournisseurs/edit&id=" . $id);
                exit();
            } catch (Throwable $e) {
                $error = $e->getMessage();
            }
        }

        $tres    = new Tresorerie($this->db);
        $caisses = $tres->listActifs('caisses');
        $banques = $tres->listActifs('banques');
        $mobiles = $tres->listActifs('mobile');

        return [
            'view' => 'versements_fournisseurs/create',
            'data' => compact('error', 'mode', 'caisses', 'banques', 'mobiles') + ['csrf_field' => CsrfMiddleware::field()]
        ];
    }

    public function edit(int $id, array $data): array
    {
        if (!hasPermission('versements_fournisseurs', 'edit')) die("Accès refusé.");

        $versement = $this->model->findById($id);
        if (!$versement) return ['view' => 'error', 'data' => ['message' => 'Versement fournisseur introuvable.']];

        $error = null;
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            try {
                CsrfMiddleware::verify();
                $userId = (int)($_SESSION['user_id'] ?? 0);
                if (!$userId) throw new RuntimeException("Utilisateur non authentifié.");
                $montantSaisi = (float)str_replace(',', '.', preg_replace('/[\s\xA0]/u', '', (string)($data['montant'] ?? '0')));
                if ($montantSaisi <= 0) throw new RuntimeException("Montant invalide.");
                $this->model->update($id, $data, $userId);
                header("Location: index.php?action=versements_fournisseurs/edit&id=" . $id);
                exit();
            } catch (Throwable $e) {
                $error = $e->getMessage();
            }
        }

        return [
            'view' => 'versements_fournisseurs/edit',
            'data' => ['versement' => $this->model->findById($id), 'error' => $error, 'csrf_field' => CsrfMiddleware::field()]
        ];
    }

    public function cancel(int $id): void
    {
        if (!hasPermission('versements_fournisseurs', 'delete')) die("Accès refusé.");
        $userId = (int)($_SESSION['user_id'] ?? 0);
        if (!$userId) die("Utilisateur non authentifié.");
        $this->model->cancel($id, $userId);
        header("Location: index.php?action=versements_fournisseurs");
        exit();
    }

    public function delete(int $id): void
    {
        if (!isset($_SESSION['user_id']) || ($_SESSION['role'] ?? '') !== 'admin') die("Accès refusé.");
        $this->model->delete($id);
        header("Location: index.php?action=versements_fournisseurs");
        exit();
    }

    public function hide(int $id): void
    {
        if (!isset($_SESSION['user_id']) || ($_SESSION['role'] ?? '') !== 'admin') die("Accès refusé.");
        CsrfMiddleware::verify();
        $userId = (int)($_SESSION['user_id'] ?? 0);
        if (!$userId) die("Utilisateur non authentifié.");
        $this->model->hide($id, $userId);
        header("Location: index.php?action=versements_fournisseurs");
        exit();
    }

    public function print(int $id): array
    {
        if (!hasPermission('versements_fournisseurs', 'view')) die("Accès refusé.");
        $versement = $this->model->findById($id);
        if (!$versement) return ['view' => 'error', 'data' => ['message' => 'Versement fournisseur introuvable.']];
        return ['view' => 'versements_fournisseurs/print', 'data' => ['versement' => $versement]];
    }
}
