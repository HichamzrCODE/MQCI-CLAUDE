<?php
class CreditLigne {
    private $db;
    public function __construct(PDO $db) { $this->db = $db; }

    public function getAllByReleve($releve_id, $date_debut = null, $date_fin = null) {
        $query = "SELECT * FROM credit_lignes WHERE releve_id = ?";
        $params = [$releve_id];
        if ($date_debut) {
            $query .= " AND date_operation >= ?";
            $params[] = $date_debut;
        }
        if ($date_fin) {
            $query .= " AND date_operation <= ?";
            $params[] = $date_fin;
        }
        $query .= " ORDER BY date_operation, id ASC";
        $stmt = $this->db->prepare($query);
        $stmt->execute($params);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function create($releve_id, $date, $bc, $facture, $montant, $versement) {
        $this->db->prepare("INSERT INTO credit_lignes (releve_id, date_operation, bc_client, numero_facture, montant, versement) VALUES (?, ?, ?, ?, ?, ?)")
            ->execute([$releve_id, $date, $bc, $facture, $montant, $versement]);
    }

    public function deleteByReleve($releve_id) {
        $this->db->prepare("DELETE FROM credit_lignes WHERE releve_id = ?")->execute([$releve_id]);
    }

    public function deleteByIds(array $ids) {
    if (empty($ids)) return;
    $in = implode(',', array_fill(0, count($ids), '?'));
    $stmt = $this->db->prepare("DELETE FROM credit_lignes WHERE id IN ($in)");
    $stmt->execute($ids);
}
}