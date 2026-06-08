<?php
$pageTitle = "Factures";
include '../views/layout.php';

$factures = $factures ?? [];
$today    = date('Y-m-d');
?>

<style>
.badge-echu    { background:#dc3545; color:#fff; }
.badge-urgent  { background:#fd7e14; color:#fff; }
.badge-ok      { background:#198754; color:#fff; }
.badge-draft   { background:#6c757d; color:#fff; }
.echu-row td   { background:#fff5f5 !important; }
.urgent-row td { background:#fff8f0 !important; }
</style>

<div class="container-fluid mt-4">
    <div class="d-flex justify-content-between align-items-center mb-3">
        <h1 class="h4 mb-0">Factures</h1>

        <!-- Légende -->
        <div class="d-flex gap-2 align-items-center" style="font-size:0.85rem;">
            <span class="badge badge-echu">Échu</span> Dépassé
            <span class="badge badge-urgent">Urgent</span> &lt; 7 jours
            <span class="badge badge-ok">OK</span> À temps
        </div>
    </div>

    <div class="card">
        <div class="card-header d-flex justify-content-between">
            <span>Liste des factures</span>
            <span class="text-muted" style="font-size:0.85rem;"><?= count($factures) ?> facture(s)</span>
        </div>
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-sm align-middle mb-0">
                    <thead class="table-light">
                        <tr>
                            <th>N°</th>
                            <th>Client</th>
                            <th>Date</th>
                            <th>Échéance</th>
                            <th>Retard</th>
                            <th>Statut</th>
                            <th class="text-end">Total TTC</th>
                            <th class="text-end">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (!$factures): ?>
                            <tr><td colspan="8" class="text-center text-muted py-3">Aucune facture</td></tr>
                        <?php endif; ?>

                        <?php foreach ($factures as $f):
                            $isValidated  = ($f['statut'] === 'validated');
                            $dateEcheance = $f['date_echeance'] ?? null;
                            $joursRetard  = 0;
                            $echeanceClass = '';
                            $rowClass      = '';
                            $badgeEcheance = '';

                            if ($isValidated && $dateEcheance) {
                                $joursRetard = (int)((strtotime($today) - strtotime($dateEcheance)) / 86400);

                                if ($joursRetard > 0) {
                                    // Échu
                                    $echeanceClass = 'badge-echu';
                                    $rowClass      = 'echu-row';
                                    $badgeEcheance = '🔴 ' . $joursRetard . 'j retard';
                                } elseif ($joursRetard >= -7) {
                                    // Urgent — moins de 7 jours
                                    $echeanceClass = 'badge-urgent';
                                    $rowClass      = 'urgent-row';
                                    $badgeEcheance = '🟠 ' . abs($joursRetard) . 'j restants';
                                } else {
                                    // OK
                                    $echeanceClass = 'badge-ok';
                                    $badgeEcheance = '🟢 ' . abs($joursRetard) . 'j restants';
                                }
                            }
                        ?>
                        <tr class="<?= $rowClass ?>">
                            <td class="fw-semibold"><?= htmlspecialchars($f['numero'] ?? '') ?></td>
                            <td><?= htmlspecialchars($f['client_nom'] ?? '') ?></td>
                            <td><?= $f['date'] ? date('d/m/Y', strtotime($f['date'])) : '' ?></td>
                            <td>
                                <?php if ($dateEcheance && $isValidated): ?>
                                    <?= date('d/m/Y', strtotime($dateEcheance)) ?>
                                    <br><span class="badge <?= $echeanceClass ?>" style="font-size:0.75rem;">
                                        <?= $badgeEcheance ?>
                                    </span>
                                <?php else: ?>
                                    <span class="text-muted">—</span>
                                <?php endif; ?>
                            </td>
                            <td>
                                <?php if ($isValidated && $joursRetard > 0): ?>
                                    <span class="text-danger fw-bold"><?= $joursRetard ?> j</span>
                                <?php else: ?>
                                    <span class="text-muted">—</span>
                                <?php endif; ?>
                            </td>
                            <td>
                                <span class="badge <?= $isValidated ? 'badge-ok' : 'badge-draft' ?>">
                                    <?= $isValidated ? 'Validée' : 'Brouillon' ?>
                                </span>
                            </td>
                            <td class="text-end fw-semibold">
                                <?= number_format((float)($f['total_ttc'] ?? 0), 0, ',', ' ') ?>
                            </td>
                            <td class="text-end">
                                <a class="btn btn-outline-primary btn-sm"
                                   href="index.php?action=factures/show&id=<?= (int)$f['id'] ?>">Voir</a>
                                <a class="btn btn-primary btn-sm"
                                   href="index.php?action=factures/edit&id=<?= (int)$f['id'] ?>">Modifier</a>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>
