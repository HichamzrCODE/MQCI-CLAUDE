<?php

class Category {
    private PDO $db;

    public function __construct(PDO $db) {
        $this->db = $db;
    }

    // ================================================================
    // LECTURE / LISTE
    // ================================================================

    public function getAll(): array {
        $stmt = $this->db->query(
            "SELECT c.*, 
                    COALESCE(p.nom, 'Aucun') AS parent_nom
             FROM categories c
             LEFT JOIN categories p ON c.parent_id = p.id
             ORDER BY c.nom ASC"
        );
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function findById(int $id): ?array {
        $stmt = $this->db->prepare(
            "SELECT c.*,
                    p.nom AS parent_nom
             FROM categories c
             LEFT JOIN categories p ON c.parent_id = p.id
             WHERE c.id = ?"
        );
        $stmt->execute([$id]);
        return $stmt->fetch(PDO::FETCH_ASSOC) ?: null;
    }

    public function getTotalCount(): int {
        $stmt = $this->db->query("SELECT COUNT(*) FROM categories");
        return (int)$stmt->fetchColumn();
    }

    // Catégories parents uniquement (pour dropdown parent_id)
    public function getParentCategories(): array {
        $stmt = $this->db->query(
            "SELECT id, nom FROM categories WHERE parent_id IS NULL ORDER BY nom ASC"
        );
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    // ================================================================
    // CRÉATION
    // ================================================================

    public function create(array $data, int $userId): int {
        $stmt = $this->db->prepare(
            "INSERT INTO categories (nom, description, parent_id, created_by)
             VALUES (:nom, :description, :parent_id, :created_by)"
        );
        $stmt->execute([
            ':nom'          => trim($data['nom']),
            ':description'  => trim($data['description'] ?? ''),
            ':parent_id'    => $data['parent_id'] ?? null,
            ':created_by'   => $userId,
        ]);
        return (int)$this->db->lastInsertId();
    }

    // ================================================================
    // MODIFICATION
    // ================================================================

    public function update(int $id, array $data, int $userId): void {
        $stmt = $this->db->prepare(
            "UPDATE categories SET
                nom = :nom,
                description = :description,
                parent_id = :parent_id,
                updated_by = :updated_by
             WHERE id = ?"
        );
        $stmt->execute([
            ':nom'          => trim($data['nom']),
            ':description'  => trim($data['description'] ?? ''),
            ':parent_id'    => $data['parent_id'] ?? null,
            ':updated_by'   => $userId,
            $id
        ]);
    }

    // ================================================================
    // SUPPRESSION
    // ================================================================

    public function delete(int $id): bool {
        // Vérifier s'il y a des articles liés
        $stmt = $this->db->prepare("SELECT COUNT(*) FROM articles WHERE categorie_id = ?");
        $stmt->execute([$id]);
        if ((int)$stmt->fetchColumn() > 0) {
            return false; // Ne peut pas supprimer si articles liés
        }

        // Vérifier s'il y a des sous-catégories
        $stmt = $this->db->prepare("SELECT COUNT(*) FROM categories WHERE parent_id = ?");
        $stmt->execute([$id]);
        if ((int)$stmt->fetchColumn() > 0) {
            return false; // Ne peut pas supprimer si sous-catégories
        }

        $stmt = $this->db->prepare("DELETE FROM categories WHERE id = ?");
        return $stmt->execute([$id]);
    }

    // ================================================================
    // RECHERCHE
    // ================================================================

    public function search(string $term, int $limit = 50): array {
        $like = '%' . $term . '%';
        $stmt = $this->db->prepare(
            "SELECT c.*, 
                    COALESCE(p.nom, 'Aucun') AS parent_nom
             FROM categories c
             LEFT JOIN categories p ON c.parent_id = p.id
             WHERE c.nom LIKE ? OR c.description LIKE ?
             ORDER BY c.nom ASC
             LIMIT ?"
        );
        $stmt->execute([$like, $like, $limit]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    // Compter les articles par catégorie
    public function getArticleCount(int $categoryId): int {
        $stmt = $this->db->prepare("SELECT COUNT(*) FROM articles WHERE categorie_id = ?");
        $stmt->execute([$categoryId]);
        return (int)$stmt->fetchColumn();
    }
}