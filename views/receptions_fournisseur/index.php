<?php include '../views/layout.php'; ?>

<div class="container-fluid mt-4">
    <div class="row mb-4">
        <div class="col-md-8">
            <h1><i class="fas fa-truck-loading"></i> Réceptions Fournisseur</h1>
            <p class="text-muted">Total: <strong><?= $totalCount ?></strong> réception(s)</p>
        </div>
        <div class="col-md-4 text-end">
            <?php if (hasPermission('receptions_fournisseur', 'create')): ?>
                <a href="index.php?action=receptions_fournisseur/create" class="btn btn-primary">
                    <i class="fas fa-plus"></i> Nouvelle Réception
                </a>
            <?php endif; ?>
        </div>
    </div>

    <!-- Filtres -->
    <div class="card mb-4">
        <div class="card-body">
            <form method="GET" action="index.php" class="row g-2 align-items-end">
                <input type="hidden" name="action" value="receptions_fournisseur">
                <div class="col-md-3">
                    <label class="form-label small">Fournisseur</label>
                    <select name="fournisseur_id" class="form-select form-select-sm">
                        <option value="">-- Tous --</option>
                        <?php foreach ($fournisseurs as $f): ?>
                            <option value="<?= $f['id_fournisseurs'] ?>"
                                <?= ($filters['fournisseur_id'] == $f['id_fournisseurs']) ? 'selected' : '' ?>>
                                <?= htmlspecialchars($f['nom_fournisseurs']) ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="col-md-2">
                    <label class="form-label small">Dépôt</label>
                    <select name="depot_id" class="form-select form-select-sm">
                        <option value="">-- Tous --</option>
                        <?php foreach ($depots as $d): ?>
                            <option value="<?= $d['id'] ?>"
                                <?= ($filters['depot_id'] == $d['id']) ? 'selected' : '' ?>>
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
                        <option value="recue"     <?= ($filters['statut'] === 'recue')     ? 'selected' : '' ?>>Reçue</option>
                        <option value="validee"   <?= ($filters['statut'] === 'validee')   ? 'selected' : '' ?>>Validée</option>
                    </select>
                </div>
                <div class="col-md-2">
                    <label class="form-label small">Du</label>
                    <input type="date" name="date_debut" class="form-control form-control-sm"
                           value="<?= htmlspecialchars($filters['date_debut']) ?>">
                </div>
                <div class="col-md-1">
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
                    <th>Fournisseur</th>
                    <th>Dépôt</th>
                    <th>Date</th>
                    <th class="text-center">Lignes</th>
                    <th>Statut</th>
                    <th>Créé par</th>
                    <th class="text-end">Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php if (empty($receptions)): ?>
                    <tr><td colspan="8" class="text-center text-muted py-4">Aucune réception trouvée.</td></tr>
                <?php else: ?>
                    <?php foreach ($receptions as $r): ?>
                        <tr>
                            <td>
                                <a href="index.php?action=receptions_fournisseur/show&id=<?= $r['id'] ?>">
                                    <strong><?= htmlspecialchars($r['numero']) ?></strong>
                                </a>
                            </td>
                            <td><?= htmlspecialchars($r['nom_fournisseurs'] ?? '') ?></td>
                            <td><?= htmlspecialchars($r['depot_nom'] ?? '') ?></td>
                            <td><?= htmlspecialchars($r['date_reception']) ?></td>
                            <td class="text-center">
                                <span class="badge bg-secondary"><?= (int)$r['nb_lignes'] ?></span>
                            </td>
                            <td>
                                <?php
                                $statutClass = match($r['statut']) {
                                    'validee' => 'bg-success',
                                    'recue'   => 'bg-info',
                                    default   => 'bg-secondary',
                                };
                                $statutLabel = match($r['statut']) {
                                    'validee' => 'Validée',
                                    'recue'   => 'Reçue',
                                    default   => 'Brouillon',
                                };
                                ?>
                                <span class="badge <?= $statutClass ?>"><?= $statutLabel ?></span>
                            </td>
                            <td><?= htmlspecialchars($r['user_nom'] ?? '') ?></td>
                            <td class="text-end">
                                <a href="index.php?action=receptions_fournisseur/show&id=<?= $r['id'] ?>"
                                   class="btn btn-sm btn-info" title="Voir">
                                    <i class="fas fa-eye"></i>
                                </a>
                                <?php if ($r['statut'] === 'brouillon' && hasPermission('receptions_fournisseur', 'delete')): ?>
                                    <a href="index.php?action=receptions_fournisseur/delete&id=<?= $r['id'] ?>"
                                       class="btn btn-sm btn-danger"
                                       onclick="return confirm('Supprimer cette réception ?');"
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