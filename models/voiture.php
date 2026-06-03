<?php

class Voiture {
    private $db;

    public function __construct(PDO $db) {
        $this->db = $db;
    }

    public function getTotalCount(): int {
        $stmt = $this->db->query("SELECT COUNT(*) FROM voiture");
        return $stmt->fetchColumn();
    }

    public function getAll(): array {
        $stmt = $this->db->query("SELECT * FROM voiture ORDER BY chauffeur ASC");
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function create(string $matricule, string $chauffeur, string $telephone_chauffeur, int $userId): int {
        $stmt = $this->db->prepare("INSERT INTO voiture (matricule, chauffeur, telephone_chauffeur, created_by) VALUES (?, ?, ?, ?)");
        $stmt->execute([$matricule, $chauffeur, $telephone_chauffeur, $userId]);
        return (int)$this->db->lastInsertId();
    }

    public function findById(int $id): ?array {
        $stmt = $this->db->prepare("SELECT * FROM voiture WHERE id = ?");
        $stmt->execute([$id]);
        $voiture = $stmt->fetch(PDO::FETCH_ASSOC);
        return $voiture ?: null;
    }

    public function update(int $id, string $matricule, string $chauffeur, string $telephone_chauffeur): void {
        $stmt = $this->db->prepare("UPDATE voiture SET matricule = ?, chauffeur = ?, telephone_chauffeur = ? WHERE id = ?");
        $stmt->execute([$matricule, $chauffeur, $telephone_chauffeur, $id]);
    }

    public function delete(int $id): void {
        $stmt = $this->db->prepare("DELETE FROM voiture WHERE id = ?");
        $stmt->execute([$id]);
    }
}