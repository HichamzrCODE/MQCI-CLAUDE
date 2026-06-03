<<<<<<< HEAD
<?php include '../views/layout.php'; ?>
<?php require_once __DIR__ . '/../../includes/permissions.php'; ?>

<div class="container mt-4">
    <h1 style="font-size:1.3rem;" class="mb-3">Liste des clients</h1>
    <div class="mb-2 d-flex justify-content-between align-items-center">
        <?php if (hasPermission('clients', 'create')): ?>
            <a href="index.php?action=clients/create" class="btn btn-success btn-sm">+ Client</a>
        <?php endif; ?>
        <span class="text-secondary" style="font-size:0.97rem;">Total : <b><?= count($clients) ?></b></span>
    </div>

    <div class="row mb-2">
        <div class="col-12 col-sm-8 col-md-6">
            <input type="text" id="client-search" class="form-control form-control-sm" placeholder="🔎 Rechercher un client...">
        </div>
    </div>

    <div class="table-responsive">
        <table class="table table-sm table-hover compact-table" id="clients-table" style="font-size:0.98rem;">
            <thead>
                <tr style="background:#e9f6e8;">
                    <th style="min-width:120px;">Nom</th>
                    <th style="min-width:95px;">Ville</th>
                    <th style="min-width:105px;">Téléphone</th>
                    <th style="min-width:105px;">Type</th>
                    <th style="width:1%;white-space:nowrap;">Actions</th>
                </tr>
            </thead>
            <tbody id="clients-table-body">
                <?php $alt = false; ?>
                <?php foreach ($clients as $client): ?>
                    <tr class="<?= $alt ? 'table-row-alt' : '' ?>">
                        <td><?= htmlspecialchars($client['nom']); ?></td>
                        <td><?= htmlspecialchars($client['ville']); ?></td>
                        <td><?= htmlspecialchars($client['telephone']); ?></td>
                        <td><?= htmlspecialchars($client['type_client'] === 'facture' ? 'Entreprise (Facture)' : 'Cash') ?></td>
                        <td class="actions-cell">
                            <div class="d-flex flex-nowrap gap-1">
                                <a href="index.php?action=clients/show&id=<?= $client['id_clients']; ?>" class="btn btn-info btn-sm px-2 py-0">Voir</a>
                                <?php if (hasPermission('clients', 'edit')): ?>
                                    <a href="index.php?action=clients/edit&id=<?= $client['id_clients']; ?>" class="btn btn-primary btn-sm px-2 py-0">✎</a>
                                <?php endif; ?>
                                <?php if (hasPermission('clients', 'delete')): ?>
                                    <a href="index.php?action=clients/delete&id=<?= $client['id_clients']; ?>" class="btn btn-danger btn-sm px-2 py-0">X</a>
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
input#client-search { font-size:0.98rem; }
@media (max-width: 600px) {
    .compact-table th, .compact-table td { font-size:0.92rem; }
}
</style>

<script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
<script>
$(function() {
    $('#client-search').on('input', function() {
        var term = $(this).val();
        $.ajax({
            url: 'index.php?action=clients/search',
            type: 'GET',
            data: { term: term },
            dataType: 'json',
            success: function (data) {
                var $tbody = $('#clients-table-body');
                $tbody.empty();
                if (data.length === 0) {
                    $tbody.append('<tr><td colspan="4" class="text-center text-muted">Aucun client trouvé</td></tr>');
                } else {
                    var alt = false;
                    $.each(data, function(i, client) {
                        var actions = '<div class="d-flex flex-nowrap gap-1">';
                        actions += '<a href="index.php?action=clients/show&id=' + client.id_clients + '" class="btn btn-info btn-sm px-2 py-0">Voir</a>';
                        if (client.editable)
                            actions += '<a href="index.php?action=clients/edit&id=' + client.id_clients + '" class="btn btn-primary btn-sm px-2 py-0">✎</a>';
                        if (client.deletable)
                            actions += '<a href="index.php?action=clients/delete&id=' + client.id_clients + '" class="btn btn-danger btn-sm px-2 py-0">X</a>';
                        actions += '</div>';
                        $tbody.append(
                            '<tr'+(alt?' class="table-row-alt"':'')+'>' +
                                '<td>' + $('<div>').text(client.nom).html() + '</td>' +
                                '<td>' + (client.ville ? $('<div>').text(client.ville).html() : '') + '</td>' +
                                '<td>' + (client.telephone ? $('<div>').text(client.telephone).html() : '') + '</td>' +
                                '<td>' + (client.type_client ? $('<div>').text(client.type_client).html() : '') + '</td>' +
                                '<td class="actions-cell">' + actions + '</td>' +
                            '</tr>'
                        );
                        alt = !alt;
                    });
                }
            }
        });
    });
});
=======
<?php include '../views/layout.php'; ?>
<?php require_once __DIR__ . '/../../includes/permissions.php'; ?>

<div class="container mt-4">
    <h1 style="font-size:1.3rem;" class="mb-3">Liste des clients</h1>
    <div class="mb-2 d-flex justify-content-between align-items-center">
        <?php if (hasPermission('clients', 'create')): ?>
            <a href="index.php?action=clients/create" class="btn btn-success btn-sm">+ Client</a>
        <?php endif; ?>
        <span class="text-secondary" style="font-size:0.97rem;">Total : <b><?= count($clients) ?></b></span>
    </div>

    <div class="row mb-2">
        <div class="col-12 col-sm-8 col-md-6">
            <input type="text" id="client-search" class="form-control form-control-sm" placeholder="🔎 Rechercher un client...">
        </div>
    </div>

    <div class="table-responsive">
        <table class="table table-sm table-hover compact-table" id="clients-table" style="font-size:0.98rem;">
            <thead>
                <tr style="background:#e9f6e8;">
                    <th style="min-width:120px;">Nom</th>
                    <th style="min-width:95px;">Ville</th>
                    <th style="min-width:105px;">Téléphone</th>
                    <th style="min-width:105px;">Type</th>
                    <th style="min-width:80px;">Délai (j)</th>
                    <?php if ($can_view_finance): ?>
                        <th style="min-width:110px;">Total facturisé</th>
                        <th style="min-width:100px;">Encours</th>
                        <th style="min-width:80px;">Statut</th>
                    <?php endif; ?>
                    <th style="min-width:100px;">Dernier devis</th>
                    <th style="width:1%;white-space:nowrap;">Actions</th>
                </tr>
            </thead>
            <tbody id="clients-table-body">
                <?php $alt = false; ?>
                <?php foreach ($clients as $client): ?>
                    <tr class="<?= $alt ? 'table-row-alt' : '' ?>">
                        <td><?= htmlspecialchars($client['nom']); ?></td>
                        <td><?= htmlspecialchars($client['ville']); ?></td>
                        <td><?= htmlspecialchars($client['telephone']); ?></td>
                        <td><?= htmlspecialchars($client['type_client'] === 'facture' ? 'Entreprise (Facture)' : 'Cash') ?></td>
                        <td><?= (int)($client['payment_delay'] ?? 30) ?> j</td>
                        <?php if ($can_view_finance): ?>
                            <td><?= number_format($client['total_facturise'] ?? 0, 0, ',', ' ') ?> FR</td>
                            <td><?= number_format($client['total_impaye'] ?? 0, 0, ',', ' ') ?> FR</td>
                            <td>
                                <?php if (($client['total_impaye'] ?? 0) > 0): ?>
                                    <?= ($client['en_retard'] ?? false) ? '<span class="badge-statut retard" title="En retard de paiement">🔴 En retard</span>' : '<span class="badge-statut ajour" title="Paiement à jour">🟢 À jour</span>' ?>
                                <?php else: ?>
                                    <span class="text-muted">—</span>
                                <?php endif; ?>
                            </td>
                        <?php endif; ?>
                        <td><?= $client['date_dernier_devis'] ? htmlspecialchars($client['date_dernier_devis']) : '<span class="text-muted">—</span>' ?></td>
                        <td class="actions-cell">
                            <div class="d-flex flex-nowrap gap-1">
                                <a href="index.php?action=clients/show&id=<?= $client['id_clients']; ?>" class="btn btn-info btn-sm px-2 py-0">Voir</a>
                                <?php if (hasPermission('clients', 'edit')): ?>
                                    <a href="index.php?action=clients/edit&id=<?= $client['id_clients']; ?>" class="btn btn-primary btn-sm px-2 py-0">✎</a>
                                <?php endif; ?>
                                <?php if (hasPermission('clients', 'delete')): ?>
                                    <a href="index.php?action=clients/delete&id=<?= $client['id_clients']; ?>" class="btn btn-danger btn-sm px-2 py-0">X</a>
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
input#client-search { font-size:0.98rem; }
.badge-statut { font-size: 0.88rem; white-space: nowrap; }
.badge-statut.retard { color: #c0392b; }
.badge-statut.ajour { color: #27ae60; }
@media (max-width: 600px) {
    .compact-table th, .compact-table td { font-size:0.92rem; }
}
</style>

<script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
<script>
var canViewFinance = <?= ($can_view_finance ?? false) ? 'true' : 'false' ?>;

$(function() {
    $('#client-search').on('input', function() {
        var term = $(this).val();
        $.ajax({
            url: 'index.php?action=clients/search',
            type: 'GET',
            data: { term: term },
            dataType: 'json',
            success: function (data) {
                var $tbody = $('#clients-table-body');
                $tbody.empty();
                if (data.length === 0) {
                    var colspan = canViewFinance ? 10 : 7;
                    $tbody.append('<tr><td colspan="' + colspan + '" class="text-center text-muted">Aucun client trouvé</td></tr>');
                } else {
                    var alt = false;
                    $.each(data, function(i, client) {
                        var actions = '<div class="d-flex flex-nowrap gap-1">';
                        actions += '<a href="index.php?action=clients/show&id=' + client.id_clients + '" class="btn btn-info btn-sm px-2 py-0">Voir</a>';
                        if (client.editable)
                            actions += '<a href="index.php?action=clients/edit&id=' + client.id_clients + '" class="btn btn-primary btn-sm px-2 py-0">✎</a>';
                        if (client.deletable)
                            actions += '<a href="index.php?action=clients/delete&id=' + client.id_clients + '" class="btn btn-danger btn-sm px-2 py-0">X</a>';
                        actions += '</div>';

                        var typeLabel = client.type_client === 'facture' ? 'Entreprise (Facture)' : 'Cash';
                        var delay = (client.payment_delay || 30) + ' j';
                        var dernierDevis = client.date_dernier_devis ? $('<div>').text(client.date_dernier_devis).html() : '<span class="text-muted">—</span>';

                        var financeHtml = '';
                        if (canViewFinance) {
                            var totalF = parseFloat(client.total_facturise || 0).toLocaleString('fr-FR', {minimumFractionDigits:2, maximumFractionDigits:2}) + ' DH';
                            var impaye = parseFloat(client.total_impaye || 0).toLocaleString('fr-FR', {minimumFractionDigits:2, maximumFractionDigits:2}) + ' DH';
                            var statut = '';
                            if ((client.total_impaye || 0) > 0) {
                                statut = client.en_retard
                                    ? '<span class="badge-statut retard" title="En retard de paiement">🔴 En retard</span>'
                                    : '<span class="badge-statut ajour" title="Paiement à jour">🟢 À jour</span>';
                            } else {
                                statut = '<span class="text-muted">—</span>';
                            }
                            financeHtml = '<td>' + totalF + '</td><td>' + impaye + '</td><td>' + statut + '</td>';
                        }

                        $tbody.append(
                            '<tr' + (alt ? ' class="table-row-alt"' : '') + '>' +
                                '<td>' + $('<div>').text(client.nom).html() + '</td>' +
                                '<td>' + (client.ville ? $('<div>').text(client.ville).html() : '') + '</td>' +
                                '<td>' + (client.telephone ? $('<div>').text(client.telephone).html() : '') + '</td>' +
                                '<td>' + $('<div>').text(typeLabel).html() + '</td>' +
                                '<td>' + delay + '</td>' +
                                financeHtml +
                                '<td>' + dernierDevis + '</td>' +
                                '<td class="actions-cell">' + actions + '</td>' +
                            '</tr>'
                        );
                        alt = !alt;
                    });
                }
            }
        });
    });
});
>>>>>>> 4f8dbbb6b83eb9c6f755d57287033c7da885a3b1
</script>