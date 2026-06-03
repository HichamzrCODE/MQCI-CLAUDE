<?php include '../views/layout.php'; ?>

<div class="container mt-4">
    <div class="d-flex justify-content-between align-items-center mb-3">
        <h1 style="font-size:1.3rem;">⚠ Alertes de stock minimal</h1>
        <div class="d-flex gap-2">
            <a href="index.php?action=stock_movements/seuils" class="btn btn-outline-secondary btn-sm">⚙ Gérer les seuils</a>
            <a href="index.php?action=stock_movements" class="btn btn-secondary btn-sm">← Retour</a>
        </div>
    </div>

    <?php if (empty($alertes)): ?>
        <div class="alert alert-success">
            ✅ Aucune alerte de stock — tous les articles sont au-dessus de leurs seuils minimaux.
        </div>
    <?php else: ?>
        <div class="alert alert-warning">
            <strong><?= count($alertes); ?></strong> article(s) en dessous du seuil minimal de stock.
        </div>

        <div class="table-responsive">
            <table class="table table-sm table-hover" style="font-size:0.97rem;">
                <thead style="background:#fff3cd;">
                    <tr>
                        <th>Article</th>
                        <th>SKU</th>
                        <th>Dépôt</th>
                        <th>Stock actuel</th>
                        <th>Seuil minimal</th>
                        <th>Manquant</th>
                        <th>Seuil maximal</th>
                        <th>Action</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($alertes as $a): ?>
                        <?php
                            $manquant = (int)$a['manquant'];
                            $actuelle = (int)$a['quantite_actuelle'];
                            $minimal  = (int)$a['stock_minimal'];
                            $critique = $actuelle === 0;
                        ?>
                        <tr class="<?= $critique ? 'table-danger' : 'table-warning'; ?>">
                            <td style="text-transform:uppercase;font-weight:600;">
                                <?= htmlspecialchars($a['nom_art']); ?>
                                <?php if ($critique): ?><span class="badge badge-danger ml-1">Rupture</span><?php endif; ?>
                            </td>
                            <td><small class="text-muted"><?= htmlspecialchars($a['sku'] ?? ''); ?></small></td>
                            <td><?= htmlspecialchars($a['depot_nom']); ?></td>
                            <td>
                                <strong class="text-<?= $critique ? 'danger' : 'warning'; ?>">
                                    <?= $actuelle; ?>
                                </strong>
                            </td>
                            <td><?= $minimal; ?></td>
                            <td><strong class="text-danger">-<?= $manquant; ?></strong></td>
                            <td class="text-muted"><?= $a['stock_maximal'] !== null ? (int)$a['stock_maximal'] : '-'; ?></td>
                            <td>
                                <?php if (hasPermission('stock_movements', 'create')): ?>
                                    <a href="index.php?action=stock_movements/create&article_id=<?= (int)$a['id_articles']; ?>&depot_id=<?= (int)$a['depot_id']; ?>&type_mouvement=entree"
                                       class="btn btn-success btn-sm px-2 py-0" title="Créer une entrée de stock">
                                        + Entrée
                                    </a>
                                <?php endif; ?>
                                <a href="index.php?action=stock_movements/historique&article_id=<?= (int)$a['id_articles']; ?>&depot_id=<?= (int)$a['depot_id']; ?>"
                                   class="btn btn-outline-info btn-sm px-2 py-0" title="Voir l'historique">
                                    📋
                                </a>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    <?php endif; ?>
</div>