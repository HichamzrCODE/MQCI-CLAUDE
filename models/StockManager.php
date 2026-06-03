<?php

class StockManager {

    private PDO $db;

    public function __construct(PDO $db) {
        $this->db = $db;
    }

    /**
     * Retourne le stock détaillé par dépôt pour un article.
     */
    public function getStockByArticle(int $articleId): array {
        $stmt = $this->db->prepare(
            "SELECT s.*, d.nom AS depot_nom, d.ville AS depot_ville, d.statut AS depot_statut
             FROM stock_par_depot s
             INNER JOIN depots d ON s.depot_id = d.id
             WHERE s.article_id = ?
             ORDER BY d.nom ASC"
        );
        $stmt->execute([$articleId]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Retourne la quantité totale d'un article (tous dépôts).
     */
    public function getTotalQuantite(int $articleId): int {
        $stmt = $this->db->prepare(
            "SELECT COALESCE(SUM(quantite), 0) FROM stock_par_depot WHERE article_id = ?"
        );
        $stmt->execute([$articleId]);
        return (int)$stmt->fetchColumn();
    }

    /**
     * Met à jour ou insère la quantité dans un dépôt.
     */
    public function upsertStock(int $articleId, int $depotId, int $quantite, ?string $emplacement = null): void {
        $stmt = $this->db->prepare(
            "INSERT INTO stock_par_depot (article_id, depot_id, quantite, emplacement)
             VALUES (?, ?, ?, ?)
             ON DUPLICATE KEY UPDATE quantite = ?, emplacement = ?"
        );
        $stmt->execute([$articleId, $depotId, $quantite, $emplacement, $quantite, $emplacement]);

        // Recalculer la quantité totale dans articles
        $this->updateTotalQuantite($articleId);
    }

    /**
     * Recalcule et met à jour la quantite_totale de l'article.
     */
    public function updateTotalQuantite(int $articleId): void {
        $stmt = $this->db->prepare(
            "UPDATE articles
             SET quantite_totale = (
                 SELECT COALESCE(SUM(quantite), 0)
                 FROM stock_par_depot
                 WHERE article_id = ?
             )
             WHERE id_articles = ?"
        );
        $stmt->execute([$articleId, $articleId]);
    }

    /**
     * Retourne tous les dépôts actifs.
     */
    public function getDepotsActifs(): array {
        $stmt = $this->db->query(
            "SELECT * FROM depots WHERE statut = 'actif' ORDER BY nom ASC"
        );
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Retourne tous les dépôts.
     */
    public function getAllDepots(): array {
        $stmt = $this->db->query(
            "SELECT d.*, u.username AS responsable_nom
             FROM depots d
             LEFT JOIN users u ON d.responsable_id = u.id_users
             ORDER BY d.nom ASC"
        );
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Crée un nouveau dépôt.
     */
    public function createDepot(string $nom, ?string $adresse, ?string $ville, ?int $responsableId, ?string $telephone, ?string $email): int {
        $stmt = $this->db->prepare(
            "INSERT INTO depots (nom, adresse, ville, responsable_id, telephone, email)
             VALUES (?, ?, ?, ?, ?, ?)"
        );
        $stmt->execute([$nom, $adresse, $ville, $responsableId, $telephone, $email]);
        return (int)$this->db->lastInsertId();
    }

    /**
     * Modifie un dépôt.
     */
    public function updateDepot(int $id, string $nom, ?string $adresse, ?string $ville, ?int $responsableId, ?string $telephone, ?string $email, string $statut): void {
        $stmt = $this->db->prepare(
            "UPDATE depots SET nom=?, adresse=?, ville=?, responsable_id=?, telephone=?, email=?, statut=? WHERE id=?"
        );
        $stmt->execute([$nom, $adresse, $ville, $responsableId, $telephone, $email, $statut, $id]);
    }

    /**
     * Trouve un dépôt par ID.
     */
    public function findDepotById(int $id): ?array {
        $stmt = $this->db->prepare("SELECT * FROM depots WHERE id = ?");
        $stmt->execute([$id]);
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        return $row ?: null;
    }

    /**
     * Articles dont le stock est en dessous du stock minimal.
     */
    public function getAlertesStockMinimal(): array {
        $stmt = $this->db->query(
            "SELECT a.id_articles, a.nom_art, a.sku, a.stock_minimal, a.quantite_totale,
                    f.nom_fournisseurs
             FROM articles a
             INNER JOIN fournisseurs f ON a.fournisseur_id = f.id_fournisseurs
             WHERE a.deleted_at IS NULL
               AND a.statut = 'actif'
               AND a.stock_minimal > 0
               AND a.quantite_totale < a.stock_minimal
             ORDER BY a.nom_art ASC"
        );
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
}
