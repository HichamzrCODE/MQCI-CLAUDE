<?php
$pageTitle = "Facture";
include '../views/layout.php';

$facture = $facture ?? null;
$lignes = $lignes ?? [];

if (!$facture) {
    echo "<div class='container mt-4'><div class='alert alert-danger'>Facture introuvable.</div></div>";
    return;
}

$isValidated = (($facture['statut'] ?? 'draft') === 'validated');
$badge = $isValidated ? 'success' : 'secondary';
?>

<div class="container-fluid mt-4">
    <div class="d-flex justify-content-between align-items-center mb-3">
        <div>
            <h1 class="h4 mb-1">Facture client</h1>
            <div class="text-muted small">
                N° <strong><?= htmlspecialchars($facture['numero']) ?></strong>
                • Date: <strong><?= htmlspecialchars($facture['date']) ?></strong>
                • <span class="badge bg-<?= $badge ?>"><?= htmlspecialchars($facture['statut']) ?></span>
            </div>

            <div class="text-muted small mt-1">
                Client : <strong><?= htmlspecialchars($facture['client_nom'] ?? '') ?></strong>
                <?php if (!empty($facture['source_devis_id'])): ?>
                    • Devis source: <strong>#<?= (int)$facture['source_devis_id'] ?></strong>
                <?php endif; ?>
                <?php if (!empty($facture['source_bl_id'])): ?>
                    • BL source: <strong>#<?= (int)$facture['source_bl_id'] ?></strong>
                <?php endif; ?>
            </div>

            <div class="text-muted small mt-1">
                TVA: <?= number_format((float)($facture['tva_rate'] ?? 18), 2, ',', ' ') ?>%
                • AIRSI:
                <?= !empty($facture['apply_airsi'])
                    ? number_format((float)($facture['airsi_rate'] ?? 5), 2, ',', ' ') . '%'
                    : 'Non appliquée' ?>
            </div>
        </div>

        <div class="d-flex gap-2">
            <a href="index.php?action=factures" class="btn btn-outline-secondary btn-sm">Retour</a>
            <a href="index.php?action=factures/edit&id=<?= (int)$facture['id'] ?>" class="btn btn-primary btn-sm">Modifier</a>
            <button class="btn btn-outline-dark btn-sm" onclick="window.print()">Imprimer</button>

            <?php if (!$isValidated): ?>
                <form method="post" action="index.php?action=factures/validate&id=<?= (int)$facture['id'] ?>" class="d-inline">
                    <button type="submit" class="btn btn-success btn-sm"
                            onclick="return confirm('Valider cette facture ?');">
                        Valider
                    </button>
                </form>
            <?php endif; ?>
        </div>
    </div>

    <div class="card">
        <div class="card-header">Lignes</div>
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-sm align-middle">
                    <thead class="table-light">
                        <tr>
                            <th>Article</th>
                            <th>Description</th>
                            <th class="text-end">Qté</th>
                            <th class="text-end">PU HT</th>
                            <th class="text-end">PU TTC</th>
                            <th class="text-end">Total HT</th>
                            <th class="text-end">Total TTC</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (empty($lignes)): ?>
                            <tr>
                                <td colspan="7" class="text-center text-muted">Aucune ligne.</td>
                            </tr>
                        <?php else: ?>
                            <?php foreach ($lignes as $ln): ?>
                                <tr>
                                    <td class="fw-semibold"><?= htmlspecialchars($ln['nom_art'] ?? '') ?></td>
                                    <td class="text-muted small">
                                        <?= !empty($ln['description']) ? nl2br(htmlspecialchars($ln['description'])) : '—' ?>
                                    </td>
                                    <td class="text-end"><?= number_format((float)($ln['quantite'] ?? 0), 0, ',', ' ') ?></td>
                                    <td class="text-end"><?= number_format((float)($ln['prix_unitaire_ht'] ?? 0), 2, ',', ' ') ?></td>
                                    <td class="text-end"><?= number_format((float)($ln['prix_unitaire_ttc'] ?? 0), 2, ',', ' ') ?></td>
                                    <td class="text-end"><?= number_format((float)($ln['total_ht'] ?? 0), 2, ',', ' ') ?></td>
                                    <td class="text-end"><?= number_format((float)($ln['total_ttc'] ?? 0), 2, ',', ' ') ?></td>
                                </tr>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>

            <div class="d-flex justify-content-end mt-3">
                <div class="text-end">
                    <div>Total HT:
                        <?= number_format((float)($facture['total_ht'] ?? 0), 0, ',', ' ') ?>
                    </div>
                    <div>TVA:
                        <?= number_format((float)($facture['total_tva'] ?? 0), 0, ',', ' ') ?>
                    </div>
                    <div>AIRSI:
                        <?= number_format((float)($facture['total_airsi'] ?? 0), 0, ',', ' ') ?>
                    </div>
                    <div class="fs-5">Total TTC:
                        <strong><?= number_format((float)($facture['total_ttc'] ?? 0), 0, ',', ' ') ?></strong>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>