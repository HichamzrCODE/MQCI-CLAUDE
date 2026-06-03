<?php
$pageTitle = "Versement fournisseur";
include '../views/layout.php';

$versement = $versement ?? null;
$error = $error ?? null;
if (!$versement) { echo "Versement fournisseur introuvable."; return; }

$mode = $versement['mode'] ?? '';

$banqueLabel = 'Banque / Établissement';
if ($mode === 'especes') $banqueLabel = 'Caisse';
elseif ($mode === 'mobile_money') $banqueLabel = 'Mobile';
elseif (in_array($mode, ['virement','cheque','depot_especes_banque'], true)) $banqueLabel = 'Banque';

$modeBadge = [
  'especes' => 'secondary',
  'virement' => 'primary',
  'cheque' => 'warning',
  'mobile_money' => 'info',
  'depot_especes_banque' => 'dark'
][$mode] ?? 'secondary';
?>

<div class="container mt-4" style="max-width: 900px;">
  <div class="d-flex justify-content-between align-items-center mb-3">
    <div>
      <h4 class="mb-0">Versement fournisseur <?= htmlspecialchars($versement['numero']) ?></h4>
      <div class="text-muted small">
        Fournisseur: <b><?= htmlspecialchars($versement['fournisseur_nom'] ?? '') ?></b>
        &nbsp;•&nbsp;
        Facture: <b><?= htmlspecialchars($versement['facture_numero'] ?? '') ?></b>
        &nbsp;•&nbsp;
        Mode:
        <span class="badge badge-<?= htmlspecialchars($modeBadge) ?>">
          <?= htmlspecialchars($mode) ?>
        </span>
      </div>
    </div>

    <div class="btn-group">
      <a class="btn btn-secondary btn-sm" href="index.php?action=versements_fournisseurs">← Retour</a>

      <a class="btn btn-outline-primary btn-sm"
         target="_blank"
         href="index.php?action=versements_fournisseurs/print&id=<?= (int)$versement['id'] ?>">
        Imprimer bordereau
      </a>
    </div>
  </div>

  <?php if ($error): ?>
    <div class="alert alert-danger"><?= htmlspecialchars($error) ?></div>
  <?php endif; ?>

  <div class="card">
    <div class="card-body p-3">
      <form method="post" action="index.php?action=versements_fournisseurs/edit&id=<?= (int)$versement['id'] ?>" autocomplete="off">
        <?= $csrf_field ?? '' ?>

        <div class="form-row">
          <div class="form-group col-md-3 mb-2">
            <label class="mb-1 font-weight-bold">Date</label>
            <input type="date" name="date" class="form-control form-control-sm"
                   value="<?= htmlspecialchars($versement['date']) ?>" required>
          </div>

          <div class="form-group col-md-3 mb-2">
            <label class="mb-1 font-weight-bold">Montant</label>
            <input type="text" name="montant" class="form-control form-control-sm"
                   value="<?= htmlspecialchars(number_format((float)$versement['montant'], 2, ',', ' ')) ?>" required>
          </div>

          <div class="form-group col-md-3 mb-2">
            <label class="mb-1 font-weight-bold">Statut</label>
            <select name="statut" class="form-control form-control-sm" required>
              <?php
                $st = $versement['statut'] ?? 'en_attente';
                $options = ['en_attente'=>'En attente','effectue'=>'Effectué','rejete'=>'Rejeté','annule'=>'Annulé'];
                foreach ($options as $k => $lbl) {
                  $sel = ($st === $k) ? 'selected' : '';
                  echo "<option value=\"{$k}\" {$sel}>{$lbl}</option>";
                }
              ?>
            </select>
          </div>

          <div class="form-group col-md-3 mb-2">
            <label class="mb-1 font-weight-bold">Référence</label>
            <input type="text" name="reference" class="form-control form-control-sm"
                   value="<?= htmlspecialchars($versement['reference'] ?? '') ?>">
          </div>
        </div>

        <div class="form-row">
          <div class="form-group col-md-6 mb-2">
            <label class="mb-1 font-weight-bold"><?= htmlspecialchars($banqueLabel) ?></label>
            <input type="text" name="banque" class="form-control form-control-sm"
                   value="<?= htmlspecialchars($versement['banque'] ?? '') ?>">
          </div>

          <div class="form-group col-md-6 mb-2">
            <label class="mb-1 font-weight-bold">Note</label>
            <input type="text" name="note" class="form-control form-control-sm"
                   value="<?= htmlspecialchars($versement['note'] ?? '') ?>">
          </div>
        </div>

        <?php if ($mode === 'cheque'): ?>
          <div class="form-row">
            <div class="form-group col-md-6 mb-2">
              <label class="mb-1 font-weight-bold">Établissement payeur</label>
              <input type="text" name="etablissement_payeur" class="form-control form-control-sm"
                     value="<?= htmlspecialchars($versement['etablissement_payeur'] ?? '') ?>" required>
            </div>
          </div>
        <?php endif; ?>

        <div class="d-flex justify-content-between align-items-center mt-3">
          <div>
            <button type="submit" class="btn btn-primary btn-sm">Mettre à jour</button>

            <?php if (($versement['statut'] ?? '') !== 'annule'): ?>
              <a class="btn btn-outline-danger btn-sm"
                 href="index.php?action=versements_fournisseurs/cancel&id=<?= (int)$versement['id'] ?>"
                 onclick="return confirm('Annuler ce versement fournisseur ?');">
                Annuler
              </a>
            <?php endif; ?>
          </div>
        </div>
      </form>
    </div>
  </div>
</div>