<?php include '../views/layout.php'; ?>

<div class="container mt-4">
  <div class="d-flex justify-content-between align-items-center mb-3">
    <h4 class="mb-0">Factures Fournisseurs</h4>
    <a href="index.php?action=factures_fournisseurs/create" class="btn btn-success btn-sm">+ Nouvelle facture</a>
  </div>

  <form method="get" class="mb-3 d-flex" style="gap:8px;">
    <input type="hidden" name="action" value="factures_fournisseurs">
    <input type="text" name="search_term" class="form-control form-control-sm" placeholder="Rechercher..." value="<?= htmlspecialchars($search_term ?? '') ?>">
    <button type="submit" class="btn btn-primary btn-sm">Rechercher</button>
  </form>

  <div class="table-responsive">
    <table class="table table-sm table-bordered table-hover">
      <thead>
        <tr>
          <th>ID</th>
          <th>Numéro</th>
          <th>Date</th>
          <th>Fournisseur</th>
          <th>Dépôt</th>
          <th>Statut</th>
          <th>Total TTC</th>
          <th>Actions</th>
        </tr>
      </thead>
      <tbody>
        <?php if (empty($factures)): ?>
          <tr>
            <td colspan="8" class="text-center text-muted">Aucune facture fournisseur.</td>
          </tr>
        <?php else: ?>
          <?php foreach ($factures as $ff): ?>
            <tr>
              <td><?= (int)$ff['id'] ?></td>
              <td><?= htmlspecialchars($ff['numero']) ?></td>
              <td><?= htmlspecialchars($ff['date']) ?></td>
              <td><?= htmlspecialchars($ff['nom_fournisseurs']) ?></td>
              <td><?= htmlspecialchars($ff['depot_nom']) ?></td>
              <td><?= htmlspecialchars($ff['statut']) ?></td>
              <td><?= number_format((float)$ff['total_ttc'], 0, ',', ' ') ?></td>
              <td>
                <a href="index.php?action=factures_fournisseurs/show&id=<?= (int)$ff['id'] ?>" class="btn btn-info btn-sm">Voir</a>
                <a href="index.php?action=factures_fournisseurs/edit&id=<?= (int)$ff['id'] ?>" class="btn btn-primary btn-sm">Modifier</a>
                <?php if (($ff['statut'] ?? 'draft') !== 'validated'): ?>
                  <a href="index.php?action=factures_fournisseurs/validate&id=<?= (int)$ff['id'] ?>"
                     class="btn btn-warning btn-sm"
                     onclick="return confirm('Valider cette facture fournisseur ? Le stock sera incrémenté.');">
                    Valider
                  </a>
                <?php endif; ?>
                <a href="index.php?action=factures_fournisseurs/delete&id=<?= (int)$ff['id'] ?>"
                   class="btn btn-danger btn-sm"
                   onclick="return confirm('Supprimer cette facture fournisseur ?');">
                  Supprimer
                </a>
              </td>
            </tr>
          <?php endforeach; ?>
        <?php endif; ?>
      </tbody>
    </table>
  </div>

  <?php
    $totalPages = max(1, (int)ceil(($total ?? 0) / ($parPage ?? 10)));
    $currentPage = max(1, (int)($page ?? 1));
    $search = $search_term ?? '';
  ?>
  <?php if ($totalPages > 1): ?>
    <nav class="mt-3">
      <ul class="pagination pagination-sm">
        <?php for ($p = 1; $p <= $totalPages; $p++): ?>
          <li class="page-item <?= $p === $currentPage ? 'active' : '' ?>">
            <a class="page-link" href="index.php?action=factures_fournisseurs&page=<?= $p ?>&search_term=<?= urlencode($search) ?>">
              <?= $p ?>
            </a>
          </li>
        <?php endfor; ?>
      </ul>
    </nav>
  <?php endif; ?>
</div>

<?php include '../views/footer.php'; ?>