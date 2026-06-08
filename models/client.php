<?php

class Client {
    private $db;

    public function __construct(PDO $db) {
        $this->db = $db;
    }

    public function getAll(): array {
        $stmt = $this->db->query("SELECT * FROM clients ORDER BY nom ASC");
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function create(string $nom, string $ville, string $telephone, int $userId,
        string $typeClient, int $paymentDelay = 30, int $applyAirsi = 0, float $airsiRate = 5.00): int {
        $stmt = $this->db->prepare(
            "INSERT INTO clients (nom, ville, telephone, created_by, type_client, payment_delay, apply_airsi, airsi_rate, created_at)
             VALUES (?, ?, ?, ?, ?, ?, ?, ?, NOW())"
        );
        $stmt->execute([$nom, $ville, $telephone, $userId, $typeClient, $paymentDelay, $applyAirsi, $airsiRate]);
        return (int)$this->db->lastInsertId();
    }

    public function findById(int $id): ?array {
        $stmt = $this->db->prepare("SELECT * FROM clients WHERE id_clients = ?");
        $stmt->execute([$id]);
        return $stmt->fetch(PDO::FETCH_ASSOC) ?: null;
    }

    public function update(int $id, string $nom, string $ville, string $telephone,
        string $typeClient, int $paymentDelay = 30, int $applyAirsi = 0, float $airsiRate = 5.00): void {
        $stmt = $this->db->prepare(
            "UPDATE clients SET nom=?, ville=?, telephone=?, type_client=?,
             payment_delay=?, apply_airsi=?, airsi_rate=? WHERE id_clients=?"
        );
        $stmt->execute([$nom, $ville, $telephone, $typeClient, $paymentDelay, $applyAirsi, $airsiRate, $id]);
    }

    public function delete(int $id): void {
        $stmt = $this->db->prepare("DELETE FROM clients WHERE id_clients = ?");
        $stmt->execute([$id]);
    }

    public function searchByName(string $term): array {
        $stmt = $this->db->prepare("SELECT id_clients, nom FROM clients WHERE nom LIKE ? ORDER BY nom ASC LIMIT 15");
        $stmt->execute(['%' . $term . '%']);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function searchFull(string $term): array {
        $stmt = $this->db->prepare("SELECT * FROM clients WHERE nom LIKE ? ORDER BY nom ASC LIMIT 30");
        $stmt->execute(['%' . $term . '%']);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function existsByName(string $nom): bool {
        $stmt = $this->db->prepare("SELECT COUNT(*) FROM clients WHERE LOWER(nom) = LOWER(?)");
        $stmt->execute([$nom]);
        return $stmt->fetchColumn() > 0;
    }

    public function getDateDernierDevis(int $clientId): ?string {
        $stmt = $this->db->prepare("SELECT MAX(date) FROM devis WHERE client_id = ?");
        $stmt->execute([$clientId]);
        return $stmt->fetchColumn() ?: null;
    }

    public function getTotalFacturise(int $clientId): float {
        $stmt = $this->db->prepare("
            SELECT COALESCE(SUM(total_ttc), 0)
            FROM factures_clients
            WHERE client_id = ? AND statut = 'validated'
        ");
        $stmt->execute([$clientId]);
        return (float)$stmt->fetchColumn();
    }

    public function getTotalImpaye(int $clientId): float {
        $totalFacture = $this->getTotalFacturise($clientId);
        $stmt = $this->db->prepare("
            SELECT COALESCE(SUM(cl.versement), 0)
            FROM credit_lignes cl
            JOIN credit_releves cr ON cl.releve_id = cr.id
            WHERE cr.client_id = ? AND cl.versement > 0
        ");
        $stmt->execute([$clientId]);
        $totalVerse = (float)$stmt->fetchColumn();
        return max(0, $totalFacture - $totalVerse);
    }

    public function isEnRetard(int $clientId): bool {
        // En retard = au moins une facture échue (date + délai < aujourd'hui)
        $stmt = $this->db->prepare("
            SELECT COUNT(*)
            FROM factures_clients fc
            JOIN clients c ON c.id_clients = fc.client_id
            WHERE fc.client_id = ?
              AND fc.statut = 'validated'
              AND DATE_ADD(fc.date, INTERVAL c.payment_delay DAY) < CURDATE()
        ");
        $stmt->execute([$clientId]);
        return (int)$stmt->fetchColumn() > 0;
    }

    public function getCashRetard(): array {
        $sql = "SELECT c.id_clients, c.nom, MAX(cl.date_operation) AS last_versement
                FROM credit_lignes cl
                JOIN credit_releves cr ON cl.releve_id = cr.id
                JOIN clients c ON cr.client_id = c.id_clients
                WHERE c.type_client = 'cash' AND cl.versement > 0
                GROUP BY c.id_clients, c.nom
                HAVING DATEDIFF(NOW(), last_versement) > 30
                ORDER BY last_versement ASC";
        return $this->db->query($sql)->fetchAll(PDO::FETCH_ASSOC);
    }

    public function getCashSansCommande(): array {
        $sql = "SELECT c.id_clients, c.nom, MAX(cl.date_operation) AS last_operation
                FROM credit_lignes cl
                JOIN credit_releves cr ON cl.releve_id = cr.id
                JOIN clients c ON cr.client_id = c.id_clients
                WHERE c.type_client = 'cash' AND cl.montant > 0
                GROUP BY c.id_clients, c.nom
                HAVING DATEDIFF(NOW(), last_operation) > 14
                ORDER BY last_operation ASC";
        return $this->db->query($sql)->fetchAll(PDO::FETCH_ASSOC);
    }

    public function getEntSansCommande(): array {
        $sql = "SELECT c.id_clients, c.nom, MAX(cl.date_operation) AS last_operation
                FROM credit_lignes cl
                JOIN credit_releves cr ON cl.releve_id = cr.id
                JOIN clients c ON cr.client_id = c.id_clients
                WHERE c.type_client = 'facture' AND cl.montant > 0
                GROUP BY c.id_clients, c.nom
                HAVING DATEDIFF(NOW(), last_operation) > 30
                ORDER BY last_operation ASC";
        return $this->db->query($sql)->fetchAll(PDO::FETCH_ASSOC);
    }

    public function getDerniersDocumentsClient(int $clientId, int $limit = 5): array {
        $sql = "
            SELECT id, numero, 'devis' AS type_doc, date, statut, total_ttc, total, NULL AS date_echeance
            FROM devis WHERE client_id = ?

            UNION ALL

            SELECT fc.id, fc.numero, 'facture' AS type_doc, fc.date, fc.statut,
                   fc.total_ttc, NULL AS total,
                   DATE_ADD(fc.date, INTERVAL c.payment_delay DAY) AS date_echeance
            FROM factures_clients fc
            JOIN clients c ON c.id_clients = fc.client_id
            WHERE fc.client_id = ?

            ORDER BY date DESC, id DESC
            LIMIT ?
        ";
        $stmt = $this->db->prepare($sql);
        $stmt->bindValue(1, $clientId, PDO::PARAM_INT);
        $stmt->bindValue(2, $clientId, PDO::PARAM_INT);
        $stmt->bindValue(3, $limit, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function getDernierPrixParArticleClient(int $clientId, int $limit = 500): array {
        $sql = "
            WITH ranked AS (
                SELECT
                    a.id_articles AS article_id,
                    a.nom_art AS article,
                    dl.prix_unitaire,
                    d.numero AS doc_numero,
                    d.type_doc,
                    d.date AS doc_date,
                    ROW_NUMBER() OVER (
                        PARTITION BY dl.article_id
                        ORDER BY (d.type_doc='facture') DESC, d.date DESC, d.id DESC
                    ) AS rn
                FROM devis_lignes dl
                JOIN devis d ON d.id = dl.devis_id
                JOIN articles a ON a.id_articles = dl.article_id
                WHERE d.client_id = ?
                  AND d.type_doc IN ('devis', 'facture')
            )
            SELECT article_id, article, prix_unitaire, doc_numero, type_doc, doc_date
            FROM ranked WHERE rn = 1
            ORDER BY article ASC LIMIT ?
        ";
        $stmt = $this->db->prepare($sql);
        $stmt->bindValue(1, $clientId, PDO::PARAM_INT);
        $stmt->bindValue(2, $limit, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Résumé financier d'un client avec échéances
     */
    public function getFinanceResumeClient(int $clientId): array
    {
        // Total facturé
        $stmt = $this->db->prepare("
            SELECT COALESCE(SUM(total_ttc), 0)
            FROM factures_clients
            WHERE client_id = ? AND statut = 'validated'
        ");
        $stmt->execute([$clientId]);
        $totalFactures = (float)$stmt->fetchColumn();

        // Total versé
        $stmt = $this->db->prepare("
            SELECT COALESCE(SUM(cl.versement), 0)
            FROM credit_lignes cl
            JOIN credit_releves cr ON cl.releve_id = cr.id
            WHERE cr.client_id = ? AND cl.versement > 0
        ");
        $stmt->execute([$clientId]);
        $totalVerse = (float)$stmt->fetchColumn();

        // Solde global
        $solde = max(0, $totalFactures - $totalVerse);

        // Factures échues (date + délai < aujourd'hui)
        $stmt = $this->db->prepare("
            SELECT COALESCE(SUM(fc.total_ttc), 0)
            FROM factures_clients fc
            JOIN clients c ON c.id_clients = fc.client_id
            WHERE fc.client_id = ?
              AND fc.statut = 'validated'
              AND DATE_ADD(fc.date, INTERVAL c.payment_delay DAY) < CURDATE()
        ");
        $stmt->execute([$clientId]);
        $totalEchues = (float)$stmt->fetchColumn();

        // Factures non encore échues
        $stmt = $this->db->prepare("
            SELECT COALESCE(SUM(fc.total_ttc), 0)
            FROM factures_clients fc
            JOIN clients c ON c.id_clients = fc.client_id
            WHERE fc.client_id = ?
              AND fc.statut = 'validated'
              AND DATE_ADD(fc.date, INTERVAL c.payment_delay DAY) >= CURDATE()
        ");
        $stmt->execute([$clientId]);
        $totalNonEchues = (float)$stmt->fetchColumn();

        return [
            'total_factures'                       => $totalFactures,
            'total_factures_payees'                => $totalVerse,
            'total_factures_echues'                => $totalEchues,
            'total_factures_non_payees_pas_echues' => $totalNonEchues,
            'solde'                                => $solde,
        ];
    }

    public function getFacturesImpayeesParReleveSansTotalVersement(): array {
        $sql = "
            SELECT cr.*, c.nom,
                (SELECT SUM(cl.versement) FROM credit_lignes cl
                 WHERE cl.releve_id = cr.id AND cl.versement > 0) AS total_versements
            FROM credit_releves cr
            JOIN clients c ON cr.client_id = c.id_clients
            WHERE c.type_client = 'facture' AND cr.total_general > 0
            ORDER BY cr.created_at ASC";
        $releves = $this->db->query($sql)->fetchAll(PDO::FETCH_ASSOC);

        $result = [];
        foreach ($releves as $releve) {
            $reste = $releve['total_general'] - ($releve['total_versements'] ?? 0);
            if ($reste <= 0) continue;
            $stmtFact = $this->db->prepare("
                SELECT cl.*, c.nom, cr.client_id FROM credit_lignes cl
                JOIN credit_releves cr ON cl.releve_id = cr.id
                JOIN clients c ON cr.client_id = c.id_clients
                WHERE cl.releve_id = ? AND cl.montant > 0
                  AND cl.date_operation <= DATE_SUB(NOW(), INTERVAL 90 DAY)
                ORDER BY cl.date_operation ASC");
            $stmtFact->execute([$releve['id']]);
            foreach ($stmtFact->fetchAll(PDO::FETCH_ASSOC) as $facture) {
                if ($reste <= 0) break;
                $facture['reste_a_payer'] = min($facture['montant'], $reste);
                $result[] = $facture;
                $reste -= $facture['montant'];
            }
        }
        return $result;
    }
}
