<?php
require_once __DIR__ . '/../includes/permissions.php';
require_once __DIR__ . '/../models/devis.php';
require_once __DIR__ . '/../models/DevisLigne.php';
require_once __DIR__ . '/../models/article.php';
require_once __DIR__ . '/../models/client.php';

class DevisController {
    private $devisModel;
    private $devisLigneModel;
    private $articleModel;
    private $clientModel;
    private $db;

    public function __construct(PDO $db) {
        $this->devisModel      = new Devis($db);
        $this->devisLigneModel = new DevisLigne($db);
        $this->articleModel    = new Article($db);
        $this->clientModel     = new Client($db);
        $this->db              = $db;
    }

    // Retourne map: [articleId => pr(TTC)]
private function getArticlesPR(array $articleIds): array {
    $articleIds = array_values(array_unique(array_filter(array_map('intval', $articleIds))));
    if (empty($articleIds)) return [];

    $placeholders = implode(',', array_fill(0, count($articleIds), '?'));
    $stmt = $this->db->prepare("SELECT id_articles, pr FROM articles WHERE id_articles IN ($placeholders)");
    $stmt->execute($articleIds);

    $map = [];
    foreach ($stmt->fetchAll(PDO::FETCH_ASSOC) as $r) {
        $map[(int)$r['id_articles']] = (float)$r['pr'];
    }
    return $map;
}

// Valide prix TTC >= PR TTC. Remplit $errorFields si erreur.
private function validatePrixVsPR(array $articles, array &$errorFields): array {
    $articleIds = [];
    foreach ($articles as $ln) {
        if (!empty($ln['article_id'])) $articleIds[] = (int)$ln['article_id'];
    }
    $prMap = $this->getArticlesPR($articleIds);

    foreach ($articles as $inputIndex => $ln) {
        $articleId = (int)($ln['article_id'] ?? 0);
        if ($articleId <= 0) continue;

        $prixUnitaire = (float)$this->normalize_price($ln['prix_unitaire'] ?? '');
        $pr = $prMap[$articleId] ?? null;

        if ($pr === null) {
            $errorFields["articles_{$inputIndex}_article_id"] = "Article introuvable.";
            continue;
        }

        if ($prixUnitaire < (float)$pr) {
            $errorFields["articles_{$inputIndex}_prix_unitaire"] =
                "Prix non valide (inférieur au prix de revient).";
        }
    }

    return $prMap;
}

    private function normalize_price($prix) {
        $prix = preg_replace('/[\s\xA0]/u', '', (string)$prix);
        $prix = str_replace(',', '.', $prix);
        return $prix;
    }

    private function checkAuth() {
        if (!isset($_SESSION['user_id'])) {
            header('Location: index.php?action=login');
            exit();
        }
    }

    private function isAdmin(): bool {
        return (($_SESSION['role'] ?? '') === 'admin');
    }

    private function ensureDevisEditable(array $devis): void {
        $statut = $devis['statut'] ?? 'draft';
        if ($statut === 'validated' && !$this->isAdmin()) {
            die("Devis validé : modification interdite.");
        }
    }

    // =================== LISTE ===================

    public function index(): array {
        $this->checkAuth();
        if (!hasPermission('devis', 'view')) die('Accès refusé.');

        $parPage = 10;
        $page = isset($_GET['page']) ? max(1, (int)$_GET['page']) : 1;
        $offset = ($page - 1) * $parPage;
        $searchTerm = isset($_GET['search_term']) ? trim($_GET['search_term']) : '';

        if ($searchTerm === '') {
            $devis = $this->devisModel->getAllDevis($parPage, $offset);
            $total = $this->devisModel->countDevis();
        } else {
            $devis = $this->devisModel->searchDevis($searchTerm, $parPage, $offset);
            $total = $this->devisModel->countSearchDevis($searchTerm);
        }

        return [
            'view' => 'devis/index',
            'data' => [
                'devis' => $devis,
                'total' => $total,
                'page' => $page,
                'parPage' => $parPage,
                'search_term' => $searchTerm
            ]
        ];
    }

    // =================== CREATE ===================

    public function create(array $data): array {
        $this->checkAuth();
        if (!hasPermission('devis', 'create')) die('Accès refusé.');

        $error = null;
        $errorFields = [];
        $lignesDevis = [];
        $clientId = null;

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $clientId  = trim($data['client_id'] ?? '');
            $date      = trim($data['date'] ?? '');
            $articles  = $data['articles'] ?? [];
            $reference = trim($data['reference'] ?? '');

            if (empty($clientId)) $errorFields['client_id'] = "Le client est obligatoire.";
            if (empty($date))     $errorFields['date'] = "La date est obligatoire.";
            if (empty($articles)) $error = "Veuillez ajouter au moins un article au devis.";

            foreach ($articles as $inputIndex => $articleData) {
                if (empty($articleData['article_id'])) {
                    $errorFields["articles_{$inputIndex}_article_id"] = "Article obligatoire.";
                }

                $quantite = isset($articleData['quantite']) ? trim($articleData['quantite']) : '';
                if ($quantite === '' || !is_numeric($quantite) || floatval($quantite) < 1) {
                    $errorFields["articles_{$inputIndex}_quantite"] = "Quantité invalide.";
                }

                $prix = $this->normalize_price($articleData['prix_unitaire'] ?? '');
                if ($prix === '' || !is_numeric($prix) || floatval($prix) < 0) {
                    $errorFields["articles_{$inputIndex}_prix_unitaire"] = "Prix unitaire invalide.";
                }
            }

            // ✅ Contrôle PRO: prix TTC >= PR TTC + récup PR map
$prMap = [];
if (empty($errorFields) && !$error) {
    $prMap = $this->validatePrixVsPR($articles, $errorFields);
}

            if (empty($errorFields) && !$error) {
                $numeroDevis = $this->generateNumeroDevis();
                $totalDevis  = 0;

                try {
                    $this->db->beginTransaction();

                    $userId  = (int)$_SESSION['user_id'];
                    $devisId = $this->devisModel->create($numeroDevis, (int)$clientId, $date, $totalDevis, $userId, $reference);

                    $ordre = 0;
                    foreach ($articles as $ligne) {
                        $articleId    = (int)$ligne['article_id'];
                        $quantite     = (int)$ligne['quantite'];
                        $prixUnitaire = (float)$this->normalize_price($ligne['prix_unitaire']);
                        $totalLigne   = $quantite * $prixUnitaire;
                        $description  = $ligne['description'] ?? null;

                        $prRefTtc = (float)($prMap[$articleId] ?? 0);

$this->devisLigneModel->create(
    $devisId,
    $articleId,
    $quantite,
    $prixUnitaire,
    $prRefTtc,
    $totalLigne,
    $ordre,
    $description
);
                        $this->updateClientArticlePrix((int)$clientId, $articleId, $prixUnitaire);

                        $totalDevis += $totalLigne;
                        $ordre++;
                    }

                    $this->devisModel->updateTotal($devisId, $totalDevis);
                    $this->db->commit();

                    header('Location: index.php?action=devis');
                    exit();
                } catch (Exception $e) {
                    $this->db->rollBack();
                    $error = "Erreur lors de la création du devis : " . $e->getMessage();
                    $lignesDevis = $this->reconstituerLignesDevis($articles);
                }
            } else {
                $lignesDevis = $this->reconstituerLignesDevis($articles);
            }
        }

        $clients      = $this->clientModel->getAll();
        $articlesList = $this->articleModel->getAll();

        if (empty($lignesDevis)) {
            // tu avais un row par défaut, je garde
            $lignesDevis = [[
                'article_id'    => '',
                'nom_art'       => '',
                'quantite'      => '',
                'prix_unitaire' => '0,00',
                'description'   => '',
            ]];
        }

        // Pré-remplissage prix si client + article connus
        foreach ($lignesDevis as &$ligne) {
            if (!empty($ligne['article_id']) && !empty($clientId)) {
                $p = $this->getSuggestedPrice((int)$clientId, (int)$ligne['article_id']);
                if ($p !== null) {
                    $ligne['prix_unitaire'] = number_format($p, 0, ',', ' ');
                }
            }
        }
        unset($ligne);

        $numeroDevis = $this->generateNumeroDevis();

        return [
            'view' => 'devis/create',
            'data' => [
                'clients'     => $clients,
                'articles'    => $articlesList,
                'error'       => $error,
                'errorFields' => $errorFields,
                'lignesDevis' => $lignesDevis,
                'numeroDevis' => $numeroDevis
            ]
        ];
    }

    // =================== EDIT ===================

    public function edit(int $id): array {
        $this->checkAuth();
        if (!hasPermission('devis', 'edit')) die('Accès refusé.');

        $devis = $this->devisModel->findById($id);
        if (!$devis) return ['view' => 'error', 'data' => ['message' => 'Devis non trouvé.']];

        // verrouiller si validé (sauf admin)
        if (($devis['statut'] ?? 'draft') === 'validated' && !$this->isAdmin()) {
            // on laisse afficher l'écran en lecture seule via la vue (mais on peut aussi die)
            // je préfère laisser la vue gérer en readonly
        }

        $lignesDevis = $this->devisLigneModel->getByDevisId($id, true);
        $clients     = $this->clientModel->getAll();
        $articles    = $this->articleModel->getAll();

        return [
            'view' => 'devis/edit',
            'data' => [
                'devis'       => $devis,
                'lignesDevis' => $lignesDevis,
                'clients'     => $clients,
                'articles'    => $articles,
                'errorFields' => [],
            ]
        ];
    }

    // =================== UPDATE ===================

    public function update(int $id, array $data): array {
        $this->checkAuth();
        if (!hasPermission('devis', 'edit')) die('Accès refusé.');

        $devis = $this->devisModel->findById($id);
        if (!$devis) {
            return ['view' => 'error', 'data' => ['message' => 'Devis non trouvé.']];
        }
        $this->ensureDevisEditable($devis);

        $error = null;
        $errorFields = [];
        $lignesDevis = [];

        try {
            $clientId  = trim($data['client_id'] ?? '');
            $date      = trim($data['date'] ?? '');
            $reference = trim($data['reference'] ?? '');
            $articles  = $data['articles'] ?? [];

            if (empty($clientId)) $errorFields['client_id'] = "Le client est obligatoire.";
            if (empty($date))     $errorFields['date'] = "La date est obligatoire.";
            if (empty($articles)) $error = "Veuillez ajouter au moins un article au devis.";

            foreach ($articles as $inputIndex => $ligne) {
                if (empty($ligne['article_id'])) {
                    $errorFields["articles_{$inputIndex}_article_id"] = "Article obligatoire.";
                }

                $quantite = isset($ligne['quantite']) ? trim($ligne['quantite']) : '';
                if ($quantite === '' || !is_numeric($quantite) || floatval($quantite) < 1) {
                    $errorFields["articles_{$inputIndex}_quantite"] = "Quantité invalide.";
                }

                $prix = $this->normalize_price($ligne['prix_unitaire'] ?? '');
                if ($prix === '' || !is_numeric($prix) || floatval($prix) < 0) {
                    $errorFields["articles_{$inputIndex}_prix_unitaire"] = "Prix unitaire invalide.";
                }
            }
$prMap = [];
if (empty($errorFields) && !$error) {
    $prMap = $this->validatePrixVsPR($articles, $errorFields);
}
            if (empty($errorFields) && !$error) {
                $this->db->beginTransaction();

                $this->devisModel->updateHeader($id, (int)$clientId, $date, $reference);
                $this->devisLigneModel->deleteByDevisId($id);

                $totalDevis = 0;
                $ordre = 0;
                foreach ($articles as $ligne) {
                    $articleId    = (int)$ligne['article_id'];
                    $quantite     = (int)$ligne['quantite'];
                    $prixUnitaire = (float)$this->normalize_price($ligne['prix_unitaire']);
                    $totalLigne   = $quantite * $prixUnitaire;
                    $description  = $ligne['description'] ?? null;

$prRefTtc = (float)($prMap[$articleId] ?? 0);

$this->devisLigneModel->create(
    $id,
    $articleId,
    $quantite,
    $prixUnitaire,
    $prRefTtc,
    $totalLigne,
    $ordre,
    $description
);
                    $this->updateClientArticlePrix((int)$clientId, $articleId, $prixUnitaire);

                    $totalDevis += $totalLigne;
                    $ordre++;
                }

                $this->devisModel->updateTotal($id, $totalDevis);

                $this->db->commit();
                header('Location: index.php?action=devis/detail&id=' . $id);
                exit();
            } else {
                $lignesDevis = $this->reconstituerLignesDevis($articles);
            }
        } catch (Exception $e) {
            if ($this->db->inTransaction()) $this->db->rollBack();
            $error = "Erreur lors de la mise à jour du devis : " . $e->getMessage();
        }

        $clients      = $this->clientModel->getAll();
        $articlesList = $this->articleModel->getAll();
        if (empty($lignesDevis)) {
            $lignesDevis = $this->devisLigneModel->getByDevisId($id, true);
        }

        return [
            'view' => 'devis/edit',
            'data' => [
                'devis'       => $this->devisModel->findById($id),
                'lignesDevis' => $lignesDevis,
                'clients'     => $clients,
                'articles'    => $articlesList,
                'error'       => $error,
                'errorFields' => $errorFields,
            ]
        ];
    }

    // =================== DETAIL ===================

    public function detail(int $id): array {
        $this->checkAuth();
        if (!hasPermission('devis', 'view')) die('Accès refusé.');

        $devis = $this->devisModel->findById($id);
        if (!$devis) {
            return ['view' => 'error', 'data' => ['message' => 'Devis non trouvé.']];
        }

        $client = $this->clientModel->findById((int)$devis['client_id']);
        $lignesDevis = $this->devisLigneModel->getByDevisId($id, true);

        return [
            'view' => 'devis/detail',
            'data' => [
                'devis'       => $devis,
                'lignesDevis' => $lignesDevis,
                'client'      => $client,
                'nom_client'  => $client ? $client['nom'] : 'Client inconnu',
                'reference'   => $devis['reference'] ?? ''
            ]
        ];
    }

    // =================== DELETE ===================

    public function delete(int $id): void {
        $this->checkAuth();
        if (!hasPermission('devis', 'delete')) die('Accès refusé.');

        try {
            $this->devisLigneModel->deleteByDevisId($id);
            $this->devisModel->delete($id);
            header('Location: index.php?action=devis');
            exit();
        } catch (Exception $e) {
            echo "Erreur lors de la suppression du devis : " . $e->getMessage();
        }
    }

    // =================== SEARCH / AJAX ===================

    public function search(array $data): array {
        $this->checkAuth();
        if (!hasPermission('devis', 'view')) die('Accès refusé.');

        $searchTerm = trim($data['search_term'] ?? '');
        $parPage = 10;
        $page = isset($_GET['page']) ? max(1, (int)$_GET['page']) : 1;
        $offset = ($page - 1) * $parPage;

        if (empty($searchTerm)) {
            $devis = $this->devisModel->getAllDevis($parPage, $offset);
            $total = $this->devisModel->countDevis();
        } else {
            $devis = $this->devisModel->searchDevis($searchTerm, $parPage, $offset);
            $total = $this->devisModel->countSearchDevis($searchTerm);
        }

        return [
            'view' => 'devis/index',
            'data' => [
                'devis' => $devis,
                'search_term' => $searchTerm,
                'total' => $total,
                'page' => $page,
                'parPage' => $parPage
            ]
        ];
    }

    public function searchArticles(string $term): array {
        header('Content-Type: application/json');
        $articles = $this->articleModel->searchByName($term);
        echo json_encode($articles);
        exit();
    }

    // IMPORTANT: Doit être void (echo+exit)
    public function getArticlePrice(int $clientId, int $articleId): void {
        header('Content-Type: application/json');

        $prix = $this->getSuggestedPrice($clientId, $articleId);
        $prixFormate = $prix !== null ? number_format($prix, 0, ',', ' ') : null;

        echo json_encode(['prix' => $prixFormate]);
        exit();
    }

    // =================== VALIDATION / DUPLICATION ===================

     public function validate(int $id): void {
        $this->checkAuth();
        if (!hasPermission('devis', 'edit')) die('Accès refusé.');

        $devis = $this->devisModel->findById($id);
        if (!$devis) die("Devis introuvable.");

        if (($devis['statut'] ?? 'draft') === 'validated') {
            header('Location: index.php?action=devis/detail&id=' . $id);
            exit();
        }

        $userId = (int)($_SESSION['user_id'] ?? 0);
        if (!$userId) die("Utilisateur non authentifié.");

        require_once __DIR__ . '/../models/Livraison.php';
        require_once __DIR__ . '/../models/Depot.php';

        $livraisonModel = new Livraison($this->db);
        $depotModel = new Depot($this->db);

        // TX guard côté controller aussi (Solution 1)
        $startedTx = false;
        if (!$this->db->inTransaction()) {
            $this->db->beginTransaction();
            $startedTx = true;
        }

        try {
            // 1) Valider le devis
            $this->devisModel->setValidated($id, $userId);

            // 2) Anti doublon BL
            $stmt = $this->db->prepare("SELECT id FROM bons_livraison WHERE devis_id = ? ORDER BY id DESC LIMIT 1");
            $stmt->execute([$id]);
            $existingBlId = (int)($stmt->fetchColumn() ?: 0);

            if ($existingBlId > 0) {
                $blId = $existingBlId;
            } else {
                $mainDepotId = $depotModel->getMainDepotId();
                $blId = $livraisonModel->createFromDevis($id, $mainDepotId, $userId);
            }

            if ($startedTx) {
                $this->db->commit();
            }

            header('Location: index.php?action=livraisons/edit&id=' . $blId);
            exit();
        } catch (Throwable $e) {
            if ($startedTx && $this->db->inTransaction()) {
                $this->db->rollBack();
            }
            die("Erreur validation devis / création BL: " . $e->getMessage());
        }
    }

    public function duplicate(int $id): void {
        $this->checkAuth();
        if (!hasPermission('devis', 'create')) die('Accès refusé.');

        $userId = (int)($_SESSION['user_id'] ?? 0);
        $newId = $this->devisModel->duplicate($id, $userId);

        header('Location: index.php?action=devis/edit&id=' . $newId);
        exit();
    }

    // =================== UTILITAIRES ===================

    private function updateClientArticlePrix(int $clientId, int $articleId, float $prix): void {
        $this->devisLigneModel->updateOrCreateClientArticlePrix($clientId, $articleId, $prix);
    }

    private function generateNumeroDevis(): string {
        $annee = date('Y');
        $dernierNumero = $this->devisModel->getLastNumeroDevis($annee);

        if ($dernierNumero) {
            if (preg_match('/(\d+)$/', $dernierNumero, $m)) $seq = (int)$m[1] + 1;
            else $seq = 1;
        } else {
            $seq = 1;
        }

        return sprintf('DEV-%s-%03d', $annee, $seq);
    }

    /**
     * Prix suggéré:
     * 1) client_articles_ligne
     * 2) tarif client (clients.tarif) => articles.prix_*
     * 3) fallback article.prix_detail/prix_vente
     */
    private function getSuggestedPrice(int $clientId, int $articleId): ?float {
        // 1) prix mémorisé client/article
        $p = $this->devisLigneModel->getLastPrixArticleClient($clientId, $articleId);
        if ($p !== null && $p > 0) return $p;

        // 2) tarif client
        $tarif = $this->devisLigneModel->getClientTarif($clientId);
        $tarifPrice = $this->devisLigneModel->getArticleTarifPrice($articleId, $tarif);
        return $tarifPrice;
    }

    private function reconstituerLignesDevis(array $articles): array {
        $lignesDevis = [];
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
            $q = $this->normalize_price($quantite);
            $p = $this->normalize_price($prixUnitaire);
            if (is_numeric($q) && is_numeric($p)) $total = (float)$q * (float)$p;

            $lignesDevis[] = [
                'id'           => $articleData['id'] ?? null,
                'article_id'    => $articleId,
                'nom_art'       => $nomArt,
                'quantite'      => $quantite,
                'prix_unitaire' => $prixUnitaire,
                'total'         => $total,
                'description'   => $articleData['description'] ?? '',
            ];
        }
        return $lignesDevis;
    }
}