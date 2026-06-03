<<<<<<< HEAD
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
    @media print {
        .btn, .btn-outline-primary, .btn-outline-secondary, .mb-3, .mt-2 { display: none !important; }
        .container, .container * { visibility: visible !important; }
        body { font-size: 13px; }
        .table th, .table td { font-size: 13px; }
    }
</style>
<div class="container">
    <h2 style="margin-top: 30px;">Relevé <?= htmlspecialchars($fournisseur['nom_fournisseurs']) ?></h2>
    <a href="index.php?action=fs/extrait&id=<?= $releve['id'] ?>" class="btn btn-secondary mb-3">Filtrer par période</a>
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
            <?php $cumul = 0; ?>
            <?php if (empty($lignes)): ?>
                <tr><td colspan="6" class="text-center text-muted">Aucune opération</td></tr>
            <?php else: ?>
                <?php foreach($lignes as $ligne): ?>
                    <?php $cumul += $ligne['montant'] - $ligne['versement']; ?>
                    <tr>
                        <td><?= date('d/m/Y', strtotime($ligne['date_operation'])) ?></td>
                        <td><?= htmlspecialchars($ligne['bc_fournisseur']) ?></td>
                        <td><?= htmlspecialchars($ligne['numero_facture']) ?></td>
                        <td><?= (empty($ligne['montant']) || $ligne['montant'] == 0) ? '' : number_format($ligne['montant'], 0, ',', ' ') ?></td>
                        <td style="font-weight: bold;"><?= (empty($ligne['versement']) || $ligne['versement'] == 0) ? '' : number_format($ligne['versement'], 0, ',', ' ') ?></td>
                        <td><?= number_format($cumul,0,',',' ') ?></td>
                    </tr>
                <?php endforeach;?>
            <?php endif;?>
        </tbody>
        <?php if (!empty($lignes)): ?>
        <tfoot>
            <tr>
                <th colspan="5" class="text-right">Total général :</th>
                <th><?= number_format($cumul, 0, ',', ' ') ?></th>
            </tr>
        </tfoot>
        <?php endif;?>
    </table>
    <a href="index.php?action=fs" class="btn btn-outline-primary mt-2">Retour à la liste</a>
    <button class="btn btn-outline-secondary mb-3" onclick="window.print()" style="margin-left:7px;background-color:#A3E3A1;"><i class="bi bi-printer"></i> Imprimer</button>
=======
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
    @media print {
        .btn, .btn-outline-primary, .btn-outline-secondary, .mb-3, .mt-2 { display: none !important; }
        .container, .container * { visibility: visible !important; }
        body { font-size: 13px; }
        .table th, .table td { font-size: 13px; }
    }
</style>
<div class="container">
    <h2 style="margin-top: 30px;">Relevé <?= htmlspecialchars($fournisseur['nom_fournisseurs']) ?></h2>
    <a href="index.php?action=fs/extrait&id=<?= $releve['id'] ?>" class="btn btn-secondary mb-3">Filtrer par période</a>
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
            <?php $cumul = 0; ?>
            <?php if (empty($lignes)): ?>
                <tr><td colspan="6" class="text-center text-muted">Aucune opération</td></tr>
            <?php else: ?>
                <?php foreach($lignes as $ligne): ?>
                    <?php $cumul += $ligne['montant'] - $ligne['versement']; ?>
                    <tr>
                        <td><?= date('d/m/Y', strtotime($ligne['date_operation'])) ?></td>
                        <td><?= htmlspecialchars($ligne['bc_fournisseur']) ?></td>
                        <td><?= htmlspecialchars($ligne['numero_facture']) ?></td>
                        <td><?= (empty($ligne['montant']) || $ligne['montant'] == 0) ? '' : number_format($ligne['montant'], 0, ',', ' ') ?></td>
                        <td style="font-weight: bold;"><?= (empty($ligne['versement']) || $ligne['versement'] == 0) ? '' : number_format($ligne['versement'], 0, ',', ' ') ?></td>
                        <td><?= number_format($cumul,0,',',' ') ?></td>
                    </tr>
                <?php endforeach;?>
            <?php endif;?>
        </tbody>
        <?php if (!empty($lignes)): ?>
        <tfoot>
            <tr>
                <th colspan="5" class="text-right">Total général :</th>
                <th><?= number_format($cumul, 0, ',', ' ') ?></th>
            </tr>
        </tfoot>
        <?php endif;?>
    </table>
    <a href="index.php?action=fs" class="btn btn-outline-primary mt-2">Retour à la liste</a>
    <button class="btn btn-outline-secondary mb-3" onclick="window.print()" style="margin-left:7px;background-color:#A3E3A1;"><i class="bi bi-printer"></i> Imprimer</button>
>>>>>>> 4f8dbbb6b83eb9c6f755d57287033c7da885a3b1
</div>