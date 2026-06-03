<?php
include '../views/layout.php';
require_once __DIR__ . '/../../includes/permissions.php';
?>

<div class="container mt-4">
    <h1 class="mb-3" style="font-size:1.3rem;">Liste des fournisseurs</h1>
    <div class="mb-2 d-flex justify-content-between align-items-center">
        <?php if (hasPermission('fournisseurs', 'create')): ?>
            <a href="index.php?action=fournisseurs/create" class="btn btn-success btn-sm">+ Fournisseur</a>
        <?php endif; ?>
        <span class="text-secondary" style="font-size:0.97rem;">Total : <b><?= count($data['fournisseurs']); ?></b></span>
    </div>

    <div class="row mb-2">
        <div class="col-12 col-sm-8 col-md-6">
            <input type="text" id="fournisseur-search" class="form-control form-control-sm" placeholder="🔎 Rechercher un fournisseur...">
        </div>
    </div>

    <div class="table-responsive">
        <table class="table table-sm table-hover compact-table" id="fournisseurs-table" style="font-size:0.98rem;">
            <thead>
                <tr style="background:#e9f6e8;">
                    <th style="min-width:130px;">Nom</th>
                    <th style="min-width:130px;">Email</th>
                    <th style="min-width:110px;">Téléphone</th>
                    <?php if ($can_view_finance): ?>
                        <th style="min-width:90px;">Nb articles</th>
                        <th style="min-width:110px;">Prix moyen</th>
                    <?php endif; ?>
                    <th style="width:1%;white-space:nowrap;">Actions</th>
                </tr>
            </thead>
            <tbody id="fournisseurs-table-body">
                <?php $alt = false; ?>
                <?php foreach ($data['fournisseurs'] as $fournisseur): ?>
                    <tr class="<?= $alt ? 'table-row-alt' : '' ?>">
                        <td><?= htmlspecialchars($fournisseur['nom_fournisseurs']); ?></td>
                        <td><?= $fournisseur['email'] ? htmlspecialchars($fournisseur['email']) : '<span class="text-muted">—</span>' ?></td>
                        <td><?= $fournisseur['telephone'] ? htmlspecialchars($fournisseur['telephone']) : '<span class="text-muted">—</span>' ?></td>
                        <?php if ($can_view_finance): ?>
                            <td><?= (int)($fournisseur['nb_articles'] ?? 0) ?></td>
                            <td><?= number_format($fournisseur['prix_moyen'] ?? 0, 0, ',', ' ') ?> FR</td>
                        <?php endif; ?>
                        <td class="actions-cell">
                            <div class="d-flex flex-nowrap gap-1">
                                <?php if (hasPermission('fournisseurs', 'edit')): ?>
                                    <a href="index.php?action=fournisseurs/edit&id=<?= $fournisseur['id_fournisseurs']; ?>" class="btn btn-primary btn-sm px-2 py-0">✎</a>
                                <?php endif; ?>
                                <?php if (hasPermission('fournisseurs', 'delete')): ?>
                                    <a href="index.php?action=fournisseurs/delete&id=<?= $fournisseur['id_fournisseurs']; ?>" class="btn btn-danger btn-sm px-2 py-0">X</a>
                                <?php endif; ?>
                            </div>
                        </td>
                    </tr>
                    <?php $alt = !$alt; ?>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</div>

<style>
.compact-table th, .compact-table td {
    vertical-align: middle;
    padding: 5px 8px;
    font-size: 0.97rem;
    background: #fff;
}
.compact-table thead th { background: #e9f6e8; color: #295b33; font-weight: 600; }
.compact-table tbody tr.table-row-alt td { background: #f8fdf7 !important; }
.compact-table tbody tr td, .compact-table tbody tr.table-row-alt td { border-bottom: 1px solid #e1e1e1; }
.compact-table .actions-cell { white-space: nowrap; }
.compact-table .btn-sm { font-size: 0.92rem; min-width: 28px; }
input#fournisseur-search { font-size:0.98rem; }
@media (max-width: 600px) {
    .compact-table th, .compact-table td { font-size:0.92rem; }
}
</style>

<script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
<script>
var canViewFinance = <?= ($can_view_finance ?? false) ? 'true' : 'false' ?>;

$('#fournisseur-search').on('input', function() {
    var term = $(this).val();
    $.ajax({
        url: 'index.php?action=fournisseurs/search',
        type: 'GET',
        data: { term: term },
        dataType: 'json',
        success: function (data) {
            var $tbody = $('#fournisseurs-table-body');
            $tbody.empty();
            if (data.length === 0) {
                var colspan = canViewFinance ? 6 : 4;
                $tbody.append('<tr><td colspan="' + colspan + '" class="text-center text-muted">Aucun fournisseur trouvé</td></tr>');
            } else {
                var alt = false;
                $.each(data, function(i, f) {
                    var actions = '<div class="d-flex flex-nowrap gap-1">';
                    if (f.editable)
                        actions += '<a href="index.php?action=fournisseurs/edit&id=' + f.id_fournisseurs + '" class="btn btn-primary btn-sm px-2 py-0">✎</a>';
                    if (f.deletable)
                        actions += '<a href="index.php?action=fournisseurs/delete&id=' + f.id_fournisseurs + '" class="btn btn-danger btn-sm px-2 py-0">✖</a>';
                    actions += '</div>';

                    var email = f.email ? $('<div>').text(f.email).html() : '<span class="text-muted">—</span>';
                    var telephone = f.telephone ? $('<div>').text(f.telephone).html() : '<span class="text-muted">—</span>';

                    var financeHtml = '';
                    if (canViewFinance) {
                        var nbArticles = (f.nb_articles || 0);
                        var prixMoyen = parseFloat(f.prix_moyen || 0).toLocaleString('fr-FR', {minimumFractionDigits:2, maximumFractionDigits:2}) + ' DH';
                        financeHtml = '<td>' + nbArticles + '</td><td>' + prixMoyen + '</td>';
                    }

                    $tbody.append(
                        '<tr' + (alt ? ' class="table-row-alt"' : '') + '>' +
                            '<td>' + $('<div>').text(f.nom_fournisseurs).html() + '</td>' +
                            '<td>' + email + '</td>' +
                            '<td>' + telephone + '</td>' +
                            financeHtml +
                            '<td class="actions-cell">' + actions + '</td>' +
                        '</tr>'
                    );
                    alt = !alt;
                });
            }
        }
    });
});
</script>