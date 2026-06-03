<?php

class TransfertStock {

    private PDO $db;

    public function __construct(PDO $db) {
        $this->db = $db;
    }

    // ================================================================
    // LECTURE
    // ================================================================

    public function findById(int $id): ?array {
        $stmt = $this->db->prepare(
            "SELECT t.*,
                    ds.nom AS depot_source_nom,
                    dd.nom AS depot_destination_nom,
                    u.username AS user_nom
             FROM transferts_stock t
             LEFT JOIN depots ds ON t.depot_source_id = ds.id
             LEFT JOIN depots dd ON t.depot_destination_id = dd.id
             LEFT JOIN users u ON t.user_id = u.id_users
             WHERE t.id = ?"
        );
        $stmt->execute([$id]);
        return $stmt->fetch(PDO::FETCH_ASSOC) ?: null;
    }

    public function getAll(array $filters = []): array {
        $where = [];
        $params = [];

        if (!empty($filters['depot_source_id'])) {
            $where[] = 't.depot_source_id = ?';
            $params[] = (int)$filters['depot_source_id'];
        }
        if (!empty($filters['depot_destination_id'])) {
            $where[] = 't.depot_destination_id = ?';
            $params[] = (int)$filters['depot_destination_id'];
        }
        if (!empty($filters['statut'])) {
            $where[] = 't.statut = ?';
            $params[] = $filters['statut'];
        }
        if (!empty($filters['date_debut'])) {
            $where[] = 't.date_transfert >= ?';
            $params[] = $filters['date_debut'];
        }
        if (!empty($filters['date_fin'])) {
            $where[] = 't.date_transfert <= ?';
            $params[] = $filters['date_fin'];
        }

        $whereSql = $where ? 'WHERE ' . implode(' AND ', $where) : '';

        $stmt = $this->db->prepare(
            "SELECT t.*,
                    ds.nom AS depot_source_nom,
                    dd.nom AS depot_destination_nom,
                    u.username AS user_nom,
                    COUNT(tl.id) AS nb_lignes
             FROM transferts_stock t
             LEFT JOIN depots ds ON t.depot_source_id = ds.id
             LEFT JOIN depots dd ON t.depot_destination_id = dd.id
             LEFT JOIN users u ON t.user_id = u.id_users
             LEFT JOIN transferts_stock_lignes tl ON t.id = tl.transfert_id
             {$whereSql}
             GROUP BY t.id
             ORDER BY t.created_at DESC
             LIMIT 200"
        );
        $stmt->execute($params);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function getTotalCount(): int {
        $stmt = $this->db->query("SELECT COUNT(*) FROM transferts_stock");
        return (int)$stmt->fetchColumn();
    }

    public function getLignes(int $transfertId): array {
        $stmt = $this->db->prepare(
            "SELECT tl.*, a.nom_art, a.sku,
                    COALESCE(sp.quantite, 0) AS stock_source
             FROM transferts_stock_lignes tl
             LEFT JOIN articles a ON tl.article_id = a.id_articles
             LEFT JOIN transferts_stock t ON tl.transfert_id = t.id
             LEFT JOIN stock_par_depot sp ON sp.article_id = tl.article_id
                 AND sp.depot_id = t.depot_source_id
             WHERE tl.transfert_id = ?
             ORDER BY tl.id ASC"
        );
        $stmt->execute([$transfertId]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    // ================================================================
    // CRÉATION / MODIFICATION
    // ================================================================

    public function create(array $data, int $userId): int {
        $numero = $this->genererNumero();

        $stmt = $this->db->prepare(
            "INSERT INTO transferts_stock
                (numero, depot_source_id, depot_destination_id, date_transfert, user_id, statut, description)
             VALUES
                (:numero, :depot_source_id, :depot_destination_id, :date_transfert, :user_id, 'brouillon', :description)"
        );
        $stmt->execute([
            ':numero'               => $numero,
            ':depot_source_id'      => (int)$data['depot_source_id'],
            ':depot_destination_id' => (int)$data['depot_destination_id'],
            ':date_transfert'       => $data['date_transfert'],
            ':user_id'              => $userId,
            ':description'          => ($data['description'] ?? '') ?: null,
        ]);
        return (int)$this->db->lastInsertId();
    }

    public function update(int $id, array $data): void {
        $stmt = $this->db->prepare(
            "UPDATE transferts_stock SET
                depot_source_id = :depot_source_id,
                depot_destination_id = :depot_destination_id,
                date_transfert = :date_transfert,
                description = :description
             WHERE id = :id AND statut = 'brouillon'"
        );
        $stmt->execute([
            ':depot_source_id'      => (int)$data['depot_source_id'],
            ':depot_destination_id' => (int)$data['depot_destination_id'],
            ':date_transfert'       => $data['date_transfert'],
            ':description'          => ($data['description'] ?? '') ?: null,
            ':id'                   => $id,
        ]);
    }

    public function delete(int $id): void {
        $stmt = $this->db->prepare(
            "DELETE FROM transferts_stock WHERE id = ? AND statut = 'brouillon'"
        );
        $stmt->execute([$id]);
    }

    // ================================================================
    // LIGNES
    // ================================================================

    public function addLigne(int $transfertId, int $articleId, int $quantite): int {
        // Supprimer la ligne si elle existe déjà pour cet article dans ce transfert
        $stmt = $this->db->prepare(
            "DELETE FROM transferts_stock_lignes WHERE transfert_id = ? AND article_id = ?"
        );
        $stmt->execute([$transfertId, $articleId]);

        $stmt = $this->db->prepare(
            "INSERT INTO transferts_stock_lignes (transfert_id, article_id, quantite)
             VALUES (?, ?, ?)"
        );
        $stmt->execute([$transfertId, $articleId, $quantite]);
        return (int)$this->db->lastInsertId();
    }

    public function removeLigne(int $ligneId): void {
        $stmt = $this->db->prepare("DELETE FROM transferts_stock_lignes WHERE id = ?");
        $stmt->execute([$ligneId]);
    }

    // ================================================================
    // VALIDATION
    // ================================================================

    /**
     * Valide le transfert : crée SORTIE dans source + ENTREE dans destination.
     *
     * @throws RuntimeException si stock insuffisant
     */
    public function valider(int $id, int $userId, MouvementStock $mouvementModel): void {
        $transfert = $this->findById($id);
        if (!$transfert) {
            throw new RuntimeException("Transfert introuvable.");
        }
        if ($transfert['statut'] === 'valide') {
            throw new RuntimeException("Ce transfert est déjà validé.");
        }

        $lignes = $this->getLignes($id);
        if (empty($lignes)) {
            throw new RuntimeException("Impossible de valider un transfert sans lignes.");
        }

        $depotSourceId = (int)$transfert['depot_source_id'];
        $depotDestId   = (int)$transfert['depot_destination_id'];

        // Vérifier le stock disponible pour chaque ligne
        foreach ($lignes as $ligne) {
            $stockDispo = $mouvementModel->getStockDisponible((int)$ligne['article_id'], $depotSourceId);
            if ($stockDispo < (int)$ligne['quantite']) {
                throw new RuntimeException(
                    "Stock insuffisant pour l'article « {$ligne['nom_art']} » : "
                    . "disponible {$stockDispo}, demandé {$ligne['quantite']}."
                );
            }
        }

        // Créer les mouvements
        foreach ($lignes as $ligne) {
            $articleId = (int)$ligne['article_id'];
            $quantite  = (int)$ligne['quantite'];

            // SORTIE du dépôt source
            $mouvementModel->create(
                $articleId, $depotSourceId, 'SORTIE', $quantite, $userId,
                'transfert', $id,
                "Transfert {$transfert['numero']} → {$transfert['depot_destination_nom']}"
            );

            // ENTREE dans le dépôt destination
            $mouvementModel->create(
                $articleId, $depotDestId, 'ENTREE', $quantite, $userId,
                'transfert', $id,
                "Transfert {$transfert['numero']} ← {$transfert['depot_source_nom']}"
            );
        }

        // Mettre à jour le statut
        $stmt = $this->db->prepare(
            "UPDATE transferts_stock SET statut = 'valide' WHERE id = ?"
        );
        $stmt->execute([$id]);
    }

    // ================================================================
    // NUMÉROTATION AUTOMATIQUE
    // ================================================================

    private function genererNumero(): string {
        $prefix = 'TRANSF-' . date('Y-m-');
        $stmt = $this->db->prepare(
            "SELECT numero FROM transferts_stock
             WHERE numero LIKE ?
             ORDER BY id DESC LIMIT 1"
        );
        $stmt->execute([$prefix . '%']);
        $last = $stmt->fetchColumn();

        if ($last) {
            $lastNum = (int)substr($last, strrpos($last, '-') + 1);
            $num = $lastNum + 1;
        } else {
            $num = 1;
        }

        return $prefix . str_pad($num, 4, '0', STR_PAD_LEFT);
    }
}