<?php

class Facture {
    private PDO $db;

    public function __construct(PDO $db) {
        $this->db = $db;
    }

    public function listAll(int $limit = 200): array {
        $stmt = $this->db->prepare(
            "SELECT fc.*,
                    c.nom AS client_nom
             FROM factures_clients fc
             JOIN clients c ON c.id_clients = fc.client_id
             ORDER BY fc.date DESC, fc.id DESC
             LIMIT ?"
        );
        $stmt->bindValue(1, $limit, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function findById(int $id): ?array {
        $stmt = $this->db->prepare(
            "SELECT fc.*,
                    c.nom AS client_nom
             FROM factures_clients fc
             JOIN clients c ON c.id_clients = fc.client_id
             WHERE fc.id = ?
             LIMIT 1"
        );
        $stmt->execute([$id]);
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        return $row ?: null;
    }

    public function findBySourceBlId(int $blId): ?array {
        $stmt = $this->db->prepare(
            "SELECT *
             FROM factures_clients
             WHERE source_bl_id = ?
             LIMIT 1"
        );
        $stmt->execute([$blId]);
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        return $row ?: null;
    }

    public function getLastNumeroFacture(string $annee): ?string {
        $stmt = $this->db->prepare(
            "SELECT numero
             FROM factures_clients
             WHERE numero LIKE ?
             ORDER BY id DESC
             LIMIT 1"
        );
        $stmt->execute(["FAC-{$annee}-%"]);
        $numero = $stmt->fetchColumn();
        return $numero !== false ? (string)$numero : null;
    }

    public function getClientAirsiConfig(int $clientId): array {
        $stmt = $this->db->prepare(
            "SELECT apply_airsi, airsi_rate
             FROM clients
             WHERE id_clients = ?
             LIMIT 1"
        );
        $stmt->execute([$clientId]);
        $r = $stmt->fetch(PDO::FETCH_ASSOC);

        return [
            'apply_airsi' => (bool)($r['apply_airsi'] ?? 0),
            'airsi_rate'  => (float)($r['airsi_rate'] ?? 5.00),
        ];
    }

    public function createHeader(
        string $numero,
        int $clientId,
        string $date,
        int $userId,
        ?int $sourceDevisId,
        ?int $sourceBlId,
        float $tvaRate,
        bool $applyAirsi,
        float $airsiRate
    ): int {
        $stmt = $this->db->prepare(
            "INSERT INTO factures_clients (
                numero, client_id, date, statut,
                source_devis_id, source_bl_id,
                tva_rate, airsi_rate, apply_airsi,
                total_ht, total_tva, total_airsi, total_ttc,
                created_by, created_at
            ) VALUES (
                ?, ?, ?, 'draft',
                ?, ?,
                ?, ?, ?,
                0, 0, 0, 0,
                ?, NOW()
            )"
        );

        $stmt->execute([
            $numero,
            $clientId,
            $date,
            $sourceDevisId,
            $sourceBlId,
            $tvaRate,
            $airsiRate,
            $applyAirsi ? 1 : 0,
            $userId
        ]);

        return (int)$this->db->lastInsertId();
    }

    public function updateTotals(int $id, float $ht, float $tva, float $airsi, float $ttc): void {
        $stmt = $this->db->prepare(
            "UPDATE factures_clients
             SET total_ht = ?,
                 total_tva = ?,
                 total_airsi = ?,
                 total_ttc = ?,
                 updated_at = NOW()
             WHERE id = ?"
        );
        $stmt->execute([$ht, $tva, $airsi, $ttc, $id]);
    }

    public function setValidated(int $id, int $userId): void {
        $stmt = $this->db->prepare(
            "UPDATE factures_clients
             SET statut = 'validated',
                 validated_at = NOW(),
                 validated_by = ?,
                 updated_by = ?
             WHERE id = ?"
        );
        $stmt->execute([$userId, $userId, $id]);
    }
}