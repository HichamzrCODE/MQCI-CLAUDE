<?php
$versement = $versement ?? null;
if (!$versement) { echo "Versement fournisseur introuvable."; return; }

$mode = $versement['mode'] ?? '';
$labelBanque = 'Banque / Établissement';
if ($mode === 'especes') $labelBanque = 'Caisse';
elseif ($mode === 'mobile_money') $labelBanque = 'Mobile';
elseif (in_array($mode, ['virement','cheque','depot_especes_banque'], true)) $labelBanque = 'Banque';
?>
<!doctype html>
<html lang="fr">
<head>
  <meta charset="utf-8">
  <title>Bordereau Versement Fournisseur - <?= htmlspecialchars($versement['numero']) ?></title>
  <style>
    body { font-family: Arial, sans-serif; font-size: 13px; color:#111; }
    .wrap { max-width: 820px; margin: 20px auto; padding: 18px; border: 1px solid #ddd; }
    .top { display:flex; justify-content:space-between; gap:16px; }
    .muted { color:#666; }
    h2 { margin:0 0 6px; font-size:18px; }
    .box { margin-top: 12px; }
    table { width:100%; border-collapse: collapse; }
    td { border:1px solid #ddd; padding:8px; vertical-align: top; }
    .right { text-align:right; }
    .btnbar { margin-bottom: 10px; }
    button { padding:6px 10px; cursor:pointer; }
    .sign { display:flex; gap:18px; margin-top: 22px; }
    .sign > div { flex:1; }
    .sign .area { height:70px; border:1px dashed #bbb; }
    @media print {
      .btnbar { display:none; }
      .wrap { border:none; padding:0; margin:0; }
    }
  </style>
</head>
<body>
  <div class="wrap">
    <div class="btnbar">
      <button onclick="window.print()">Imprimer / PDF</button>
      <button onclick="window.close()">Fermer</button>
    </div>

    <div class="top">
      <div>
        <h2>Bordereau de versement fournisseur</h2>
        <div class="muted">N°: <b><?= htmlspecialchars($versement['numero']) ?></b></div>
        <div class="muted">Date: <?= htmlspecialchars($versement['date']) ?></div>
      </div>
      <div class="right">
        <div class="muted">Fournisseur</div>
        <div><b><?= htmlspecialchars($versement['fournisseur_nom'] ?? '') ?></b></div>
        <div class="muted">Facture : <?= htmlspecialchars($versement['facture_numero'] ?? '') ?></div>
      </div>
    </div>

    <div class="box">
      <table>
        <tr><td width="35%">Mode</td><td><?= htmlspecialchars($mode) ?></td></tr>
        <tr><td>Statut</td><td><?= htmlspecialchars($versement['statut'] ?? '') ?></td></tr>
        <tr><td>Référence</td><td><?= htmlspecialchars($versement['reference'] ?? '') ?></td></tr>
        <tr><td><?= htmlspecialchars($labelBanque) ?></td><td><?= htmlspecialchars($versement['banque'] ?? '') ?></td></tr>

        <?php if ($mode === 'cheque'): ?>
          <tr><td>Établissement payeur</td><td><?= htmlspecialchars($versement['etablissement_payeur'] ?? '') ?></td></tr>
        <?php endif; ?>

        <tr>
          <td><b>Montant</b></td>
          <td class="right"><b><?= number_format((float)$versement['montant'], 2, ',', ' ') ?></b></td>
        </tr>

        <?php if (!empty($versement['note'])): ?>
          <tr><td>Note</td><td><?= htmlspecialchars($versement['note']) ?></td></tr>
        <?php endif; ?>
      </table>
    </div>

    <div class="sign">
      <div>
        <div class="muted">Signature caisse / banque</div>
        <div class="area"></div>
      </div>
      <div>
        <div class="muted">Signature fournisseur</div>
        <div class="area"></div>
      </div>
    </div>
  </div>

  <script>
    window.onload = () => window.print();
  </script>
</body>
</html>