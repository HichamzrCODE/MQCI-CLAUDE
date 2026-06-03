<?php
class ClientReleve {
    private $db;
    public function __construct(PDO $db) { $this->db = $db; }

    /**
     * Retourne toutes les lignes (devis + versements credit_lignes) pour un client,
     * triées par date croissante, factures avant versements à date égale.
     */
    public function getAllLignes(int $client_id, ?string $date_debut = null, ?string $date_fin = null): array {
        $where_devis = "d.client_id = :cid1";
        $where_vers  = "cr.client_id = :cid2 AND cl.versement > 0";
        $params = [':cid1' => $client_id, ':cid2' => $client_id];

        if ($date_debut) {
            $where_devis .= " AND d.date >= :dd1";
            $where_vers  .= " AND cl.date_operation >= :dd2";
            $params[':dd1'] = $date_debut;
            $params[':dd2'] = $date_debut;
        }
        if ($date_fin) {
            $where_devis .= " AND d.date <= :df1";
            $where_vers  .= " AND cl.date_operation <= :df2";
            $params[':df1'] = $date_fin;
            $params[':df2'] = $date_fin;
        }

        $sql = "SELECT date_operation, reference, montant, versement, type FROM (
                    SELECT d.date       AS date_operation,
                           d.numero    AS reference,
                           d.total     AS montant,
                           0.00        AS versement,
                           'facture'   AS type
                    FROM devis d
                    WHERE $where_devis

                    UNION ALL

                    SELECT cl.date_operation,
                           COALESCE(NULLIF(cl.numero_facture,''), cl.bc_client, '') AS reference,
                           0.00        AS montant,
                           cl.versement,
                           'versement' AS type
                    FROM credit_lignes cl
                    JOIN credit_releves cr ON cl.releve_id = cr.id
                    WHERE $where_vers
                ) AS combined
                ORDER BY date_operation ASC,
                         CASE WHEN type = 'facture' THEN 0 ELSE 1 END ASC";

        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Solde cumulé de toutes les opérations AVANT date_debut (solde d'ouverture de période).
     */
    public function getSoldeOuverture(int $client_id, ?string $date_debut): float {
        if (!$date_debut) return 0.0;

        $sql = "SELECT COALESCE(SUM(montant - versement), 0) FROM (
                    SELECT d.total AS montant, 0.00 AS versement
                    FROM devis d
                    WHERE d.client_id = :cid1 AND d.date < :dd1

                    UNION ALL

                    SELECT 0.00 AS montant, cl.versement
                    FROM credit_lignes cl
                    JOIN credit_releves cr ON cl.releve_id = cr.id
                    WHERE cr.client_id = :cid2 AND cl.versement > 0 AND cl.date_operation < :dd2
                ) AS combined";

        $stmt = $this->db->prepare($sql);
        $stmt->execute([':cid1' => $client_id, ':dd1' => $date_debut,
                        ':cid2' => $client_id, ':dd2' => $date_debut]);
        return (float)$stmt->fetchColumn();
    }

    /**
     * Solde global (toutes dates) pour un client.
     */
    public function getTotalGeneral(int $client_id): float {
        $sql = "SELECT COALESCE(SUM(montant - versement), 0) FROM (
                    SELECT d.total AS montant, 0.00 AS versement
                    FROM devis d WHERE d.client_id = :cid1

                    UNION ALL

                    SELECT 0.00 AS montant, cl.versement
                    FROM credit_lignes cl
                    JOIN credit_releves cr ON cl.releve_id = cr.id
                    WHERE cr.client_id = :cid2 AND cl.versement > 0
                ) AS combined";

        $stmt = $this->db->prepare($sql);
        $stmt->execute([':cid1' => $client_id, ':cid2' => $client_id]);
        return (float)$stmt->fetchColumn();
    }
}