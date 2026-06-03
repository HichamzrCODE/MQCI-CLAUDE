<?php

class DevisLigne {
    private $db;

    public function __construct(PDO $db) {
        $this->db = $db;
    }

    /**
     * ✅ PRO: on stocke aussi pr_ref_ttc (prix de revient TTC figé au moment du devis)
     *
     * ORDRE DES PARAMÈTRES IMPORTANT:
     * (doit correspondre aux appels dans DevisController)
     */
    public function create(
        int $devisId,
        int $articleId,
        int $quantite,
        float $prixUnitaire,
        float $prRefTtc,
        float $total,
        int $ordre,
        string $description = null
    ): int {
        $stmt = $this->db->prepare("
            INSERT INTO devis_lignes (devis_id, article_id, quantite, prix_unitaire, pr_ref_ttc, total, ordre, description)
            VALUES (?, ?, ?, ?, ?, ?, ?, ?)
        ");
        $stmt->execute([
            $devisId,
            $articleId,
            $quantite,
            $prixUnitaire,
            $prRefTtc,
            $total,
            $ordre,
            $description
        ]);

        return (int)$this->db->lastInsertId();
    }

    public function getByDevisId(int $devisId, bool $orderByOrdre = false): array {
        $sql = "SELECT dl.*, a.nom_art
                FROM devis_lignes dl
                JOIN articles a ON dl.article_id = a.id_articles
                WHERE dl.devis_id = ?
                ORDER BY dl.ordre ASC";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([$devisId]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function getLastPrixArticleClient(int $clientId, int $articleId): ?float {
        $stmt = $this->db->prepare("SELECT prix FROM client_articles_ligne WHERE client_id = ? AND article_id = ? LIMIT 1");
        $stmt->execute([$clientId, $articleId]);
        $r = $stmt->fetch(PDO::FETCH_ASSOC);
        return $r ? (float)$r['prix'] : null;
    }

    public function updateOrCreateClientArticlePrix(int $clientId, int $articleId, float $prix): void {
        $stmt = $this->db->prepare("SELECT prix FROM client_articles_ligne WHERE client_id = ? AND article_id = ?");
        $stmt->execute([$clientId, $articleId]);
        $result = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($result) {
            if ($prix != $result['prix']) {
                $updateStmt = $this->db->prepare("UPDATE client_articles_ligne SET prix = ? WHERE client_id = ? AND article_id = ?");
                $updateStmt->execute([$prix, $clientId, $articleId]);
            }
        } else {
            $insertStmt = $this->db->prepare("INSERT INTO client_articles_ligne (client_id, article_id, prix) VALUES (?, ?, ?)");
            $insertStmt->execute([$clientId, $articleId, $prix]);
        }
    }

    public function deleteByDevisId(int $devisId): void {
        $stmt = $this->db->prepare("DELETE FROM devis_lignes WHERE devis_id = ?");
        $stmt->execute([$devisId]);
    }

    public function delete(int $ligneId): void {
        $stmt = $this->db->prepare("DELETE FROM devis_lignes WHERE id = ?");
        $stmt->execute([$ligneId]);
    }

    public function getClientTarif(int $clientId): string {
        $stmt = $this->db->prepare("SELECT tarif FROM clients WHERE id_clients=? LIMIT 1");
        $stmt->execute([$clientId]);
        $tarif = $stmt->fetchColumn();
        return $tarif ? (string)$tarif : 'detail';
    }

    public function getArticleTarifPrice(int $articleId, string $tarif): float {
        $stmt = $this->db->prepare(
            "SELECT prix_detail, prix_semi_gros, prix_gros, prix_vente
             FROM articles
             WHERE id_articles=? LIMIT 1"
        );
        $stmt->execute([$articleId]);
        $a = $stmt->fetch(PDO::FETCH_ASSOC);
        if (!$a) return 0.0;

        $price = null;
        if ($tarif === 'gros') $price = $a['prix_gros'];
        elseif ($tarif === 'semi_gros') $price = $a['prix_semi_gros'];
        else $price = $a['prix_detail'];

        if ($price === null || (float)$price <= 0) $price = $a['prix_detail'];
        if ($price === null || (float)$price <= 0) $price = $a['prix_vente'];
        if ($price === null) $price = 0;

        return (float)$price;
    }
}

