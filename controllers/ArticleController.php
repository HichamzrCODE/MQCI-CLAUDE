<?php
<<<<<<< HEAD
require_once __DIR__ . '/../includes/permissions.php'; // Chemin universel et portable
require_once __DIR__ . '/../models/article.php';

class ArticleController {
    private $articleModel;
    private $db;

    public function __construct(PDO $db) {
        $this->articleModel = new article($db);
=======
require_once __DIR__ . '/../includes/permissions.php';
require_once __DIR__ . '/../includes/Validator.php';
require_once __DIR__ . '/../includes/AuditLogger.php';
require_once __DIR__ . '/../includes/CsrfMiddleware.php';
require_once __DIR__ . '/../models/article.php';
require_once __DIR__ . '/../models/StockManager.php';

class ArticleController {
    private Article $articleModel;
    private StockManager $stockManager;
    private AuditLogger $auditLogger;
    private PDO $db;

    public function __construct(PDO $db) {
        $this->articleModel = new Article($db);
        $this->stockManager = new StockManager($db);
        $this->auditLogger  = new AuditLogger($db);
>>>>>>> 4f8dbbb6b83eb9c6f755d57287033c7da885a3b1
        $this->db = $db;
    }

    public function index(array $getData = []): array {
        if (!hasPermission('articles', 'view')) {
            die("Accès refusé.");
        }
<<<<<<< HEAD

        // On affiche les 50 premiers au chargement initial pour éviter de saturer le navigateur
        $articles = $this->articleModel->getLimited(50);
=======
        $articles      = $this->articleModel->getLimited(50);
>>>>>>> 4f8dbbb6b83eb9c6f755d57287033c7da885a3b1
        $totalArticles = $this->articleModel->getTotalCount();

        return [
            'view' => 'articles/index',
            'data' => [
<<<<<<< HEAD
                'articles' => $articles,
                'totalArticles' => $totalArticles
            ]
        ];
    }

    // Recherche AJAX serveur (rapide même avec 7000+ articles)
    public function search(array $data): void {
        $term = trim($data['term'] ?? '');
        header('Content-Type: application/json');
        if ($term === '') {
            $articles = $this->articleModel->getLimited(50);
        } else {
            $articles = $this->articleModel->searchFull($term, 50);
        }
        foreach ($articles as &$article) {
            $article['editable'] = hasPermission('articles', 'edit');
            $article['deletable'] = hasPermission('articles', 'delete');
        }
=======
                'articles'      => $articles,
                'totalArticles' => $totalArticles,
            ],
        ];
    }

    // ✅ CORRIGÉ : Utiliser 'pr' au lieu de 'prix_revient_display'
    public function search(array $data): void {
        $term = trim($data['term'] ?? '');
        header('Content-Type: application/json');
        $articles = $term === ''
            ? $this->articleModel->getLimited(50)
            : $this->articleModel->searchFull($term, 50);

        foreach ($articles as &$article) {
            $article['editable']  = hasPermission('articles', 'edit');
            $article['deletable'] = hasPermission('articles', 'delete');
            $article['viewable']  = hasPermission('articles', 'view');
        }
        unset($article);
>>>>>>> 4f8dbbb6b83eb9c6f755d57287033c7da885a3b1
        echo json_encode($articles);
        exit();
    }

    public function create(array $data): array {
        if (!hasPermission('articles', 'create')) {
            die("Accès refusé.");
        }

<<<<<<< HEAD
        $error = null;
        $fournisseurs = $this->getFournisseurs();

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $nom_art = trim($data['nom_art'] ?? '');
            $pr = floatval($data['pr'] ?? 0);
            $fournisseur_id = trim($data['fournisseur_id'] ?? '');

            if (empty($nom_art)) {
                $error = "Le nom de l'article est obligatoire.";
            }
            if ($pr <= 0) {
                $error = "Le prix de revient doit être supérieur à zéro.";
            }
            if (empty($fournisseur_id)) {
                $error = "Veuillez sélectionner un fournisseur.";
            }
            $userId = $_SESSION['user_id'] ?? null;
            if (!$userId) {
                $error = "Utilisateur non authentifié.";
            }
            if (!$error) {
                try {
                    $articleId = $this->articleModel->create($nom_art, $pr, $fournisseur_id, $userId);
                    header('Location: index.php?action=articles');
                    exit();
                } catch (PDOException $e) {
                    $error = "Erreur lors de la création de l'article : " . $e->getMessage();
=======
        $error      = null;
        $fournisseurs = $this->getFournisseurs();
        $categories   = $this->articleModel->getAllCategories();
        $depots       = $this->stockManager->getDepotsActifs();

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            CsrfMiddleware::verify();

            $v = new Validator($data);
            $v->required('nom_art', "Nom de l'article")
              ->maxLength('nom_art', 255, "Nom de l'article")
              ->required('pr', 'Prix de revient')  // ✅ CHANGÉ : pr au lieu de prix_revient
              ->positiveNumber('pr', 'Prix de revient')  // ✅ CHANGÉ
              ->nonNegativeNumber('prix_vente', 'Prix de vente')
              ->required('fournisseur_id', 'Fournisseur')
              ->nonNegativeNumber('stock_minimal', 'Stock minimal')
              ->nonNegativeNumber('stock_maximal', 'Stock maximal')
              ->nonNegativeNumber('poids_kg', 'Poids')
              ->inList('statut', ['actif', 'inactif', 'discontinued'], 'Statut');

            $userId = $_SESSION['user_id'] ?? null;
            if (!$userId) {
                $error = "Utilisateur non authentifié.";
            } elseif ($v->fails()) {
                $error = $v->getFirstError();
            } else {
                try {
                    $articleId = $this->articleModel->create($data, (int)$userId);

                    // Gestion image
                    if (!empty($_FILES['image']['name'])) {
                        $imagePath = $this->handleImageUpload($_FILES['image'], $articleId);
                        if ($imagePath) {
                            $this->articleModel->updateImage($articleId, $imagePath, (int)$userId);
                        }
                    }

                    // Stock initial par dépôt
                    if (!empty($data['depots']) && is_array($data['depots'])) {
                        foreach ($data['depots'] as $depotId => $qtInfo) {
                            $qt = (int)($qtInfo['quantite'] ?? 0);
                            $em = trim($qtInfo['emplacement'] ?? '');
                            if ($qt > 0 || $em !== '') {
                                $this->stockManager->upsertStock($articleId, (int)$depotId, $qt, $em ?: null);
                            }
                        }
                    }

                    $this->auditLogger->log('articles', $articleId, 'CREATE', (int)$userId, null, $data);
                    header('Location: index.php?action=articles');
                    exit();
                } catch (PDOException $e) {
                    $error = "Erreur lors de la création : " . $e->getMessage();
>>>>>>> 4f8dbbb6b83eb9c6f755d57287033c7da885a3b1
                    error_log($error);
                }
            }
        }
<<<<<<< HEAD
        return ['view' => 'articles/create', 'data' => ['error' => $error, 'fournisseurs' => $fournisseurs]];
    }

    private function getFournisseurs(): array {
        try {
            $stmt = $this->db->query("SELECT id_fournisseurs, nom_fournisseurs FROM fournisseurs");
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            error_log("Erreur lors de la récupération des fournisseurs : " . $e->getMessage());
            return [];
        }
    }

    public function delete(int $id): void {
        if (!hasPermission('articles', 'delete')) {
            die("Accès refusé.");
        }

        $articles = $this->articleModel->findById($id);
        if (!$articles) {
            echo "Article non trouvé.";
            return;
        }

        $this->articleModel->delete($id);
        header('Location: index.php?action=articles');
        exit();
=======

        return [
            'view' => 'articles/create',
            'data' => [
                'error'        => $error,
                'fournisseurs' => $fournisseurs,
                'categories'   => $categories,
                'depots'       => $depots,
                'csrf_field'   => CsrfMiddleware::field(),
            ],
        ];
>>>>>>> 4f8dbbb6b83eb9c6f755d57287033c7da885a3b1
    }

    public function edit(int $id, array $data = []): array {
        if (!hasPermission('articles', 'edit')) {
            die("Accès refusé.");
        }

<<<<<<< HEAD
        $error = null;
        $article = $this->articleModel->findById($id);
        $fournisseurs = $this->getFournisseurs();
=======
        $error      = null;
        $article    = $this->articleModel->findById($id);
        $fournisseurs = $this->getFournisseurs();
        $categories   = $this->articleModel->getAllCategories();
        $stockDepots  = $this->stockManager->getStockByArticle($id);
        $depots       = $this->stockManager->getDepotsActifs();
>>>>>>> 4f8dbbb6b83eb9c6f755d57287033c7da885a3b1

        if (!$article) {
            return ['view' => 'error', 'data' => ['message' => "Article non trouvé."]];
        }

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
<<<<<<< HEAD
            $nom_art = trim($data['nom_art'] ?? '');
            $pr = floatval($data['pr'] ?? 0);
            $fournisseur_id = trim($data['fournisseur_id'] ?? '');

            if (empty($nom_art)) {
                $error = "Le nom de l'article est obligatoire.";
            }
            if ($pr <= 0) {
                $error = "Le prix de revient doit être supérieur à zéro.";
            }
            if (empty($fournisseur_id)) {
                $error = "Veuillez sélectionner un fournisseur.";
            }
            if (!$error) {
                try {
                    $this->articleModel->update($id, $nom_art, $pr, $fournisseur_id);
                    header('Location: index.php?action=articles');
                    exit();
                } catch (PDOException $e) {
                    $error = "Erreur lors de la mise à jour de l'article : " . $e->getMessage();
=======
            CsrfMiddleware::verify();

            $v = new Validator($data);
            $v->required('nom_art', "Nom de l'article")
              ->maxLength('nom_art', 255, "Nom de l'article")
              ->required('pr', 'Prix de revient')  // ✅ CHANGÉ
              ->positiveNumber('pr', 'Prix de revient')  // ✅ CHANGÉ
              ->nonNegativeNumber('prix_vente', 'Prix de vente')
              ->required('fournisseur_id', 'Fournisseur')
              ->nonNegativeNumber('stock_minimal', 'Stock minimal')
              ->nonNegativeNumber('stock_maximal', 'Stock maximal')
              ->nonNegativeNumber('poids_kg', 'Poids')
              ->inList('statut', ['actif', 'inactif', 'discontinued'], 'Statut');

            $userId = $_SESSION['user_id'] ?? null;
            if (!$userId) {
                $error = "Utilisateur non authentifié.";
            } elseif ($v->fails()) {
                $error = $v->getFirstError();
            } else {
                try {
                    // ✅ CHANGÉ : Utiliser 'pr' au lieu de 'prix_revient'
                    $oldPr = (float)($article['pr'] ?? 0);
                    $oldPv = (float)($article['prix_vente'] ?? 0);
                    $newPr = (float)$data['pr'];  // ✅ CHANGÉ
                    $newPv = (float)($data['prix_vente'] ?? 0);

                    $this->articleModel->update($id, $data, (int)$userId);

                    // Historique prix si changement
                    if ($oldPr !== $newPr || $oldPv !== $newPv) {
                        $raison = trim($data['raison_changement_prix'] ?? '');
                        $this->articleModel->addPrixHistorique($id, $oldPr, $newPr, $oldPv, $newPv, (int)$userId, $raison);
                    }

                    // Gestion image
                    if (!empty($_FILES['image']['name'])) {
                        $imagePath = $this->handleImageUpload($_FILES['image'], $id);
                        if ($imagePath) {
                            $this->articleModel->updateImage($id, $imagePath, (int)$userId);
                        }
                    }

                    // Stock par dépôt
                    if (!empty($data['depots']) && is_array($data['depots'])) {
                        foreach ($data['depots'] as $depotId => $qtInfo) {
                            $qt = (int)($qtInfo['quantite'] ?? 0);
                            $em = trim($qtInfo['emplacement'] ?? '');
                            $this->stockManager->upsertStock($id, (int)$depotId, $qt, $em ?: null);
                        }
                    }

                    $this->auditLogger->log('articles', $id, 'UPDATE', (int)$userId, $article, $data);
                    header('Location: index.php?action=articles');
                    exit();
                } catch (PDOException $e) {
                    $error = "Erreur lors de la mise à jour : " . $e->getMessage();
>>>>>>> 4f8dbbb6b83eb9c6f755d57287033c7da885a3b1
                    error_log($error);
                }
            }
        }

<<<<<<< HEAD
        return ['view' => 'articles/edit', 'data' => ['article' => $article, 'error' => $error, 'fournisseurs' => $fournisseurs]];
    }
    
    public function update(int $id, string $nom_art, float $pr, string $fournisseur_id): bool {
        $sql = "UPDATE articles SET nom_art = :nom_art, pr = :pr, fournisseur_id = :fournisseur_id WHERE id_articles = :id";
        $stmt = $this->db->prepare($sql);
        $stmt->bindValue(':id', $id, PDO::PARAM_INT);
        $stmt->bindValue(':nom_art', $nom_art, PDO::PARAM_STR);
        $stmt->bindValue(':pr', $pr, PDO::PARAM_STR);
        $stmt->bindValue(':fournisseur_id', $fournisseur_id, PDO::PARAM_STR);

        try {
            return $stmt->execute();
        } catch (PDOException $e) {
            error_log("Erreur lors de la mise à jour de l'article : " . $e->getMessage());
            return false;
        }
=======
        return [
            'view' => 'articles/edit',
            'data' => [
                'article'      => $article,
                'error'        => $error,
                'fournisseurs' => $fournisseurs,
                'categories'   => $categories,
                'stockDepots'  => $stockDepots,
                'depots'       => $depots,
                'csrf_field'   => CsrfMiddleware::field(),
            ],
        ];
>>>>>>> 4f8dbbb6b83eb9c6f755d57287033c7da885a3b1
    }

    public function show(int $id): array {
        if (!hasPermission('articles', 'view')) {
            die("Accès refusé.");
        }
<<<<<<< HEAD

        $articles = $this->articleModel->findById($id);
        if (!$articles) {
            return ['view' => 'error', 'data' => ['message' => "Article non trouvé."]];
        }
        return ['view' => 'articles/show', 'data' => ['article' => $articles]];
=======
        $article = $this->articleModel->findById($id);
        if (!$article) {
            return ['view' => 'error', 'data' => ['message' => "Article non trouvé."]];
        }

        $stockDepots    = $this->stockManager->getStockByArticle($id);
        $prixHistorique = $this->articleModel->getPrixHistorique($id);

        $userId = $_SESSION['user_id'] ?? null;
        if ($userId) {
            $this->auditLogger->log('articles', $id, 'VIEW', (int)$userId);
        }

        return [
            'view' => 'articles/show',
            'data' => [
                'article'        => $article,
                'stockDepots'    => $stockDepots,
                'prixHistorique' => $prixHistorique,
            ],
        ];
    }

    public function delete(int $id): void {
        if (!hasPermission('articles', 'delete')) {
            die("Accès refusé.");
        }

        $article = $this->articleModel->findById($id);
        if (!$article) {
            echo "Article non trouvé.";
            return;
        }

        $userId = (int)($_SESSION['user_id'] ?? 0);
        $this->articleModel->delete($id);
        $this->auditLogger->log('articles', $id, 'DELETE', $userId, $article, null);
        header('Location: index.php?action=articles');
        exit();
    }

    public function historiquePrix(int $id): void {
        if (!hasPermission('articles', 'view')) {
            die("Accès refusé.");
        }
        header('Content-Type: application/json');
        $historique = $this->articleModel->getPrixHistorique($id);
        echo json_encode($historique);
        exit();
    }

    public function stockParDepot(int $id): void {
        if (!hasPermission('articles', 'view')) {
            die("Accès refusé.");
        }
        header('Content-Type: application/json');
        $stock = $this->stockManager->getStockByArticle($id);
        echo json_encode($stock);
        exit();
    }

    public function export(): void {
        if (!hasPermission('articles', 'view')) {
            die("Accès refusé.");
        }
        $articles = $this->articleModel->getAllForExport();
        header('Content-Type: text/csv; charset=utf-8');
        header('Content-Disposition: attachment; filename="articles_' . date('Y-m-d') . '.csv"');
        $out = fopen('php://output', 'w');
        fputs($out, "\xEF\xBB\xBF");
        // ✅ CHANGÉ : 'pr' au lieu de 'Prix Revient'
        fputcsv($out, ['ID', 'SKU', 'Nom', 'PR', 'Prix Vente', 'Quantité', 'Statut', 'Unité', 'Stock min', 'Stock max', 'Poids (kg)', 'Couleur', 'Notes', 'Fournisseur'], ';');
        foreach ($articles as $a) {
            fputcsv($out, [
                $a['id_articles'],
                $a['sku'] ?? '',
                $a['nom_art'],
                $a['pr'] ?? '',  // ✅ CHANGÉ : pr au lieu de prix_revient
                $a['prix_vente'] ?? '',
                $a['quantite_totale'] ?? 0,
                $a['statut'] ?? '',
                $a['unite_mesure'] ?? '',
                $a['stock_minimal'] ?? 0,
                $a['stock_maximal'] ?? 0,
                $a['poids_kg'] ?? '',
                $a['couleur'] ?? '',
                $a['notes_internes'] ?? '',
                $a['nom_fournisseurs'],
            ], ';');
        }
        fclose($out);
        exit();
    }

    public function import(array $data): array {
        if (!hasPermission('articles', 'create')) {
            die("Accès refusé.");
        }
        $error   = null;
        $success = null;

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            CsrfMiddleware::verify();
            $userId = (int)($_SESSION['user_id'] ?? 0);

            if (empty($_FILES['csv_file']['name'])) {
                $error = "Veuillez sélectionner un fichier CSV.";
            } else {
                $file = $_FILES['csv_file'];
                $ext  = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
                if ($ext !== 'csv') {
                    $error = "Seuls les fichiers .csv sont acceptés.";
                } elseif ($file['size'] > 5 * 1024 * 1024) {
                    $error = "Le fichier ne doit pas dépasser 5 Mo.";
                } else {
                    $count = 0;
                    $errors = [];
                    if (($handle = fopen($file['tmp_name'], 'r')) !== false) {
                        fgetcsv($handle, 0, ';');
                        while (($row = fgetcsv($handle, 0, ';')) !== false) {
                            if (count($row) < 4) continue;
                            $nomFournisseur = trim($row[13] ?? '');
                            $fournisseurId  = $this->resolveFournisseurId($nomFournisseur);
                            if (!$fournisseurId) {
                                $errors[] = "Ligne ignorée (" . trim($row[2] ?? '') . ") : fournisseur introuvable « {$nomFournisseur} ».";
                                continue;
                            }
                            // ✅ CHANGÉ : 'pr' au lieu de 'prix_revient'
                            $rowData = [
                                'nom_art'        => trim($row[2] ?? ''),
                                'sku'            => trim($row[1] ?? ''),
                                'pr'             => (float)str_replace(',', '.', $row[3] ?? 0),  // ✅ CHANGÉ
                                'prix_vente'     => (float)str_replace(',', '.', $row[4] ?? 0),
                                'fournisseur_id' => $fournisseurId,
                                'statut'         => $row[6] ?? 'actif',
                                'unite_mesure'   => trim($row[7] ?? 'Piece'),
                                'stock_minimal'  => (int)($row[8] ?? 0),
                                'stock_maximal'  => (int)($row[9] ?? 0),
                            ];
                            if (empty($rowData['nom_art'])) continue;
                            try {
                                $this->articleModel->create($rowData, $userId);
                                $count++;
                            } catch (PDOException $e) {
                                $errors[] = "Ligne ignorée ({$rowData['nom_art']}) : " . $e->getMessage();
                            }
                        }
                        fclose($handle);
                    }
                    $success = "{$count} article(s) importé(s) avec succès.";
                    if ($errors) {
                        $success .= " " . count($errors) . " erreur(s) : " . implode('; ', array_slice($errors, 0, 3));
                    }
                }
            }
        }

        return [
            'view' => 'articles/import',
            'data' => [
                'error'      => $error,
                'success'    => $success,
                'csrf_field' => CsrfMiddleware::field(),
            ],
        ];
    }

    private function resolveFournisseurId(string $nom): ?int {
        if ($nom === '') return null;
        try {
            $stmt = $this->db->prepare(
                "SELECT id_fournisseurs FROM fournisseurs WHERE nom_fournisseurs = ? LIMIT 1"
            );
            $stmt->execute([$nom]);
            $row = $stmt->fetch(PDO::FETCH_ASSOC);
            return $row ? (int)$row['id_fournisseurs'] : null;
        } catch (PDOException $e) {
            return null;
        }
    }

    private function getFournisseurs(): array {
        try {
            $stmt = $this->db->query("SELECT id_fournisseurs, nom_fournisseurs FROM fournisseurs ORDER BY nom_fournisseurs ASC");
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            error_log("Erreur récupération fournisseurs : " . $e->getMessage());
            return [];
        }
    }

    private function handleImageUpload(array $file, int $articleId): ?string {
        $allowedTypes = ['image/jpeg', 'image/png', 'image/gif', 'image/webp'];
        $maxSize      = 2 * 1024 * 1024;

        if ($file['error'] !== UPLOAD_ERR_OK) {
            return null;
        }
        if (!in_array($file['type'], $allowedTypes, true)) {
            return null;
        }
        if ($file['size'] > $maxSize) {
            return null;
        }

        $finfo = finfo_open(FILEINFO_MIME_TYPE);
        $mime  = finfo_file($finfo, $file['tmp_name']);
        finfo_close($finfo);
        if (!in_array($mime, $allowedTypes, true)) {
            return null;
        }

        $uploadDir = __DIR__ . '/../public/img/articles/';
        if (!is_dir($uploadDir)) {
            mkdir($uploadDir, 0755, true);
        }

        $ext      = pathinfo($file['name'], PATHINFO_EXTENSION);
        $filename = 'article_' . $articleId . '_' . time() . '.' . $ext;
        $dest     = $uploadDir . $filename;

        if (!move_uploaded_file($file['tmp_name'], $dest)) {
            return null;
        }

        return 'img/articles/' . $filename;
>>>>>>> 4f8dbbb6b83eb9c6f755d57287033c7da885a3b1
    }
}