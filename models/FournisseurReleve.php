<?php

class FournisseurReleve
{
    private PDO $db;

    public function __construct(PDO $db)
    {
        $this->db = $db;
    }

    public function getAllLignes(int $fournisseur_id, ?string $date_debut = null, ?string $date_fin = null): array
    {
        $where_factures   = "ff.fournisseur_id = :fid1 AND ff.statut = 'validated'";
        $where_versements = "vf.fournisseur_id = :fid2 AND vf.statut != 'annule'";
        $params = [':fid1' => $fournisseur_id, ':fid2' => $fournisseur_id];

        if ($date_debut) {
            $where_factures   .= " AND ff.date >= :dd1";
            $where_versements .= " AND vf.date >= :dd2";
            $params[':dd1'] = $date_debut;
            $params[':dd2'] = $date_debut;
        }
        if ($date_fin) {
            $where_factures   .= " AND ff.date <= :df1";
            $where_versements .= " AND vf.date <= :df2";
            $params[':df1'] = $date_fin;
            $params[':df2'] = $date_fin;
        }

        $sql = "SELECT date_operation, reference, montant, versement, type FROM (
                    SELECT ff.date AS date_operation, ff.numero AS reference,
                           ff.total_ttc AS montant, 0.00 AS versement, 'facture' AS type
                    FROM factures_fournisseurs ff WHERE {$where_factures}
                    UNION ALL
                    SELECT vf.date AS date_operation, vf.reference AS reference,
                           0.00 AS montant, vf.montant AS versement, 'versement' AS type
                    FROM versements_fournisseurs vf WHERE {$where_versements}
                ) AS combined
                ORDER BY date_operation ASC, CASE WHEN type = 'facture' THEN 0 ELSE 1 END ASC";

        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function getSoldeOuverture(int $fournisseur_id, ?string $date_debut): float
    {
        if (!$date_debut) return 0.0;

        $sql = "SELECT COALESCE(SUM(montant - versement), 0) FROM (
                    SELECT ff.total_ttc AS montant, 0.00 AS versement
                    FROM factures_fournisseurs ff
                    WHERE ff.fournisseur_id = :fid1 AND ff.statut = 'validated' AND ff.date < :dd1
                    UNION ALL
                    SELECT 0.00 AS montant, vf.montant AS versement
                    FROM versements_fournisseurs vf
                    WHERE vf.fournisseur_id = :fid2 AND vf.statut != 'annule' AND vf.date < :dd2
                ) AS combined";

        $stmt = $this->db->prepare($sql);
        $stmt->execute([':fid1' => $fournisseur_id, ':dd1' => $date_debut,
                        ':fid2' => $fournisseur_id, ':dd2' => $date_debut]);
        return (float) $stmt->fetchColumn();
    }

    public function getTotalGeneral(int $fournisseur_id): float
    {
        $sql = "SELECT COALESCE(SUM(montant - versement), 0) FROM (
                    SELECT ff.total_ttc AS montant, 0.00 AS versement
                    FROM factures_fournisseurs ff
                    WHERE ff.fournisseur_id = :fid1 AND ff.statut = 'validated'
                    UNION ALL
                    SELECT 0.00 AS montant, vf.montant AS versement
                    FROM versements_fournisseurs vf
                    WHERE vf.fournisseur_id = :fid2 AND vf.statut != 'annule'
                ) AS combined";

        $stmt = $this->db->prepare($sql);
        $stmt->execute([':fid1' => $fournisseur_id, ':fid2' => $fournisseur_id]);
        return (float) $stmt->fetchColumn();
    }

    /**
     * Liste tous les fournisseurs avec leurs totaux (factures, versements, solde)
     */
    public function getAllFournisseursAvecTotaux(): array
    {
        $sql = "SELECT
                    f.id_fournisseurs,
                    f.nom_fournisseurs,
                    f.telephone,
                    COALESCE(fc.total_facture, 0)   AS total_facture,
                    COALESCE(vf.total_verse, 0)     AS total_verse,
                    COALESCE(fc.total_facture, 0) - COALESCE(vf.total_verse, 0) AS solde
                FROM fournisseurs f
                LEFT JOIN (
                    SELECT fournisseur_id, SUM(total_ttc) AS total_facture
                    FROM factures_fournisseurs
                    WHERE statut = 'validated'
                    GROUP BY fournisseur_id
                ) fc ON fc.fournisseur_id = f.id_fournisseurs
                LEFT JOIN (
                    SELECT fournisseur_id, SUM(montant) AS total_verse
                    FROM versements_fournisseurs
                    WHERE statut != 'annule'
                    GROUP BY fournisseur_id
                ) vf ON vf.fournisseur_id = f.id_fournisseurs
                ORDER BY f.nom_fournisseurs ASC";

        return $this->db->query($sql)->fetchAll(PDO::FETCH_ASSOC);
    }
}
