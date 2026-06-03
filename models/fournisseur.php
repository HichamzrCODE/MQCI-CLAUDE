<?php

class fournisseur {
    private $db;

    public function __construct(PDO $db) {
        $this->db = $db;
    }

    public function getAll(): array {
        $stmt = $this->db->query("SELECT * FROM fournisseurs ORDER BY nom_fournisseurs ASC");
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function create(string $nom_fournisseurs, int $userId, string $email = '', string $telephone = ''): int {
        $stmt = $this->db->prepare("INSERT INTO fournisseurs (nom_fournisseurs, created_by, email, telephone) VALUES (?, ?, ?, ?)");
        $stmt->execute([$nom_fournisseurs, $userId, $email ?: null, $telephone ?: null]);
        return (int)$this->db->lastInsertId();
    }

    public function findById(int $id_fournisseurs): ?array {
        $stmt = $this->db->prepare("SELECT * FROM fournisseurs WHERE id_fournisseurs = ?");
        $stmt->execute([$id_fournisseurs]);
        $fournisseurs = $stmt->fetch(PDO::FETCH_ASSOC);
        return $fournisseurs ?: null;
    }

    public function update(int $id, string $nom_fournisseurs, string $email = '', string $telephone = ''): void {
        $stmt = $this->db->prepare("UPDATE fournisseurs SET nom_fournisseurs = ?, email = ?, telephone = ? WHERE id_fournisseurs = ?");
        $stmt->execute([$nom_fournisseurs, $email ?: null, $telephone ?: null, $id]);
    }

    public function delete(int $id_fournisseurs): void {
        $stmt = $this->db->prepare("DELETE FROM fournisseurs WHERE id_fournisseurs = ?");
        $stmt->execute([$id_fournisseurs]);
    }


    public function searchFull($term) {
    $sql = "SELECT * FROM fournisseurs WHERE nom_fournisseurs LIKE ?";
    $stmt = $this->db->prepare($sql);
    $stmt->execute(['%' . $term . '%']);
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

public function existsByName($nom_fournisseurs) {
    $sql = "SELECT COUNT(*) FROM fournisseurs WHERE LOWER(nom_fournisseurs) = LOWER(?)";
    $stmt = $this->db->prepare($sql);
    $stmt->execute([$nom_fournisseurs]);
    return $stmt->fetchColumn() > 0;
}

    // Nombre d'articles fournis par ce fournisseur
    public function countArticles(int $fournisseurId): int {
        $stmt = $this->db->prepare("SELECT COUNT(*) FROM articles WHERE fournisseur_id = ?");
        $stmt->execute([$fournisseurId]);
        return (int)$stmt->fetchColumn();
    }

    // Prix moyen d'achat des articles de ce fournisseur
    public function getPrixMoyenAchat(int $fournisseurId): float {
        $stmt = $this->db->prepare("SELECT COALESCE(AVG(pr), 0) FROM articles WHERE fournisseur_id = ?");
        $stmt->execute([$fournisseurId]);
        return (float)$stmt->fetchColumn();
    }
}