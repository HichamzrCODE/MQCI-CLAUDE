<?php
$pageTitle = "Livraisons";
include '../views/layout.php';
?>

<div class="container-fluid mt-4">
  <div class="d-flex justify-content-between align-items-center mb-3">
    <h1 style="font-size:1.3rem;">🚚 Livraisons (BL)</h1>
    <div class="text-muted small">Création uniquement depuis un devis validé</div>
  </div>

  <div class="table-responsive">
    <table class="table table-hover">
      <thead class="table-light">
        <tr>
          <th>N° BL</th>
          <th>Date</th>
          <th>Client</th>
          <th>Dépôt</th>
          <th>Devis</th>
          <th>Statut</th>
          <th class="text-end">Actions</th>
        </tr>
      </thead>
      <tbody>
        <?php foreach ($livraisons as $bl): ?>
          <?php $badge = ($bl['statut'] === 'validated') ? 'success' : 'secondary'; ?>
          <tr>
            <td><strong><?= htmlspecialchars($bl['numero']) ?></strong></td>
            <td><?= htmlspecialchars($bl['date']) ?></td>
            <td><?= htmlspecialchars($bl['client_nom']) ?></td>
            <td><?= htmlspecialchars($bl['depot_nom']) ?></td>
            <td><?= htmlspecialchars($bl['devis_numero']) ?></td>
            <td><span class="badge bg-<?= $badge ?>"><?= htmlspecialchars($bl['statut']) ?></span></td>
            <td class="text-end">
              <a class="btn btn-sm btn-info" href="index.php?action=livraisons/show&id=<?= (int)$bl['id'] ?>">
                <i class="fas fa-eye"></i>
              </a>
              <a class="btn btn-sm btn-primary" href="index.php?action=livraisons/edit&id=<?= (int)$bl['id'] ?>">
                <i class="fas fa-edit"></i>
              </a>
            </td>
          </tr>
        <?php endforeach; ?>
      </tbody>
    </table>
  </div>
</div>