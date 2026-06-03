<?php

class FactureClientLigne {
    private PDO $db;

    public function __construct(PDO $db) {
        $this->db = $db;
    }

    public function create(
        int $factureId,
        int $articleId,
        int $quantite,
        float $prixUnitaireHt,
        float $prixUnitaireTtc,
        float $prRefTtc,
        float $totalHt,
        float $totalTtc,
        int $ordre,
        ?string $description = null
    ): int {
        $stmt = $this->db->prepare(
            "INSERT INTO factures_clients_lignes
             (facture_id, article_id, description, quantite,
              prix_unitaire_ht, prix_unitaire_ttc, pr_ref_ttc,
              total_ht, total_ttc, ordre)
             VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)"
        );

        $stmt->execute([
            $factureId,
            $articleId,
            $description,
            $quantite,
            $prixUnitaireHt,
            $prixUnitaireTtc,
            $prRefTtc,
            $totalHt,
            $totalTtc,
            $ordre
        ]);

        return (int)$this->db->lastInsertId();
    }

    public function getByFactureId(int $factureId, bool $orderByOrdre = true): array {
        $sql = "SELECT fcl.*, a.nom_art, a.sku
                FROM factures_clients_lignes fcl
                JOIN articles a ON a.id_articles = fcl.article_id
                WHERE fcl.facture_id = ?";

        if ($orderByOrdre) {
            $sql .= " ORDER BY fcl.ordre ASC, fcl.id ASC";
        }

        $stmt = $this->db->prepare($sql);
        $stmt->execute([$factureId]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
}