<?php

class FactureFournisseurLigne {
    private PDO $db;

    public function __construct(PDO $db) {
        $this->db = $db;
    }

    public function create(
        int $factureId,
        int $articleId,
        int $quantite,
        float $prixUnitaire,
        float $total,
        int $ordre,
        ?string $description = null
    ): int {
        $stmt = $this->db->prepare(
            "INSERT INTO factures_fournisseurs_lignes
             (facture_id, article_id, description, quantite, prix_unitaire, total, ordre)
             VALUES (?, ?, ?, ?, ?, ?, ?)"
        );
        $stmt->execute([
            $factureId,
            $articleId,
            $description,
            $quantite,
            $prixUnitaire,
            $total,
            $ordre
        ]);

        return (int)$this->db->lastInsertId();
    }

    public function getByFactureId(int $factureId, bool $orderByOrdre = true): array {
        $sql = "SELECT ffl.*, a.nom_art, a.sku
                FROM factures_fournisseurs_lignes ffl
                JOIN articles a ON a.id_articles = ffl.article_id
                WHERE ffl.facture_id = ?";

        if ($orderByOrdre) {
            $sql .= " ORDER BY ffl.ordre ASC, ffl.id ASC";
        }

        $stmt = $this->db->prepare($sql);
        $stmt->execute([$factureId]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function deleteByFactureId(int $factureId): void {
        $stmt = $this->db->prepare("DELETE FROM factures_fournisseurs_lignes WHERE facture_id = ?");
        $stmt->execute([$factureId]);
    }
}