<?php
class CreditReleve {
    private $db;
    public function __construct(PDO $db) { $this->db = $db; }
    public function getByClient($client_id) {
        $stmt = $this->db->prepare("SELECT * FROM credit_releves WHERE client_id = ?");
        $stmt->execute([$client_id]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }
    public function get($id) {
        $stmt = $this->db->prepare("SELECT * FROM credit_releves WHERE id = ?");
        $stmt->execute([$id]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }
    public function getAll() {
        return $this->db->query("SELECT r.*, c.nom FROM credit_releves r JOIN clients c ON r.client_id = c.id_clients ORDER BY updated_at desc")->fetchAll(PDO::FETCH_ASSOC);
    }
    public function create($client_id, $user_id) {
        $this->db->prepare("INSERT INTO credit_releves (client_id, created_by) VALUES (?, ?)")->execute([$client_id, $user_id]);
        return $this->db->lastInsertId();
    }
    public function update($id, $client_id) {
        $this->db->prepare("UPDATE credit_releves SET client_id = ?, updated_at=NOW() WHERE id = ?")->execute([$client_id, $id]);
    }
    public function delete($id) {
        $this->db->prepare("DELETE FROM credit_releves WHERE id = ?")->execute([$id]);
    }

    public function updateSolde($releve_id) {
    $stmt = $this->db->prepare("SELECT SUM(montant-versement) AS solde FROM credit_lignes WHERE releve_id=?");
    $stmt->execute([$releve_id]);
    $solde = $stmt->fetchColumn() ?: 0;
    $this->db->prepare("UPDATE credit_releves SET total_general=? WHERE id=?")->execute([$solde, $releve_id]);
}
}