<?php
$pageTitle = "Versements";
include '../views/layout.php';
$versements  = $versements  ?? [];
$page        = $page        ?? 1;
$total_pages = $total_pages ?? 1;
$total       = $total       ?? 0;
?>

<div class="container mt-4">
  <div class="d-flex justify-content-between align-items-center mb-3">
    <h4 class="mb-0">
      Versements
      <span class="badge bg-secondary" style="font-size:0.8rem;"><?= $total ?> au total</span>
    </h4>
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
          <th class="text-end">Montant</th>
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
              <td class="text-end"><?= number_format((float)$v['montant'], 0, ',', ' ') ?></td>
              <td><?= htmlspecialchars($v['reference'] ?? '') ?></td>
              <td><?= htmlspecialchars($v['banque'] ?? '') ?></td>
              <td>
                <a class="btn btn-sm btn-secondary"
                   href="index.php?action=versements/edit&id=<?= (int)$v['id'] ?>">Ouvrir</a>
                <?php if (($_SESSION['role'] ?? '') === 'admin'): ?>
                  <form method="post"
                        action="index.php?action=versements/hide&id=<?= (int)$v['id'] ?>"
                        style="display:inline;"
                        onsubmit="return confirm('Supprimer ce versement ?');">
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

  <!-- PAGINATION -->
  <?php if ($total_pages > 1): ?>
  <nav class="d-flex align-items-center gap-2 mt-3 flex-wrap">
    <?php if ($page > 1): ?>
      <a href="index.php?action=versements&page=1" class="btn btn-sm btn-outline-secondary">&laquo; Début</a>
      <a href="index.php?action=versements&page=<?= $page - 1 ?>" class="btn btn-sm btn-outline-secondary">&#8249; Préc.</a>
    <?php else: ?>
      <button class="btn btn-sm btn-outline-secondary" disabled>&laquo; Début</button>
      <button class="btn btn-sm btn-outline-secondary" disabled>&#8249; Préc.</button>
    <?php endif; ?>

    <span class="btn btn-sm btn-primary" style="pointer-events:none;">
      Page <?= $page ?> / <?= $total_pages ?>
    </span>
    <span class="text-muted" style="font-size:0.9rem;">(<?= $total ?> versement<?= $total > 1 ? 's' : '' ?>)</span>

    <?php if ($page < $total_pages): ?>
      <a href="index.php?action=versements&page=<?= $page + 1 ?>" class="btn btn-sm btn-outline-secondary">Suiv. &#8250;</a>
      <a href="index.php?action=versements&page=<?= $total_pages ?>" class="btn btn-sm btn-outline-secondary">Fin &raquo;</a>
    <?php else: ?>
      <button class="btn btn-sm btn-outline-secondary" disabled>Suiv. &#8250;</button>
      <button class="btn btn-sm btn-outline-secondary" disabled>Fin &raquo;</button>
    <?php endif; ?>
  </nav>
  <?php endif; ?>

</div>
