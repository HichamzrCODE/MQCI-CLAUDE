<?php

class Livraison {
    private PDO $db;

    public function __construct(PDO $db) {
        $this->db = $db;
    }

    public function getAll(int $limit = 200): array {
        $stmt = $this->db->prepare(
            "SELECT bl.*, c.nom AS client_nom, d.numero AS devis_numero, dp.nom AS depot_nom
             FROM bons_livraison bl
             JOIN clients c ON c.id_clients = bl.client_id
             JOIN devis d ON d.id = bl.devis_id
             JOIN depots dp ON dp.id = bl.depot_id
             ORDER BY bl.id DESC
             LIMIT ?"
        );
        $stmt->bindValue(1, $limit, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function findById(int $id): ?array {
        $stmt = $this->db->prepare(
            "SELECT bl.*, c.nom AS client_nom, d.numero AS devis_numero, dp.nom AS depot_nom
             FROM bons_livraison bl
             JOIN clients c ON c.id_clients = bl.client_id
             JOIN devis d ON d.id = bl.devis_id
             JOIN depots dp ON dp.id = bl.depot_id
             WHERE bl.id = ?"
        );
        $stmt->execute([$id]);
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        return $row ?: null;
    }

    public function getLignes(int $blId): array {
        $stmt = $this->db->prepare(
            "SELECT bll.*, a.nom_art, a.sku
             FROM bons_livraison_lignes bll
             JOIN articles a ON a.id_articles = bll.article_id
             WHERE bll.bl_id = ?
             ORDER BY bll.ordre ASC, bll.id ASC"
        );
        $stmt->execute([$blId]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    // ====== NUMERO BL UNIQUE ======
    private function blNumeroExiste(string $numero): bool {
        $stmt = $this->db->prepare("SELECT 1 FROM bons_livraison WHERE numero = ? LIMIT 1");
        $stmt->execute([$numero]);
        return (bool)$stmt->fetchColumn();
    }

    private function genererNumeroBlUnique(string $baseNumero): string {
        $baseNumero = trim($baseNumero);
        if ($baseNumero === '') $baseNumero = 'BL';

        $numero = $baseNumero;
        $i = 1;
        while ($this->blNumeroExiste($numero)) {
            $i++;
            $numero = $baseNumero . '-' . $i; // ex: BL-DEV-2026-001-2
        }
        return $numero;
    }

    /**
     * Crée un BL à partir d'un devis VALIDÉ.
     * NOTE TX:
     * - Démarre une transaction uniquement si aucune transaction n'est déjà active.
     * - Commit/Rollback uniquement si la transaction a été démarrée ici.
     */
    public function createFromDevis(int $devisId, int $depotId, int $userId): int {
        // Devis doit être validé
        $stmt = $this->db->prepare("SELECT * FROM devis WHERE id=? LIMIT 1");
        $stmt->execute([$devisId]);
        $devis = $stmt->fetch(PDO::FETCH_ASSOC);
        if (!$devis) throw new RuntimeException("Devis introuvable.");
        if (($devis['statut'] ?? 'draft') !== 'validated') {
            throw new RuntimeException("Devis non validé : impossible de créer un BL.");
        }

        $stmt = $this->db->prepare("SELECT * FROM devis_lignes WHERE devis_id=? ORDER BY ordre ASC, id ASC");
        $stmt->execute([$devisId]);
        $lignes = $stmt->fetchAll(PDO::FETCH_ASSOC);

        // ✅ Numéro BL unique (évite 1062)
        $baseNumero = 'BL-' . ($devis['numero'] ?? $devisId);
        $numero = $this->genererNumeroBlUnique($baseNumero);

        // ===== TX guard =====
        $startedTx = false;
        if (!$this->db->inTransaction()) {
            $this->db->beginTransaction();
            $startedTx = true;
        }

        try {
            $stmt = $this->db->prepare(
                "INSERT INTO bons_livraison (numero,devis_id,client_id,depot_id,date,statut,created_by)
                 VALUES (?,?,?,?,?,'draft',?)"
            );
            $stmt->execute([
                $numero,
                $devisId,
                (int)$devis['client_id'],
                $depotId,
                date('Y-m-d'),
                $userId
            ]);
            $blId = (int)$this->db->lastInsertId();

            $stmtIns = $this->db->prepare(
                "INSERT INTO bons_livraison_lignes
                 (bl_id,article_id,description,quantite_demandee,quantite_livree,prix_unitaire,total,ordre)
                 VALUES (?,?,?,?,?,?,?,?)"
            );

            $ordre = 1;
            foreach ($lignes as $dl) {
                $qte = (int)$dl['quantite'];
                $pu  = (float)$dl['prix_unitaire'];
                $tot = round($qte * $pu, 2);

                $stmtIns->execute([
                    $blId,
                    (int)$dl['article_id'],
                    $dl['description'] ?? null,
                    $qte,
                    $qte,
                    $pu,
                    $tot,
                    $ordre++
                ]);
            }

            if ($startedTx) {
                $this->db->commit();
            }

            return $blId;
        } catch (Throwable $e) {
            if ($startedTx && $this->db->inTransaction()) {
                $this->db->rollBack();
            }
            throw $e;
        }
    }

    public function updateLignesQuantites(int $blId, array $postedLignes, int $userId): void {
        $stmt = $this->db->prepare("UPDATE bons_livraison SET updated_by=? WHERE id=?");
        $stmt->execute([$userId, $blId]);

        $stmtGet = $this->db->prepare(
            "SELECT id, quantite_demandee, prix_unitaire
             FROM bons_livraison_lignes
             WHERE id=? AND bl_id=?"
        );
        $stmtUp = $this->db->prepare(
            "UPDATE bons_livraison_lignes
             SET quantite_livree=?, total=?
             WHERE id=? AND bl_id=?"
        );

        foreach ($postedLignes as $ligneId => $row) {
            $ligneId = (int)$ligneId;

            $stmtGet->execute([$ligneId, $blId]);
            $cur = $stmtGet->fetch(PDO::FETCH_ASSOC);
            if (!$cur) continue;

            $qd = (int)$cur['quantite_demandee'];
            $pu = (float)$cur['prix_unitaire'];
            $ql = (int)($row['quantite_livree'] ?? 0);

            if ($ql < 0) $ql = 0;
            if ($ql > $qd) $ql = $qd;

            $total = round($ql * $pu, 2);
            $stmtUp->execute([$ql, $total, $ligneId, $blId]);
        }
    }

    public function markValidated(int $blId, int $userId): void {
        $stmt = $this->db->prepare(
            "UPDATE bons_livraison
             SET statut='validated', validated_at=NOW(), validated_by=?, updated_by=?
             WHERE id=?"
        );
        $stmt->execute([$userId, $userId, $blId]);
    }
}