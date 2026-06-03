<?php
require_once __DIR__ . '/../includes/permissions.php';
require_once __DIR__ . '/../models/Livraison.php';
require_once __DIR__ . '/../models/Depot.php';
require_once __DIR__ . '/../models/MouvementStock.php';

class LivraisonController {
    private Livraison $model;
    private Depot $depotModel;
    private PDO $db;
    private MouvementStock $mouvementModel;

    public function __construct(PDO $db) {
        $this->db = $db;
        $this->model = new Livraison($db);
        $this->depotModel = new Depot($db);
        $this->mouvementModel = new MouvementStock($db);
    }

    public function index(array $getData = []): array {
        if (!hasPermission('livraisons', 'view')) die("Accès refusé.");
        $items = $this->model->getAll(200);

        return ['view' => 'livraisons/index', 'data' => ['livraisons' => $items]];
    }

    public function createFromDevis(int $devisId): void {
        if (!hasPermission('livraisons', 'create')) die("Accès refusé.");
        $userId = (int)($_SESSION['user_id'] ?? 0);
        if (!$userId) die("Utilisateur non authentifié.");

        $mainDepotId = $this->depotModel->getMainDepotId();
        $blId = $this->model->createFromDevis($devisId, $mainDepotId, $userId);

        header("Location: index.php?action=livraisons/edit&id=" . $blId);
        exit();
    }

    public function edit(int $id, array $data = []): array {
        if (!hasPermission('livraisons', 'edit')) die("Accès refusé.");

        $bl = $this->model->findById($id);
        if (!$bl) return ['view' => 'error', 'data' => ['message' => "BL introuvable."]];

        $lignes = $this->model->getLignes($id);

        $error = null;
        $isValidated = (($bl['statut'] ?? 'draft') === 'validated');
        $isAdmin = (($_SESSION['role'] ?? '') === 'admin');
        $canEdit = (!$isValidated) || $isAdmin;

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            if (!$canEdit) {
                $error = "BL déjà validé : modification interdite (sauf admin).";
            } else {
                $userId = (int)($_SESSION['user_id'] ?? 0);
                if (!$userId) $error = "Utilisateur non authentifié.";
                else {
                    $posted = $data['lignes'] ?? [];
                    $this->model->updateLignesQuantites($id, $posted, $userId);
                    header("Location: index.php?action=livraisons/edit&id=" . $id);
                    exit();
                }
            }
        }

        // warnings stock
        $stockWarnings = [];
        $depotId = (int)$bl['depot_id'];
        foreach ($lignes as $ln) {
            $articleId = (int)$ln['article_id'];
            $ql = (float)$ln['quantite_livree'];
            $dispo = $this->getStockDisponible($articleId, $depotId);
            if ($ql > $dispo) $stockWarnings[(int)$ln['id']] = "Stock insuffisant (dispo: {$dispo})";
        }

        return [
            'view' => 'livraisons/edit',
            'data' => [
                'bl' => $bl,
                'lignes' => $lignes,
                'error' => $error,
                'stockWarnings' => $stockWarnings
            ]
        ];
    }

    public function show(int $id): array {
        if (!hasPermission('livraisons', 'view')) die("Accès refusé.");

        $bl = $this->model->findById($id);
        if (!$bl) return ['view' => 'error', 'data' => ['message' => "BL introuvable."]];

        $lignes = $this->model->getLignes($id);

        return ['view' => 'livraisons/show', 'data' => ['bl' => $bl, 'lignes' => $lignes]];
    }

    public function validate(int $id): void {
        if (!hasPermission('livraisons', 'edit')) die("Accès refusé.");

        $bl = $this->model->findById($id);
        if (!$bl) die("BL introuvable.");

        if (($bl['statut'] ?? 'draft') === 'validated') {
            header("Location: index.php?action=livraisons/show&id=" . $id);
            exit();
        }

        $userId = (int)($_SESSION['user_id'] ?? 0);
        if (!$userId) die("Utilisateur non authentifié.");

        $depotId = (int)$bl['depot_id'];
        $lignes = $this->model->getLignes($id);

        // Vérifier stock
        $errors = [];
        foreach ($lignes as $ln) {
            $articleId = (int)$ln['article_id'];
            $ql = (float)$ln['quantite_livree'];
            if ($ql <= 0) continue;

            $dispo = $this->getStockDisponible($articleId, $depotId);
            if ($ql > $dispo) {
                $errors[] = "Stock insuffisant pour {$ln['nom_art']} (demande: {$ql}, dispo: {$dispo}).";
            }
        }

        if ($errors) {
            // On renvoie vers edit avec un message simple
            $msg = urlencode(implode(" | ", array_slice($errors, 0, 3)));
            header("Location: index.php?action=livraisons/edit&id={$id}&error={$msg}");
            exit();
        }

        $this->db->beginTransaction();
        try {
            foreach ($lignes as $ln) {
                $articleId = (int)$ln['article_id'];
                $ql = (float)$ln['quantite_livree'];
                if ($ql <= 0) continue;
                $this->mouvementModel->create([
    'article_id'      => $articleId,
    'depot_id'        => $depotId,
    'type_mouvement'  => 'sortie',
    'quantite'        => $ql,
    'reference'       => $bl['numero'] ?? null,
    'description'     => 'Sortie automatique via bon de livraison',
    'document_type'   => 'livraison',
    'document_id'     => $id,
    'prix_unitaire'   => $ln['prix_unitaire'] ?? null,
], $userId);
            }

            $this->model->markValidated($id, $userId);

            $this->db->commit();
            header("Location: index.php?action=factures/createFromBl&bl_id=" . $id);
            exit();
        } catch (Throwable $e) {
            $this->db->rollBack();
            die("Erreur validation BL: " . $e->getMessage());
        }
    }

    private function getStockDisponible(int $articleId, int $depotId): float {
        $stmt = $this->db->prepare(
            "SELECT quantite, COALESCE(quantite_bloquee, 0) AS quantite_bloquee
             FROM stock_par_depot
             WHERE article_id=? AND depot_id=?
             LIMIT 1"
        );
        $stmt->execute([$articleId, $depotId]);
        $row = $stmt->fetch(PDO::FETCH_ASSOC);

        $q = (float)($row['quantite'] ?? 0);
        $qb = (float)($row['quantite_bloquee'] ?? 0);
        $dispo = $q - $qb;
        return $dispo < 0 ? 0 : $dispo;
    }

  

    private function refreshArticleQuantiteTotale(int $articleId): void {
    $stmt = $this->db->prepare(
        "SELECT COALESCE(SUM(quantite), 0)
         FROM stock_par_depot
         WHERE article_id = ?"
    );
    $stmt->execute([$articleId]);
    $total = (float)$stmt->fetchColumn();

    $stmtUp = $this->db->prepare(
        "UPDATE articles
         SET quantite_totale = ?, updated_at = NOW()
         WHERE id_articles = ?"
    );
    $stmtUp->execute([$total, $articleId]);
}
}