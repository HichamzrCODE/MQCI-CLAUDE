<?php include '../views/layout.php'; ?>
<style>
    .table th, .table td { 
        vertical-align: middle; 
        white-space: nowrap; 
        padding: 4px 6px; 
        font-size: 0.98rem;
    }
    .table th { background: #F4FAF2; }
    .table th:nth-child(2), .table td:nth-child(2) { width: 65px; }
    .table th:nth-child(3), .table td:nth-child(3) { width: 78px; }
    .table th:last-child, .table td:last-child { min-width: 90px; width: 100px; text-align: right; }
    .table-striped>tbody>tr:nth-child(odd)>td { background: #f8fdf7; }
    .table-bordered th, .table-bordered td { border: 1px solid #dee2e6; }
    .btn, .btn-outline-primary, .btn-outline-secondary { font-size: 0.93rem; padding: 0.22rem 0.7rem; }
    h2 { font-size: 1.21rem; margin-bottom: 1.3rem; margin-top: 1.1rem;}
    .mt-2, .mb-3 { margin-top: 0.6rem !important; margin-bottom: 0.6rem !important; }
    /* Style pour les champs date */
    .releve-dates {
        margin-bottom: 0.7rem;
        margin-top: 0.7rem;
        display: flex;
        align-items: flex-end;
        gap: 12px;
    }
    .releve-date-field label {
        font-size: 0.95rem;
        color: #555;
        margin-bottom: 2px;
        margin-right: 4px;
        display: block;
    }
    .releve-date-field input[type="date"] {
        font-size: 0.98rem;
        padding: 2px 6px;
        margin: 0;
        min-width: 120px;
        max-width: 180px;
        border-radius: 3px;
        border: 1px solid #d2d2d2;
    }
    @media (max-width: 600px) {
        .releve-dates { flex-direction: column; gap: 3px; }
        .releve-date-field input[type="date"] { min-width: 90px; max-width: 100%; }
    }
    @media print {
        .btn, .btn-outline-primary, .btn-outline-secondary, .mb-3, .mt-2 { display: none !important; }
        .container, .container * { visibility: visible !important; }
        body { font-size: 13px; }
        .table th, .table td { font-size: 13px; }
    }
</style>
<div class="container">
    <h2 style="margin-top:30px;">Relevé <?= htmlspecialchars($fournisseur['nom_fournisseurs']) ?> <span class="text-secondary" style="font-size:1rem;">(Extrait)</span></h2>
    <form method="get" class="releve-dates mb-3 row g-2">
        <input type="hidden" name="action" value="fs/extrait">
        <input type="hidden" name="id" value="<?= $releve['id'] ?>">
        <div class="col-auto releve-date-field">
            <label>Date début</label>
            <input type="date" name="date_debut" value="<?= htmlspecialchars($date_debut) ?>" class="form-control form-control-sm">
        </div>
        <div class="col-auto releve-date-field">
            <label>Date fin</label>
            <input type="date" name="date_fin" value="<?= htmlspecialchars($date_fin) ?>" class="form-control form-control-sm">
        </div>
        <div class="col-auto align-self-end">
            <button type="submit" class="btn btn-primary btn-sm">Afficher</button>
        </div>
    </form>
    <table class="table table-bordered table-striped">
        <thead>
            <tr>
                <th>Date</th>
                <th>BC F/S</th>
                <th>N° FACTURE</th>
                <th>Montant</th>
                <th>Versement</th>
                <th>Total</th>
            </tr>
        </thead>
        <tbody>
            <?php $cumul = $solde_ouverture; ?>
            <?php if (empty($lignes)): ?>
                <tr><td colspan="6" class="text-center text-muted">Aucune opération sur cette période</td></tr>
            <?php else: ?>
                <?php foreach($lignes as $ligne): ?>
                    <?php $cumul += $ligne['montant'] - $ligne['versement']; ?>
                    <tr>
                        <td><?= date('d/m/Y', strtotime($ligne['date_operation'])) ?></td>
                        <td><?= htmlspecialchars($ligne['bc_fournisseur']) ?></td>
                        <td><?= htmlspecialchars($ligne['numero_facture']) ?></td>
                        <td><?= (empty($ligne['montant']) || $ligne['montant'] == 0) ? '' : number_format($ligne['montant'], 0, ',', ' ') ?></td>
                        <td style="font-weight: bold;"><?= (empty($ligne['versement']) || $ligne['versement'] == 0) ? '' : number_format($ligne['versement'], 0, ',', ' ') ?></td>
                        <td><?= number_format($cumul, 0, ',', ' ') ?></td>
                    </tr>
                <?php endforeach;?>
            <?php endif;?>
        </tbody>
        <tfoot>
            <tr>
                <th colspan="5" class="text-right">Solde périodique :</th>
                <th><?= number_format($cumul, 0, ',', ' ') ?></th>
            </tr>
        </tfoot>
    </table>
    <div class="text-end mt-2">
        <strong>Solde général (global) :</strong>
        <span class="text-primary"><?= number_format($total_general, 0, ',', ' ') ?></span>
    </div>
    <a href="index.php?action=fs/show&id=<?= $releve['id'] ?>" class="btn btn-outline-primary mt-2">Voir tout le relevé</a>
    <a href="index.php?action=fs" class="btn btn-outline-secondary mt-2">Retour à la liste</a>
    <button class="btn btn-outline-secondary mb-3" onclick="window.print()" style="margin-left:7px;background-color:#A3E3A1;"><i class="bi bi-printer"></i> Imprimer</button>
</div>