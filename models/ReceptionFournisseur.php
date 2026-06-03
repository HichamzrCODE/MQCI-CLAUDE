<?php

class ReceptionFournisseur {

    private PDO $db;

    public function __construct(PDO $db) {
        $this->db = $db;
    }

    // ================================================================
    // LECTURE
    // ================================================================

    public function findById(int $id): ?array {
        $stmt = $this->db->prepare(
            "SELECT r.*,
                    f.nom_fournisseurs,
                    d.nom AS depot_nom,
                    u.username AS user_nom
             FROM receptions_fournisseur r
             LEFT JOIN fournisseurs f ON r.fournisseur_id = f.id_fournisseurs
             LEFT JOIN depots d ON r.depot_id = d.id
             LEFT JOIN users u ON r.user_id = u.id_users
             WHERE r.id = ?"
        );
        $stmt->execute([$id]);
        return $stmt->fetch(PDO::FETCH_ASSOC) ?: null;
    }

    public function getAll(array $filters = []): array {
        $where = [];
        $params = [];

        if (!empty($filters['fournisseur_id'])) {
            $where[] = 'r.fournisseur_id = ?';
            $params[] = (int)$filters['fournisseur_id'];
        }
        if (!empty($filters['depot_id'])) {
            $where[] = 'r.depot_id = ?';
            $params[] = (int)$filters['depot_id'];
        }
        if (!empty($filters['statut'])) {
            $where[] = 'r.statut = ?';
            $params[] = $filters['statut'];
        }
        if (!empty($filters['date_debut'])) {
            $where[] = 'r.date_reception >= ?';
            $params[] = $filters['date_debut'];
        }
        if (!empty($filters['date_fin'])) {
            $where[] = 'r.date_reception <= ?';
            $params[] = $filters['date_fin'];
        }

        $whereSql = $where ? 'WHERE ' . implode(' AND ', $where) : '';

        $stmt = $this->db->prepare(
            "SELECT r.*,
                    f.nom_fournisseurs,
                    d.nom AS depot_nom,
                    u.username AS user_nom,
                    COUNT(rl.id) AS nb_lignes
             FROM receptions_fournisseur r
             LEFT JOIN fournisseurs f ON r.fournisseur_id = f.id_fournisseurs
             LEFT JOIN depots d ON r.depot_id = d.id
             LEFT JOIN users u ON r.user_id = u.id_users
             LEFT JOIN receptions_fournisseur_lignes rl ON r.id = rl.reception_id
             {$whereSql}
             GROUP BY r.id
             ORDER BY r.created_at DESC
             LIMIT 200"
        );
        $stmt->execute($params);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function getTotalCount(): int {
        $stmt = $this->db->query("SELECT COUNT(*) FROM receptions_fournisseur");
        return (int)$stmt->fetchColumn();
    }

    public function getLignes(int $receptionId): array {
        $stmt = $this->db->prepare(
            "SELECT rl.*, a.nom_art, a.sku
             FROM receptions_fournisseur_lignes rl
             LEFT JOIN articles a ON rl.article_id = a.id_articles
             WHERE rl.reception_id = ?
             ORDER BY rl.id ASC"
        );
        $stmt->execute([$receptionId]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    // ================================================================
    // CRÉATION / MODIFICATION
    // ================================================================

    public function create(array $data, int $userId): int {
        $numero = $this->genererNumero();

        $stmt = $this->db->prepare(
            "INSERT INTO receptions_fournisseur
                (numero, fournisseur_id, depot_id, date_reception, user_id, statut, description)
             VALUES
                (:numero, :fournisseur_id, :depot_id, :date_reception, :user_id, 'brouillon', :description)"
        );
        $stmt->execute([
            ':numero'          => $numero,
            ':fournisseur_id'  => (int)$data['fournisseur_id'],
            ':depot_id'        => (int)$data['depot_id'],
            ':date_reception'  => $data['date_reception'],
            ':user_id'         => $userId,
            ':description'     => ($data['description'] ?? '') ?: null,
        ]);
        return (int)$this->db->lastInsertId();
    }

    public function update(int $id, array $data): void {
        $stmt = $this->db->prepare(
            "UPDATE receptions_fournisseur SET
                fournisseur_id = :fournisseur_id,
                depot_id = :depot_id,
                date_reception = :date_reception,
                description = :description
             WHERE id = :id AND statut = 'brouillon'"
        );
        $stmt->execute([
            ':fournisseur_id' => (int)$data['fournisseur_id'],
            ':depot_id'       => (int)$data['depot_id'],
            ':date_reception' => $data['date_reception'],
            ':description'    => ($data['description'] ?? '') ?: null,
            ':id'             => $id,
        ]);
    }

    public function delete(int $id): void {
        // Only delete brouillon receptions
        $check = $this->db->prepare(
            "SELECT id FROM receptions_fournisseur WHERE id = ? AND statut = 'brouillon'"
        );
        $check->execute([$id]);
        if (!$check->fetch()) {
            return; // Non-brouillon or not found — do nothing
        }

        // Delete lines first (cascade)
        $stmt = $this->db->prepare(
            "DELETE FROM receptions_fournisseur_lignes WHERE reception_id = ?"
        );
        $stmt->execute([$id]);

        // Then delete the parent
        $stmt = $this->db->prepare(
            "DELETE FROM receptions_fournisseur WHERE id = ? AND statut = 'brouillon'"
        );
        $stmt->execute([$id]);
    }

    // ================================================================
    // LIGNES
    // ================================================================

    public function addLigne(int $receptionId, int $articleId, int $qteCmdee, ?float $prixUnitaire = null): int {
        // Supprimer si déjà existant pour cet article dans cette réception
        $stmt = $this->db->prepare(
            "DELETE FROM receptions_fournisseur_lignes WHERE reception_id = ? AND article_id = ?"
        );
        $stmt->execute([$receptionId, $articleId]);

        $stmt = $this->db->prepare(
            "INSERT INTO receptions_fournisseur_lignes
                (reception_id, article_id, quantite_commandee, quantite_recue, prix_unitaire)
             VALUES (?, ?, ?, 0, ?)"
        );
        $stmt->execute([$receptionId, $articleId, $qteCmdee, $prixUnitaire]);
        return (int)$this->db->lastInsertId();
    }

    public function updateLigne(int $ligneId, int $qteRecue): void {
        $stmt = $this->db->prepare(
            "UPDATE receptions_fournisseur_lignes SET quantite_recue = ? WHERE id = ?"
        );
        $stmt->execute([$qteRecue, $ligneId]);
    }

    public function removeLigne(int $ligneId): void {
        $stmt = $this->db->prepare("DELETE FROM receptions_fournisseur_lignes WHERE id = ?");
        $stmt->execute([$ligneId]);
    }

    // ================================================================
    // VALIDATION
    // ================================================================

    /**
     * Valide la réception : crée ENTREE pour chaque ligne reçue.
     *
     * @throws RuntimeException si pas de lignes ou quantités invalides
     */
    public function valider(int $id, int $userId, MouvementStock $mouvementModel): void {
        $reception = $this->findById($id);
        if (!$reception) {
            throw new RuntimeException("Réception introuvable.");
        }
        if ($reception['statut'] === 'validee') {
            throw new RuntimeException("Cette réception est déjà validée.");
        }

        $lignes = $this->getLignes($id);
        if (empty($lignes)) {
            throw new RuntimeException("Impossible de valider une réception sans lignes.");
        }

        $depotId = (int)$reception['depot_id'];

        // Vérifier que chaque ligne a une quantité reçue > 0
        $hasRecue = false;
        foreach ($lignes as $ligne) {
            if ((int)$ligne['quantite_recue'] > 0) {
                $hasRecue = true;
                break;
            }
        }
        if (!$hasRecue) {
            throw new RuntimeException("Aucune quantité reçue enregistrée.");
        }

        // Créer les mouvements d'entrée
        foreach ($lignes as $ligne) {
            $qteRecue = (int)$ligne['quantite_recue'];
            if ($qteRecue <= 0) {
                continue;
            }

            $mouvementModel->create(
                (int)$ligne['article_id'],
                $depotId,
                'ENTREE',
                $qteRecue,
                $userId,
                'reception',
                $id,
                "Réception {$reception['numero']} - {$reception['nom_fournisseurs']}"
            );
        }

        // Mettre à jour le statut
        $stmt = $this->db->prepare(
            "UPDATE receptions_fournisseur SET statut = 'validee' WHERE id = ?"
        );
        $stmt->execute([$id]);
    }

    // ================================================================
    // NUMÉROTATION AUTOMATIQUE
    // ================================================================

    private function genererNumero(): string {
        $prefix = 'REC-' . date('Y-m-');
        $stmt = $this->db->prepare(
            "SELECT numero FROM receptions_fournisseur
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