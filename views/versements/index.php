<?php
$pageTitle = "Versements";
include '../views/layout.php';
$versements = $versements ?? [];
?>

<div class="container mt-4">
  <div class="d-flex justify-content-between align-items-center mb-3">
    <h4 class="mb-0">Versements</h4>
    <a class="btn btn-primary btn-sm" href="index.php?action=versements/create">+ Nouveau versement</a>
  </div>

  <div class="table-responsive">
    <table class="table table-sm table-bordered">
      <thead>
        <tr>
          <th>#</th>
          <th>Numéro</th>
          <th>Date</th>
          <th>Client</th>
          <th>Mode</th>
          <th>Statut</th>
          <th class="text-right">Montant</th>
          <th>Référence</th>
          <th>Banque</th>
          <th>Action</th>
        </tr>
      </thead>
      <tbody>
        <?php if (!$versements): ?>
          <tr><td colspan="10" class="text-center text-muted">Aucun versement.</td></tr>
        <?php else: ?>
          <?php foreach ($versements as $v): ?>
            <tr>
              <td><?= (int)$v['id'] ?></td>
              <td><b><?= htmlspecialchars($v['numero']) ?></b></td>
              <td><?= htmlspecialchars($v['date']) ?></td>
              <td><?= htmlspecialchars($v['client_nom'] ?? '') ?></td>
              <td><?= htmlspecialchars($v['mode']) ?></td>
              <td><?= htmlspecialchars($v['statut']) ?></td>
              <td class="text-right"><?= number_format((float)$v['montant'], 0, ',', ' ') ?></td>
              <td><?= htmlspecialchars($v['reference'] ?? '') ?></td>
              <td><?= htmlspecialchars($v['banque'] ?? '') ?></td>
              <td>
  <a class="btn btn-sm btn-secondary"
     href="index.php?action=versements/edit&id=<?= (int)$v['id'] ?>">Ouvrir</a>

  <?php if (($_SESSION['role'] ?? '') === 'admin'): ?>
    <form method="post"
          action="index.php?action=versements/hide&id=<?= (int)$v['id'] ?>"
          style="display:inline;"
          onsubmit="return confirm('Supprimer ce versement?');">
      <?= $csrf_field ?? '' ?>
      <button type="submit" class="btn btn-sm btn-danger">x</button>
    </form>
  <?php endif; ?>
</td>
            </tr>
          <?php endforeach; ?>
        <?php endif; ?>
      </tbody>
    </table>
  </div>
</div>