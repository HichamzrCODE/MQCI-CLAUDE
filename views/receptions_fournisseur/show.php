<?php include '../views/layout.php'; ?>

<div class="container-fluid mt-4">

    <?php if (isset($_GET['success'])): ?>
        <div class="alert alert-success alert-dismissible fade show">
            <i class="fas fa-check-circle"></i>
            <?php if ($_GET['success'] === 'validee'): ?>
                Réception validée avec succès. Les stocks ont été mis à jour.
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
            <h2><i class="fas fa-truck-loading"></i> Réception <?= htmlspecialchars($reception['numero']) ?></h2>
            <?php
            $statutClass = match($reception['statut']) {
                'validee' => 'bg-success',
                'recue'   => 'bg-info',
                default   => 'bg-secondary',
            };
            $statutLabel = match($reception['statut']) {
                'validee' => 'Validée',
                'recue'   => 'Reçue',
                default   => 'Brouillon',
            };
            ?>
            <span class="badge <?= $statutClass ?> fs-6"><?= $statutLabel ?></span>
        </div>
        <div class="col-md-4 text-end">
            <a href="index.php?action=receptions_fournisseur" class="btn btn-secondary btn-sm">
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
                            <td><?= htmlspecialchars($reception['numero']) ?></td>
                        </tr>
                        <tr>
                            <th>Fournisseur :</th>
                            <td><strong><?= htmlspecialchars($reception['nom_fournisseurs'] ?? '') ?></strong></td>
                        </tr>
                        <tr>
                            <th>Dépôt :</th>
                            <td><strong><?= htmlspecialchars($reception['depot_nom'] ?? '') ?></strong></td>
                        </tr>
                        <tr>
                            <th>Date :</th>
                            <td><?= htmlspecialchars($reception['date_reception']) ?></td>
                        </tr>
                        <tr>
                            <th>Créé par :</th>
                            <td><?= htmlspecialchars($reception['user_nom'] ?? '') ?></td>
                        </tr>
                        <?php if (!empty($reception['description'])): ?>
                        <tr>
                            <th>Description :</th>
                            <td><?= nl2br(htmlspecialchars($reception['description'])) ?></td>
                        </tr>
                        <?php endif; ?>
                    </table>
                </div>
            </div>
        </div>
        <div class="col-md-6">
            <?php if ($reception['statut'] !== 'validee' && hasPermission('receptions_fournisseur', 'edit')): ?>
                <div class="card border-info">
                    <div class="card-header bg-info text-white">
                        <i class="fas fa-info-circle"></i> Actions disponibles
                    </div>
                    <div class="card-body">
                        <?php if ($reception['statut'] === 'brouillon'): ?>
                            <!-- Ajouter une ligne -->
                            <button type="button" class="btn btn-primary w-100 mb-2"
                                    data-bs-toggle="modal" data-bs-target="#modalAddLigne">
                                <i class="fas fa-plus"></i> Ajouter un article
                            </button>
                        <?php endif; ?>

                        <!-- Valider -->
                        <?php if (!empty($lignes)): ?>
                        <form method="POST" action="index.php?action=receptions_fournisseur/valider"
                              onsubmit="return confirm('Valider cette réception ? Les stocks seront mis à jour.');">
                            <?= $csrf_field ?>
                            <input type="hidden" name="reception_id" value="<?= $reception['id'] ?>">
                            <button type="submit" class="btn btn-success w-100 mb-2">
                                <i class="fas fa-check-circle"></i> Valider la réception
                            </button>
                        </form>
                        <?php endif; ?>

                        <?php if ($reception['statut'] === 'brouillon' && hasPermission('receptions_fournisseur', 'delete')): ?>
                        <form method="POST" action="index.php?action=receptions_fournisseur/delete"
                              onsubmit="return confirm('Supprimer cette réception ?');">
                            <?= $csrf_field ?>
                            <input type="hidden" name="reception_id" value="<?= $reception['id'] ?>">
                            <button type="submit" class="btn btn-outline-danger w-100">
                                <i class="fas fa-trash"></i> Supprimer
                            </button>
                        </form>
                        <?php endif; ?>
                    </div>
                </div>
            <?php elseif ($reception['statut'] === 'validee'): ?>
                <div class="alert alert-success">
                    <i class="fas fa-check-circle"></i>
                    Cette réception a été validée. Les entrées en stock ont été enregistrées.
                </div>
            <?php endif; ?>
        </div>
    </div>

    <!-- Lignes -->
    <div class="card">
        <div class="card-header">
            <i class="fas fa-list"></i> Articles reçus (<?= count($lignes) ?>)
        </div>
        <div class="table-responsive">
            <table class="table table-sm mb-0">
                <thead class="table-light">
                    <tr>
                        <th>Article</th>
                        <th>SKU</th>
                        <th class="text-center">Qté Commandée</th>
                        <th class="text-center">Qté Reçue</th>
                        <th class="text-end">Prix Unitaire</th>
                        <?php if ($reception['statut'] !== 'validee'): ?>
                            <th class="text-end">Actions</th>
                        <?php endif; ?>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($lignes)): ?>
                        <tr>
                            <td colspan="6" class="text-center text-muted py-3">
                                Aucun article. Cliquez sur "Ajouter un article" pour commencer.
                            </td>
                        </tr>
                    <?php else: ?>
                        <?php foreach ($lignes as $ligne): ?>
                            <tr>
                                <td><?= htmlspecialchars($ligne['nom_art'] ?? '') ?></td>
                                <td><small class="text-muted"><?= htmlspecialchars($ligne['sku'] ?? '') ?></small></td>
                                <td class="text-center"><?= (int)$ligne['quantite_commandee'] ?></td>
                                <td class="text-center">
                                    <?php if ($reception['statut'] !== 'validee' && hasPermission('receptions_fournisseur', 'edit')): ?>
                                        <form method="POST" action="index.php?action=receptions_fournisseur/updateLigne"
                                              class="d-flex align-items-center justify-content-center gap-1">
                                            <?= $csrf_field ?>
                                            <input type="hidden" name="ligne_id" value="<?= $ligne['id'] ?>">
                                            <input type="hidden" name="reception_id" value="<?= $reception['id'] ?>">
                                            <input type="number" name="quantite_recue" class="form-control form-control-sm text-center"
                                                   style="width:80px;" min="0"
                                                   max="<?= (int)$ligne['quantite_commandee'] ?>"
                                                   value="<?= (int)$ligne['quantite_recue'] ?>">
                                            <button type="submit" class="btn btn-xs btn-outline-primary btn-sm" title="Enregistrer">
                                                <i class="fas fa-save"></i>
                                            </button>
                                        </form>
                                    <?php else: ?>
                                        <strong><?= (int)$ligne['quantite_recue'] ?></strong>
                                    <?php endif; ?>
                                </td>
                                <td class="text-end">
                                    <?= !empty($ligne['prix_unitaire']) ? number_format((float)$ligne['prix_unitaire'], 2) : '-' ?>
                                </td>
                                <?php if ($reception['statut'] !== 'validee'): ?>
                                    <td class="text-end">
                                        <form method="POST" action="index.php?action=receptions_fournisseur/removeLigne"
                                              style="display:inline;"
                                              onsubmit="return confirm('Supprimer cette ligne ?');">
                                            <?= $csrf_field ?>
                                            <input type="hidden" name="ligne_id" value="<?= $ligne['id'] ?>">
                                            <input type="hidden" name="reception_id" value="<?= $reception['id'] ?>">
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
<?php if ($reception['statut'] === 'brouillon'): ?>
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
const receptionId = <?= (int)$reception['id'] ?>;
const csrfToken   = '<?= addslashes(CsrfMiddleware::getToken()) ?>';

document.getElementById('searchArticle').addEventListener('input', function() {
    const term = this.value.trim();
    if (term.length < 1) {
        document.getElementById('articleResults').innerHTML = '';
        return;
    }
    fetch('index.php?action=receptions_fournisseur/searchArticles&term=' + encodeURIComponent(term))
        .then(r => r.json())
        .then(articles => {
            let html = '<table class="table table-sm table-hover">';
            html += '<thead><tr><th>Article</th><th>SKU</th><th>PR</th><th>Qté Cmd.</th><th>Prix Unit.</th><th></th></tr></thead><tbody>';
            if (articles.length === 0) {
                html += '<tr><td colspan="6" class="text-center text-muted">Aucun résultat</td></tr>';
            }
            articles.forEach(a => {
                html += `<tr>
                    <td>${escapeHtml(a.nom_art)}</td>
                    <td><small class="text-muted">${escapeHtml(a.sku || '')}</small></td>
                    <td><small>${a.pr || ''}</small></td>
                    <td><input type="number" class="form-control form-control-sm qte-input" data-id="${a.id_articles}"
                               min="1" value="1" style="width:70px;"></td>
                    <td><input type="number" step="0.01" class="form-control form-control-sm prix-input" data-id="${a.id_articles}"
                               min="0" value="${a.pr || ''}" style="width:80px;" placeholder="0.00"></td>
                    <td>
                        <button class="btn btn-sm btn-primary btn-add-ligne" data-id="${a.id_articles}"
                                data-nom="${escapeHtml(a.nom_art)}">
                            <i class="fas fa-plus"></i>
                        </button>
                    </td>
                </tr>`;
            });
            html += '</tbody></table>';
            document.getElementById('articleResults').innerHTML = html;

            document.querySelectorAll('.btn-add-ligne').forEach(btn => {
                btn.addEventListener('click', function() {
                    const articleId  = this.dataset.id;
                    const qteInput   = document.querySelector(`.qte-input[data-id="${articleId}"]`);
                    const prixInput  = document.querySelector(`.prix-input[data-id="${articleId}"]`);
                    const quantite   = parseInt(qteInput.value);
                    const prixUnit   = prixInput.value;

                    if (quantite <= 0) {
                        alert('La quantité doit être supérieure à 0.');
                        return;
                    }

                    const formData = new FormData();
                    formData.append('csrf_token', csrfToken);
                    formData.append('reception_id', receptionId);
                    formData.append('article_id', articleId);
                    formData.append('quantite_commandee', quantite);
                    if (prixUnit) formData.append('prix_unitaire', prixUnit);

                    fetch('index.php?action=receptions_fournisseur/addLigne', {
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