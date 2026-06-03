<?php

class FactureFournisseur {
    private PDO $db;

    public function __construct(PDO $db) {
        $this->db = $db;
    }

    public function getAll(int $limit = 200, int $offset = 0): array {
        $stmt = $this->db->prepare(
            "SELECT ff.*,
                    f.nom_fournisseurs,
                    d.nom AS depot_nom
             FROM factures_fournisseurs ff
             JOIN fournisseurs f ON f.id_fournisseurs = ff.fournisseur_id
             JOIN depots d ON d.id = ff.depot_id
             ORDER BY ff.id DESC
             LIMIT ? OFFSET ?"
        );
        $stmt->bindValue(1, $limit, PDO::PARAM_INT);
        $stmt->bindValue(2, $offset, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function countAll(): int {
        $stmt = $this->db->query("SELECT COUNT(*) FROM factures_fournisseurs");
        return (int)$stmt->fetchColumn();
    }

    public function search(string $term, int $limit = 200, int $offset = 0): array {
        $like = '%' . $term . '%';
        $stmt = $this->db->prepare(
            "SELECT ff.*,
                    f.nom_fournisseurs,
                    d.nom AS depot_nom
             FROM factures_fournisseurs ff
             JOIN fournisseurs f ON f.id_fournisseurs = ff.fournisseur_id
             JOIN depots d ON d.id = ff.depot_id
             WHERE ff.numero LIKE ?
                OR f.nom_fournisseurs LIKE ?
                OR d.nom LIKE ?
             ORDER BY ff.id DESC
             LIMIT ? OFFSET ?"
        );
        $stmt->bindValue(1, $like);
        $stmt->bindValue(2, $like);
        $stmt->bindValue(3, $like);
        $stmt->bindValue(4, $limit, PDO::PARAM_INT);
        $stmt->bindValue(5, $offset, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function countSearch(string $term): int {
        $like = '%' . $term . '%';
        $stmt = $this->db->prepare(
            "SELECT COUNT(*)
             FROM factures_fournisseurs ff
             JOIN fournisseurs f ON f.id_fournisseurs = ff.fournisseur_id
             JOIN depots d ON d.id = ff.depot_id
             WHERE ff.numero LIKE ?
                OR f.nom_fournisseurs LIKE ?
                OR d.nom LIKE ?"
        );
        $stmt->execute([$like, $like, $like]);
        return (int)$stmt->fetchColumn();
    }

    public function findById(int $id): ?array {
        $stmt = $this->db->prepare(
            "SELECT ff.*,
                    f.nom_fournisseurs,
                    d.nom AS depot_nom
             FROM factures_fournisseurs ff
             JOIN fournisseurs f ON f.id_fournisseurs = ff.fournisseur_id
             JOIN depots d ON d.id = ff.depot_id
             WHERE ff.id = ?
             LIMIT 1"
        );
        $stmt->execute([$id]);
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        return $row ?: null;
    }

    public function create(
        string $numero,
        int $fournisseurId,
        int $depotId,
        string $date,
        ?string $notes,
        int $userId
    ): int {
        $stmt = $this->db->prepare(
            "INSERT INTO factures_fournisseurs
             (numero, fournisseur_id, depot_id, date, statut, notes, total_ht, total_tva, total_ttc, created_by)
             VALUES (?, ?, ?, ?, 'draft', ?, 0, 0, 0, ?)"
        );
        $stmt->execute([
            $numero,
            $fournisseurId,
            $depotId,
            $date,
            $notes,
            $userId
        ]);

        return (int)$this->db->lastInsertId();
    }

    public function updateHeader(
        int $id,
        int $fournisseurId,
        int $depotId,
        string $date,
        ?string $notes,
        int $userId
    ): void {
        $stmt = $this->db->prepare(
            "UPDATE factures_fournisseurs
             SET fournisseur_id = ?,
                 depot_id = ?,
                 date = ?,
                 notes = ?,
                 updated_by = ?
             WHERE id = ?"
        );
        $stmt->execute([
            $fournisseurId,
            $depotId,
            $date,
            $notes,
            $userId,
            $id
        ]);
    }

    public function updateTotals(int $id, float $totalHt, float $totalTva, float $totalTtc): void {
        $stmt = $this->db->prepare(
            "UPDATE factures_fournisseurs
             SET total_ht = ?, total_tva = ?, total_ttc = ?
             WHERE id = ?"
        );
        $stmt->execute([$totalHt, $totalTva, $totalTtc, $id]);
    }

    public function setValidated(int $id, int $userId): void {
        $stmt = $this->db->prepare(
            "UPDATE factures_fournisseurs
             SET statut = 'validated',
                 validated_by = ?,
                 validated_at = NOW(),
                 updated_by = ?
             WHERE id = ?"
        );
        $stmt->execute([$userId, $userId, $id]);
    }

    public function delete(int $id): void {
        $stmt = $this->db->prepare("DELETE FROM factures_fournisseurs WHERE id = ?");
        $stmt->execute([$id]);
    }

    public function getLastNumeroFactureFournisseur(string $annee): ?string {
        $stmt = $this->db->prepare(
            "SELECT numero
             FROM factures_fournisseurs
             WHERE numero LIKE ?
             ORDER BY id DESC
             LIMIT 1"
        );
        $stmt->execute(['FF-' . $annee . '-%']);
        $numero = $stmt->fetchColumn();
        return $numero !== false ? (string)$numero : null;
    }
}