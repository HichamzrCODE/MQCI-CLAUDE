<<<<<<< HEAD
<?php

class FsReleve {
    private $db;
    public function __construct(PDO $db) { $this->db = $db; }

    public function getByFournisseur($fournisseur_id) {
        $stmt = $this->db->prepare("SELECT * FROM fs_releves WHERE fournisseur_id = ?");
        $stmt->execute([$fournisseur_id]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    public function get($id) {
        $stmt = $this->db->prepare("SELECT * FROM fs_releves WHERE id = ?");
        $stmt->execute([$id]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    public function getAll() {
        // Jointure avec fournisseurs pour afficher le nom dans les listes
        return $this->db->query(
            "SELECT r.*, f.nom_fournisseurs 
             FROM fs_releves r 
             JOIN fournisseurs f ON r.fournisseur_id = f.id_fournisseurs"
        )->fetchAll(PDO::FETCH_ASSOC);
    }

    public function create($fournisseur_id, $user_id) {
        $this->db->prepare(
            "INSERT INTO fs_releves (fournisseur_id, created_by) VALUES (?, ?)"
        )->execute([$fournisseur_id, $user_id]);
        return $this->db->lastInsertId();
    }

    public function update($id, $fournisseur_id) {
        $this->db->prepare(
            "UPDATE fs_releves SET fournisseur_id = ?, updated_at=NOW() WHERE id = ?"
        )->execute([$fournisseur_id, $id]);
    }

    public function delete($id) {
        $this->db->prepare("DELETE FROM fs_releves WHERE id = ?")->execute([$id]);
    }

    public function updateSolde($releve_id) {
        $stmt = $this->db->prepare("SELECT SUM(montant-versement) AS solde FROM fs_lignes WHERE releve_id=?");
        $stmt->execute([$releve_id]);
        $solde = $stmt->fetchColumn() ?: 0;
        $this->db->prepare("UPDATE fs_releves SET total_general=? WHERE id=?")->execute([$solde, $releve_id]);
    }
=======
<?php

class FsReleve {
    private $db;
    public function __construct(PDO $db) { $this->db = $db; }

    public function getByFournisseur($fournisseur_id) {
        $stmt = $this->db->prepare("SELECT * FROM fs_releves WHERE fournisseur_id = ?");
        $stmt->execute([$fournisseur_id]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    public function get($id) {
        $stmt = $this->db->prepare("SELECT * FROM fs_releves WHERE id = ?");
        $stmt->execute([$id]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    public function getAll() {
        // Jointure avec fournisseurs pour afficher le nom dans les listes
        return $this->db->query(
            "SELECT r.*, f.nom_fournisseurs 
             FROM fs_releves r 
             JOIN fournisseurs f ON r.fournisseur_id = f.id_fournisseurs"
        )->fetchAll(PDO::FETCH_ASSOC);
    }

    public function create($fournisseur_id, $user_id) {
        $this->db->prepare(
            "INSERT INTO fs_releves (fournisseur_id, created_by) VALUES (?, ?)"
        )->execute([$fournisseur_id, $user_id]);
        return $this->db->lastInsertId();
    }

    public function update($id, $fournisseur_id) {
        $this->db->prepare(
            "UPDATE fs_releves SET fournisseur_id = ?, updated_at=NOW() WHERE id = ?"
        )->execute([$fournisseur_id, $id]);
    }

    public function delete($id) {
        $this->db->prepare("DELETE FROM fs_releves WHERE id = ?")->execute([$id]);
    }

    public function updateSolde($releve_id) {
        $stmt = $this->db->prepare("SELECT SUM(montant-versement) AS solde FROM fs_lignes WHERE releve_id=?");
        $stmt->execute([$releve_id]);
        $solde = $stmt->fetchColumn() ?: 0;
        $this->db->prepare("UPDATE fs_releves SET total_general=? WHERE id=?")->execute([$solde, $releve_id]);
    }
>>>>>>> 4f8dbbb6b83eb9c6f755d57287033c7da885a3b1
}