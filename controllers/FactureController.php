<?php

require_once __DIR__ . '/../includes/permissions.php';
require_once __DIR__ . '/../models/Facture.php';
require_once __DIR__ . '/../models/FactureClientLigne.php';
require_once __DIR__ . '/../models/Livraison.php';

class FactureController {
    private PDO $db;
    private Facture $factureModel;
    private FactureClientLigne $factureLigneModel;
    private Livraison $livraisonModel;

    public function __construct(PDO $db) {
        $this->db = $db;
        $this->factureModel = new Facture($db);
        $this->factureLigneModel = new FactureClientLigne($db);
        $this->livraisonModel = new Livraison($db);
    }

    public function index(array $get = []): array {
        if (function_exists('hasPermission') && !hasPermission('factures', 'view')) {
            die("Accès refusé.");
        }

        $factures = $this->factureModel->listAll(200);

        return [
            'view' => 'factures/index',
            'data' => ['factures' => $factures]
        ];
    }

    private function generateNumeroFacture(): string {
        $annee = date('Y');
        $dernier = $this->factureModel->getLastNumeroFacture($annee);

        $seq = 1;
        if ($dernier && preg_match('/(\d+)$/', $dernier, $m)) {
            $seq = (int)$m[1] + 1;
        }

        return sprintf('FAC-%s-%03d', $annee, $seq);
    }

    public function createFromBl(int $blId): void {
        if (function_exists('hasPermission') && !hasPermission('factures', 'create')) {
            die("Accès refusé.");
        }

        $existing = $this->factureModel->findBySourceBlId($blId);
        if ($existing) {
            header("Location: index.php?action=factures/edit&id=" . (int)$existing['id']);
            exit();
        }

        $bl = $this->livraisonModel->findById($blId);
        if (!$bl) die("BL introuvable.");
        if (($bl['statut'] ?? 'draft') !== 'validated') {
            die("BL non validé : impossible de facturer.");
        }

        $userId = (int)($_SESSION['user_id'] ?? 0);
        if (!$userId) die("Utilisateur non authentifié.");

        $clientId = (int)$bl['client_id'];
        $devisId = (int)$bl['devis_id'];

        $tvaRate = 18.00;
        $tvaCoef = 1 + ($tvaRate / 100);

        $airsiCfg = $this->factureModel->getClientAirsiConfig($clientId);
        $applyAirsi = $airsiCfg['apply_airsi'];
        $airsiRate = $airsiCfg['airsi_rate'];

        $numero = $this->generateNumeroFacture();

        $this->db->beginTransaction();
        try {
            $factureId = $this->factureModel->createHeader(
                $numero,
                $clientId,
                date('Y-m-d'),
                $userId,
                $devisId,
                $blId,
                $tvaRate,
                $applyAirsi,
                $airsiRate
            );

            $blLignes = $this->livraisonModel->getLignes($blId);

            $ordre = 1;
            $totalHt = 0.0;
            $totalTtc = 0.0;

            foreach ($blLignes as $ln) {
                $qte = (int)($ln['quantite_livree'] ?? 0);
                if ($qte <= 0) continue;

                $puTtc = (float)($ln['prix_unitaire'] ?? 0);
                $puHt = round($puTtc / $tvaCoef, 2);
                $prRefTtc = $puTtc;

                $lineTotalHt = round($qte * $puHt, 2);
                $lineTotalTtc = round($qte * $puTtc, 2);

                $this->factureLigneModel->create(
                    $factureId,
                    (int)$ln['article_id'],
                    $qte,
                    $puHt,
                    $puTtc,
                    $prRefTtc,
                    $lineTotalHt,
                    $lineTotalTtc,
                    $ordre++,
                    $ln['description'] ?? null
                );

                $totalHt += $lineTotalHt;
                $totalTtc += $lineTotalTtc;
            }

            $totalHt = round($totalHt, 2);
            $totalTva = round($totalHt * ($tvaRate / 100), 2);
            $totalAirsi = $applyAirsi ? round($totalHt * ($airsiRate / 100), 2) : 0.0;
            $totalTtcFinal = round($totalHt + $totalTva + $totalAirsi, 2);

            $this->factureModel->updateTotals($factureId, $totalHt, $totalTva, $totalAirsi, $totalTtcFinal);

            $this->db->commit();

            header("Location: index.php?action=factures/edit&id=" . $factureId);
            exit();
        } catch (Throwable $e) {
            if ($this->db->inTransaction()) {
                $this->db->rollBack();
            }
            die("Erreur création facture : " . $e->getMessage());
        }
    }

    public function edit(int $id, array $post = []): array {
        if (function_exists('hasPermission') && !hasPermission('factures', 'edit')) {
            die("Accès refusé.");
        }

        $facture = $this->factureModel->findById($id);
        if (!$facture) {
            return ['view' => 'error', 'data' => ['message' => 'Facture introuvable.']];
        }

        $lignes = $this->factureLigneModel->getByFactureId($id, true);

        $isValidated = (($facture['statut'] ?? 'draft') === 'validated');
        $isAdmin = (($_SESSION['role'] ?? '') === 'admin');
        $readonly = ($isValidated && !$isAdmin);

        return [
            'view' => 'factures/edit',
            'data' => [
                'facture' => $facture,
                'lignes' => $lignes,
                'readonly' => $readonly
            ]
        ];
    }

    public function show(int $id): array {
        if (function_exists('hasPermission') && !hasPermission('factures', 'view')) {
            die("Accès refusé.");
        }

        $facture = $this->factureModel->findById($id);
        if (!$facture) {
            return ['view' => 'error', 'data' => ['message' => 'Facture introuvable.']];
        }

        $lignes = $this->factureLigneModel->getByFactureId($id, true);

        return [
            'view' => 'factures/show',
            'data' => [
                'facture' => $facture,
                'lignes' => $lignes
            ]
        ];
    }

    public function validate(int $id): void {
        if (function_exists('hasPermission') && !hasPermission('factures', 'edit')) {
            die("Accès refusé.");
        }

        $facture = $this->factureModel->findById($id);
        if (!$facture) die("Facture introuvable.");

        if (($facture['statut'] ?? 'draft') === 'validated') {
            header("Location: index.php?action=factures/show&id=" . $id);
            exit();
        }

        $userId = (int)($_SESSION['user_id'] ?? 0);
        if (!$userId) die("Utilisateur non authentifié.");

        $this->factureModel->setValidated($id, $userId);

        header("Location: index.php?action=factures/show&id=" . $id);
        exit();
    }
}