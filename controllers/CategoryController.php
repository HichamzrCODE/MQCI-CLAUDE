<?php
require_once __DIR__ . '/../includes/permissions.php';
require_once __DIR__ . '/../models/Category.php';

class CategoryController {
    private $categoryModel;
    private $db;

    public function __construct(PDO $db) {
        $this->categoryModel = new Category($db);
        $this->db = $db;
    }

    // ================================================================
    // INDEX - LISTE DES CATÉGORIES
    // ================================================================

    public function index(array $getData = []): array {
        if (!hasPermission('categories', 'view')) {
            die("Accès refusé.");
        }

        $categories = $this->categoryModel->getAll();
        $totalCategories = $this->categoryModel->getTotalCount();

        // Ajouter le nombre d'articles par catégorie
        foreach ($categories as &$cat) {
            $cat['article_count'] = $this->categoryModel->getArticleCount($cat['id']);
        }

        return [
            'view' => 'categories/index',
            'data' => [
                'categories' => $categories,
                'totalCategories' => $totalCategories
            ]
        ];
    }

    // ================================================================
    // CRÉATION
    // ================================================================

    public function create(array $data): array {
        if (!hasPermission('categories', 'create')) {
            die("Accès refusé.");
        }

        $error = null;
        $parentCategories = $this->categoryModel->getParentCategories();

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $nom = trim($data['nom'] ?? '');
            $description = trim($data['description'] ?? '');
            $parent_id = $data['parent_id'] ?? null;

            if (empty($nom)) {
                $error = "Le nom de la catégorie est obligatoire.";
            }

            $userId = $_SESSION['user_id'] ?? null;
            if (!$userId) {
                $error = "Utilisateur non authentifié.";
            }

            if (!$error) {
                try {
                    $categoryData = [
                        'nom' => $nom,
                        'description' => $description,
                        'parent_id' => $parent_id ?: null
                    ];
                    $this->categoryModel->create($categoryData, $userId);
                    header('Location: index.php?action=categories');
                    exit();
                } catch (PDOException $e) {
                    $error = "Erreur lors de la création : " . $e->getMessage();
                    error_log($error);
                }
            }
        }

        return [
            'view' => 'categories/create',
            'data' => [
                'error' => $error,
                'parentCategories' => $parentCategories
            ]
        ];
    }

    // ================================================================
    // ÉDITION
    // ================================================================

    public function edit(int $id, array $data = []): array {
        if (!hasPermission('categories', 'edit')) {
            die("Accès refusé.");
        }

        $error = null;
        $category = $this->categoryModel->findById($id);
        $parentCategories = $this->categoryModel->getParentCategories();

        if (!$category) {
            return ['view' => 'error', 'data' => ['message' => "Catégorie non trouvée."]];
        }

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $nom = trim($data['nom'] ?? '');
            $description = trim($data['description'] ?? '');
            $parent_id = $data['parent_id'] ?? null;

            if (empty($nom)) {
                $error = "Le nom de la catégorie est obligatoire.";
            }

            // Vérifier qu'on ne crée pas une boucle (parent = lui-même)
            if ($parent_id == $id) {
                $error = "Une catégorie ne peut pas être sa propre parent.";
            }

            if (!$error) {
                try {
                    $userId = $_SESSION['user_id'] ?? null;
                    $categoryData = [
                        'nom' => $nom,
                        'description' => $description,
                        'parent_id' => $parent_id ?: null
                    ];
                    $this->categoryModel->update($id, $categoryData, $userId);
                    header('Location: index.php?action=categories');
                    exit();
                } catch (PDOException $e) {
                    $error = "Erreur lors de la mise à jour : " . $e->getMessage();
                    error_log($error);
                }
            }
        }

        return [
            'view' => 'categories/edit',
            'data' => [
                'category' => $category,
                'error' => $error,
                'parentCategories' => $parentCategories
            ]
        ];
    }

    // ================================================================
    // SUPPRESSION
    // ================================================================

    public function delete(int $id): void {
        if (!hasPermission('categories', 'delete')) {
            die("Accès refusé.");
        }

        $category = $this->categoryModel->findById($id);
        if (!$category) {
            echo "Catégorie non trouvée.";
            return;
        }

        if (!$this->categoryModel->delete($id)) {
            header('Location: index.php?action=categories&error=impossible_supprimer');
            exit();
        }

        header('Location: index.php?action=categories');
        exit();
    }

    // ================================================================
    // AFFICHAGE DÉTAILLÉ
    // ================================================================

    public function show(int $id): array {
        if (!hasPermission('categories', 'view')) {
            die("Accès refusé.");
        }

        $category = $this->categoryModel->findById($id);
        if (!$category) {
            return ['view' => 'error', 'data' => ['message' => "Catégorie non trouvée."]];
        }

        // Récupérer les articles de cette catégorie
        $stmt = $this->db->prepare(
            "SELECT a.id_articles, a.nom_art, a.sku, a.pr, a.prix_vente, a.statut
             FROM articles a
             WHERE a.categorie_id = ? AND a.deleted_at IS NULL
             ORDER BY a.nom_art ASC"
        );
        $stmt->execute([$id]);
        $articles = $stmt->fetchAll(PDO::FETCH_ASSOC);

        return [
            'view' => 'categories/show',
            'data' => [
                'category' => $category,
                'articles' => $articles,
                'article_count' => count($articles)
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
            $categories = $this->categoryModel->getAll();
        } else {
            $categories = $this->categoryModel->search($term);
        }

        foreach ($categories as &$cat) {
            $cat['article_count'] = $this->categoryModel->getArticleCount($cat['id']);
            $cat['editable'] = hasPermission('categories', 'edit');
            $cat['deletable'] = hasPermission('categories', 'delete');
        }

        echo json_encode($categories);
        exit();
    }
}