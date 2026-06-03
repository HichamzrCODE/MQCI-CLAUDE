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
            <input type="text" id="client-search" class="form-control form-control-sm" placeholder="Rechercher un client...">
        </div>
    </div>

    <div class="table-responsive">
        <table class="table table-sm table-hover compact-table" id="clients-table">
            <thead>
                <tr style="background:#e9f6e8;">
                    <th style="min-width:70px;">ID</th>
                    <th style="min-width:180px;">Nom</th>
                    <th style="min-width:120px;">Ville</th>
                    <th style="min-width:130px;">Téléphone</th>
                    <th style="min-width:150px;">Type</th>
                    <th style="width:1%;white-space:nowrap;">Actions</th>
                </tr>
            </thead>
            <tbody id="clients-table-body">
                <?php $alt = false; ?>
                <?php foreach ($clients as $client): ?>
                    <tr class="<?= $alt ? 'table-row-alt' : '' ?>">
                        <td>#<?= (int)$client['id_clients']; ?></td>
                        <td><?= htmlspecialchars($client['nom']); ?></td>
                        <td><?= htmlspecialchars($client['ville']); ?></td>
                        <td><?= htmlspecialchars($client['telephone']); ?></td>
                        <td><?= htmlspecialchars(($client['type_client'] ?? '') === 'facture' ? 'Entreprise (Facture)' : 'Cash') ?></td>
                        <td class="actions-cell">
                            <div class="d-flex flex-nowrap gap-1">
                                <a href="index.php?action=clients/show&id=<?= (int)$client['id_clients']; ?>"
                                   class="btn btn-outline-info btn-sm px-2 py-0"
                                   title="Détails">ℹ</a>
                                <a href="index.php?action=client_releve/show&id=<?= $client['id_clients']; ?>" class="btn btn-success btn-sm px-2 py-0" title="Relevé client">📄</a>

                                <?php if (hasPermission('clients', 'edit')): ?>
                                    <a href="index.php?action=clients/edit&id=<?= (int)$client['id_clients']; ?>"
                                       class="btn btn-primary btn-sm px-2 py-0"
                                       title="Modifier">✎</a>
                                <?php endif; ?>

                                <?php if (hasPermission('clients', 'delete')): ?>
                                    <a href="index.php?action=clients/delete&id=<?= (int)$client['id_clients']; ?>"
                                       class="btn btn-danger btn-sm px-2 py-0"
                                       title="Supprimer"
                                       onclick="return confirm('Supprimer ce client ?');">X</a>
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
    white-space: nowrap;
}
.compact-table thead th { background: #e9f6e8; color: #295b33; font-weight: 600; }
.compact-table tbody tr.table-row-alt td { background: #f8fdf7 !important; }
.compact-table tbody tr td, .compact-table tbody tr.table-row-alt td { border-bottom: 1px solid #e1e1e1; }
.compact-table .actions-cell { white-space: nowrap; }
.compact-table .btn-sm { font-size: 0.92rem; min-width: 28px; }
input#client-search { font-size:0.98rem; }
</style>

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
                    $tbody.append('<tr><td colspan="6" class="text-center text-muted">Aucun client trouvé</td></tr>');
                    return;
                }

                var alt = false;
                $.each(data, function(i, client) {
                    var typeLabel = client.type_client === 'facture' ? 'Entreprise (Facture)' : 'Cash';

                    var actions = '<div class="d-flex flex-nowrap gap-1">';
                    actions += '<a href="index.php?action=clients/show&id=' + client.id_clients + '" class="btn btn-outline-info btn-sm px-2 py-0" title="Détails">ℹ</a>';
                    if (client.editable) actions += '<a href="index.php?action=clients/edit&id=' + client.id_clients + '" class="btn btn-primary btn-sm px-2 py-0" title="Modifier">✎</a>';
                    if (client.deletable) actions += '<a href="index.php?action=clients/delete&id=' + client.id_clients + '" class="btn btn-danger btn-sm px-2 py-0" title="Supprimer">X</a>';
                    actions += '</div>';

                    $tbody.append(
                        '<tr' + (alt ? ' class="table-row-alt"' : '') + '>' +
                            '<td>#' + client.id_clients + '</td>' +
                            '<td>' + $('<div>').text(client.nom || '').html() + '</td>' +
                            '<td>' + (client.ville ? $('<div>').text(client.ville).html() : '') + '</td>' +
                            '<td>' + (client.telephone ? $('<div>').text(client.telephone).html() : '') + '</td>' +
                            '<td>' + $('<div>').text(typeLabel).html() + '</td>' +
                            '<td class="actions-cell">' + actions + '</td>' +
                        '</tr>'
                    );
                    alt = !alt;
                });
            }
        });
    });
});
</script>