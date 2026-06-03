<?php

class SeuilStock {

    private PDO $db;

    public function __construct(PDO $db) {
        $this->db = $db;
    }

    /**
     * Crée ou met à jour un seuil pour un article/dépôt.
     */
    public function upsert(int $articleId, int $depotId, int $stockMinimal, ?int $stockMaximal): void {
        $stmt = $this->db->prepare(
            "INSERT INTO seuils_stocks (article_id, depot_id, stock_minimal, stock_maximal)
             VALUES (?, ?, ?, ?)
             ON DUPLICATE KEY UPDATE stock_minimal = ?, stock_maximal = ?"
        );
        $stmt->execute([
            $articleId,
            $depotId,
            $stockMinimal,
            $stockMaximal,
            $stockMinimal,
            $stockMaximal,
        ]);
    }

    /**
     * Retourne tous les seuils avec infos articles et dépôts.
     */
    public function getAll(): array {
        $stmt = $this->db->query(
            "SELECT s.*,
                    a.nom_art, a.sku,
                    d.nom AS depot_nom
             FROM seuils_stocks s
             INNER JOIN articles a ON s.article_id = a.id_articles
             INNER JOIN depots   d ON s.depot_id   = d.id
             ORDER BY a.nom_art ASC, d.nom ASC"
        );
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Retourne le seuil pour un article/dépôt donné.
     */
    public function findByArticleDepot(int $articleId, int $depotId): ?array {
        $stmt = $this->db->prepare(
            "SELECT * FROM seuils_stocks WHERE article_id = ? AND depot_id = ?"
        );
        $stmt->execute([$articleId, $depotId]);
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        return $row ?: null;
    }

    /**
     * Articles en alerte (stock < seuil minimal) par dépôt.
     */
    public function getAlertes(): array {
        $stmt = $this->db->query(
            "SELECT s.stock_minimal, s.stock_maximal,
                    a.id_articles, a.nom_art, a.sku,
                    d.id AS depot_id, d.nom AS depot_nom,
                    COALESCE(spd.quantite, 0) AS quantite_actuelle,
                    (s.stock_minimal - COALESCE(spd.quantite, 0)) AS manquant
             FROM seuils_stocks s
             INNER JOIN articles       a   ON s.article_id = a.id_articles
             INNER JOIN depots         d   ON s.depot_id   = d.id
             LEFT  JOIN stock_par_depot spd ON spd.article_id = s.article_id AND spd.depot_id = s.depot_id
             WHERE s.stock_minimal > 0
               AND COALESCE(spd.quantite, 0) < s.stock_minimal
               AND a.deleted_at IS NULL
             ORDER BY manquant DESC, a.nom_art ASC"
        );
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Supprime un seuil par ID.
     */
    public function delete(int $id): void {
        $stmt = $this->db->prepare("DELETE FROM seuils_stocks WHERE id = ?");
        $stmt->execute([$id]);
    }
}