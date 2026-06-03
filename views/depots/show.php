<?php include '../views/layout.php'; ?>

<?php
$pageTitle = "Dépôt : " . htmlspecialchars($depot['nom']);
?>

<div class="container-fluid mt-4">
    <div class="row mb-4">
        <div class="col-md-8">
            <h2><i class="fas fa-warehouse"></i> <?= htmlspecialchars($depot['nom']) ?></h2>
            <p class="text-muted">
                <i class="fas fa-map-marker-alt"></i> 
                <?= htmlspecialchars($depot['adresse'] ?? '') ?>, 
                <?= htmlspecialchars($depot['ville'] ?? '') ?>
            </p>
        </div>
        <div class="col-md-4 text-end">
            <?php if (hasPermission('depots', 'edit')): ?>
                <a href="index.php?action=depots/edit&id=<?= $depot['id'] ?>" class="btn btn-warning">
                    <i class="fas fa-edit"></i> Modifier
                </a>
            <?php endif; ?>
            <a href="index.php?action=depots" class="btn btn-secondary">
                <i class="fas fa-arrow-left"></i> Retour
            </a>
        </div>
    </div>

    <div class="row mb-4">
        <div class="col-md-6">
            <div class="card">
                <div class="card-header bg-light">
                    <h5 class="mb-0">📋 Informations</h5>
                </div>
                <div class="card-body">
                    <p>
                        <strong>Adresse :</strong><br>
                        <?= htmlspecialchars($depot['adresse'] ?? 'Non spécifié') ?>
                    </p>
                    <p>
                        <strong>Ville :</strong><br>
                        <?= htmlspecialchars($depot['ville'] ?? 'Non spécifié') ?>
                    </p>
                    <p>
                        <strong>Téléphone :</strong><br>
                        <?= htmlspecialchars($depot['telephone'] ?? 'Non spécifié') ?>
                    </p>
                    <p>
                        <strong>Email :</strong><br>
                        <?= htmlspecialchars($depot['email'] ?? 'Non spécifié') ?>
                    </p>
                </div>
            </div>
        </div>
        <div class="col-md-6">
            <div class="card">
                <div class="card-header bg-light">
                    <h5 class="mb-0">🔧 État du Dépôt</h5>
                </div>
                <div class="card-body">
                    <p>
                        <strong>Statut :</strong><br>
                        <?php if ($depot['statut'] === 'actif'): ?>
                            <span class="badge bg-success">Actif</span>
                        <?php else: ?>
                            <span class="badge bg-danger">Inactif</span>
                        <?php endif; ?>
                    </p>
                    <p>
                        <strong>Créé le :</strong><br>
                        <?= date('d/m/Y H:i', strtotime($depot['created_at'] ?? now())) ?>
                    </p>
                    <p>
                        <strong>Articles stockés :</strong><br>
                        <span class="badge bg-info"><?= count($stocks) ?></span>
                    </p>
                </div>
            </div>
        </div>
    </div>

    <h3><i class="fas fa-boxes"></i> Stock dans ce dépôt</h3>
    <?php if (count($stocks) > 0): ?>
        <div class="table-responsive">
            <table class="table table-hover">
                <thead class="table-light">
                    <tr>
                        <th>Article</th>
                        <th>SKU</th>
                        <th>Quantité</th>
                        <th>PR (Revient)</th>
                        <th>PV (Vente)</th>
                        <th>Emplacement</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($stocks as $stock): ?>
                        <tr>
                            <td>
                                <strong><?= htmlspecialchars($stock['nom_art']) ?></strong>
                            </td>
                            <td>
                                <span class="badge bg-secondary">
                                    <?= htmlspecialchars($stock['sku'] ?? '-') ?>
                                </span>
                            </td>
                            <td>
                                <span class="badge bg-info"><?= $stock['quantite'] ?? 0 ?></span>
                            </td>
                            <td>
                                <?= number_format($stock['pr'] ?? 0, 2, ',', ' ') ?> F
                            </td>
                            <td>
                                <?= number_format($stock['prix_vente'] ?? 0, 2, ',', ' ') ?> F
                            </td>
                            <td>
                                <?= htmlspecialchars($stock['emplacement'] ?? '-') ?>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    <?php else: ?>
        <div class="alert alert-info">
            <i class="fas fa-info-circle"></i> Aucun stock dans ce dépôt pour le moment.
        </div>
    <?php endif; ?>
</div>