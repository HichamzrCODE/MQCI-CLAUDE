<?php include '../views/layout.php'; ?>

<div class="container mt-4">
    <div class="d-flex justify-content-between align-items-center mb-3">
        <h1 style="font-size:1.3rem;">⚙ Gestion des seuils de stock</h1>
        <div class="d-flex gap-2">
            <a href="index.php?action=stock_movements/alerts" class="btn btn-warning btn-sm">⚠ Alertes</a>
            <a href="index.php?action=stock_movements" class="btn btn-secondary btn-sm">← Retour</a>
        </div>
    </div>

    <?php if (!empty($error)): ?>
        <div class="alert alert-danger"><?= htmlspecialchars($error); ?></div>
    <?php endif; ?>
    <?php if (!empty($success)): ?>
        <div class="alert alert-success"><?= htmlspecialchars($success); ?></div>
    <?php endif; ?>

    <?php if (hasPermission('stock_movements', 'create')): ?>
    <div class="card mb-4">
        <div class="card-header font-weight-bold">Définir / modifier un seuil</div>
        <div class="card-body">
            <form method="post" action="index.php?action=stock_movements/seuils">
                <?= $csrf_field ?? ''; ?>
                <div class="form-row">
                    <div class="form-group col-md-4">
                        <label>Article <span class="text-danger">*</span></label>
                        <select name="article_id" class="form-control form-control-sm" required>
                            <option value="">Sélectionner</option>
                            <?php foreach ($articles as $a): ?>
                                <option value="<?= $a['id_articles']; ?>">
                                    <?= htmlspecialchars($a['nom_art']); ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="form-group col-md-3">
                        <label>Dépôt <span class="text-danger">*</span></label>
                        <select name="depot_id" class="form-control form-control-sm" required>
                            <option value="">Sélectionner</option>
                            <?php foreach ($depots as $d): ?>
                                <option value="<?= $d['id']; ?>">
                                    <?= htmlspecialchars($d['nom']); ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="form-group col-md-2">
                        <label>Stock minimal <span class="text-danger">*</span></label>
                        <input type="number" name="stock_minimal" class="form-control form-control-sm" min="0" value="0" required>
                    </div>
                    <div class="form-group col-md-2">
                        <label>Stock maximal</label>
                        <input type="number" name="stock_maximal" class="form-control form-control-sm" min="0" placeholder="Optionnel">
                    </div>
                    <div class="form-group col-md-1 d-flex align-items-end">
                        <button type="submit" class="btn btn-primary btn-sm w-100">✔</button>
                    </div>
                </div>
            </form>
        </div>
    </div>
    <?php endif; ?>

    <h5 class="mb-2">Seuils configurés</h5>
    <?php if (empty($seuils)): ?>
        <div class="alert alert-light text-muted">Aucun seuil configuré.</div>
    <?php else: ?>
        <div class="table-responsive">
            <table class="table table-sm table-hover" style="font-size:0.97rem;">
                <thead style="background:#e9f6e8;">
                    <tr>
                        <th>Article</th>
                        <th>SKU</th>
                        <th>Dépôt</th>
                        <th>Stock minimal</th>
                        <th>Stock maximal</th>
                        <th>Créé le</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($seuils as $s): ?>
                        <tr>
                            <td style="text-transform:uppercase;"><?= htmlspecialchars($s['nom_art']); ?></td>
                            <td><small class="text-muted"><?= htmlspecialchars($s['sku'] ?? ''); ?></small></td>
                            <td><?= htmlspecialchars($s['depot_nom']); ?></td>
                            <td><strong><?= (int)$s['stock_minimal']; ?></strong></td>
                            <td><?= $s['stock_maximal'] !== null ? (int)$s['stock_maximal'] : '<span class="text-muted">-</span>'; ?></td>
                            <td class="text-muted small"><?= htmlspecialchars(date('d/m/Y', strtotime($s['created_at']))); ?></td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    <?php endif; ?>
</div>