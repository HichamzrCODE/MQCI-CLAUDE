<?php

class MouvementStock {

    private PDO $db;

    public function __construct(PDO $db) {
        $this->db = $db;
    }

    /**
     * Crée un mouvement de stock et met à jour stock_par_depot + articles.quantite_totale.
     */
    public function create(array $data, int $userId): int {
        $articleId    = (int)$data['article_id'];
        $depotId      = (int)$data['depot_id'];
        $type         = trim((string)$data['type_mouvement']);
        $quantite     = (int)$data['quantite'];
        $reference    = trim((string)($data['reference'] ?? ''));
        $description  = trim((string)($data['description'] ?? ''));
        $documentType = trim((string)($data['document_type'] ?? ''));
        $documentId   = isset($data['document_id']) && $data['document_id'] !== '' ? (int)$data['document_id'] : null;
        $prixUnitaire = isset($data['prix_unitaire']) && $data['prix_unitaire'] !== '' ? (float)$data['prix_unitaire'] : null;

        if ($articleId <= 0) {
            throw new \InvalidArgumentException("Article invalide.");
        }
        if ($depotId <= 0) {
            throw new \InvalidArgumentException("Dépôt invalide.");
        }
        if ($quantite < 0) {
            throw new \InvalidArgumentException("Quantité invalide.");
        }

        $typesAutorises = ['entree', 'sortie', 'ajustement', 'retour', 'transfert'];
        if (!in_array($type, $typesAutorises, true)) {
            throw new \InvalidArgumentException("Type de mouvement invalide.");
        }

        $stmt = $this->db->prepare(
            "SELECT COALESCE(quantite, 0)
             FROM stock_par_depot
             WHERE article_id = ? AND depot_id = ?
             LIMIT 1"
        );
        $stmt->execute([$articleId, $depotId]);
        $quantiteAvant = (int)$stmt->fetchColumn();

        if ($type === 'entree' || $type === 'retour') {
            $quantiteApres = $quantiteAvant + $quantite;
        } elseif ($type === 'sortie' || $type === 'transfert') {
            if ($quantite > $quantiteAvant) {
                throw new \InvalidArgumentException(
                    "Stock insuffisant : disponible {$quantiteAvant}, demandé {$quantite}."
                );
            }
            $quantiteApres = $quantiteAvant - $quantite;
        } elseif ($type === 'ajustement') {
            $quantiteApres = $quantite;
        } else {
            $quantiteApres = $quantiteAvant;
        }

        $stmt = $this->db->prepare(
            "INSERT INTO mouvements_stock
                (article_id, depot_id, type_mouvement, quantite, quantite_avant, quantite_apres,
                 reference, description, document_type, document_id, prix_unitaire, user_id)
             VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)"
        );
        $stmt->execute([
            $articleId,
            $depotId,
            $type,
            $quantite,
            $quantiteAvant,
            $quantiteApres,
            $reference !== '' ? $reference : null,
            $description !== '' ? $description : null,
            $documentType !== '' ? $documentType : null,
            $documentId,
            $prixUnitaire,
            $userId,
        ]);
        $mouvementId = (int)$this->db->lastInsertId();

        $stmt = $this->db->prepare(
            "INSERT INTO stock_par_depot (article_id, depot_id, quantite)
             VALUES (?, ?, ?)
             ON DUPLICATE KEY UPDATE quantite = VALUES(quantite)"
        );
        $stmt->execute([$articleId, $depotId, $quantiteApres]);

        $stmt = $this->db->prepare(
            "UPDATE articles
             SET quantite_totale = (
                 SELECT COALESCE(SUM(quantite), 0)
                 FROM stock_par_depot
                 WHERE article_id = ?
             ),
             updated_at = NOW()
             WHERE id_articles = ?"
        );
        $stmt->execute([$articleId, $articleId]);

        return $mouvementId;
    }

    public function getAll(array $filters = [], int $limit = 100, int $offset = 0): array {
        $where  = [];
        $params = [];

        if (!empty($filters['article_id'])) {
            $where[]  = 'm.article_id = ?';
            $params[] = (int)$filters['article_id'];
        }
        if (!empty($filters['depot_id'])) {
            $where[]  = 'm.depot_id = ?';
            $params[] = (int)$filters['depot_id'];
        }
        if (!empty($filters['type_mouvement'])) {
            $where[]  = 'm.type_mouvement = ?';
            $params[] = $filters['type_mouvement'];
        }
        if (!empty($filters['date_debut'])) {
            $where[]  = 'DATE(m.created_at) >= ?';
            $params[] = $filters['date_debut'];
        }
        if (!empty($filters['date_fin'])) {
            $where[]  = 'DATE(m.created_at) <= ?';
            $params[] = $filters['date_fin'];
        }

        $sql = "SELECT m.*,
                       a.nom_art, a.sku,
                       d.nom AS depot_nom,
                       u.username AS user_nom
                FROM mouvements_stock m
                INNER JOIN articles a ON m.article_id = a.id_articles
                INNER JOIN depots d   ON m.depot_id = d.id
                LEFT JOIN users u     ON m.user_id = u.id_users";

        if ($where) {
            $sql .= ' WHERE ' . implode(' AND ', $where);
        }

        $sql .= ' ORDER BY m.created_at DESC LIMIT ? OFFSET ?';
        $params[] = $limit;
        $params[] = $offset;

        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);

        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function count(array $filters = []): int {
        $where  = [];
        $params = [];

        if (!empty($filters['article_id'])) {
            $where[]  = 'm.article_id = ?';
            $params[] = (int)$filters['article_id'];
        }
        if (!empty($filters['depot_id'])) {
            $where[]  = 'm.depot_id = ?';
            $params[] = (int)$filters['depot_id'];
        }
        if (!empty($filters['type_mouvement'])) {
            $where[]  = 'm.type_mouvement = ?';
            $params[] = $filters['type_mouvement'];
        }
        if (!empty($filters['date_debut'])) {
            $where[]  = 'DATE(m.created_at) >= ?';
            $params[] = $filters['date_debut'];
        }
        if (!empty($filters['date_fin'])) {
            $where[]  = 'DATE(m.created_at) <= ?';
            $params[] = $filters['date_fin'];
        }

        $sql = "SELECT COUNT(*) FROM mouvements_stock m";
        if ($where) {
            $sql .= ' WHERE ' . implode(' AND ', $where);
        }

        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);

        return (int)$stmt->fetchColumn();
    }

    public function findById(int $id): ?array {
        $stmt = $this->db->prepare(
            "SELECT m.*,
                    a.nom_art, a.sku,
                    d.nom AS depot_nom,
                    u.username AS user_nom
             FROM mouvements_stock m
             INNER JOIN articles a ON m.article_id = a.id_articles
             INNER JOIN depots d   ON m.depot_id = d.id
             LEFT JOIN users u     ON m.user_id = u.id_users
             WHERE m.id = ?"
        );
        $stmt->execute([$id]);
        $row = $stmt->fetch(PDO::FETCH_ASSOC);

        return $row ?: null;
    }

    public function getHistorique(int $articleId, ?int $depotId = null): array {
        $where  = ['m.article_id = ?'];
        $params = [$articleId];

        if ($depotId !== null) {
            $where[]  = 'm.depot_id = ?';
            $params[] = $depotId;
        }

        $stmt = $this->db->prepare(
            "SELECT m.*,
                    d.nom AS depot_nom,
                    u.username AS user_nom
             FROM mouvements_stock m
             INNER JOIN depots d ON m.depot_id = d.id
             LEFT JOIN users u   ON m.user_id = u.id_users
             WHERE " . implode(' AND ', $where) . "
             ORDER BY m.created_at DESC
             LIMIT 200"
        );
        $stmt->execute($params);

        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
}