<?php
$pageTitle = "Modifier BL";
include '../views/layout.php';

$bl = $bl ?? null;
$lignes = $lignes ?? [];
$error = $error ?? null;
$stockWarnings = $stockWarnings ?? [];

if (!$bl) {
    echo "<div class='container mt-4'><div class='alert alert-danger'>BL introuvable.</div></div>";
    return;
}

$isValidated = (($bl['statut'] ?? 'draft') === 'validated');
$isAdmin = (($_SESSION['role'] ?? '') === 'admin');
$readonly = ($isValidated && !$isAdmin);

// support erreur passé en query string
if (!$error && !empty($_GET['error'])) {
    $error = $_GET['error'];
}
?>

<div class="container-fluid mt-4">
    <div class="d-flex justify-content-between align-items-center mb-3">
        <div>
            <h1 class="h4 mb-1">Bon de Livraison (BL)</h1>
            <div class="text-muted small">
                N° <strong><?= htmlspecialchars($bl['numero']) ?></strong>
                • Client: <strong><?= htmlspecialchars($bl['client_nom']) ?></strong>
                • Dépôt: <strong><?= htmlspecialchars($bl['depot_nom']) ?></strong>
                • Devis: <strong><?= htmlspecialchars($bl['devis_numero']) ?></strong>
                • Date: <strong><?= htmlspecialchars($bl['date']) ?></strong>
            </div>
            <div class="text-muted small mt-1">
                Document de livraison — sans valeur commerciale.
            </div>
        </div>

        <div class="d-flex gap-2">
            <a href="index.php?action=livraisons" class="btn btn-outline-secondary btn-sm"
               data-bs-toggle="tooltip" data-bs-title="Retour à la liste des BL">
                <i class="fas fa-arrow-left"></i>
            </a>

            <a href="index.php?action=devis/detail&id=<?= (int)$bl['devis_id'] ?>" class="btn btn-outline-secondary btn-sm"
               data-bs-toggle="tooltip" data-bs-title="Retour au devis">
                <i class="fas fa-file-invoice"></i>
            </a>

            <a href="index.php?action=livraisons/show&id=<?= (int)$bl['id'] ?>" class="btn btn-outline-primary btn-sm"
               data-bs-toggle="tooltip" data-bs-title="Voir (lecture seule)">
                <i class="fas fa-eye"></i>
            </a>

            <?php if (!$isValidated): ?>
                <form method="post" action="index.php?action=livraisons/validate&id=<?= (int)$bl['id'] ?>" class="d-inline">
                    <button type="submit"
                            class="btn btn-success btn-sm"
                            data-bs-toggle="tooltip"
                            data-bs-title="Valider le BL (déduire stock)"
                            onclick="return confirm('Valider ce BL ? Cette action va déduire le stock du dépôt.');">
                        <i class="fas fa-check"></i>
                    </button>
                </form>
            <?php else: ?>
                <span class="badge bg-success align-self-center">validated</span>
            <?php endif; ?>
        </div>
    </div>

    <?php if ($error): ?>
        <div class="alert alert-danger alert-dismissible fade show" role="alert">
            <?= htmlspecialchars($error) ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Fermer"></button>
        </div>
    <?php endif; ?>

    <?php if ($readonly): ?>
        <div class="alert alert-warning">
            BL déjà validé : lecture seule. (Admin peut modifier)
        </div>
    <?php endif; ?>

    <div class="card">
        <div class="card-header d-flex justify-content-between align-items-center">
            <span>Lignes</span>
            <span class="text-muted small">Quantité livrée ≤ quantité demandée</span>
        </div>

        <div class="card-body">
            <form method="post" action="index.php?action=livraisons/edit&id=<?= (int)$bl['id'] ?>">
                <div class="table-responsive">
                    <table class="table table-sm align-middle">
                        <thead class="table-light">
                            <tr>
                                <th style="width:35%;">Article</th>
                                <th style="width:33%;">Description</th>
                                <th class="text-end" style="width:12%;">Demandée</th>
                                <th class="text-end" style="width:20%;">Livrée</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($lignes as $ln): ?>
                                <?php
                                    $ligneId = (int)$ln['id'];
                                    $warn = $stockWarnings[$ligneId] ?? null;

                                    $qd = (float)($ln['quantite_demandee'] ?? 0);
                                    $ql = (float)($ln['quantite_livree'] ?? 0);
                                ?>
                                <tr>
                                    <td>
                                        <div class="fw-semibold"><?= htmlspecialchars($ln['nom_art'] ?? '') ?></div>
                                        <?php if (!empty($ln['sku'])): ?>
                                            <div class="text-muted small">SKU: <?= htmlspecialchars($ln['sku']) ?></div>
                                        <?php endif; ?>
                                        <?php if ($warn): ?>
                                            <div class="text-danger small">
                                                <i class="fas fa-triangle-exclamation"></i>
                                                <?= htmlspecialchars($warn) ?>
                                            </div>
                                        <?php endif; ?>
                                    </td>

                                    <td class="text-muted small">
                                        <?= !empty($ln['description']) ? nl2br(htmlspecialchars($ln['description'])) : '—' ?>
                                    </td>

                                    <td class="text-end">
                                        <?= number_format($qd, 2, ',', ' ') ?>
                                    </td>

                                    <td class="text-end">
                                        <input
                                            type="number"
                                            step="1"
                                            min="0"
                                            max="<?= htmlspecialchars((string)$qd) ?>"
                                            class="form-control form-control-sm text-end"
                                            style="max-width:140px; display:inline-block;"
                                            name="lignes[<?= $ligneId ?>][quantite_livree]"
                                            value="<?= htmlspecialchars((string)$ql) ?>"
                                            <?= $readonly ? 'disabled' : '' ?>
                                        >
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>

                <?php if (!$readonly): ?>
                    <div class="d-flex justify-content-end gap-2">
                        <button type="submit" class="btn btn-primary">
                            Enregistrer
                        </button>
                    </div>
                <?php endif; ?>
            </form>

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