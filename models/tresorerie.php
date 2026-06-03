<?php

class Tresorerie {
    private PDO $db;

    public function __construct(PDO $db) {
        $this->db = $db;
    }

    public function list(string $type): array {
        if ($type === 'caisses') {
            $stmt = $this->db->query("SELECT * FROM tresorerie_caisses ORDER BY actif DESC, nom ASC");
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        }
        if ($type === 'banques') {
            $stmt = $this->db->query("SELECT * FROM tresorerie_banques ORDER BY actif DESC, nom ASC");
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        }
        if ($type === 'mobile') {
            $stmt = $this->db->query("SELECT * FROM tresorerie_mobile_money ORDER BY actif DESC, operateur ASC, nom_compte ASC");
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        }
        return [];
    }

    public function create(string $type, array $data): void {
        if ($type === 'caisses') {
            $stmt = $this->db->prepare("INSERT INTO tresorerie_caisses (nom, localisation, actif) VALUES (?, ?, 1)");
            $stmt->execute([trim($data['nom'] ?? ''), trim($data['localisation'] ?? '') ?: null]);
            return;
        }

        if ($type === 'banques') {
            $stmt = $this->db->prepare("INSERT INTO tresorerie_banques (nom, localisation, rib, actif) VALUES (?, ?, ?, 1)");
            $stmt->execute([
                trim($data['nom'] ?? ''),
                trim($data['localisation'] ?? '') ?: null,
                trim($data['rib'] ?? '') ?: null
            ]);
            return;
        }

        if ($type === 'mobile') {
            $stmt = $this->db->prepare("INSERT INTO tresorerie_mobile_money (nom_compte, operateur, telephone, localisation, actif) VALUES (?, ?, ?, ?, 1)");
            $stmt->execute([
                trim($data['nom_compte'] ?? ''),
                trim($data['operateur'] ?? ''),
                trim($data['telephone'] ?? ''),
                trim($data['localisation'] ?? '') ?: null
            ]);
            return;
        }
    }

    public function delete(string $type, int $id): void {
        if ($type === 'caisses') {
            $stmt = $this->db->prepare("DELETE FROM tresorerie_caisses WHERE id = ?");
            $stmt->execute([$id]);
            return;
        }
        if ($type === 'banques') {
            $stmt = $this->db->prepare("DELETE FROM tresorerie_banques WHERE id = ?");
            $stmt->execute([$id]);
            return;
        }
        if ($type === 'mobile') {
            $stmt = $this->db->prepare("DELETE FROM tresorerie_mobile_money WHERE id = ?");
            $stmt->execute([$id]);
            return;
        }
    }


public function listActifs(string $type): array {
    if ($type === 'caisses') {
        $stmt = $this->db->query("SELECT * FROM tresorerie_caisses WHERE actif = 1 ORDER BY nom ASC");
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
    if ($type === 'banques') {
        $stmt = $this->db->query("SELECT * FROM tresorerie_banques WHERE actif = 1 ORDER BY nom ASC");
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
    if ($type === 'mobile') {
        $stmt = $this->db->query("SELECT * FROM tresorerie_mobile_money WHERE actif = 1 ORDER BY operateur ASC, telephone ASC");
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
    return [];
}
}