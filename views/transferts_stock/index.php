<?php include '../views/layout.php'; ?>

<div class="container-fluid mt-4">
    <div class="row mb-4">
        <div class="col-md-8">
            <h1><i class="fas fa-exchange-alt"></i> Transferts de Stock</h1>
            <p class="text-muted">Total: <strong><?= $totalCount ?></strong> transfert(s)</p>
        </div>
        <div class="col-md-4 text-end">
            <?php if (hasPermission('transferts_stock', 'create')): ?>
                <a href="index.php?action=transferts_stock/create" class="btn btn-primary">
                    <i class="fas fa-plus"></i> Nouveau Transfert
                </a>
            <?php endif; ?>
        </div>
    </div>

    <!-- Filtres -->
    <div class="card mb-4">
        <div class="card-body">
            <form method="GET" action="index.php" class="row g-2 align-items-end">
                <input type="hidden" name="action" value="transferts_stock">
                <div class="col-md-2">
                    <label class="form-label small">Dépôt source</label>
                    <select name="depot_source_id" class="form-select form-select-sm">
                        <option value="">-- Tous --</option>
                        <?php foreach ($depots as $d): ?>
                            <option value="<?= $d['id'] ?>"
                                <?= ($filters['depot_source_id'] == $d['id']) ? 'selected' : '' ?>>
                                <?= htmlspecialchars($d['nom']) ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="col-md-2">
                    <label class="form-label small">Dépôt destination</label>
                    <select name="depot_destination_id" class="form-select form-select-sm">
                        <option value="">-- Tous --</option>
                        <?php foreach ($depots as $d): ?>
                            <option value="<?= $d['id'] ?>"
                                <?= ($filters['depot_destination_id'] == $d['id']) ? 'selected' : '' ?>>
                                <?= htmlspecialchars($d['nom']) ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="col-md-2">
                    <label class="form-label small">Statut</label>
                    <select name="statut" class="form-select form-select-sm">
                        <option value="">-- Tous --</option>
                        <option value="brouillon" <?= ($filters['statut'] === 'brouillon') ? 'selected' : '' ?>>Brouillon</option>
                        <option value="en_cours"  <?= ($filters['statut'] === 'en_cours')  ? 'selected' : '' ?>>En cours</option>
                        <option value="valide"    <?= ($filters['statut'] === 'valide')    ? 'selected' : '' ?>>Validé</option>
                    </select>
                </div>
                <div class="col-md-2">
                    <label class="form-label small">Du</label>
                    <input type="date" name="date_debut" class="form-control form-control-sm"
                           value="<?= htmlspecialchars($filters['date_debut']) ?>">
                </div>
                <div class="col-md-2">
                    <label class="form-label small">Au</label>
                    <input type="date" name="date_fin" class="form-control form-control-sm"
                           value="<?= htmlspecialchars($filters['date_fin']) ?>">
                </div>
                <div class="col-md-2">
                    <button type="submit" class="btn btn-sm btn-secondary w-100">
                        <i class="fas fa-filter"></i> Filtrer
                    </button>
                </div>
            </form>
        </div>
    </div>

    <?php if (isset($_GET['error'])): ?>
        <div class="alert alert-danger alert-dismissible fade show">
            <i class="fas fa-exclamation-circle"></i> <?= htmlspecialchars($_GET['error']) ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    <?php endif; ?>

    <!-- Tableau -->
    <div class="table-responsive">
        <table class="table table-hover table-sm">
            <thead class="table-light">
                <tr>
                    <th>Numéro</th>
                    <th>Dépôt Source</th>
                    <th>Dépôt Destination</th>
                    <th>Date</th>
                    <th class="text-center">Lignes</th>
                    <th>Statut</th>
                    <th>Créé par</th>
                    <th class="text-end">Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php if (empty($transferts)): ?>
                    <tr><td colspan="8" class="text-center text-muted py-4">Aucun transfert trouvé.</td></tr>
                <?php else: ?>
                    <?php foreach ($transferts as $t): ?>
                        <tr>
                            <td><a href="index.php?action=transferts_stock/show&id=<?= $t['id'] ?>">
                                <strong><?= htmlspecialchars($t['numero']) ?></strong>
                            </a></td>
                            <td><?= htmlspecialchars($t['depot_source_nom'] ?? '') ?></td>
                            <td><?= htmlspecialchars($t['depot_destination_nom'] ?? '') ?></td>
                            <td><?= htmlspecialchars($t['date_transfert']) ?></td>
                            <td class="text-center">
                                <span class="badge bg-secondary"><?= (int)$t['nb_lignes'] ?></span>
                            </td>
                            <td>
                                <?php
                                $statutClass = match($t['statut']) {
                                    'valide'   => 'bg-success',
                                    'en_cours' => 'bg-warning text-dark',
                                    default    => 'bg-secondary',
                                };
                                $statutLabel = match($t['statut']) {
                                    'valide'   => 'Validé',
                                    'en_cours' => 'En cours',
                                    default    => 'Brouillon',
                                };
                                ?>
                                <span class="badge <?= $statutClass ?>"><?= $statutLabel ?></span>
                            </td>
                            <td><?= htmlspecialchars($t['user_nom'] ?? '') ?></td>
                            <td class="text-end">
                                <a href="index.php?action=transferts_stock/show&id=<?= $t['id'] ?>"
                                   class="btn btn-sm btn-info" title="Voir">
                                    <i class="fas fa-eye"></i>
                                </a>
                                <?php if ($t['statut'] === 'brouillon' && hasPermission('transferts_stock', 'delete')): ?>
                                    <a href="index.php?action=transferts_stock/delete&id=<?= $t['id'] ?>"
                                       class="btn btn-sm btn-danger"
                                       onclick="return confirm('Supprimer ce transfert ?');"
                                       title="Supprimer">
                                        <i class="fas fa-trash"></i>
                                    </a>
                                <?php endif; ?>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>