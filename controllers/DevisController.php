<<<<<<< HEAD
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

    // --- Utilitaire pour le formatage des prix venant du formulaire ---
    private function normalize_price($prix) {
        // Enlève tous les espaces (normaux, insécables, etc.)
        $prix = preg_replace('/[\s\xA0]/u', '', $prix);
        // Remplace la virgule par un point
        $prix = str_replace(',', '.', $prix);
        return $prix;
    }

    // ------------------- CRUD -------------------

    public function index(): array {
        $this->checkAuth();
        if (!hasPermission('devis', 'view')) die('Accès refusé.');

        // Pagination
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

    public function create(array $data): array {
        $this->checkAuth();
        if (!hasPermission('devis', 'create')) die('Accès refusé.');
        $error = null;
        $errorFields = [];
        $lignesDevis = [];

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
                // Correction: nettoyage du prix avant validation
                $prix = $this->normalize_price($articleData['prix_unitaire'] ?? '');
                if (!isset($articleData['prix_unitaire']) || $prix === '' || !is_numeric($prix) || floatval($prix) < 0) {
                    $errorFields["articles_{$inputIndex}_prix_unitaire"] = "Prix unitaire invalide.";
                }
            }

            if (empty($errorFields) && !$error) {
                $numeroDevis = $this->generateNumeroDevis();
                $totalDevis  = 0;
                try {
                    $this->db->beginTransaction();
                    $userId  = $_SESSION['user_id'];
                    $devisId = $this->devisModel->create($numeroDevis, $clientId, $date, $totalDevis, $userId, $reference);

                    $ordre = 0;
                    foreach ($articles as $ligne) {
                        $articleId   = (int)$ligne['article_id'];
                        $quantite    = (int)$ligne['quantite'];
                        $prixUnitaire= (float)$this->normalize_price($ligne['prix_unitaire']);
                        $totalLigne  = $quantite * $prixUnitaire;
                        $description = $ligne['description'] ?? null;
                        $this->devisLigneModel->create($devisId, $articleId, $quantite, $prixUnitaire, $totalLigne, $ordre, $description);
                        $this->updateClientArticlePrix($clientId, $articleId, $prixUnitaire);
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
            $lignesDevis = [[
                'article_id'    => '',
                'nom_art'       => '',
                'quantite'      => '',
                'prix_unitaire' => '0,00',
                'description'   => '',
            ]];
        }

        foreach ($lignesDevis as &$ligne) {
            if (!empty($ligne['article_id']) && !empty($clientId)) {
                $dernierPrix = $this->getLastPrixArticleClient((int)$clientId, (int)$ligne['article_id']);
                if ($dernierPrix !== null) {
                    $ligne['prix_unitaire'] = number_format($dernierPrix, 2, ',', ' ');
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

    public function edit(int $id): array {
        $this->checkAuth();
        if (!hasPermission('devis', 'edit')) die('Accès refusé.');
        $devis      = $this->devisModel->findById($id);
        if (!$devis) return ['view' => 'error', 'data' => ['message' => 'Devis non trouvé.']];

        $lignesDevis = $this->devisLigneModel->getByDevisId($id, true);
        $clients     = $this->clientModel->getAll();
        $articles    = $this->articleModel->getAll();

        return [
            'view' => 'devis/edit',
            'data' => [
                'devis'      => $devis,
                'lignesDevis'=> $lignesDevis,
                'clients'    => $clients,
                'articles'   => $articles,
                'errorFields'=> [],
            ]
        ];
    }

    public function update(int $id, array $data): array {
        $this->checkAuth();
        if (!hasPermission('devis', 'edit')) die('Accès refusé.');
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
                // Correction: nettoyage du prix avant validation
                $prix = $this->normalize_price($ligne['prix_unitaire'] ?? '');
                if (!isset($ligne['prix_unitaire']) || $prix === '' || !is_numeric($prix) || floatval($prix) < 0) {
                    $errorFields["articles_{$inputIndex}_prix_unitaire"] = "Prix unitaire invalide.";
                }
            }

            if (empty($errorFields) && !$error) {
                $this->db->beginTransaction();

                $devis = $this->devisModel->findById($id);
                if (!$devis) throw new Exception("Devis non trouvé.");

                $this->devisModel->updateHeader($id, $clientId, $date, $reference);
                $this->devisLigneModel->deleteByDevisId($id);

                $totalDevis = 0;
                $ordre = 0;
                foreach ($articles as $ligne) {
                    $articleId    = (int)$ligne['article_id'];
                    $quantite     = (int)$ligne['quantite'];
                    $prixUnitaire = (float)$this->normalize_price($ligne['prix_unitaire']);
                    $totalLigne   = $quantite * $prixUnitaire;
                    $description  = $ligne['description'] ?? null;
                    $this->devisLigneModel->create($id, $articleId, $quantite, $prixUnitaire, $totalLigne, $ordre, $description);
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
            $this->db->rollBack();
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
                'lignesDevis'  => $lignesDevis,
                'clients'     => $clients,
                'articles'    => $articlesList,
                'error'       => $error,
                'errorFields' => $errorFields,
            ]
        ];
    }

    public function detail(int $id): array {
        $this->checkAuth();
        if (!hasPermission('devis', 'view')) die('Accès refusé.');

        $devis = $this->devisModel->findById($id);
        if (!$devis) {
            return [
                'view' => 'error',
                'data' => ['message' => 'Devis non trouvé.']
            ];
        }

        $client = $this->clientModel->findById($devis['client_id']);
        $nomClient = $client ? $client['nom'] : 'Client inconnu';
        $reference = $devis['reference'] ?? '';
        $lignesDevis = $this->devisLigneModel->getByDevisId($id, true);

        return [
            'view' => 'devis/detail',
            'data' => [
                'devis'        => $devis,        // Toutes les infos sur le devis, dont 'reference'
                'lignesDevis'  => $lignesDevis,
                'client'       => $client,       // Toutes les infos sur le client
                'nom_client'   => $nomClient,
                'reference'    => $reference     // Référence en accès direct
            ]
        ];
    }

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

    public function deleteLigne(): void {
        $this->checkAuth();
        $ligneId = $_POST['ligne_id'] ?? null;
        if (!$ligneId || !is_numeric($ligneId)) {
            echo json_encode(['success' => false, 'message' => 'ID invalide']);
            exit();
        }
        try {
            $this->devisLigneModel->delete($ligneId);
            header('Content-Type: application/json');
            echo json_encode(['success' => true, 'message' => 'Ligne supprimée avec succès']);
            exit();
        } catch (Exception $e) {
            header('Content-Type: application/json');
            echo json_encode(['success' => false, 'message' => 'Erreur : ' . $e->getMessage()]);
            exit();
        }
    }

    // ------------------- RECHERCHE ET AJAX -------------------

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
        header("Access-Control-Allow-Origin: http://localhost");
        header("Access-Control-Allow-Methods: GET, POST, OPTIONS");
        header("Access-Control-Allow-Headers: X-Requested-With, Content-Type, Origin, Authorization, Accept, Client-Security-Token, Accept-Encoding");
        header('Content-Type: application/json');
        $articles = $this->articleModel->searchByName($term);
        echo json_encode($articles);
        exit();
    }

    public function getArticlePrice(int $clientId, int $articleId): array {
        header("Access-Control-Allow-Origin: http://localhost");
        header("Access-Control-Allow-Methods: GET, POST, OPTIONS");
        header("Access-Control-Allow-Headers: X-Requested-With, Content-Type, Origin, Authorization, Accept, Client-Security-Token, Accept-Encoding");
        header('Content-Type: application/json');
        $prix = $this->devisLigneModel->getLastPrixArticleClient($clientId, $articleId);
        $prixFormate = $prix !== null ? number_format($prix, 0, ',', ' ') : null;
        echo json_encode(['prix' => $prixFormate]);
        exit();
    }

    // ------------------- UTILITAIRES -------------------

    private function updateClientArticlePrix(int $clientId, int $articleId, float $prix): void {
        $this->devisLigneModel->updateOrCreateClientArticlePrix($clientId, $articleId, $prix);
    }

  private function generateNumeroDevis(): string {
        $annee = date('Y');
        // Supposons que getLastNumeroDevis($annee) retourne le dernier numero au format "DEV-2025-012"
        $dernierNumero = $this->devisModel->getLastNumeroDevis($annee);

        if ($dernierNumero) {
            // On extrait la dernière séquence numérique à la fin du numéro
            if (preg_match('/(\d+)$/', $dernierNumero, $m)) {
                $seq = (int)$m[1] + 1;
            } else {
                $seq = 1;
            }
        } else {
            $seq = 1;
        }

        return sprintf('DEV-%s-%03d', $annee, $seq);
    }


    private function getLastPrixArticleClient(int $clientId, int $articleId): ?float {
        return $this->devisLigneModel->getLastPrixArticleClient($clientId, $articleId);
    }

    private function checkAuth() {
        if (!isset($_SESSION['user_id'])) {
            header('Location: index.php?action=login');
            exit();
        }
    }

    /**
     * Reconstitue les lignes pour affichage après erreur validation (garde tout ce que l'utilisateur a tapé)
     */
    private function reconstituerLignesDevis(array $articles): array {
        $lignesDevis = [];
        foreach ($articles as $articleData) {
            $articleId    = $articleData['article_id'] ?? '';
            $quantite     = $articleData['quantite'] ?? '';
            $prixUnitaire = $articleData['prix_unitaire'] ?? '';
            $nomArt       = '';
            if ($articleId) {
                $article = $this->articleModel->findById($articleId);
                $nomArt = $article ? $article['nom_art'] : '';
            }
            $idLigne = $articleData['id'] ?? null;
            $total = 0;
            // Correction: nettoyer le prix pour le calcul du total
            $q = $this->normalize_price($quantite);
            $p = $this->normalize_price($prixUnitaire);
            if (is_numeric($q) && is_numeric($p)) $total = (float)$q * (float)$p;
            $lignesDevis[] = [
                'id'           => $idLigne,
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
=======
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

    // --- Utilitaire pour le formatage des prix venant du formulaire ---
    private function normalize_price($prix) {
        // Enlève tous les espaces (normaux, insécables, etc.)
        $prix = preg_replace('/[\s\xA0]/u', '', $prix);
        // Remplace la virgule par un point
        $prix = str_replace(',', '.', $prix);
        return $prix;
    }

    // ------------------- CRUD -------------------

    public function index(): array {
        $this->checkAuth();
        if (!hasPermission('devis', 'view')) die('Accès refusé.');

        // Pagination
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

    public function create(array $data): array {
        $this->checkAuth();
        if (!hasPermission('devis', 'create')) die('Accès refusé.');
        $error = null;
        $errorFields = [];
        $lignesDevis = [];

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
                // Correction: nettoyage du prix avant validation
                $prix = $this->normalize_price($articleData['prix_unitaire'] ?? '');
                if (!isset($articleData['prix_unitaire']) || $prix === '' || !is_numeric($prix) || floatval($prix) < 0) {
                    $errorFields["articles_{$inputIndex}_prix_unitaire"] = "Prix unitaire invalide.";
                }
            }

            if (empty($errorFields) && !$error) {
                $numeroDevis = $this->generateNumeroDevis();
                $totalDevis  = 0;
                try {
                    $this->db->beginTransaction();
                    $userId  = $_SESSION['user_id'];
                    $devisId = $this->devisModel->create($numeroDevis, $clientId, $date, $totalDevis, $userId, $reference);

                    $ordre = 0;
                    foreach ($articles as $ligne) {
                        $articleId   = (int)$ligne['article_id'];
                        $quantite    = (int)$ligne['quantite'];
                        $prixUnitaire= (float)$this->normalize_price($ligne['prix_unitaire']);
                        $totalLigne  = $quantite * $prixUnitaire;
                        $description = $ligne['description'] ?? null;
                        $this->devisLigneModel->create($devisId, $articleId, $quantite, $prixUnitaire, $totalLigne, $ordre, $description);
                        $this->updateClientArticlePrix($clientId, $articleId, $prixUnitaire);
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
            $lignesDevis = [[
                'article_id'    => '',
                'nom_art'       => '',
                'quantite'      => '',
                'prix_unitaire' => '0,00',
                'description'   => '',
            ]];
        }

        foreach ($lignesDevis as &$ligne) {
            if (!empty($ligne['article_id']) && !empty($clientId)) {
                $dernierPrix = $this->getLastPrixArticleClient((int)$clientId, (int)$ligne['article_id']);
                if ($dernierPrix !== null) {
                    $ligne['prix_unitaire'] = number_format($dernierPrix, 2, ',', ' ');
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

    public function edit(int $id): array {
        $this->checkAuth();
        if (!hasPermission('devis', 'edit')) die('Accès refusé.');
        $devis      = $this->devisModel->findById($id);
        if (!$devis) return ['view' => 'error', 'data' => ['message' => 'Devis non trouvé.']];

        $lignesDevis = $this->devisLigneModel->getByDevisId($id, true);
        $clients     = $this->clientModel->getAll();
        $articles    = $this->articleModel->getAll();

        return [
            'view' => 'devis/edit',
            'data' => [
                'devis'      => $devis,
                'lignesDevis'=> $lignesDevis,
                'clients'    => $clients,
                'articles'   => $articles,
                'errorFields'=> [],
            ]
        ];
    }

    public function update(int $id, array $data): array {
        $this->checkAuth();
        if (!hasPermission('devis', 'edit')) die('Accès refusé.');
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
                // Correction: nettoyage du prix avant validation
                $prix = $this->normalize_price($ligne['prix_unitaire'] ?? '');
                if (!isset($ligne['prix_unitaire']) || $prix === '' || !is_numeric($prix) || floatval($prix) < 0) {
                    $errorFields["articles_{$inputIndex}_prix_unitaire"] = "Prix unitaire invalide.";
                }
            }

            if (empty($errorFields) && !$error) {
                $this->db->beginTransaction();

                $devis = $this->devisModel->findById($id);
                if (!$devis) throw new Exception("Devis non trouvé.");

                $this->devisModel->updateHeader($id, $clientId, $date, $reference);
                $this->devisLigneModel->deleteByDevisId($id);

                $totalDevis = 0;
                $ordre = 0;
                foreach ($articles as $ligne) {
                    $articleId    = (int)$ligne['article_id'];
                    $quantite     = (int)$ligne['quantite'];
                    $prixUnitaire = (float)$this->normalize_price($ligne['prix_unitaire']);
                    $totalLigne   = $quantite * $prixUnitaire;
                    $description  = $ligne['description'] ?? null;
                    $this->devisLigneModel->create($id, $articleId, $quantite, $prixUnitaire, $totalLigne, $ordre, $description);
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
            $this->db->rollBack();
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
                'lignesDevis'  => $lignesDevis,
                'clients'     => $clients,
                'articles'    => $articlesList,
                'error'       => $error,
                'errorFields' => $errorFields,
            ]
        ];
    }

    public function detail(int $id): array {
        $this->checkAuth();
        if (!hasPermission('devis', 'view')) die('Accès refusé.');

        $devis = $this->devisModel->findById($id);
        if (!$devis) {
            return [
                'view' => 'error',
                'data' => ['message' => 'Devis non trouvé.']
            ];
        }

        $client = $this->clientModel->findById($devis['client_id']);
        $nomClient = $client ? $client['nom'] : 'Client inconnu';
        $reference = $devis['reference'] ?? '';
        $lignesDevis = $this->devisLigneModel->getByDevisId($id, true);

        return [
            'view' => 'devis/detail',
            'data' => [
                'devis'        => $devis,        // Toutes les infos sur le devis, dont 'reference'
                'lignesDevis'  => $lignesDevis,
                'client'       => $client,       // Toutes les infos sur le client
                'nom_client'   => $nomClient,
                'reference'    => $reference     // Référence en accès direct
            ]
        ];
    }

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

    public function deleteLigne(): void {
        $this->checkAuth();
        $ligneId = $_POST['ligne_id'] ?? null;
        if (!$ligneId || !is_numeric($ligneId)) {
            echo json_encode(['success' => false, 'message' => 'ID invalide']);
            exit();
        }
        try {
            $this->devisLigneModel->delete($ligneId);
            header('Content-Type: application/json');
            echo json_encode(['success' => true, 'message' => 'Ligne supprimée avec succès']);
            exit();
        } catch (Exception $e) {
            header('Content-Type: application/json');
            echo json_encode(['success' => false, 'message' => 'Erreur : ' . $e->getMessage()]);
            exit();
        }
    }

    // ------------------- RECHERCHE ET AJAX -------------------

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
        header("Access-Control-Allow-Origin: http://localhost");
        header("Access-Control-Allow-Methods: GET, POST, OPTIONS");
        header("Access-Control-Allow-Headers: X-Requested-With, Content-Type, Origin, Authorization, Accept, Client-Security-Token, Accept-Encoding");
        header('Content-Type: application/json');
        $articles = $this->articleModel->searchByName($term);
        echo json_encode($articles);
        exit();
    }

    public function getArticlePrice(int $clientId, int $articleId): array {
        header("Access-Control-Allow-Origin: http://localhost");
        header("Access-Control-Allow-Methods: GET, POST, OPTIONS");
        header("Access-Control-Allow-Headers: X-Requested-With, Content-Type, Origin, Authorization, Accept, Client-Security-Token, Accept-Encoding");
        header('Content-Type: application/json');
        $prix = $this->devisLigneModel->getLastPrixArticleClient($clientId, $articleId);
        $prixFormate = $prix !== null ? number_format($prix, 0, ',', ' ') : null;
        echo json_encode(['prix' => $prixFormate]);
        exit();
    }

    // ------------------- UTILITAIRES -------------------

    private function updateClientArticlePrix(int $clientId, int $articleId, float $prix): void {
        $this->devisLigneModel->updateOrCreateClientArticlePrix($clientId, $articleId, $prix);
    }

  private function generateNumeroDevis(): string {
        $annee = date('Y');
        // Supposons que getLastNumeroDevis($annee) retourne le dernier numero au format "DEV-2025-012"
        $dernierNumero = $this->devisModel->getLastNumeroDevis($annee);

        if ($dernierNumero) {
            // On extrait la dernière séquence numérique à la fin du numéro
            if (preg_match('/(\d+)$/', $dernierNumero, $m)) {
                $seq = (int)$m[1] + 1;
            } else {
                $seq = 1;
            }
        } else {
            $seq = 1;
        }

        return sprintf('DEV-%s-%03d', $annee, $seq);
    }


    private function getLastPrixArticleClient(int $clientId, int $articleId): ?float {
        return $this->devisLigneModel->getLastPrixArticleClient($clientId, $articleId);
    }

    private function checkAuth() {
        if (!isset($_SESSION['user_id'])) {
            header('Location: index.php?action=login');
            exit();
        }
    }

    /**
     * Reconstitue les lignes pour affichage après erreur validation (garde tout ce que l'utilisateur a tapé)
     */
    private function reconstituerLignesDevis(array $articles): array {
        $lignesDevis = [];
        foreach ($articles as $articleData) {
            $articleId    = $articleData['article_id'] ?? '';
            $quantite     = $articleData['quantite'] ?? '';
            $prixUnitaire = $articleData['prix_unitaire'] ?? '';
            $nomArt       = '';
            if ($articleId) {
                $article = $this->articleModel->findById($articleId);
                $nomArt = $article ? $article['nom_art'] : '';
            }
            $idLigne = $articleData['id'] ?? null;
            $total = 0;
            // Correction: nettoyer le prix pour le calcul du total
            $q = $this->normalize_price($quantite);
            $p = $this->normalize_price($prixUnitaire);
            if (is_numeric($q) && is_numeric($p)) $total = (float)$q * (float)$p;
            $lignesDevis[] = [
                'id'           => $idLigne,
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
>>>>>>> 4f8dbbb6b83eb9c6f755d57287033c7da885a3b1
}