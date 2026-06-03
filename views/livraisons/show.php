<?php
$pageTitle = "BL";
include '../views/layout.php';

$bl = $bl ?? null;
$lignes = $lignes ?? [];

if (!$bl) {
    echo "<div class='container mt-4'><div class='alert alert-danger'>BL introuvable.</div></div>";
    return;
}

$isValidated = (($bl['statut'] ?? 'draft') === 'validated');
$badge = $isValidated ? 'success' : 'secondary';
?>

<div class="container-fluid mt-4">
    <div class="d-flex justify-content-between align-items-center mb-3">
        <div>
            <h1 class="h4 mb-1">Bon de Livraison</h1>
            <div class="text-muted small">
                N° <strong><?= htmlspecialchars($bl['numero']) ?></strong>
                • Client: <strong><?= htmlspecialchars($bl['client_nom']) ?></strong>
                • Dépôt: <strong><?= htmlspecialchars($bl['depot_nom']) ?></strong>
                • Devis: <strong><?= htmlspecialchars($bl['devis_numero']) ?></strong>
                • Date: <strong><?= htmlspecialchars($bl['date']) ?></strong>
                • <span class="badge bg-<?= $badge ?>" style='color: white;'><?= htmlspecialchars($bl['statut']) ?></span>
            </div>
            <div class="text-muted small mt-1">
                Document de livraison — sans valeur commerciale.
            </div>
        </div>

        <div class="d-flex gap-2">
            <a href="index.php?action=livraisons" class="btn btn-outline-secondary btn-sm">Retour</a>
            <a href="index.php?action=livraisons/edit&id=<?= (int)$bl['id'] ?>" class="btn btn-primary btn-sm">Modifier</a>
            <button class="btn btn-outline-dark btn-sm" onclick="window.print()">Imprimer</button>

            <?php if (!$isValidated): ?>
                <form method="post" action="index.php?action=livraisons/validate&id=<?= (int)$bl['id'] ?>" class="d-inline">
                    <button type="submit" class="btn btn-success btn-sm"
                        onclick="return confirm('Valider ce BL ? Cette action va déduire le stock du dépôt.');">
                        Valider (déduire stock)
                    </button>
                </form>
            <?php endif; ?>
        </div>
    </div>

    <div class="card">
        <div class="card-header">
            Lignes
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-sm align-middle">
                    <thead class="table-light">
                        <tr>
                            <th>Article</th>
                            <th>Description</th>
                            <th class="text-end">Demandée</th>
                            <th class="text-end">Livrée</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($lignes as $ln): ?>
                            <tr>
                                <td class="fw-semibold"><?= htmlspecialchars($ln['nom_art'] ?? '') ?></td>
                                <td class="text-muted small">
                                    <?= !empty($ln['description']) ? nl2br(htmlspecialchars($ln['description'])) : '—' ?>
                                </td>
                                <td class="text-end"><?= number_format((float)$ln['quantite_demandee'], 0, ',', ' ') ?></td>
                                <td class="text-end"><?= number_format((float)$ln['quantite_livree'], 0, ',', ' ') ?></td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>

            <div class="mt-4 d-flex justify-content-between">
                <div class="text-muted small">
                    Signature / Cachet:
                    <div style="height:70px; border:1px dashed #ccc; border-radius:6px;"></div>
                </div>
                <div class="text-muted small text-end">
                    Réceptionné par:
                    <div style="height:70px; border:1px dashed #ccc; border-radius:6px;"></div>
                </div>
            </div>
        </div>
    </div>
</div>