<?php

class Devis {
    private $db;

    public function __construct(PDO $db) {
        $this->db = $db;
    }

    // Pagination classique (pas de recherche)
    public function getAllDevis(int $limit = 10, int $offset = 0): array {
        $stmt = $this->db->prepare("
            SELECT devis.*, clients.nom AS nom_client
            FROM devis
            INNER JOIN clients ON devis.client_id = clients.id_clients
            ORDER BY devis.date DESC, devis.numero DESC
            LIMIT :limit OFFSET :offset
        ");
        $stmt->bindValue(':limit', $limit, PDO::PARAM_INT);
        $stmt->bindValue(':offset', $offset, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    // Nombre total de devis (pour la pagination hors recherche)
    public function countDevis(): int {
        $stmt = $this->db->query("SELECT COUNT(*) FROM devis");
        return (int)$stmt->fetchColumn();
    }

    // Recherche paginée sur TOUS les devis (et non juste la page courante)
    public function searchDevis(string $searchTerm, int $limit = 10, int $offset = 0): array {
        $searchTerm = '%' . strtolower($searchTerm) . '%';
        $sql = "SELECT d.*, c.nom AS nom_client
                FROM devis d
                INNER JOIN clients c ON d.client_id = c.id_clients
                WHERE LOWER(d.numero) LIKE :searchTerm
                   OR LOWER(c.nom) LIKE :searchTerm
                   OR CAST(d.total AS CHAR) LIKE :searchTerm
                ORDER BY d.numero DESC
                LIMIT :limit OFFSET :offset";
        $stmt = $this->db->prepare($sql);
        $stmt->bindValue(':searchTerm', $searchTerm, PDO::PARAM_STR);
        $stmt->bindValue(':limit', $limit, PDO::PARAM_INT);
        $stmt->bindValue(':offset', $offset, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    // Compte le nombre total de devis correspondant à la recherche (pour la pagination)
    public function countSearchDevis(string $searchTerm): int {
        $searchTerm = '%' . strtolower($searchTerm) . '%';
        $sql = "SELECT COUNT(*) FROM devis d
                INNER JOIN clients c ON d.client_id = c.id_clients
                WHERE LOWER(d.numero) LIKE :searchTerm
                   OR LOWER(c.nom) LIKE :searchTerm
                   OR CAST(d.total AS CHAR) LIKE :searchTerm";
        $stmt = $this->db->prepare($sql);
        $stmt->bindValue(':searchTerm', $searchTerm, PDO::PARAM_STR);
        $stmt->execute();
        return (int)$stmt->fetchColumn();
    }

    // ... (tes autres méthodes inchangées) ...

    public function findById(int $id): ?array {
        $stmt = $this->db->prepare("SELECT * FROM devis WHERE id = ?");
        $stmt->execute([$id]);
        return $stmt->fetch(PDO::FETCH_ASSOC) ?: null;
    }

    public function create(string $numero, int $clientId, string $date, float $total, int $userId, ?string $reference = null): int {
        $stmt = $this->db->prepare("INSERT INTO devis (numero, client_id, date, total, user_id, reference) VALUES (?, ?, ?, ?, ?, ?)");
        $stmt->execute([$numero, $clientId, $date, $total, $userId, $reference]);
        return (int)$this->db->lastInsertId();
    }

    public function update(int $id, string $numero, int $clientId, string $date, float $total): void {
        $stmt = $this->db->prepare("UPDATE devis SET numero = ?, client_id = ?, date = ?, total = ? WHERE id = ?");
        $stmt->execute([$numero, $clientId, $date, $total, $id]);
    }

    public function delete(int $id): void {
        $stmt = $this->db->prepare("DELETE FROM devis WHERE id = ?");
        $stmt->execute([$id]);
    }

    public function getLastNumeroDevis(string $annee): ?string {
        $stmt = $this->db->prepare("SELECT numero FROM devis WHERE YEAR(date) = ? ORDER BY id DESC LIMIT 1");
        $stmt->execute([$annee]);
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        return $result ? $result['numero'] : null;
    }

    public function updateHeader(int $id, int $clientId, string $date, ?string $reference = null): void {
        $stmt = $this->db->prepare("UPDATE devis SET client_id = ?, date = ?, reference = ? WHERE id = ?");
        $stmt->execute([$clientId, $date, $reference, $id]);
    }

    public function updateTotal(int $devisId, float $totalDevis): bool {
        try {
            $stmt = $this->db->prepare("UPDATE devis SET total = ? WHERE id = ?");
            $stmt->execute([$totalDevis, $devisId]);
            return $stmt->rowCount() > 0;
        } catch (PDOException $e) {
            error_log("Erreur PDO dans updateTotal: " . $e->getMessage());
            return false;
        }
    }

    public function setValidated(int $id, int $userId): void {
    $stmt = $this->db->prepare(
        "UPDATE devis
         SET statut='validated', validated_at=NOW(), validated_by=?
         WHERE id=?"
    );
    $stmt->execute([$userId, $id]);
}

public function duplicate(int $id, int $userId): int {
    $this->db->beginTransaction();
    try {
        $stmt = $this->db->prepare("SELECT * FROM devis WHERE id=? LIMIT 1");
        $stmt->execute([$id]);
        $devis = $stmt->fetch(PDO::FETCH_ASSOC);
        if (!$devis) throw new RuntimeException("Devis introuvable.");

        $newNumero = $devis['numero'] . '-COPIE-' . date('His');

        $stmt = $this->db->prepare(
            "INSERT INTO devis (numero, client_id, date, total, user_id, reference, statut)
             VALUES (?, ?, ?, ?, ?, ?, 'draft')"
        );
        $stmt->execute([
            $newNumero,
            (int)$devis['client_id'],
            date('Y-m-d'),
            (float)($devis['total'] ?? 0),
            $userId,
            $devis['reference'] ?? null
        ]);
        $newDevisId = (int)$this->db->lastInsertId();

        $stmt = $this->db->prepare("SELECT * FROM devis_lignes WHERE devis_id=? ORDER BY ordre ASC, id ASC");
        $stmt->execute([$id]);
        $lignes = $stmt->fetchAll(PDO::FETCH_ASSOC);

        $stmtIns = $this->db->prepare(
            "INSERT INTO devis_lignes (devis_id, article_id, quantite, prix_unitaire, total, ordre, description)
             VALUES (?, ?, ?, ?, ?, ?, ?)"
        );

        foreach ($lignes as $ln) {
            $stmtIns->execute([
                $newDevisId,
                (int)$ln['article_id'],
                (int)$ln['quantite'],
                (float)$ln['prix_unitaire'],
                (float)$ln['total'],
                (int)$ln['ordre'],
                $ln['description'] ?? null
            ]);
        }

        $this->db->commit();
        return $newDevisId;
    } catch (Throwable $e) {
        $this->db->rollBack();
        throw $e;
    }
}
}