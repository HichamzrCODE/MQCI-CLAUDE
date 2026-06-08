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

    // PR est déjà en TTC dans la table articles
    private const TVA_COEF = 1.18;

    public function __construct(PDO $db) {
        $this->db              = $db;
        $this->factureModel    = new FactureFournisseur($db);
        $this->ligneModel      = new FactureFournisseurLigne($db);
        $this->articleModel    = new Article($db);
        $this->depotModel      = new Depot($db);
        $this->fournisseurModel= new fournisseur($db);
        $this->mouvementModel  = new MouvementStock($db);
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
            if (hasPermission('devis', $action)) return true;
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
        return str_replace(',', '.', $prix);
    }

    /**
     * Prix saisis en TTC → calcul HT = TTC ÷ 1.18, TVA = TTC - HT
     */
    private function calculerTotaux(float $totalTtc): array {
        $totalHt  = round($totalTtc / self::TVA_COEF, 2);
        $totalTva = round($totalTtc - $totalHt, 2);
        return ['ht' => $totalHt, 'tva' => $totalTva, 'ttc' => round($totalTtc, 2)];
    }

    private function generateNumero(): string {
        $annee   = date('Y');
        $dernier = $this->factureModel->getLastNumeroFactureFournisseur($annee);
        $seq = 1;
        if ($dernier && preg_match('/(\d+)$/', $dernier, $m)) {
            $seq = (int)$m[1] + 1;
        }
        return sprintf('FF-%s-%03d', $annee, $seq);
    }

    /**
     * Coût Moyen Pondéré (CMP)
     * PR en TTC, utilisé directement sans conversion
     * Appelé AVANT mouvementModel->create() pour lire le stock avant réception
     */
    private function mettreAJourCMP(
        int    $articleId,
        float  $qteRecue,
        float  $prixAchat,
        int    $userId,
        string $referenceFacture
    ): void {
        $stmt = $this->db->prepare(
            "SELECT quantite_totale, pr FROM articles WHERE id_articles = ? FOR UPDATE"
        );
        $stmt->execute([$articleId]);
        $article = $stmt->fetch(PDO::FETCH_ASSOC);
        if (!$article) return;

        $stockActuel = (float)$article['quantite_totale'];
        $prActuel    = (float)$article['pr'];
        $totalQte    = $stockActuel + $qteRecue;
        if ($totalQte <= 0) return;

        $nouveauCMP = ($stockActuel <= 0 || $prActuel <= 0)
            ? $prixAchat
            : ($stockActuel * $prActuel + $qteRecue * $prixAchat) / $totalQte;

        $nouveauCMP = round($nouveauCMP, 4);
        if (abs($nouveauCMP - $prActuel) < 0.01) return;

        $this->db->prepare(
            "UPDATE articles SET pr = ?, updated_at = NOW() WHERE id_articles = ?"
        )->execute([$nouveauCMP, $articleId]);

        $this->articleModel->addPrixHistorique(
            $articleId, $prActuel, $nouveauCMP, null, null, $userId,
            "CMP auto — FF {$referenceFacture}"
            . " | Stock avant: {$stockActuel} | Qté reçue: {$qteRecue}"
            . " | Prix achat TTC: {$prixAchat}"
        );
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
                $nomArt  = $article ? $article['nom_art'] : '';
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
        $parPage    = 10;
        $page       = isset($_GET['page']) ? max(1, (int)$_GET['page']) : 1;
        $offset     = ($page - 1) * $parPage;
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
            'data' => ['factures' => $items, 'total' => $total, 'page' => $page, 'parPage' => $parPage, 'search_term' => $searchTerm]
        ];
    }

    public function create(array $data): array {
        $this->checkAuth();
        if (!$this->can('create')) die('Accès refusé.');
        $error = null; $errorFields = []; $lignes = [];

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $fournisseurId = trim($data['fournisseur_id'] ?? '');
            $depotId       = trim($data['depot_id'] ?? '');
            $date          = trim($data['date'] ?? '');
            $notes         = trim($data['notes'] ?? '');
            $articles      = $data['articles'] ?? [];

            if ($fournisseurId === '') $errorFields['fournisseur_id'] = "Le fournisseur est obligatoire.";
            if ($depotId === '')       $errorFields['depot_id']       = "Le dépôt est obligatoire.";
            if ($date === '')          $errorFields['date']            = "La date est obligatoire.";
            if (empty($articles))      $error = "Veuillez ajouter au moins un article.";

            foreach ($articles as $i => $ligne) {
                if (empty($ligne['article_id'])) $errorFields["articles_{$i}_article_id"] = "Article obligatoire.";
                $q = trim((string)($ligne['quantite'] ?? ''));
                if ($q === '' || !is_numeric($q) || (float)$q <= 0) $errorFields["articles_{$i}_quantite"] = "Quantité invalide.";
                $p = $this->normalizePrice($ligne['prix_unitaire'] ?? '');
                if ($p === '' || !is_numeric($p) || (float)$p < 0) $errorFields["articles_{$i}_prix_unitaire"] = "Prix invalide.";
            }

            if (empty($errorFields) && !$error) {
                $this->db->beginTransaction();
                try {
                    $userId    = (int)($_SESSION['user_id'] ?? 0);
                    $numero    = $this->generateNumero();
                    $factureId = $this->factureModel->create(
                        $numero, (int)$fournisseurId, (int)$depotId,
                        $date, $notes !== '' ? $notes : null, $userId
                    );
                    $ordre = 0; $totalTtc = 0.0;
                    foreach ($articles as $ligne) {
                        $articleId    = (int)$ligne['article_id'];
                        $quantite     = (int)$ligne['quantite'];
                        $prixUnitaire = (float)$this->normalizePrice($ligne['prix_unitaire']);
                        $description  = $ligne['description'] ?? null;
                        $totalLigne   = round($quantite * $prixUnitaire, 2);
                        $this->ligneModel->create($factureId, $articleId, $quantite, $prixUnitaire, $totalLigne, $ordre++, $description);
                        $totalTtc += $totalLigne;
                    }
                    $totaux = $this->calculerTotaux($totalTtc);
                    $this->factureModel->updateTotals($factureId, $totaux['ht'], $totaux['tva'], $totaux['ttc']);
                    $this->db->commit();
                    header('Location: index.php?action=factures_fournisseurs/show&id=' . $factureId);
                    exit();
                } catch (Throwable $e) {
                    if ($this->db->inTransaction()) $this->db->rollBack();
                    $error = "Erreur création : " . $e->getMessage();
                }
            } else {
                $lignes = $this->reconstituerLignes($articles);
            }
        }

        return [
            'view' => 'factures_fournisseurs/create',
            'data' => [
                'error' => $error, 'errorFields' => $errorFields, 'lignes' => $lignes,
                'fournisseurs' => $this->fournisseurModel->getAll(),
                'depots'       => $this->depotModel->getAll(),
                'articles'     => $this->articleModel->getAll(),
            ]
        ];
    }

    public function edit(int $id): array {
        $this->checkAuth();
        if (!$this->can('view')) die('Accès refusé.');
        $facture = $this->factureModel->findById($id);
        if (!$facture) return ['view' => 'error', 'data' => ['message' => 'Facture fournisseur introuvable.']];
        return [
            'view' => 'factures_fournisseurs/edit',
            'data' => [
                'facture'      => $facture,
                'lignes'       => $this->ligneModel->getByFactureId($id, true),
                'fournisseurs' => $this->fournisseurModel->getAll(),
                'depots'       => $this->depotModel->getAll(),
                'articles'     => $this->articleModel->getAll(),
                'error'        => null,
                'errorFields'  => [],
            ]
        ];
    }

    public function update(int $id, array $data): array {
        $this->checkAuth();
        if (!$this->can('edit')) die('Accès refusé.');
        $facture = $this->factureModel->findById($id);
        if (!$facture) return ['view' => 'error', 'data' => ['message' => 'Facture fournisseur introuvable.']];
        $this->ensureEditable($facture);

        $error = null; $errorFields = []; $lignes = [];

        try {
            $fournisseurId = trim($data['fournisseur_id'] ?? '');
            $depotId       = trim($data['depot_id'] ?? '');
            $date          = trim($data['date'] ?? '');
            $notes         = trim($data['notes'] ?? '');
            $articles      = $data['articles'] ?? [];

            if ($fournisseurId === '') $errorFields['fournisseur_id'] = "Le fournisseur est obligatoire.";
            if ($depotId === '')       $errorFields['depot_id']       = "Le dépôt est obligatoire.";
            if ($date === '')          $errorFields['date']            = "La date est obligatoire.";
            if (empty($articles))      $error = "Veuillez ajouter au moins un article.";

            foreach ($articles as $i => $ligne) {
                if (empty($ligne['article_id'])) $errorFields["articles_{$i}_article_id"] = "Article obligatoire.";
                $q = trim((string)($ligne['quantite'] ?? ''));
                if ($q === '' || !is_numeric($q) || (float)$q <= 0) $errorFields["articles_{$i}_quantite"] = "Quantité invalide.";
                $p = $this->normalizePrice($ligne['prix_unitaire'] ?? '');
                if ($p === '' || !is_numeric($p) || (float)$p < 0) $errorFields["articles_{$i}_prix_unitaire"] = "Prix invalide.";
            }

            if (empty($errorFields) && !$error) {
                $this->db->beginTransaction();
                $userId = (int)($_SESSION['user_id'] ?? 0);
                $this->factureModel->updateHeader($id, (int)$fournisseurId, (int)$depotId, $date, $notes !== '' ? $notes : null, $userId);
                $this->ligneModel->deleteByFactureId($id);
                $ordre = 0; $totalTtc = 0.0;
                foreach ($articles as $ligne) {
                    $articleId    = (int)$ligne['article_id'];
                    $quantite     = (int)$ligne['quantite'];
                    $prixUnitaire = (float)$this->normalizePrice($ligne['prix_unitaire']);
                    $description  = $ligne['description'] ?? null;
                    $totalLigne   = round($quantite * $prixUnitaire, 2);
                    $this->ligneModel->create($id, $articleId, $quantite, $prixUnitaire, $totalLigne, $ordre++, $description);
                    $totalTtc += $totalLigne;
                }
                $totaux = $this->calculerTotaux($totalTtc);
                $this->factureModel->updateTotals($id, $totaux['ht'], $totaux['tva'], $totaux['ttc']);
                $this->db->commit();
                header('Location: index.php?action=factures_fournisseurs/show&id=' . $id);
                exit();
            } else {
                $lignes = $this->reconstituerLignes($articles);
            }
        } catch (Throwable $e) {
            if ($this->db->inTransaction()) $this->db->rollBack();
            $error = "Erreur mise à jour : " . $e->getMessage();
        }

        if (empty($lignes)) $lignes = $this->ligneModel->getByFactureId($id, true);

        return [
            'view' => 'factures_fournisseurs/edit',
            'data' => [
                'facture'      => $this->factureModel->findById($id),
                'lignes'       => $lignes,
                'fournisseurs' => $this->fournisseurModel->getAll(),
                'depots'       => $this->depotModel->getAll(),
                'articles'     => $this->articleModel->getAll(),
                'error'        => $error,
                'errorFields'  => $errorFields,
            ]
        ];
    }

    public function show(int $id): array {
        $this->checkAuth();
        if (!$this->can('view')) die('Accès refusé.');
        $facture = $this->factureModel->findById($id);
        if (!$facture) return ['view' => 'error', 'data' => ['message' => 'Facture fournisseur introuvable.']];
        return [
            'view' => 'factures_fournisseurs/show',
            'data' => ['facture' => $facture, 'lignes' => $this->ligneModel->getByFactureId($id, true)]
        ];
    }

    /**
     * Validation — ordre strict :
     *  1. CMP calculé AVANT mouvementModel (lit le stock avant réception)
     *  2. mouvementModel->create() incrémente le stock (pas besoin d'incrementerStock séparé)
     *  3. Marquer validée
     *
     * ✅ PAS de incrementerStock() car mouvementModel->create() le fait déjà
     */
    public function validate(int $id): void {
        $this->checkAuth();
        if (!$this->can('edit')) die('Accès refusé.');

        $facture = $this->factureModel->findById($id);
        if (!$facture) die("Facture fournisseur introuvable.");

        if (($facture['statut'] ?? 'draft') === 'validated') {
            header('Location: index.php?action=factures_fournisseurs/show&id=' . $id);
            exit();
        }

        $userId  = (int)($_SESSION['user_id'] ?? 0);
        if (!$userId) die("Utilisateur non authentifié.");

        $lignes  = $this->ligneModel->getByFactureId($id, true);
        $depotId = (int)$facture['depot_id'];
        $numero  = $facture['numero'] ?? "FF-{$id}";

        $this->db->beginTransaction();
        try {
            foreach ($lignes as $ln) {
                $articleId = (int)$ln['article_id'];
                $qte       = (float)$ln['quantite'];
                $prixAchat = (float)$ln['prix_unitaire']; // TTC

                if ($qte <= 0) continue;

                // ── 1. CMP calculé AVANT l'entrée en stock ────────────
                // (lit quantite_totale avant réception pour le calcul correct)
                $this->mettreAJourCMP($articleId, $qte, $prixAchat, $userId, $numero);

                // ── 2. mouvementModel->create() incrémente le stock ───
                // ✅ PAS besoin d'appeler incrementerStock() en plus
                // mouvementModel fait déjà : stock_par_depot + quantite_totale
                $this->mouvementModel->create([
                    'article_id'     => $articleId,
                    'depot_id'       => $depotId,
                    'type_mouvement' => 'entree',
                    'quantite'       => $qte,
                    'reference'      => $numero,
                    'description'    => 'Entrée automatique — facture fournisseur',
                    'document_type'  => 'facture_fournisseur',
                    'document_id'    => $id,
                    'prix_unitaire'  => $prixAchat,
                ], $userId);
            }

            // ── 3. Marquer la facture validée ─────────────────────────
            $this->factureModel->setValidated($id, $userId);

            $this->db->commit();
            header('Location: index.php?action=factures_fournisseurs/show&id=' . $id);
            exit();

        } catch (Throwable $e) {
            if ($this->db->inTransaction()) $this->db->rollBack();
            die("Erreur validation : " . $e->getMessage());
        }
    }

    public function delete(int $id): void {
        $this->checkAuth();
        if (!$this->can('delete')) die('Accès refusé.');
        $facture = $this->factureModel->findById($id);
        if (!$facture) die("Facture fournisseur introuvable.");
        if (($facture['statut'] ?? 'draft') === 'validated' && !$this->isAdmin()) {
            die("Suppression interdite : facture validée.");
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
