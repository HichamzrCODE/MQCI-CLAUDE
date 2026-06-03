<?php include '../views/layout.php'; ?>

<div class="container mt-4" style="max-width:900px;">
    <div class="d-flex justify-content-between align-items-center mb-3">
        <h1 style="font-size:1.3rem;">Détail de l'article</h1>
        <div class="d-flex gap-2">
            <?php if (hasPermission('articles', 'edit')): ?>
                <a href="index.php?action=articles/edit&id=<?= $article['id_articles']; ?>" class="btn btn-primary btn-sm">✎ Modifier</a>
            <?php endif; ?>
            <a href="index.php?action=articles" class="btn btn-secondary btn-sm">← Retour</a>
        </div>
    </div>

    <?php
        $pr = $article['prix_revient_display'] ?? $article['prix_revient'] ?? $article['pr'] ?? 0;
        $pv = $article['prix_vente'] ?? 0;
        $marge = ($pr > 0 && $pv > 0) ? round(($pv - $pr) / $pr * 100, 1) : null;
        $statutColors = ['actif' => 'success', 'inactif' => 'warning', 'discontinued' => 'secondary'];
        $statutColor = $statutColors[$article['statut'] ?? 'actif'] ?? 'secondary';
    ?>

    <div class="row">
        <!-- Colonne principale -->
        <div class="col-md-8">
            <!-- Infos générales -->
            <div class="card mb-3">
                <div class="card-header font-weight-bold d-flex justify-content-between">
                    <span>Informations générales</span>
                    <span class="badge badge-<?= $statutColor; ?>"><?= htmlspecialchars(ucfirst($article['statut'] ?? 'actif')); ?></span>
                </div>
                <div class="card-body">
                    <dl class="row mb-0">
                        <dt class="col-sm-4">Nom</dt>
                        <dd class="col-sm-8" style="text-transform:uppercase;font-weight:600;"><?= htmlspecialchars($article['nom_art']); ?></dd>

                        <?php if (!empty($article['sku'])): ?>
                            <dt class="col-sm-4">SKU</dt>
                            <dd class="col-sm-8"><code><?= htmlspecialchars($article['sku']); ?></code></dd>
                        <?php endif; ?>

                        <dt class="col-sm-4">Fournisseur</dt>
                        <dd class="col-sm-8"><?= htmlspecialchars($article['nom_fournisseurs']); ?></dd>

                        <?php if (!empty($article['nom_fournisseur_alt'])): ?>
                            <dt class="col-sm-4">Fournisseur alt.</dt>
                            <dd class="col-sm-8"><?= htmlspecialchars($article['nom_fournisseur_alt']); ?></dd>
                        <?php endif; ?>

                        <?php if (!empty($article['nom_categorie'])): ?>
                            <dt class="col-sm-4">Catégorie</dt>
                            <dd class="col-sm-8"><?= htmlspecialchars($article['nom_categorie']); ?></dd>
                        <?php endif; ?>

                        <dt class="col-sm-4">Unité de mesure</dt>
                        <dd class="col-sm-8"><?= htmlspecialchars($article['unite_mesure'] ?? 'Piece'); ?></dd>
                    </dl>
                </div>
            </div>

            <!-- Tarification -->
            <div class="card mb-3">
                <div class="card-header font-weight-bold">Tarification</div>
                <div class="card-body">
                    <div class="row text-center">
                        <div class="col-4">
                            <div class="p-2 bg-light rounded">
                                <div class="text-muted small">Prix de revient</div>
                                <div class="font-weight-bold"><?= number_format($pr, 0, ',', ' '); ?></div>
                            </div>
                        </div>
                        <div class="col-4">
                            <div class="p-2 bg-light rounded">
                                <div class="text-muted small">Prix de vente</div>
                                <div class="font-weight-bold"><?= $pv ? number_format($pv, 0, ',', ' ') : '<span class="text-muted">-</span>'; ?></div>
                            </div>
                        </div>
                        <div class="col-4">
                            <div class="p-2 bg-light rounded">
                                <div class="text-muted small">Marge</div>
                                <div class="font-weight-bold <?= $marge !== null ? ($marge >= 0 ? 'text-success' : 'text-danger') : ''; ?>">
                                    <?= $marge !== null ? $marge . '%' : '<span class="text-muted">-</span>'; ?>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Stock par dépôt -->
            <div class="card mb-3">
                <div class="card-header font-weight-bold d-flex justify-content-between">
                    <span>Stock par dépôt</span>
                    <span class="badge badge-primary">Total : <?= (int)($article['quantite_totale'] ?? 0); ?></span>
                </div>
                <div class="card-body p-0">
                    <?php if (empty($stockDepots)): ?>
                        <p class="text-muted text-center p-3 mb-0">Aucun stock enregistré dans un dépôt.</p>
                    <?php else: ?>
                        <div class="table-responsive">
                            <table class="table table-sm mb-0">
                                <thead class="thead-light">
                                    <tr>
                                        <th>Dépôt</th>
                                        <th>Ville</th>
                                        <th>Quantité</th>
                                        <th>En transit</th>
                                        <th>Bloquée</th>
                                        <th>Emplacement</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($stockDepots as $sd): ?>
                                        <tr>
                                            <td><?= htmlspecialchars($sd['depot_nom']); ?></td>
                                            <td><?= htmlspecialchars($sd['depot_ville'] ?? '-'); ?></td>
                                            <td><strong><?= (int)$sd['quantite']; ?></strong></td>
                                            <td><?= (int)$sd['quantite_en_transit']; ?></td>
                                            <td><?= (int)$sd['quantite_bloquee']; ?></td>
                                            <td><?= htmlspecialchars($sd['emplacement'] ?? '-'); ?></td>
                                        </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    <?php endif; ?>
                </div>
            </div>

            <!-- Caractéristiques physiques -->
            <?php if (!empty($article['poids_kg']) || !empty($article['longueur_cm']) || !empty($article['couleur'])): ?>
            <div class="card mb-3">
                <div class="card-header font-weight-bold">Caractéristiques physiques</div>
                <div class="card-body">
                    <dl class="row mb-0">
                        <?php if (!empty($article['poids_kg'])): ?>
                            <dt class="col-sm-4">Poids</dt>
                            <dd class="col-sm-8"><?= htmlspecialchars($article['poids_kg']); ?> kg</dd>
                        <?php endif; ?>
                        <?php if (!empty($article['longueur_cm']) || !empty($article['largeur_cm']) || !empty($article['hauteur_cm'])): ?>
                            <dt class="col-sm-4">Dimensions</dt>
                            <dd class="col-sm-8">
                                <?= htmlspecialchars($article['longueur_cm'] ?? '-'); ?> × <?= htmlspecialchars($article['largeur_cm'] ?? '-'); ?> × <?= htmlspecialchars($article['hauteur_cm'] ?? '-'); ?> cm
                            </dd>
                        <?php endif; ?>
                        <?php if (!empty($article['couleur'])): ?>
                            <dt class="col-sm-4">Couleur</dt>
                            <dd class="col-sm-8"><?= htmlspecialchars($article['couleur']); ?></dd>
                        <?php endif; ?>
                    </dl>
                </div>
            </div>
            <?php endif; ?>

            <!-- Historique prix -->
            <?php if (!empty($prixHistorique)): ?>
            <div class="card mb-3">
                <div class="card-header font-weight-bold">Historique des prix</div>
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table table-sm mb-0">
                            <thead class="thead-light">
                                <tr>
                                    <th>Date</th>
                                    <th>Prix rev. ancien</th>
                                    <th>Prix rev. nouveau</th>
                                    <th>Prix vte. ancien</th>
                                    <th>Prix vte. nouveau</th>
                                    <th>Modifié par</th>
                                    <th>Raison</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($prixHistorique as $h): ?>
                                    <tr>
                                        <td style="white-space:nowrap;font-size:0.85rem;"><?= htmlspecialchars(date('d/m/Y H:i', strtotime($h['changed_at']))); ?></td>
                                        <td><?= $h['prix_revient_ancien'] !== null ? number_format($h['prix_revient_ancien'], 0, ',', ' ') : '-'; ?></td>
                                        <td><?= $h['prix_revient_nouveau'] !== null ? number_format($h['prix_revient_nouveau'], 0, ',', ' ') : '-'; ?></td>
                                        <td><?= $h['prix_vente_ancien'] !== null ? number_format($h['prix_vente_ancien'], 0, ',', ' ') : '-'; ?></td>
                                        <td><?= $h['prix_vente_nouveau'] !== null ? number_format($h['prix_vente_nouveau'], 0, ',', ' ') : '-'; ?></td>
                                        <td><?= htmlspecialchars($h['changed_by_name'] ?? '-'); ?></td>
                                        <td><?= htmlspecialchars($h['raison'] ?? '-'); ?></td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
            <?php endif; ?>

            <!-- Notes internes -->
            <?php if (!empty($article['notes_internes'])): ?>
            <div class="card mb-3">
                <div class="card-header font-weight-bold">Notes internes</div>
                <div class="card-body">
                    <p class="mb-0" style="white-space:pre-line;"><?= htmlspecialchars($article['notes_internes']); ?></p>
                </div>
            </div>
            <?php endif; ?>
        </div>

        <!-- Colonne latérale -->
        <div class="col-md-4">
            <!-- Image -->
            <?php if (!empty($article['image_path'])): ?>
            <div class="card mb-3">
                <div class="card-header font-weight-bold">Image</div>
                <div class="card-body text-center">
                    <img src="/S6/public/<?= htmlspecialchars($article['image_path']); ?>" alt="Image article"
                         class="img-fluid rounded" style="max-height:200px;">
                </div>
            </div>
            <?php endif; ?>

            <!-- Seuils de stock -->
            <div class="card mb-3">
                <div class="card-header font-weight-bold">Seuils de stock</div>
                <div class="card-body">
                    <dl class="row mb-0">
                        <dt class="col-6">Minimal</dt>
                        <dd class="col-6"><?= (int)($article['stock_minimal'] ?? 0); ?></dd>
                        <dt class="col-6">Maximal</dt>
                        <dd class="col-6"><?= (int)($article['stock_maximal'] ?? 0); ?></dd>
                    </dl>
                    <?php
                        $qt = (int)($article['quantite_totale'] ?? 0);
                        $smin = (int)($article['stock_minimal'] ?? 0);
                        if ($smin > 0 && $qt < $smin):
                    ?>
                        <div class="alert alert-warning mt-2 mb-0 p-2" style="font-size:0.9rem;">
                            ⚠ Stock en dessous du minimum !<br>
                            Actuel : <strong><?= $qt; ?></strong> / Min : <strong><?= $smin; ?></strong>
                        </div>
                    <?php endif; ?>
                </div>
            </div>

            <!-- Audit -->
            <div class="card mb-3">
                <div class="card-header font-weight-bold">Traçabilité</div>
                <div class="card-body" style="font-size:0.9rem;">
                    <dl class="row mb-0">
                        <?php if (!empty($article['created_by_name'])): ?>
                            <dt class="col-5">Créé par</dt>
                            <dd class="col-7"><?= htmlspecialchars($article['created_by_name']); ?></dd>
                        <?php endif; ?>
                        <?php if (!empty($article['created_at'])): ?>
                            <dt class="col-5">Créé le</dt>
                            <dd class="col-7"><?= htmlspecialchars(date('d/m/Y', strtotime($article['created_at']))); ?></dd>
                        <?php endif; ?>
                        <?php if (!empty($article['updated_by_name'])): ?>
                            <dt class="col-5">Modifié par</dt>
                            <dd class="col-7"><?= htmlspecialchars($article['updated_by_name']); ?></dd>
                        <?php endif; ?>
                        <?php if (!empty($article['updated_at'])): ?>
                            <dt class="col-5">Modifié le</dt>
                            <dd class="col-7"><?= htmlspecialchars(date('d/m/Y', strtotime($article['updated_at']))); ?></dd>
                        <?php endif; ?>
                    </dl>
                </div>
            </div>
        </div>
    </div>
</div>
