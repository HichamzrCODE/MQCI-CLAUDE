<?php
$cumul = 0;
?>
<img src="http://localhost/S6/public/img/LOGO.png" style="width: 200px; height: auto;">
<h3 class="text-center mb-4">RELEVE <?= htmlspecialchars($client_nom ?? '') ?></h3>
<table class="table table-bordered">
  <thead class="thead-light">
    <tr>
      <th>Date</th>
      <th>BC CLIENT</th>
      <th>N° FACTURE</th>
      <th>Montant</th>
      <th>Versement</th>
    </tr>
  </thead>
  <tbody>
    <?php if (empty($lignes)): ?>
      <tr><td colspan="6" class="text-center">Désolé, Relevé non disponible</td></tr>
    <?php else: ?>
      <?php foreach($lignes as $ligne): ?>
        <?php $cumul += $ligne['montant'] - $ligne['versement']; ?>
        <tr>
          <td><?= date('d/m/Y', strtotime($ligne['date_operation'])) ?></td>
          <td><?= htmlspecialchars($ligne['bc_client']) ?></td>
          <td><?= htmlspecialchars($ligne['numero_facture']) ?></td>
          <td><?= $ligne['montant'] != 0 ? number_format($ligne['montant'],0,',',' ') : '' ?></td>
          <td><?= $ligne['versement'] != 0 ? number_format($ligne['versement'],0,',',' ') : '' ?></td>
        </tr>
      <?php endforeach; ?>
    <?php endif; ?>
  </tbody>
</table>

<?php if (isset($total_general)): ?>
  <div class="text-end mt-2" style="text-align: right; margin-right: 20px;">
    <strong>Total général :</strong>
    <span class="text-primary"><?= number_format($total_general, 0, ',', ' ') ?></span>
  </div>
<?php endif; ?>

<?php if (isset($total_lignes) && $total_lignes > 10): ?>
  <div class="text-center">
    <a href="index.php?action=releve/show&id=<?= $releve_id ?>" target="_blank" class="btn btn-info btn-sm mt-2">
      Voir tout le relevé
    </a>
  </div>
<?php endif; ?>