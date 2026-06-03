<?php include '../views/layout.php'; ?>

<div class="container mt-4">
    <h1 class="mb-3" style="font-size:1.3rem;">Liste des articles</h1>
    <div class="mb-2 d-flex justify-content-between align-items-center flex-wrap gap-2">
        <div class="d-flex gap-2">
            <?php if (hasPermission('articles', 'create')): ?>
                <a href="index.php?action=articles/create" class="btn btn-success btn-sm">+ Article</a>
            <?php endif; ?>
            <?php if (hasPermission('articles', 'view')): ?>
                <a href="index.php?action=articles/export" class="btn btn-outline-secondary btn-sm">⬇ Export CSV</a>
            <?php endif; ?>
            <?php if (hasPermission('articles', 'create')): ?>
                <a href="index.php?action=articles/import" class="btn btn-outline-primary btn-sm">⬆ Import CSV</a>
            <?php endif; ?>
            <?php if (hasPermission('articles', 'view')): ?>
                <a href="index.php?action=depots" class="btn btn-outline-info btn-sm">🏭 Dépôts</a>
            <?php endif; ?>
        </div>
        <span class="text-secondary" style="font-size:0.97rem;">Total : <b><?= htmlspecialchars($totalArticles ?? 0); ?></b></span>
    </div>

    <div class="row mb-2">
        <div class="col-12 col-sm-8 col-md-5">
            <input type="text" id="article-search" class="form-control form-control-sm" placeholder="🔎 Rechercher par SKU, nom ou fournisseur...">
        </div>
    </div>

    <div class="table-responsive">
        <table class="table table-sm table-hover compact-table" id="articles-table" style="font-size:0.98rem;">
            <thead>
                <tr style="background:#e9f6e8;">
                    <th style="width:90px;">SKU</th>
                    <th style="min-width:140px;">Nom de l'article</th>
                    <th style="width:70px;" title="Cliquer pour voir le stock par dépôt">Qté</th>
                    <th style="width:80px;">Prix vente</th>
                    <th style="width:80px;">Prix rev.</th>
                    <th style="min-width:110px;">Fournisseur</th>
                    <th style="width:1%;white-space:nowrap;">Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php $alt = false; ?>
                <?php foreach ($articles as $article): ?>
                    <?php
                        $pr = $article['prix_revient_display'] ?? $article['prix_revient'] ?? $article['pr'] ?? 0;
                        $pv = $article['prix_vente'] ?? 0;
                        $qt = $article['quantite_totale'] ?? 0;
                        $statut = $article['statut'] ?? 'actif';
                        $rowClass = $alt ? 'table-row-alt' : '';
                        if ($statut === 'inactif') $rowClass .= ' text-muted';
                        if ($statut === 'discontinued') $rowClass .= ' article-discontinued';
                        $stockAlert = ($article['stock_minimal'] ?? 0) > 0 && $qt < ($article['stock_minimal'] ?? 0);
                    ?>
                    <tr class="<?= $rowClass ?>">
                        <td style="font-size:0.85rem;color:#666;"><?= htmlspecialchars($article['sku'] ?? ''); ?></td>
                        <td style="text-transform:uppercase;word-break:break-word;">
                            <?= htmlspecialchars($article['nom_art']); ?>
                            <?php if ($statut === 'discontinued'): ?><span class="badge badge-secondary" style="font-size:0.75rem;">Arrêté</span><?php endif; ?>
                            <?php if ($statut === 'inactif'): ?><span class="badge badge-warning" style="font-size:0.75rem;">Inactif</span><?php endif; ?>
                        </td>
                        <td>
                            <a href="#" class="btn-stock-popup text-decoration-none <?= $stockAlert ? 'text-danger font-weight-bold' : '' ?>"
                               data-id="<?= $article['id_articles']; ?>"
                               title="Voir stock par dépôt">
                                <?= (int)$qt; ?>
                                <?php if ($stockAlert): ?><span title="Stock sous le minimum !">⚠</span><?php endif; ?>
                            </a>
                        </td>
                        <td><?= $pv ? htmlspecialchars(number_format($pv, 0, '', ' ')) : '<span class="text-muted">-</span>'; ?></td>
                        <td><?= htmlspecialchars(number_format($pr, 0, '', ' ')); ?></td>
                        <td><?= htmlspecialchars($article['nom_fournisseurs'] ?? ''); ?></td>
                        <td class="actions-cell">
                            <div class="d-flex flex-nowrap gap-1">
                                <?php if (hasPermission('articles', 'view')): ?>
                                    <a href="index.php?action=articles/show&id=<?= $article['id_articles']; ?>" class="btn btn-info btn-sm px-2 py-0" title="Voir détail">👁</a>
                                <?php endif; ?>
                                <?php if (hasPermission('articles', 'edit')): ?>
                                    <a href="index.php?action=articles/edit&id=<?= $article['id_articles']; ?>" class="btn btn-primary btn-sm px-2 py-0" title="Modifier">✎</a>
                                <?php endif; ?>
                                <?php if (hasPermission('articles', 'delete')): ?>
                                    <a href="index.php?action=articles/delete&id=<?= $article['id_articles']; ?>"
                                       class="btn btn-danger btn-sm px-2 py-0"
                                       title="Supprimer"
                                       onclick="return confirm('Supprimer cet article ?')">✕</a>
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

<!-- Modal stock par dépôt -->
<div class="modal fade" id="stockModal" tabindex="-1" role="dialog" aria-labelledby="stockModalLabel" aria-hidden="true">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="stockModalLabel">Stock par dépôt</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Fermer">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body" id="stockModalBody">
                <div class="text-center"><div class="spinner-border spinner-border-sm" role="status"></div> Chargement...</div>
            </div>
        </div>
    </div>
</div>

<style>
.compact-table th, .compact-table td {
    vertical-align: middle; padding: 5px 8px; font-size: 0.97rem; background: #fff;
}
.compact-table thead th { background: #e9f6e8; color: #295b33; font-weight: 600; }
.compact-table tbody tr.table-row-alt td { background: #f8fdf7 !important; }
.compact-table tbody tr td, .compact-table tbody tr.table-row-alt td { border-bottom: 1px solid #e1e1e1; }
.compact-table .actions-cell { white-space: nowrap; }
.compact-table .btn-sm { font-size: 0.92rem; min-width: 28px; }
.article-discontinued td { opacity: 0.6; }
@media (max-width: 600px) {
    .compact-table th, .compact-table td { font-size:0.92rem; }
}
</style>

<script>
function formatPrix(val) {
    let n = parseFloat(val);
    if (isNaN(n)) return '';
    return n.toLocaleString('fr-FR', {minimumFractionDigits: 0, maximumFractionDigits: 0});
}

function buildRow(article, alt) {
    var pr = article.prix_revient_display || article.prix_revient || article.pr || 0;
    var pv = article.prix_vente || 0;
    var qt = parseInt(article.quantite_totale) || 0;
    var statut = article.statut || 'actif';
    var rowClass = alt ? 'table-row-alt' : '';
    if (statut === 'inactif') rowClass += ' text-muted';
    if (statut === 'discontinued') rowClass += ' article-discontinued';
    var stockMin = parseInt(article.stock_minimal) || 0;
    var stockAlert = stockMin > 0 && qt < stockMin;

    var skuHtml = article.sku ? $('<div>').text(article.sku).html() : '';
    var nomHtml = $('<div>').text(article.nom_art).html();
    if (statut === 'discontinued') nomHtml += ' <span class="badge badge-secondary" style="font-size:.75rem;">Arrêté</span>';
    if (statut === 'inactif') nomHtml += ' <span class="badge badge-warning" style="font-size:.75rem;">Inactif</span>';

    var qtHtml = '<a href="#" class="btn-stock-popup text-decoration-none ' + (stockAlert ? 'text-danger font-weight-bold' : '') + '" data-id="' + article.id_articles + '" title="Voir stock par dépôt">' + qt + (stockAlert ? ' <span title="Stock sous le minimum !">⚠</span>' : '') + '</a>';
    var pvHtml = pv ? formatPrix(pv) : '<span class="text-muted">-</span>';

    var actions = '<div class="d-flex flex-nowrap gap-1">';
    if (article.viewable)  actions += '<a href="index.php?action=articles/show&id=' + article.id_articles + '" class="btn btn-info btn-sm px-2 py-0" title="Voir détail">👁</a>';
    if (article.editable)  actions += '<a href="index.php?action=articles/edit&id=' + article.id_articles + '" class="btn btn-primary btn-sm px-2 py-0" title="Modifier">✎</a>';
    if (article.deletable) actions += '<a href="index.php?action=articles/delete&id=' + article.id_articles + '" class="btn btn-danger btn-sm px-2 py-0" title="Supprimer" onclick="return confirm(\'Supprimer cet article ?\')">✕</a>';
    actions += '</div>';

    return '<tr class="' + rowClass + '">' +
        '<td style="font-size:.85rem;color:#666;">' + skuHtml + '</td>' +
        '<td style="text-transform:uppercase;word-break:break-word;">' + nomHtml + '</td>' +
        '<td>' + qtHtml + '</td>' +
        '<td>' + pvHtml + '</td>' +
        '<td>' + (pr ? formatPrix(pr) : '') + '</td>' +
        '<td>' + (article.nom_fournisseurs ? $('<div>').text(article.nom_fournisseurs).html() : '') + '</td>' +
        '<td class="actions-cell">' + actions + '</td>' +
        '</tr>';
}

$('#article-search').on('input', function() {
    var term = $(this).val();
    $.ajax({
        url: 'index.php?action=articles/search',
        type: 'GET',
        data: { term: term },
        dataType: 'json',
        success: function(data) {
            var $tbody = $('#articles-table tbody');
            $tbody.empty();
            if (data.length === 0) {
                $tbody.append('<tr><td colspan="7" class="text-center text-muted">Aucun article trouvé</td></tr>');
            } else {
                var alt = false;
                $.each(data, function(i, article) {
                    $tbody.append(buildRow(article, alt));
                    alt = !alt;
                });
            }
        }
    });
});

// Popup stock par dépôt
$(document).on('click', '.btn-stock-popup', function(e) {
    e.preventDefault();
    var id = $(this).data('id');
    $('#stockModalBody').html('<div class="text-center"><div class="spinner-border spinner-border-sm" role="status"></div> Chargement...</div>');
    $('#stockModal').modal('show');
    $.ajax({
        url: 'index.php?action=articles/stock-par-depot&id=' + id,
        type: 'GET',
        dataType: 'json',
        success: function(data) {
            if (!data || data.length === 0) {
                $('#stockModalBody').html('<p class="text-muted text-center">Aucun stock enregistré dans un dépôt.</p>');
                return;
            }
            var html = '<table class="table table-sm">' +
                '<thead><tr><th>Dépôt</th><th>Ville</th><th>Qté</th><th>Transit</th><th>Bloquée</th><th>Empl.</th></tr></thead><tbody>';
            $.each(data, function(i, s) {
                html += '<tr>' +
                    '<td>' + $('<div>').text(s.depot_nom).html() + '</td>' +
                    '<td>' + (s.depot_ville ? $('<div>').text(s.depot_ville).html() : '-') + '</td>' +
                    '<td><strong>' + (parseInt(s.quantite) || 0) + '</strong></td>' +
                    '<td>' + (parseInt(s.quantite_en_transit) || 0) + '</td>' +
                    '<td>' + (parseInt(s.quantite_bloquee) || 0) + '</td>' +
                    '<td>' + (s.emplacement ? $('<div>').text(s.emplacement).html() : '-') + '</td>' +
                    '</tr>';
            });
            html += '</tbody></table>';
            $('#stockModalBody').html(html);
        },
        error: function() {
            $('#stockModalBody').html('<p class="text-danger">Erreur lors du chargement.</p>');
        }
    });
});
</script>