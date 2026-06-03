<?php
require_once __DIR__ . '/../includes/permissions.php';
require_once __DIR__ . '/../models/FactureFournisseur.php';
require_once __DIR__ . '/../models/FactureFournisseurLigne.php';
require_once __DIR__ . '/../models/article.php';
require_once __DIR__ . '/../models/Depot.php';
require_once __DIR__ . '/../models/fournisseur.php';
require_once __DIR__ . '/../models/MouvementStock.php';

class FactureFournisseurController {
    private PDO $db;
    private FactureFournisseur $factureModel;
    private FactureFournisseurLigne $ligneModel;
    private Article $articleModel;
    private Depot $depotModel;
    private fournisseur $fournisseurModel;
    private MouvementStock $mouvementModel;

    public function __construct(PDO $db) {
        $this->db = $db;
        $this->factureModel = new FactureFournisseur($db);
        $this->ligneModel = new FactureFournisseurLigne($db);
        $this->articleModel = new Article($db);
        $this->depotModel = new Depot($db);
        $this->fournisseurModel = new fournisseur($db);
        $this->mouvementModel = new MouvementStock($db);
    }

    private function checkAuth(): void {
        if (!isset($_SESSION['user_id'])) {
            header('Location: index.php?action=login');
            exit();
        }
    }

    private function can(string $action): bool {
        if (function_exists('hasPermission')) {
            if (hasPermission('factures_fournisseurs', $action)) return true;
            if (hasPermission('achats', $action)) return true;
            if (hasPermission('devis', $action)) return true; // fallback défensif
        }
        return true;
    }

    private function isAdmin(): bool {
        return (($_SESSION['role'] ?? '') === 'admin');
    }

    private function ensureEditable(array $facture): void {
        if (($facture['statut'] ?? 'draft') === 'validated' && !$this->isAdmin()) {
            die("Facture fournisseur validée : modification interdite.");
        }
    }

    private function normalizePrice($prix): string {
        $prix = preg_replace('/[\s\xA0]/u', '', (string)$prix);
        $prix = str_replace(',', '.', $prix);
        return $prix;
    }

    private function generateNumero(): string {
        $annee = date('Y');
        $dernier = $this->factureModel->getLastNumeroFactureFournisseur($annee);

        $seq = 1;
        if ($dernier && preg_match('/(\d+)$/', $dernier, $m)) {
            $seq = (int)$m[1] + 1;
        }

        return sprintf('FF-%s-%03d', $annee, $seq);
    }

    private function incrementerStock(int $articleId, int $depotId, float $qte): void {
    $stmt = $this->db->prepare(
        "SELECT id, quantite
         FROM stock_par_depot
         WHERE article_id = ? AND depot_id = ?
         FOR UPDATE"
    );
    $stmt->execute([$articleId, $depotId]);
    $row = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($row) {
        $newQ = (float)$row['quantite'] + $qte;

        $stmtUp = $this->db->prepare(
            "UPDATE stock_par_depot
             SET quantite = ?,
                 updated_at = NOW()
             WHERE article_id = ? AND depot_id = ?"
        );
        $stmtUp->execute([$newQ, $articleId, $depotId]);
    } else {
        $stmtIns = $this->db->prepare(
            "INSERT INTO stock_par_depot
             (article_id, depot_id, quantite, quantite_en_transit, quantite_bloquee, emplacement, updated_at)
             VALUES (?, ?, ?, 0, 0, '', NOW())"
        );
        $stmtIns->execute([
            $articleId,
            $depotId,
            $qte
        ]);
    }

    $this->refreshArticleQuantiteTotale($articleId);
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

    private function reconstituerLignes(array $articles): array {
        $lignes = [];

        foreach ($articles as $articleData) {
            $articleId    = $articleData['article_id'] ?? '';
            $quantite     = $articleData['quantite'] ?? '';
            $prixUnitaire = $articleData['prix_unitaire'] ?? '';
            $nomArt       = '';

            if ($articleId) {
                $article = $this->articleModel->findById((int)$articleId);
                $nomArt = $article ? $article['nom_art'] : '';
            }

            $total = 0;
            $q = $this->normalizePrice($quantite);
            $p = $this->normalizePrice($prixUnitaire);
            if (is_numeric($q) && is_numeric($p)) {
                $total = (float)$q * (float)$p;
            }

            $lignes[] = [
                'id'            => $articleData['id'] ?? null,
                'article_id'    => $articleId,
                'nom_art'       => $nomArt,
                'quantite'      => $quantite,
                'prix_unitaire' => $prixUnitaire,
                'total'         => $total,
                'description'   => $articleData['description'] ?? '',
            ];
        }

        return $lignes;
    }

    public function index(): array {
        $this->checkAuth();
        if (!$this->can('view')) die('Accès refusé.');

        $parPage = 10;
        $page = isset($_GET['page']) ? max(1, (int)$_GET['page']) : 1;
        $offset = ($page - 1) * $parPage;
        $searchTerm = trim($_GET['search_term'] ?? '');

        if ($searchTerm === '') {
            $items = $this->factureModel->getAll($parPage, $offset);
            $total = $this->factureModel->countAll();
        } else {
            $items = $this->factureModel->search($searchTerm, $parPage, $offset);
            $total = $this->factureModel->countSearch($searchTerm);
        }

        return [
            'view' => 'factures_fournisseurs/index',
            'data' => [
                'factures' => $items,
                'total' => $total,
                'page' => $page,
                'parPage' => $parPage,
                'search_term' => $searchTerm,
            ]
        ];
    }

    public function create(array $data): array {
        $this->checkAuth();
        if (!$this->can('create')) die('Accès refusé.');

        $error = null;
        $errorFields = [];
        $lignes = [];

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $fournisseurId = trim($data['fournisseur_id'] ?? '');
            $depotId = trim($data['depot_id'] ?? '');
            $date = trim($data['date'] ?? '');
            $notes = trim($data['notes'] ?? '');
            $articles = $data['articles'] ?? [];

            if ($fournisseurId === '') $errorFields['fournisseur_id'] = "Le fournisseur est obligatoire.";
            if ($depotId === '') $errorFields['depot_id'] = "Le dépôt est obligatoire.";
            if ($date === '') $errorFields['date'] = "La date est obligatoire.";
            if (empty($articles)) $error = "Veuillez ajouter au moins un article.";

            foreach ($articles as $inputIndex => $ligne) {
                if (empty($ligne['article_id'])) {
                    $errorFields["articles_{$inputIndex}_article_id"] = "Article obligatoire.";
                }

                $quantite = trim((string)($ligne['quantite'] ?? ''));
                if ($quantite === '' || !is_numeric($quantite) || (float)$quantite <= 0) {
                    $errorFields["articles_{$inputIndex}_quantite"] = "Quantité invalide.";
                }

                $prix = $this->normalizePrice($ligne['prix_unitaire'] ?? '');
                if ($prix === '' || !is_numeric($prix) || (float)$prix < 0) {
                    $errorFields["articles_{$inputIndex}_prix_unitaire"] = "Prix unitaire invalide.";
                }
            }

            if (empty($errorFields) && !$error) {
                try {
                    $this->db->beginTransaction();

                    $numero = $this->generateNumero();
                    $userId = (int)($_SESSION['user_id'] ?? 0);

                    $factureId = $this->factureModel->create(
                        $numero,
                        (int)$fournisseurId,
                        (int)$depotId,
                        $date,
                        $notes !== '' ? $notes : null,
                        $userId
                    );

                    $ordre = 0;
                    $totalHt = 0.0;

                    foreach ($articles as $ligne) {
                        $articleId    = (int)$ligne['article_id'];
                        $quantite     = (int)$ligne['quantite'];
                        $prixUnitaire = (float)$this->normalizePrice($ligne['prix_unitaire']);
                        $description  = $ligne['description'] ?? null;
                        $totalLigne   = round($quantite * $prixUnitaire, 2);

                        $this->ligneModel->create(
                            $factureId,
                            $articleId,
                            $quantite,
                            $prixUnitaire,
                            $totalLigne,
                            $ordre++,
                            $description
                        );

                        $totalHt += $totalLigne;
                    }

                    $totalTva = 0.0;
                    $totalTtc = $totalHt;

                    $this->factureModel->updateTotals($factureId, $totalHt, $totalTva, $totalTtc);

                    $this->db->commit();

                    header('Location: index.php?action=factures_fournisseurs');
                    exit();
                } catch (Throwable $e) {
                    if ($this->db->inTransaction()) $this->db->rollBack();
                    $error = "Erreur lors de la création : " . $e->getMessage();
                    $lignes = $this->reconstituerLignes($articles);
                }
            } else {
                $lignes = $this->reconstituerLignes($articles);
            }
        }

        $fournisseurs = $this->fournisseurModel->getAll();
        $depots = $this->depotModel->getAll();
        $articlesList = $this->articleModel->getAll();

        if (empty($lignes)) {
            $lignes = [[
                'article_id'    => '',
                'nom_art'       => '',
                'quantite'      => '',
                'prix_unitaire' => '0',
                'description'   => '',
            ]];
        }

        $numero = $this->generateNumero();

        return [
            'view' => 'factures_fournisseurs/create',
            'data' => [
                'fournisseurs' => $fournisseurs,
                'depots' => $depots,
                'articles' => $articlesList,
                'error' => $error,
                'errorFields' => $errorFields,
                'lignes' => $lignes,
                'numeroFacture' => $numero
            ]
        ];
    }

    public function edit(int $id): array {
        $this->checkAuth();
        if (!$this->can('edit')) die('Accès refusé.');

        $facture = $this->factureModel->findById($id);
        if (!$facture) {
            return ['view' => 'error', 'data' => ['message' => 'Facture fournisseur introuvable.']];
        }

        $lignes = $this->ligneModel->getByFactureId($id, true);
        $fournisseurs = $this->fournisseurModel->getAll();
        $depots = $this->depotModel->getAll();
        $articles = $this->articleModel->getAll();

        return [
            'view' => 'factures_fournisseurs/edit',
            'data' => [
                'facture' => $facture,
                'lignes' => $lignes,
                'fournisseurs' => $fournisseurs,
                'depots' => $depots,
                'articles' => $articles,
                'errorFields' => [],
            ]
        ];
    }

    public function update(int $id, array $data): array {
        $this->checkAuth();
        if (!$this->can('edit')) die('Accès refusé.');

        $facture = $this->factureModel->findById($id);
        if (!$facture) {
            return ['view' => 'error', 'data' => ['message' => 'Facture fournisseur introuvable.']];
        }

        $this->ensureEditable($facture);

        $error = null;
        $errorFields = [];
        $lignes = [];

        try {
            $fournisseurId = trim($data['fournisseur_id'] ?? '');
            $depotId = trim($data['depot_id'] ?? '');
            $date = trim($data['date'] ?? '');
            $notes = trim($data['notes'] ?? '');
            $articles = $data['articles'] ?? [];

            if ($fournisseurId === '') $errorFields['fournisseur_id'] = "Le fournisseur est obligatoire.";
            if ($depotId === '') $errorFields['depot_id'] = "Le dépôt est obligatoire.";
            if ($date === '') $errorFields['date'] = "La date est obligatoire.";
            if (empty($articles)) $error = "Veuillez ajouter au moins un article.";

            foreach ($articles as $inputIndex => $ligne) {
                if (empty($ligne['article_id'])) {
                    $errorFields["articles_{$inputIndex}_article_id"] = "Article obligatoire.";
                }

                $quantite = trim((string)($ligne['quantite'] ?? ''));
                if ($quantite === '' || !is_numeric($quantite) || (float)$quantite <= 0) {
                    $errorFields["articles_{$inputIndex}_quantite"] = "Quantité invalide.";
                }

                $prix = $this->normalizePrice($ligne['prix_unitaire'] ?? '');
                if ($prix === '' || !is_numeric($prix) || (float)$prix < 0) {
                    $errorFields["articles_{$inputIndex}_prix_unitaire"] = "Prix unitaire invalide.";
                }
            }

            if (empty($errorFields) && !$error) {
                $this->db->beginTransaction();

                $userId = (int)($_SESSION['user_id'] ?? 0);

                $this->factureModel->updateHeader(
                    $id,
                    (int)$fournisseurId,
                    (int)$depotId,
                    $date,
                    $notes !== '' ? $notes : null,
                    $userId
                );

                $this->ligneModel->deleteByFactureId($id);

                $ordre = 0;
                $totalHt = 0.0;

                foreach ($articles as $ligne) {
                    $articleId    = (int)$ligne['article_id'];
                    $quantite     = (int)$ligne['quantite'];
                    $prixUnitaire = (float)$this->normalizePrice($ligne['prix_unitaire']);
                    $description  = $ligne['description'] ?? null;
                    $totalLigne   = round($quantite * $prixUnitaire, 2);

                    $this->ligneModel->create(
                        $id,
                        $articleId,
                        $quantite,
                        $prixUnitaire,
                        $totalLigne,
                        $ordre++,
                        $description
                    );

                    $totalHt += $totalLigne;
                }

                $totalTva = 0.0;
                $totalTtc = $totalHt;

                $this->factureModel->updateTotals($id, $totalHt, $totalTva, $totalTtc);

                $this->db->commit();

                header('Location: index.php?action=factures_fournisseurs/show&id=' . $id);
                exit();
            } else {
                $lignes = $this->reconstituerLignes($articles);
            }
        } catch (Throwable $e) {
            if ($this->db->inTransaction()) $this->db->rollBack();
            $error = "Erreur lors de la mise à jour : " . $e->getMessage();
        }

        $fournisseurs = $this->fournisseurModel->getAll();
        $depots = $this->depotModel->getAll();
        $articlesList = $this->articleModel->getAll();

        if (empty($lignes)) {
            $lignes = $this->ligneModel->getByFactureId($id, true);
        }

        return [
            'view' => 'factures_fournisseurs/edit',
            'data' => [
                'facture' => $this->factureModel->findById($id),
                'lignes' => $lignes,
                'fournisseurs' => $fournisseurs,
                'depots' => $depots,
                'articles' => $articlesList,
                'error' => $error,
                'errorFields' => $errorFields,
            ]
        ];
    }

    public function show(int $id): array {
        $this->checkAuth();
        if (!$this->can('view')) die('Accès refusé.');

        $facture = $this->factureModel->findById($id);
        if (!$facture) {
            return ['view' => 'error', 'data' => ['message' => 'Facture fournisseur introuvable.']];
        }

        $lignes = $this->ligneModel->getByFactureId($id, true);

        return [
            'view' => 'factures_fournisseurs/show',
            'data' => [
                'facture' => $facture,
                'lignes' => $lignes
            ]
        ];
    }

    public function validate(int $id): void {
    $this->checkAuth();
    if (!$this->can('edit')) die('Accès refusé.');

    $facture = $this->factureModel->findById($id);
    if (!$facture) die("Facture fournisseur introuvable.");

    if (($facture['statut'] ?? 'draft') === 'validated') {
        header('Location: index.php?action=factures_fournisseurs/show&id=' . $id);
        exit();
    }

    $userId = (int)($_SESSION['user_id'] ?? 0);
    if (!$userId) die("Utilisateur non authentifié.");

    $lignes = $this->ligneModel->getByFactureId($id, true);
    $depotId = (int)$facture['depot_id'];

    $this->db->beginTransaction();
    try {
        foreach ($lignes as $ln) {
            $articleId = (int)$ln['article_id'];
            $qte = (float)$ln['quantite'];
            if ($qte <= 0) continue;

            $this->mouvementModel->create([
                'article_id'      => $articleId,
                'depot_id'        => $depotId,
                'type_mouvement'  => 'entree',
                'quantite'        => $qte,
                'reference'       => $facture['numero'] ?? null,
                'description'     => 'Entrée automatique via facture fournisseur',
                'document_type'   => 'facture_fournisseur',
                'document_id'     => $id,
                'prix_unitaire'   => $ln['prix_unitaire'] ?? null,
            ], $userId);
        }

        $this->factureModel->setValidated($id, $userId);

        $this->db->commit();
        header('Location: index.php?action=factures_fournisseurs/show&id=' . $id);
        exit();
    } catch (Throwable $e) {
        if ($this->db->inTransaction()) $this->db->rollBack();
        die("Erreur validation facture fournisseur : " . $e->getMessage());
    }
}

    public function delete(int $id): void {
        $this->checkAuth();
        if (!$this->can('delete')) die('Accès refusé.');

        $facture = $this->factureModel->findById($id);
        if (!$facture) die("Facture fournisseur introuvable.");

        if (($facture['statut'] ?? 'draft') === 'validated' && !$this->isAdmin()) {
            die("Suppression interdite : facture fournisseur validée.");
        }

        try {
            $this->ligneModel->deleteByFactureId($id);
            $this->factureModel->delete($id);

            header('Location: index.php?action=factures_fournisseurs');
            exit();
        } catch (Throwable $e) {
            die("Erreur suppression : " . $e->getMessage());
        }
    }
}