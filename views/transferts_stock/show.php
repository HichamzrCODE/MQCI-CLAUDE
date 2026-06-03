<?php include '../views/layout.php'; ?>

<div class="container-fluid mt-4">

    <?php if (isset($_GET['success'])): ?>
        <div class="alert alert-success alert-dismissible fade show">
            <i class="fas fa-check-circle"></i>
            <?php if ($_GET['success'] === 'valide'): ?>
                Transfert validé avec succès. Les stocks ont été mis à jour.
            <?php else: ?>
                Opération effectuée avec succès.
            <?php endif; ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    <?php endif; ?>

    <?php if (isset($_GET['error'])): ?>
        <div class="alert alert-danger alert-dismissible fade show">
            <i class="fas fa-exclamation-circle"></i> <?= htmlspecialchars($_GET['error']) ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    <?php endif; ?>

    <!-- En-tête -->
    <div class="row mb-3">
        <div class="col-md-8">
            <h2><i class="fas fa-exchange-alt"></i> Transfert <?= htmlspecialchars($transfert['numero']) ?></h2>
            <?php
            $statutClass = match($transfert['statut']) {
                'valide'   => 'bg-success',
                'en_cours' => 'bg-warning text-dark',
                default    => 'bg-secondary',
            };
            $statutLabel = match($transfert['statut']) {
                'valide'   => 'Validé',
                'en_cours' => 'En cours',
                default    => 'Brouillon',
            };
            ?>
            <span class="badge <?= $statutClass ?> fs-6"><?= $statutLabel ?></span>
        </div>
        <div class="col-md-4 text-end">
            <a href="index.php?action=transferts_stock" class="btn btn-secondary btn-sm">
                <i class="fas fa-arrow-left"></i> Retour à la liste
            </a>
        </div>
    </div>

    <!-- Informations générales -->
    <div class="row mb-4">
        <div class="col-md-6">
            <div class="card">
                <div class="card-body">
                    <table class="table table-sm mb-0">
                        <tr>
                            <th class="w-50">Numéro :</th>
                            <td><?= htmlspecialchars($transfert['numero']) ?></td>
                        </tr>
                        <tr>
                            <th>Dépôt Source :</th>
                            <td><strong><?= htmlspecialchars($transfert['depot_source_nom'] ?? '') ?></strong></td>
                        </tr>
                        <tr>
                            <th>Dépôt Destination :</th>
                            <td><strong><?= htmlspecialchars($transfert['depot_destination_nom'] ?? '') ?></strong></td>
                        </tr>
                        <tr>
                            <th>Date :</th>
                            <td><?= htmlspecialchars($transfert['date_transfert']) ?></td>
                        </tr>
                        <tr>
                            <th>Créé par :</th>
                            <td><?= htmlspecialchars($transfert['user_nom'] ?? '') ?></td>
                        </tr>
                        <?php if (!empty($transfert['description'])): ?>
                        <tr>
                            <th>Description :</th>
                            <td><?= nl2br(htmlspecialchars($transfert['description'])) ?></td>
                        </tr>
                        <?php endif; ?>
                    </table>
                </div>
            </div>
        </div>
        <div class="col-md-6">
            <?php if ($transfert['statut'] === 'brouillon' && hasPermission('transferts_stock', 'edit')): ?>
                <div class="card border-warning">
                    <div class="card-header bg-warning text-dark">
                        <i class="fas fa-info-circle"></i> Actions disponibles
                    </div>
                    <div class="card-body">
                        <!-- Ajouter une ligne -->
                        <button type="button" class="btn btn-primary w-100 mb-2"
                                data-bs-toggle="modal" data-bs-target="#modalAddLigne">
                            <i class="fas fa-plus"></i> Ajouter un article
                        </button>

                        <!-- Valider -->
                        <?php if (!empty($lignes)): ?>
                        <form method="POST" action="index.php?action=transferts_stock/valider"
                              onsubmit="return confirm('Valider ce transfert ? Cette action est irréversible.');">
                            <?= $csrf_field ?>
                            <input type="hidden" name="transfert_id" value="<?= $transfert['id'] ?>">
                            <button type="submit" class="btn btn-success w-100 mb-2">
                                <i class="fas fa-check-circle"></i> Valider le transfert
                            </button>
                        </form>
                        <?php endif; ?>

                        <!-- Supprimer -->
                        <?php if (hasPermission('transferts_stock', 'delete')): ?>
                        <form method="POST" action="index.php?action=transferts_stock/delete"
                              onsubmit="return confirm('Supprimer ce transfert ?');">
                            <?= $csrf_field ?>
                            <input type="hidden" name="transfert_id" value="<?= $transfert['id'] ?>">
                            <button type="submit" class="btn btn-outline-danger w-100">
                                <i class="fas fa-trash"></i> Supprimer
                            </button>
                        </form>
                        <?php endif; ?>
                    </div>
                </div>
            <?php elseif ($transfert['statut'] === 'valide'): ?>
                <div class="alert alert-success">
                    <i class="fas fa-check-circle"></i>
                    Ce transfert a été validé. Les mouvements de stock ont été enregistrés.
                </div>
            <?php endif; ?>
        </div>
    </div>

    <!-- Lignes -->
    <div class="card">
        <div class="card-header">
            <i class="fas fa-list"></i> Articles à transférer (<?= count($lignes) ?>)
        </div>
        <div class="table-responsive">
            <table class="table table-sm mb-0" id="lignesTable">
                <thead class="table-light">
                    <tr>
                        <th>Article</th>
                        <th>SKU</th>
                        <th class="text-center">Stock Source</th>
                        <th class="text-center">Quantité à transférer</th>
                        <?php if ($transfert['statut'] === 'brouillon'): ?>
                            <th class="text-end">Action</th>
                        <?php endif; ?>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($lignes)): ?>
                        <tr>
                            <td colspan="5" class="text-center text-muted py-3">
                                Aucun article. Cliquez sur "Ajouter un article" pour commencer.
                            </td>
                        </tr>
                    <?php else: ?>
                        <?php foreach ($lignes as $ligne): ?>
                            <tr>
                                <td><?= htmlspecialchars($ligne['nom_art'] ?? '') ?></td>
                                <td><small class="text-muted"><?= htmlspecialchars($ligne['sku'] ?? '') ?></small></td>
                                <td class="text-center">
                                    <span class="badge bg-info"><?= (int)$ligne['stock_source'] ?></span>
                                </td>
                                <td class="text-center">
                                    <strong><?= (int)$ligne['quantite'] ?></strong>
                                </td>
                                <?php if ($transfert['statut'] === 'brouillon'): ?>
                                    <td class="text-end">
                                        <form method="POST" action="index.php?action=transferts_stock/removeLigne"
                                              style="display:inline;"
                                              onsubmit="return confirm('Supprimer cette ligne ?');">
                                            <?= $csrf_field ?>
                                            <input type="hidden" name="ligne_id" value="<?= $ligne['id'] ?>">
                                            <input type="hidden" name="transfert_id" value="<?= $transfert['id'] ?>">
                                            <button type="submit" class="btn btn-sm btn-outline-danger">
                                                <i class="fas fa-times"></i>
                                            </button>
                                        </form>
                                    </td>
                                <?php endif; ?>
                            </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<!-- Modal Ajout Article -->
<?php if ($transfert['statut'] === 'brouillon'): ?>
<div class="modal fade" id="modalAddLigne" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title"><i class="fas fa-plus"></i> Ajouter un article</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <div class="mb-3">
                    <input type="text" id="searchArticle" class="form-control"
                           placeholder="Rechercher un article par nom ou SKU...">
                </div>
                <div id="articleResults"></div>
            </div>
        </div>
    </div>
</div>

<script>
const transfertId   = <?= (int)$transfert['id'] ?>;
const depotSourceId = <?= (int)$transfert['depot_source_id'] ?>;
const csrfToken     = '<?= addslashes(CsrfMiddleware::getToken()) ?>';

document.getElementById('searchArticle').addEventListener('input', function() {
    const term = this.value.trim();
    if (term.length < 1) {
        document.getElementById('articleResults').innerHTML = '';
        return;
    }
    fetch('index.php?action=transferts_stock/searchArticles&term=' + encodeURIComponent(term) + '&depot_id=' + depotSourceId)
        .then(r => r.json())
        .then(articles => {
            let html = '<table class="table table-sm table-hover">';
            html += '<thead><tr><th>Article</th><th>SKU</th><th class="text-center">Stock dispo</th><th>Quantité</th><th></th></tr></thead><tbody>';
            if (articles.length === 0) {
                html += '<tr><td colspan="5" class="text-center text-muted">Aucun résultat</td></tr>';
            }
            articles.forEach(a => {
                html += `<tr>
                    <td>${escapeHtml(a.nom_art)}</td>
                    <td><small class="text-muted">${escapeHtml(a.sku || '')}</small></td>
                    <td class="text-center"><span class="badge bg-info">${a.stock_depot || 0}</span></td>
                    <td><input type="number" class="form-control form-control-sm qte-input" data-id="${a.id_articles}"
                               min="1" max="${a.stock_depot || 0}" value="1" style="width:80px;"></td>
                    <td>
                        <button class="btn btn-sm btn-primary btn-add-ligne"
                                data-id="${a.id_articles}" data-nom="${escapeHtml(a.nom_art)}"
                                data-stock="${a.stock_depot || 0}">
                            <i class="fas fa-plus"></i>
                        </button>
                    </td>
                </tr>`;
            });
            html += '</tbody></table>';
            document.getElementById('articleResults').innerHTML = html;

            document.querySelectorAll('.btn-add-ligne').forEach(btn => {
                btn.addEventListener('click', function() {
                    const articleId = this.dataset.id;
                    const stock = parseInt(this.dataset.stock);
                    const qteInput = document.querySelector(`.qte-input[data-id="${articleId}"]`);
                    const quantite = parseInt(qteInput.value);

                    if (quantite <= 0 || quantite > stock) {
                        alert('Quantité invalide. Stock disponible : ' + stock);
                        return;
                    }

                    const formData = new FormData();
                    formData.append('csrf_token', csrfToken);
                    formData.append('transfert_id', transfertId);
                    formData.append('article_id', articleId);
                    formData.append('quantite', quantite);

                    fetch('index.php?action=transferts_stock/addLigne', {
                        method: 'POST',
                        body: formData
                    })
                    .then(r => r.json())
                    .then(resp => {
                        if (resp.success) {
                            location.reload();
                        } else {
                            alert(resp.message || 'Erreur lors de l\'ajout.');
                        }
                    });
                });
            });
        });
});

function escapeHtml(text) {
    const map = {'&':'&amp;','<':'&lt;','>':'&gt;','"':'&quot;',"'":'&#039;'};
    return String(text).replace(/[&<>"']/g, m => map[m]);
}
</script>
<?php endif; ?>