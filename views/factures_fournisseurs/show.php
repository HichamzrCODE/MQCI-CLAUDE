<?php include '../views/layout.php'; ?>

<div class="container mt-4">
  <div class="d-flex justify-content-between align-items-center mb-3">
    <h4 class="mb-0">Facture Fournisseur</h4>

    <div class="d-flex" style="gap:8px;">
      <a class="btn btn-secondary btn-sm" href="index.php?action=factures_fournisseurs">← Retour</a>
      <a class="btn btn-primary btn-sm" href="index.php?action=factures_fournisseurs/edit&id=<?= (int)$facture['id'] ?>">Modifier</a>
    </div>
  </div>

  <div class="card mb-3">
    <div class="card-body">
      <div class="row">
        <div class="col-md-3"><strong>Numéro :</strong> <?= htmlspecialchars($facture['numero']) ?></div>
        <div class="col-md-3"><strong>Date :</strong> <?= htmlspecialchars($facture['date']) ?></div>
        <div class="col-md-3"><strong>Fournisseur :</strong> <?= htmlspecialchars($facture['nom_fournisseurs']) ?></div>
        <div class="col-md-3"><strong>Dépôt :</strong> <?= htmlspecialchars($facture['depot_nom']) ?></div>
      </div>

      <div class="row mt-2">
        <div class="col-md-3"><strong>Statut :</strong> <?= htmlspecialchars($facture['statut']) ?></div>
        <div class="col-md-3"><strong>Total HT :</strong> <?= number_format((float)$facture['total_ht'], 0, ',', ' ') ?></div>
        <div class="col-md-3"><strong>TVA :</strong> <?= number_format((float)$facture['total_tva'], 0, ',', ' ') ?></div>
        <div class="col-md-3"><strong>Total TTC :</strong> <?= number_format((float)$facture['total_ttc'], 0, ',', ' ') ?></div>
      </div>

      <?php if (!empty($facture['notes'])): ?>
        <div class="mt-3">
          <strong>Notes :</strong><br>
          <?= nl2br(htmlspecialchars($facture['notes'])) ?>
        </div>
      <?php endif; ?>
    </div>
  </div>

  <div class="table-responsive">
    <table class="table table-sm table-bordered">
      <thead>
        <tr>
          <th>#</th>
          <th>Article</th>
          <th>Description</th>
          <th>Quantité</th>
          <th>PU Achat</th>
          <th>Total</th>
        </tr>
      </thead>
      <tbody>
        <?php foreach ($lignes as $i => $ln): ?>
          <tr>
            <td><?= $i + 1 ?></td>
            <td><?= htmlspecialchars($ln['nom_art'] ?? '') ?></td>
            <td><?= htmlspecialchars($ln['description'] ?? '') ?></td>
            <td><?= (int)$ln['quantite'] ?></td>
            <td><?= number_format((float)$ln['prix_unitaire'], 0, ',', ' ') ?></td>
            <td><?= number_format((float)$ln['total'], 0, ',', ' ') ?></td>
          </tr>
        <?php endforeach; ?>
      </tbody>
      <tfoot>
        <tr>
          <th colspan="5" class="text-right">Total</th>
          <th><?= number_format((float)$facture['total_ttc'], 0, ',', ' ') ?></th>
        </tr>
      </tfoot>
    </table>
  </div>
</div>

<?php include '../views/footer.php'; ?>