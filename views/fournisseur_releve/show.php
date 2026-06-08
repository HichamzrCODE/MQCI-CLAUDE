<?php include '../views/layout.php'; ?>
<style>
.releve-header    { font-size: 1.18rem; font-weight: 600; margin-top: 22px; margin-bottom: 4px; }
.releve-subtitle  { font-size: 0.95rem; color: #6c757d; margin-bottom: 14px; }
.releve-dates     { display: flex; flex-wrap: wrap; align-items: flex-end; gap: 10px; margin-bottom: 14px; }
.releve-dates label { font-size: 0.93rem; color: #555; margin-bottom: 2px; display: block; }
.releve-dates input[type="date"] { font-size: 0.96rem; padding: 3px 7px; border: 1px solid #ccc; border-radius: 3px; }

.releve-table     { width: 100%; border-collapse: collapse; font-size: 0.97rem; }
.releve-table th  { background: #FFF4E5; color: #7a3e00; font-weight: 600; padding: 6px 8px; border: 1px solid #f0d8b0; white-space: nowrap; }
.releve-table td  { padding: 5px 8px; border: 1px solid #e2e2e2; vertical-align: middle; white-space: nowrap; }
.releve-table tbody tr:nth-child(odd) td { background: #fffaf4; }
.releve-table tbody tr:hover td          { background: #fff3e0; }
.releve-table .td-ref  { max-width: 160px; overflow: hidden; text-overflow: ellipsis; white-space: nowrap; }
.releve-table .td-num  { text-align: right; }
.releve-table .row-solde-ouv td { background: #fff8dc !important; font-style: italic; color: #7a6000; }
.releve-table tfoot td, .releve-table tfoot th { background: #fff0d6; font-weight: bold; padding: 6px 8px; border: 1px solid #f0d8b0; }

.releve-pagination { display: flex; align-items: center; gap: 8px; margin-top: 12px; flex-wrap: wrap; }
.releve-pagination a, .releve-pagination span { font-size: 0.95rem; padding: 4px 12px; border-radius: 4px; border: 1px solid #ccc; text-decoration: none; color: #7a3e00; background: #fff; }
.releve-pagination a:hover  { background: #fff0d6; }
.releve-pagination .active  { background: #c0620a; color: #fff; border-color: #c0620a; pointer-events: none; }
.releve-pagination .disabled { color: #aaa; pointer-events: none; background: #f5f5f5; }

.releve-total-general { margin-top: 10px; font-size: 1.01rem; }
.badge-facture   { display: inline-block; font-size: 0.78rem; padding: 1px 6px; border-radius: 3px; background: #ffe0b2; color: #7a3e00; }
.badge-versement { display: inline-block; font-size: 0.78rem; padding: 1px 6px; border-radius: 3px; background: #cce5ff; color: #004085; }

@media print {
    .no-print { display: none !important; }
    .releve-table th, .releve-table td { font-size: 12px; padding: 3px 5px; }
    .releve-header { font-size: 1rem; }
}
@media (max-width: 600px) {
    .releve-table { font-size: 0.88rem; }
    .releve-dates { gap: 6px; }
}
</style>

<div class="container" style="max-width:960px; padding-top:20px;">

    <div class="releve-header">
        Relevé fournisseur — <?= htmlspecialchars($fournisseur['nom_fournisseurs']) ?>
    </div>
    <div class="releve-subtitle">
        <?= htmlspecialchars($fournisseur['ville'] ?? '') ?>
        <?php if (!empty($fournisseur['telephone'])): ?>
            · <?= htmlspecialchars($fournisseur['telephone']) ?>
        <?php endif; ?>
        <?php if (!empty($fournisseur['email'])): ?>
            · <?= htmlspecialchars($fournisseur['email']) ?>
        <?php endif; ?>
    </div>

    <!-- Filtre par dates -->
    <form method="get" class="releve-dates no-print mb-3" id="releve-filter-form">
        <input type="hidden" name="action" value="fournisseur_releve/show">
        <input type="hidden" name="id"     value="<?= (int)$fournisseur_id ?>">
        <input type="hidden" name="page"   value="1">
        <div>
            <label>Date début</label>
            <input type="date" name="date_debut"
                   value="<?= htmlspecialchars($date_debut ?? '') ?>"
                   class="form-control form-control-sm" style="display:inline-block;width:auto;">
        </div>
        <div>
            <label>Date fin</label>
            <input type="date" name="date_fin"
                   value="<?= htmlspecialchars($date_fin ?? '') ?>"
                   class="form-control form-control-sm" style="display:inline-block;width:auto;">
        </div>
        <div style="align-self:flex-end;">
            <button type="submit" class="btn btn-warning btn-sm">Afficher</button>
            <?php if ($date_debut || $date_fin): ?>
                <a href="index.php?action=fournisseur_releve/show&id=<?= (int)$fournisseur_id ?>"
                   class="btn btn-outline-secondary btn-sm">Tout afficher</a>
            <?php endif; ?>
        </div>
    </form>

    <?php if ($total_lignes === 0): ?>
        <div class="alert alert-info">
            Aucune opération trouvée<?= ($date_debut || $date_fin) ? ' sur cette période' : '' ?>.
        </div>
    <?php else: ?>

    <div class="table-responsive">
        <table class="releve-table">
            <thead>
                <tr>
                    <th style="width:95px;">Date</th>
                    <th>Référence</th>
                    <th>Type</th>
                    <th class="td-num" style="min-width:110px;">Débit (Facture)</th>
                    <th class="td-num" style="min-width:110px;">Crédit (Versement)</th>
                    <th class="td-num" style="min-width:110px;">Solde</th>
                </tr>
            </thead>
            <tbody>
                <?php
                $show_solde_row = ($solde_avant_page != 0 || ($date_debut && $page === 1));
                if ($show_solde_row):
                    $label_solde = ($page > 1) ? 'Solde reporté' : "Solde d'ouverture";
                ?>
                <tr class="row-solde-ouv">
                    <td></td>
                    <td colspan="4"><em><?= htmlspecialchars($label_solde) ?></em></td>
                    <td class="td-num"><?= number_format($solde_avant_page, 0, ',', ' ') ?></td>
                </tr>
                <?php endif; ?>

                <?php
                $cumul = $solde_avant_page;
                foreach ($lignes as $ligne):
                    $montant   = (float)$ligne['montant'];
                    $versement = (float)$ligne['versement'];
                    $cumul    += $montant - $versement;
                    $type      = $ligne['type'];
                    $solde_color = $cumul > 0 ? '#c0620a' : ($cumul < 0 ? '#155724' : '#333');
                ?>
                <tr>
                    <td><?= date('d/m/Y', strtotime($ligne['date_operation'])) ?></td>
                    <td class="td-ref" title="<?= htmlspecialchars($ligne['reference'] ?? '') ?>">
                        <?= htmlspecialchars($ligne['reference'] ?? '') ?>
                    </td>
                    <td>
                        <?php if ($type === 'facture'): ?>
                            <span class="badge-facture">Facture</span>
                        <?php else: ?>
                            <span class="badge-versement">Versement</span>
                        <?php endif; ?>
                    </td>
                    <td class="td-num">
                        <?= $montant > 0 ? number_format($montant, 0, ',', ' ') : '' ?>
                    </td>
                    <td class="td-num" style="font-weight:<?= $versement > 0 ? 'bold' : 'normal' ?>;">
                        <?= $versement > 0 ? number_format($versement, 0, ',', ' ') : '' ?>
                    </td>
                    <td class="td-num" style="color:<?= $solde_color ?>; font-weight:600;">
                        <?= number_format($cumul, 0, ',', ' ') ?>
                    </td>
                </tr>
                <?php endforeach; ?>
            </tbody>
            <tfoot>
                <tr>
                    <td colspan="5" style="text-align:right;">
                        Solde fin de page (<?= $page ?>/<?= $total_pages ?>) :
                    </td>
                    <td class="td-num"><?= number_format($cumul, 0, ',', ' ') ?></td>
                </tr>
            </tfoot>
        </table>
    </div>

    <!-- Pagination -->
    <?php if ($total_pages > 1): ?>
    <nav class="releve-pagination no-print" aria-label="Pagination relevé fournisseur">
        <?php
        $base_url = 'index.php?action=fournisseur_releve/show&id=' . (int)$fournisseur_id
            . ($date_debut ? '&date_debut=' . urlencode($date_debut) : '')
            . ($date_fin   ? '&date_fin='   . urlencode($date_fin)   : '');
        ?>
        <?php if ($page > 1): ?>
            <a href="<?= $base_url ?>&page=1">&laquo; Début</a>
            <a href="<?= $base_url ?>&page=<?= $page - 1 ?>">&#8249; Préc.</a>
        <?php else: ?>
            <span class="disabled">&laquo; Début</span>
            <span class="disabled">&#8249; Préc.</span>
        <?php endif; ?>

        <span class="active">Page <?= $page ?> / <?= $total_pages ?></span>
        <span class="text-muted" style="font-size:0.9rem;">
            (<?= $total_lignes ?> opération<?= $total_lignes > 1 ? 's' : '' ?>)
        </span>

        <?php if ($page < $total_pages): ?>
            <a href="<?= $base_url ?>&page=<?= $page + 1 ?>">Suiv. &#8250;</a>
            <a href="<?= $base_url ?>&page=<?= $total_pages ?>">Fin &raquo;</a>
        <?php else: ?>
            <span class="disabled">Suiv. &#8250;</span>
            <span class="disabled">Fin &raquo;</span>
        <?php endif; ?>
    </nav>
    <?php endif; ?>

    <!-- Solde global -->
    <div class="releve-total-general mt-3">
        <strong>Solde global (toutes dates) :</strong>
        <span style="font-size:1.08rem; font-weight:700; color:<?= $total_general > 0 ? '#c0620a' : '#155724' ?>;">
            <?= number_format($total_general, 0, ',', ' ') ?>
        </span>
        <?php if ($total_general > 0): ?>
            <span class="text-muted" style="font-size:0.9rem;">&nbsp;— Reste à payer au fournisseur</span>
        <?php elseif ($total_general < 0): ?>
            <span class="text-muted" style="font-size:0.9rem;">&nbsp;— Fournisseur vous doit</span>
        <?php else: ?>
            <span class="text-muted" style="font-size:0.9rem;">&nbsp;— Compte soldé</span>
        <?php endif; ?>
    </div>

    <?php endif; ?>

    <!-- Actions -->
    <div class="no-print mt-3 mb-4 d-flex gap-2 flex-wrap">
        <a href="index.php?action=fournisseurs"
           class="btn btn-outline-secondary btn-sm">Liste fournisseurs</a>
        <button class="btn btn-outline-secondary btn-sm"
                onclick="window.print()" style="background:#FFD59E;">
            🖨️ Imprimer
        </button>
    </div>

</div>
