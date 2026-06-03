<?php

require_once __DIR__ . '/../includes/permissions.php';
require_once __DIR__ . '/../models/fournisseur.php';

class FournisseurController {
    private $fournisseurModel;

    public function __construct(PDO $db) {
        $this->fournisseurModel = new fournisseur($db);
    }

    public function index(): array {
        if (!isset($_SESSION['user_id'])) {
            header('Location: index.php?action=login');
            exit();
        }
        if (!hasPermission('fournisseurs', 'view')) {
            die("Accès refusé.");
        }

        $fournisseurs = $this->fournisseurModel->getAll();
        $canViewFinance = isset($_SESSION['role']) && in_array($_SESSION['role'], ['admin', 'manager']);
        if ($canViewFinance) {
            foreach ($fournisseurs as &$f) {
                $id = (int)$f['id_fournisseurs'];
                $f['nb_articles'] = $this->fournisseurModel->countArticles($id);
                $f['prix_moyen'] = $this->fournisseurModel->getPrixMoyenAchat($id);
            }
            unset($f);
        }
        return ['view' => 'fournisseurs/index', 'data' => ['fournisseurs' => $fournisseurs, 'can_view_finance' => $canViewFinance]];
    }

    public function create(array $data): array {
    if (!isset($_SESSION['user_id'])) {
        header('Location: index.php?action=login');
        exit();
    }
    if (!hasPermission('fournisseurs', 'create')) {
        die("Accès refusé.");
    }

    $error = null;
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        $nom_fournisseurs = trim($data['nom_fournisseurs'] ?? '');

        if (empty($nom_fournisseurs)) {
            $error = "Le nom du fournisseur est obligatoire.";
        } elseif ($this->fournisseurModel->existsByName($nom_fournisseurs)) {
            $error = "Ce fournisseur existe déjà !.";
        }

        $userId = $_SESSION['user_id'] ?? null;
        if (!$userId) {
            $error = "Utilisateur non authentifié.";
        }

        if (!$error) {
            $email = trim($data['email'] ?? '');
            $telephone = trim($data['telephone'] ?? '');
            $fournisseurId = $this->fournisseurModel->create($nom_fournisseurs, $userId, $email, $telephone);
            header('Location: index.php?action=fournisseurs');
            exit();
        }
    }
    return ['view' => 'fournisseurs/create', 'data' => ['error' => $error]];
}

    public function edit(int $id, array $data): array {
        if (!isset($_SESSION['user_id'])) {
            header('Location: index.php?action=login');
            exit();
        }
        if (!hasPermission('fournisseurs', 'edit')) {
            die("Accès refusé.");
        }

        $fournisseur = $this->fournisseurModel->findById($id);
        if (!$fournisseur) {
            return ['view' => 'error', 'data' => ['message' => "Fournisseur non trouvé."]];
        }

        $error = null;
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $nom_fournisseurs = trim($data['nom_fournisseurs'] ?? '');
            $email = trim($data['email'] ?? '');
            $telephone = trim($data['telephone'] ?? '');

            if (empty($nom_fournisseurs)) {
                $error = "Le nom du fournisseur est obligatoire.";
            }

            if (!$error) {
                $this->fournisseurModel->update($id, $nom_fournisseurs, $email, $telephone);
                header('Location: index.php?action=fournisseurs');
                exit();
            }
        }
        return ['view' => 'fournisseurs/edit', 'data' => ['fournisseur' => $fournisseur, 'error' => $error]];
    }

    public function delete(int $id): void {
        if (!isset($_SESSION['user_id'])) {
            header('Location: index.php?action=login');
            exit();
        }
        if (!hasPermission('fournisseurs', 'delete')) {
            die("Accès refusé.");
        }

        $fournisseur = $this->fournisseurModel->findById($id);
        if (!$fournisseur) {
            echo "Fournisseur non trouvé.";
            return;
        }

        $this->fournisseurModel->delete($id);
        header('Location: index.php?action=fournisseurs');
        exit();
    }

    public function show(int $id): array {
        if (!isset($_SESSION['user_id'])) {
            header('Location: index.php?action=login');
            exit();
        }
        if (!hasPermission('fournisseurs', 'view')) {
            die("Accès refusé.");
        }

        $fournisseur = $this->fournisseurModel->findById($id);
        if (!$fournisseur) {
            return ['view' => 'error', 'data' => ['message' => "Fournisseur non trouvé."]];
        }
        return ['view' => 'fournisseurs/show', 'data' => ['fournisseur' => $fournisseur]];
    }

public function search(array $data): void {
    $term = trim($data['term'] ?? '');
    header('Content-Type: application/json');
    if ($term === '') {
        $fournisseurs = $this->fournisseurModel->getAll();
    } else {
        $fournisseurs = $this->fournisseurModel->searchFull($term);
    }
    $canViewFinance = isset($_SESSION['role']) && in_array($_SESSION['role'], ['admin', 'manager']);
    foreach ($fournisseurs as &$fournisseur) {
        $fournisseur['editable'] = hasPermission('fournisseurs', 'edit');
        $fournisseur['deletable'] = hasPermission('fournisseurs', 'delete');
        if ($canViewFinance) {
            $id = (int)$fournisseur['id_fournisseurs'];
            $fournisseur['nb_articles'] = $this->fournisseurModel->countArticles($id);
            $fournisseur['prix_moyen'] = $this->fournisseurModel->getPrixMoyenAchat($id);
        }
    }
    unset($fournisseur);
    echo json_encode($fournisseurs);
    exit();
}
}