<?php

class VersementFournisseur {
    private PDO $db;

    public function __construct(PDO $db) {
        $this->db = $db;
    }

    private function normalizeAmount(string $v): float {
        $v = preg_replace('/[\s\xA0]/u', '', $v);
        $v = str_replace(',', '.', $v);
        return (float)$v;
    }

    private function modeToCode(string $mode): string {
        return match ($mode) {
            'especes' => 'DSP',
            'virement' => 'VFP',
            'cheque' => 'CFP',
            'mobile_money' => 'MFP',
            'depot_especes_banque' => 'DBP',
            default => throw new InvalidArgumentException("Mode invalide."),
        };
    }

    private function nextNumeroLocked(string $mode, int $year): string {
        $code = $this->modeToCode($mode);

        $stmt = $this->db->prepare("SELECT last_seq FROM numerotation WHERE code=? AND annee=? FOR UPDATE");
        $stmt->execute([$code, $year]);
        $last = $stmt->fetchColumn();

        if ($last === false) {
            $stmtIns = $this->db->prepare("INSERT INTO numerotation (code, annee, last_seq) VALUES (?,?,0)");
            $stmtIns->execute([$code, $year]);
            $last = 0;
        }

        $next = ((int)$last) + 1;
        $stmtUp = $this->db->prepare("UPDATE numerotation SET last_seq=? WHERE code=? AND annee=?");
        $stmtUp->execute([$next, $code, $year]);

        return sprintf("%s%d-%06d", $code, $year, $next);
    }

    public function create(array $data, int $userId): int {
        $fournisseurId = (int)($data['fournisseur_id'] ?? 0);
        $date = trim((string)($data['date'] ?? date('Y-m-d')));
        $mode = trim((string)($data['mode'] ?? ''));
        $statut = trim((string)($data['statut'] ?? ''));

        $montant = $this->normalizeAmount((string)($data['montant'] ?? '0'));
        $reference = trim((string)($data['reference'] ?? ''));
        $banque = trim((string)($data['banque'] ?? ''));
        $etablissementPayeur = trim((string)($data['etablissement_payeur'] ?? ''));
        $note = trim((string)($data['note'] ?? ''));

        if ($fournisseurId <= 0) throw new RuntimeException("Fournisseur obligatoire.");
        if ($date === '') throw new RuntimeException("Date obligatoire.");
        if ($montant <= 0) throw new RuntimeException("Montant invalide.");

        $modes = ['especes', 'cheque', 'virement', 'mobile_money', 'depot_especes_banque'];
        if (!in_array($mode, $modes, true)) throw new RuntimeException("Mode invalide.");

        $effectueDirect = ['especes', 'virement', 'mobile_money', 'depot_especes_banque'];
        if (in_array($mode, $effectueDirect, true)) {
            $statut = 'effectue';
        } else {
            $allowed = ['en_attente', 'effectue', 'rejete', 'annule'];
            if ($statut === '' || !in_array($statut, $allowed, true)) {
                $statut = 'en_attente';
            }
        }

        if ($mode === 'cheque') {
            if ($reference === '') throw new RuntimeException("Numéro de chèque obligatoire.");
            if ($banque === '') throw new RuntimeException("Banque obligatoire pour un chèque.");
            if ($etablissementPayeur === '') throw new RuntimeException("Établissement payeur obligatoire.");
        } else {
            $etablissementPayeur = '';
        }

        if (in_array($mode, ['virement', 'mobile_money'], true) && $reference === '') {
            throw new RuntimeException("Référence obligatoire pour virement / mobile money.");
        }

        if ($mode === 'depot_especes_banque') {
            if ($banque === '') throw new RuntimeException("Banque obligatoire pour un dépôt espèces banque.");
            if ($reference === '') throw new RuntimeException("Référence bordereau obligatoire.");
        }

        $startedTx = false;
        if (!$this->db->inTransaction()) {
            $this->db->beginTransaction();
            $startedTx = true;
        }

        try {
            $year = (int)date('Y', strtotime($date));
            $numero = $this->nextNumeroLocked($mode, $year);

            $stmt = $this->db->prepare(
                "INSERT INTO versements_fournisseurs
                 (numero, fournisseur_id, date, montant, mode, statut, reference, banque, etablissement_payeur, note, created_by)
                 VALUES (?,?,?,?,?,?,?,?,?,?,?)"
            );
            $stmt->execute([
                $numero,
                $fournisseurId,
                $date,
                $montant,
                $mode,
                $statut,
                ($reference !== '' ? $reference : null),
                ($banque !== '' ? $banque : null),
                ($etablissementPayeur !== '' ? $etablissementPayeur : null),
                ($note !== '' ? $note : null),
                $userId
            ]);

            $id = (int)$this->db->lastInsertId();

            if ($startedTx) $this->db->commit();
            return $id;

        } catch (Throwable $e) {
            if ($startedTx && $this->db->inTransaction()) $this->db->rollBack();
            throw $e;
        }
    }

    public function getAll(int $limit = 200): array {
        $stmt = $this->db->prepare(
            "SELECT vf.*, f.nom_fournisseurs AS fournisseur_nom
             FROM versements_fournisseurs vf
             JOIN fournisseurs f ON f.id_fournisseurs = vf.fournisseur_id
             WHERE vf.hidden = 0
             ORDER BY vf.id DESC
             LIMIT ?"
        );
        $stmt->bindValue(1, $limit, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function findById(int $id): ?array {
        $stmt = $this->db->prepare(
            "SELECT vf.*, f.nom_fournisseurs AS fournisseur_nom
             FROM versements_fournisseurs vf
             JOIN fournisseurs f ON f.id_fournisseurs = vf.fournisseur_id
             WHERE vf.id = ?"
        );
        $stmt->execute([$id]);
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        return $row ?: null;
    }

    public function update(int $id, array $data, int $userId): void {
        $current = $this->findById($id);
        if (!$current) throw new RuntimeException("Versement fournisseur introuvable.");

        $date = trim((string)($data['date'] ?? ''));
        $montant = $this->normalizeAmount((string)($data['montant'] ?? '0'));
        $statut = trim((string)($data['statut'] ?? ''));
        $reference = trim((string)($data['reference'] ?? ''));
        $banque = trim((string)($data['banque'] ?? ''));
        $etablissementPayeur = trim((string)($data['etablissement_payeur'] ?? ''));
        $note = trim((string)($data['note'] ?? ''));

        if ($date === '') throw new RuntimeException("Date obligatoire.");
        if ($montant <= 0) throw new RuntimeException("Montant invalide.");

        $allowed = ['en_attente', 'effectue', 'rejete', 'annule'];
        if (!in_array($statut, $allowed, true)) throw new RuntimeException("Statut invalide.");

        if (($current['mode'] ?? '') === 'cheque' && $etablissementPayeur === '') {
            throw new RuntimeException("Établissement payeur obligatoire.");
        }

        $stmt = $this->db->prepare(
            "UPDATE versements_fournisseurs
             SET date=?, montant=?, statut=?, reference=?, banque=?, etablissement_payeur=?, note=?, updated_by=?, updated_at=NOW()
             WHERE id=?"
        );
        $stmt->execute([
            $date,
            $montant,
            $statut,
            ($reference !== '' ? $reference : null),
            ($banque !== '' ? $banque : null),
            ($etablissementPayeur !== '' ? $etablissementPayeur : null),
            ($note !== '' ? $note : null),
            $userId,
            $id
        ]);
    }

    public function cancel(int $id, int $userId): void {
        $current = $this->findById($id);
        if (!$current) throw new RuntimeException("Versement fournisseur introuvable.");

        $stmt = $this->db->prepare(
            "UPDATE versements_fournisseurs
             SET statut='annule', updated_by=?, updated_at=NOW()
             WHERE id=?"
        );
        $stmt->execute([$userId, $id]);
    }

    public function delete(int $id): void {
        $current = $this->findById($id);
        if (!$current) return;

        $stmt = $this->db->prepare("DELETE FROM versements_fournisseurs WHERE id=?");
        $stmt->execute([$id]);
    }

    public function hide(int $id, int $userId): void {
        $current = $this->findById($id);
        if (!$current) throw new RuntimeException("Versement fournisseur introuvable.");

        $stmt = $this->db->prepare(
            "UPDATE versements_fournisseurs
             SET hidden = 1, updated_by = ?, updated_at = NOW()
             WHERE id = ?"
        );
        $stmt->execute([$userId, $id]);
    }
}