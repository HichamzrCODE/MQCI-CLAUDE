<?php
$pageTitle = "Détail Devis";
include '../views/layout.php';

require_once __DIR__ . '/../../models/Settings.php';
$settingsModel = new Settings($db);

// Settings (entreprise sous logo)
$headerLine1 = $settingsModel->get('devis_header_line1', '');
$headerLine2 = $settingsModel->get('devis_header_line2', '');
$footerText  = $settingsModel->get('devis_footer_text', '');

// Logo = logo app
$logoUrl = $settingsModel->getLogoUrl();
if (!$logoUrl) $logoUrl = defined('BASE_URL') ? (BASE_URL . '/img/LOGO.png') : '';

if (!isset($devis)) {
    echo "<div class='container mt-4'><div class='alert alert-danger'>Devis non trouvé.</div></div>";
    return;
}

$statut = $devis['statut'] ?? 'draft';
$isValidated = ($statut === 'validated');
$badge = $isValidated ? 'success' : 'secondary';
$label = $isValidated ? 'Validé' : 'Brouillon';

$dateStr = !empty($devis['date']) ? (new DateTime($devis['date']))->format("d/m/Y") : '';

$client = $client ?? null;
$clientNom = $client['nom'] ?? ($nom_client ?? 'Client inconnu');
$clientVille = $client['ville'] ?? '';
$clientTel = $client['telephone'] ?? '';
$reference = $devis['reference'] ?? '';

// TVA
$tvaRate = 0.18;
$divisor = 1 + $tvaRate;

// Totaux calculés
$totalTtc = 0.0;
$totalHt = 0.0;
?>

<style>
.devis-sheet{
  background:#fff;
  border:1px solid #e5e7eb;
  border-radius:12px;
  padding:18px;
  max-width: 980px;
}
.devis-top{
  display:flex; justify-content:space-between; gap:18px;
  border-bottom:1px solid #eef2f7;
  padding-bottom:12px; margin-bottom:12px;
}
.brand{ min-width: 420px; }
.brand-logo img{ height:56px; width:auto; object-fit:contain; }
.brand-info{
  margin-top:8px; font-size:12px; color:#374151; line-height:1.25;
}
.brand-info .muted{ color:#6b7280; }

.meta{ text-align:right; min-width: 280px; }
.meta .title{ font-size:18px; font-weight:800; letter-spacing:.4px; margin:0; }
.meta .num{ font-size:15px; font-weight:700; margin-top:3px; }
.meta .date{ font-size:12.5px; font-weight:700; margin-top:6px; color:#111827; }
.meta .badge{ margin-top:6px; }

.client-box{
  display:flex; justify-content:space-between; gap:18px;
  background:#f9fafb; border:1px solid #eef2f7;
  border-radius:10px; padding:12px 14px; margin-bottom:12px;
}
.label{
  font-size:12px; color:#6b7280; font-weight:700;
  text-transform:uppercase; letter-spacing:.4px;
}
.val{
  font-size:13px; color:#111827; margin-top:3px; line-height:1.3;
}
.client-name{ font-weight:400; font-size:16px; } /* pas en gras */
.client-right{ text-align:right; min-width: 240px; }

.devis-actions{
  display:flex; gap:8px; justify-content:flex-end; flex-wrap:wrap;
  margin: 10px 0 12px 0;
}

.devis-table{
  width:100%; border-collapse:collapse; font-size:13px;
}
.devis-table thead th{
  background:#f3f4f6; border-bottom:1px solid #e5e7eb;
  padding:7px 10px; font-size:12px;
  text-transform:uppercase; letter-spacing:.4px; color:#374151;
}
.devis-table td{
  border-bottom:1px solid #eef2f7;
  padding:7px 10px; vertical-align:top;
}
.col-article{ width:44%; }
.col-qte{ width:10%; text-align:right; white-space:nowrap; }
.col-pu{ width:15%; text-align:right; white-space:nowrap; }
.col-total{ width:16%; text-align:right; white-space:nowrap; }

.article-name{ font-weight:400; color:#111827; } /* pas en gras */
.article-desc{
  font-size:12px; color:#6b7280; font-style:italic;
  margin-top:2px; white-space:pre-wrap;
}

.totals{ display:flex; justify-content:flex-end; margin-top:12px; }
.total-box{
  min-width:380px; border:1px solid #e5e7eb; border-radius:10px;
  padding:12px 14px; background:#fff;
}
.total-box .rowline{
  display:flex; justify-content:space-between; align-items:center; gap:12px;
  margin-top:6px;
}
.total-box .rowline:first-child{ margin-top:0; }
.total-box .lbl{
  font-size:12px; color:#6b7280; font-weight:700;
  text-transform:uppercase; letter-spacing:.4px;
}
.total-box .val{
  font-size:15px;
  font-weight:400;   /* normal */
  color:#111827;
}
.total-box .grand{
  border-top:1px solid #e5e7eb; padding-top:8px; margin-top:8px;
}
.total-box .grand .val{
  font-size:22px;
  font-weight:900;   /* gras uniquement ici */
}

.devis-footer{
  margin-top:16px; padding-top:10px; border-top:1px dashed #e5e7eb;
  font-size:11.5px; color:#6b7280; text-align:center; white-space:pre-wrap;
}

@media print{
  .devis-actions, .navbar, .sidebar, .breadcrumb-pill { display:none !important; }
  .main-content{ padding:0 !important; }
  .devis-sheet{ border:none !important; border-radius:0 !important; padding:0 !important; max-width:none !important; }
}
</style>

<div class="container mt-4">
  <div class="devis-sheet">

    <!-- TOP -->
    <div class="devis-top">
      <div class="brand">
        <?php if (!empty($logoUrl)): ?>
          <div class="brand-logo">
            <img src="<?= htmlspecialchars($logoUrl) ?>" alt="Logo">
          </div>
        <?php endif; ?>

        <div class="brand-info">
          <?php if ($headerLine1 !== ''): ?><div class="muted"><?= htmlspecialchars($headerLine1) ?></div><?php endif; ?>
          <?php if ($headerLine2 !== ''): ?><div class="muted"><?= htmlspecialchars($headerLine2) ?></div><?php endif; ?>
        </div>
      </div>

      <div class="meta">
        <p class="title">DEVIS</p>
        <div class="num"><?= htmlspecialchars($devis['numero']) ?></div>
        <?php if ($dateStr !== ''): ?><div class="date">Date : <?= htmlspecialchars($dateStr) ?></div><?php endif; ?>
        <span class="badge bg-<?= $badge ?>"><?= $label ?></span>
      </div>
    </div>

    <!-- CLIENT -->
    <div class="client-box">
      <div>
        <div class="label">Client</div>
        <div class="val">
          <div class="client-name"><?= htmlspecialchars($clientNom) ?></div>
          <?php if ($clientVille !== ''): ?><div>Ville : <?= htmlspecialchars($clientVille) ?></div><?php endif; ?>
          <?php if ($clientTel !== ''): ?><div>Tél : <?= htmlspecialchars($clientTel) ?></div><?php endif; ?>
        </div>
      </div>

      <div class="client-right">
        <?php if ($reference !== ''): ?>
          <div class="label">Référence</div>
          <div class="val"><?= htmlspecialchars($reference) ?></div>
        <?php endif; ?>
      </div>
    </div>

    <!-- ACTIONS (pas de BL, BL auto) -->
    <div class="devis-actions">
      <a href="index.php?action=devis" class="btn btn-outline-secondary btn-sm">Retour</a>

      <a href="index.php?action=devis/duplicate&id=<?= (int)$devis['id'] ?>" class="btn btn-outline-secondary btn-sm">
        Dupliquer
      </a>

      <?php if (!$isValidated): ?>
        <form method="post" action="index.php?action=devis/validate&id=<?= (int)$devis['id'] ?>" class="d-inline">
          <button type="submit" class="btn btn-success btn-sm"
                  onclick="return confirm('Valider ce devis ? Un BL sera créé automatiquement.');">
            Valider
          </button>
        </form>
      <?php endif; ?>

      <button onclick="window.print()" class="btn btn-primary btn-sm">Imprimer</button>
    </div>

    <!-- TABLE -->
    <table class="devis-table">
      <thead>
        <tr>
          <th class="col-article">Article</th>
          <th class="col-qte">Quantité</th>
          <th class="col-pu">PU HT</th>
          <th class="col-pu">PU TTC</th>
          <th class="col-total">Total</th>
        </tr>
      </thead>
      <tbody>
        <?php foreach ($lignesDevis as $ligne): ?>
          <?php
            $qte = (float)($ligne['quantite'] ?? 0);
            $puTtc = (float)($ligne['prix_unitaire'] ?? 0);
            $ligneTtc = (float)($ligne['total'] ?? ($qte * $puTtc));

            $puHt = $puTtc / $divisor;
            $ligneHt = $ligneTtc / $divisor;

            $totalTtc += $ligneTtc;
            $totalHt  += $ligneHt;
          ?>
          <tr>
            <td class="col-article">
              <div class="article-name"><?= htmlspecialchars($ligne['nom_art']) ?></div>
              <?php if (!empty($ligne['description'])): ?>
                <div class="article-desc"><?= htmlspecialchars($ligne['description']) ?></div>
              <?php endif; ?>
            </td>
            <td class="col-qte"><?= number_format($qte, 0, '', ' ') ?></td>
            <td class="col-pu"><?= number_format($puHt, 0, '', ' ') ?></td>
            <td class="col-pu"><?= number_format($puTtc, 0, '', ' ') ?></td>
            <td class="col-total"><?= number_format($ligneTtc, 0, '', ' ') ?></td>
          </tr>
        <?php endforeach; ?>
      </tbody>
    </table>

    <?php $tvaMontant = $totalTtc - $totalHt; ?>

    <!-- TOTALS -->
    <div class="totals">
      <div class="total-box">
        <div class="rowline">
          <div class="lbl">Total HT</div>
          <div class="val"><?= number_format($totalHt, 0, '', ' ') ?></div>
        </div>
        <div class="rowline">
          <div class="lbl">TVA 18%</div>
          <div class="val"><?= number_format($tvaMontant, 0, '', ' ') ?></div>
        </div>
        <div class="rowline grand">
          <div class="lbl">Total TTC</div>
          <div class="val"><?= number_format($totalTtc, 0, '', ' ') ?></div>
        </div>
      </div>
    </div>

    <!-- FOOTER -->
    <?php if (trim($footerText) !== ''): ?>
      <div class="devis-footer"><?= htmlspecialchars($footerText) ?></div>
    <?php endif; ?>

  </div>
</div>