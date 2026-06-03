<?php

class Depot {
    private PDO $db;

    public function __construct(PDO $db) {
        $this->db = $db;
    }

    // ================================================================
    // LECTURE / LISTE
    // ================================================================

    public function getAll(): array {
        $stmt = $this->db->query(
            "SELECT d.*, 
                    COUNT(sp.id) AS total_articles
             FROM depots d
             LEFT JOIN stock_par_depot sp ON d.id = sp.depot_id
             GROUP BY d.id
             ORDER BY d.nom ASC"
        );
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function findById(int $id): ?array {
        $stmt = $this->db->prepare(
            "SELECT d.* FROM depots d WHERE d.id = ?"
        );
        $stmt->execute([$id]);
        return $stmt->fetch(PDO::FETCH_ASSOC) ?: null;
    }

    public function getTotalCount(): int {
        $stmt = $this->db->query("SELECT COUNT(*) FROM depots");
        return (int)$stmt->fetchColumn();
    }

    // ================================================================
    // CRÉATION
    // ================================================================

    public function create(array $data, int $userId): int {
        $stmt = $this->db->prepare(
            "INSERT INTO depots (nom, adresse, ville, telephone, email, responsable_id, statut)
             VALUES (:nom, :adresse, :ville, :telephone, :email, :responsable_id, :statut)"
        );
        $stmt->execute([
            ':nom'              => trim($data['nom']),
            ':adresse'          => trim($data['adresse'] ?? ''),
            ':ville'            => trim($data['ville'] ?? ''),
            ':telephone'        => trim($data['telephone'] ?? ''),
            ':email'            => trim($data['email'] ?? ''),
            ':responsable_id'   => ($data['responsable_id'] ?? '') !== '' ? (int)$data['responsable_id'] : null,
            ':statut'           => $data['statut'] ?? 'actif',
        ]);
        return (int)$this->db->lastInsertId();
    }

    // ================================================================
    // MODIFICATION
    // ================================================================

    public function update(int $id, array $data, int $userId): void {
        $stmt = $this->db->prepare(
            "UPDATE depots SET
                nom = :nom,
                adresse = :adresse,
                ville = :ville,
                telephone = :telephone,
                email = :email,
                responsable_id = :responsable_id,
                statut = :statut
             WHERE id = :id"
        );
        $stmt->execute([
            ':nom'              => trim($data['nom']),
            ':adresse'          => trim($data['adresse'] ?? ''),
            ':ville'            => trim($data['ville'] ?? ''),
            ':telephone'        => trim($data['telephone'] ?? ''),
            ':email'            => trim($data['email'] ?? ''),
            ':responsable_id'   => ($data['responsable_id'] ?? '') !== '' ? (int)$data['responsable_id'] : null,
            ':statut'           => $data['statut'] ?? 'actif',
            ':id'               => $id
        ]);
    }

    // ================================================================
    // SUPPRESSION
    // ================================================================

    public function delete(int $id): bool {
        // Vérifier s'il y a du stock lié
        $stmt = $this->db->prepare("SELECT COUNT(*) FROM stock_par_depot WHERE depot_id = ?");
        $stmt->execute([$id]);
        if ((int)$stmt->fetchColumn() > 0) {
            return false; // Ne peut pas supprimer si stock lié
        }

        $stmt = $this->db->prepare("DELETE FROM depots WHERE id = ?");
        return $stmt->execute([$id]);
    }

    // ================================================================
    // RECHERCHE
    // ================================================================

    public function search(string $term, int $limit = 50): array {
        $like = '%' . $term . '%';
        $stmt = $this->db->prepare(
            "SELECT d.*, 
                    COUNT(sp.id) AS total_articles
             FROM depots d
             LEFT JOIN stock_par_depot sp ON d.id = sp.depot_id
             WHERE d.nom LIKE ? OR d.adresse LIKE ? OR d.ville LIKE ?
             GROUP BY d.id
             ORDER BY d.nom ASC
             LIMIT ?"
        );
        $stmt->execute([$like, $like, $like, $limit]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    // Récupérer le stock d'un article dans ce dépôt
    public function getStockParDepot(int $depotId, int $articleId): ?array {
        $stmt = $this->db->prepare(
            "SELECT sp.*, a.nom_art, a.sku
             FROM stock_par_depot sp
             JOIN articles a ON sp.article_id = a.id_articles
             WHERE sp.depot_id = ? AND sp.article_id = ?"
        );
        $stmt->execute([$depotId, $articleId]);
        return $stmt->fetch(PDO::FETCH_ASSOC) ?: null;
    }

    // Récupérer tous les stocks d'un dépôt
    public function getAllStocksInDepot(int $depotId): array {
        $stmt = $this->db->prepare(
            "SELECT sp.*, a.nom_art, a.sku, a.pr, a.prix_vente
             FROM stock_par_depot sp
             JOIN articles a ON sp.article_id = a.id_articles
             WHERE sp.depot_id = ?
             ORDER BY a.nom_art ASC"
        );
        $stmt->execute([$depotId]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    // Ajouter/modifier du stock dans un dépôt
    public function upsertStock(int $depotId, int $articleId, int $quantite, int $userId): void {
        // Vérifier si existe
        $stmt = $this->db->prepare(
            "SELECT id FROM stock_par_depot WHERE depot_id = ? AND article_id = ?"
        );
        $stmt->execute([$depotId, $articleId]);
        $exists = $stmt->fetch();

        if ($exists) {
            // Mise à jour
            $stmt = $this->db->prepare(
                "UPDATE stock_par_depot SET quantite = ? WHERE depot_id = ? AND article_id = ?"
            );
            $stmt->execute([$quantite, $depotId, $articleId]);
        } else {
            // Création
            $stmt = $this->db->prepare(
                "INSERT INTO stock_par_depot (depot_id, article_id, quantite) VALUES (?, ?, ?)"
            );
            $stmt->execute([$depotId, $articleId, $quantite]);
        }
    }
}