<?php
$pageTitle = "Factures";
include '../views/layout.php';

$factures = $factures ?? [];
?>

<div class="container-fluid mt-4">
    <div class="d-flex justify-content-between align-items-center mb-3">
        <h1 class="h4 mb-0">Factures</h1>
    </div>

    <div class="card">
        <div class="card-header">Liste</div>
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-sm align-middle">
                    <thead class="table-light">
                        <tr>
                            <th>N°</th>
                            <th>Client</th>
                            <th>Date</th>
                            <th>Statut</th>
                            <th class="text-end">Total TTC</th>
                            <th class="text-end">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($factures as $f): ?>
                            <?php
                                $isValidated = (($f['statut'] ?? 'draft') === 'validated');
                                $badge = $isValidated ? 'success' : 'secondary';
                            ?>
                            <tr>
                                <td class="fw-semibold"><?= htmlspecialchars($f['numero'] ?? '') ?></td>
                                <td><?= htmlspecialchars($f['client_nom'] ?? '') ?></td>
                                <td><?= htmlspecialchars($f['date'] ?? '') ?></td>
                                <td><span class="badge bg-<?= $badge ?>"><?= htmlspecialchars($f['statut'] ?? '') ?></span></td>
                                <td class="text-end fw-semibold"><?= number_format((float)($f['total_ttc'] ?? $f['total'] ?? 0), 0, ',', ' ') ?></td>

                                <td class="text-end">
                                    <a class="btn btn-outline-primary btn-sm" href="index.php?action=factures/show&id=<?= (int)$f['id'] ?>">Voir</a>
                                    <a class="btn btn-primary btn-sm" href="index.php?action=factures/edit&id=<?= (int)$f['id'] ?>">Modifier</a>
                                </td>
                            </tr>
                        <?php endforeach; ?>

                        <?php if (!$factures): ?>
                            <tr><td colspan="9" class="text-muted text-center">Aucune facture</td></tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>

        </div>
    </div>
</div>