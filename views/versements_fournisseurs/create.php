<?php
$pageTitle = "Nouveau Versement Fournisseur";
include '../views/layout.php';

$error = $error ?? null;
$mode = $_GET['mode'] ?? '';

$fournisseurs = $fournisseurs ?? [];
$fournisseurIdSelected = $_POST['fournisseur_id'] ?? '';
$fournisseurNomSelected = $_POST['fournisseur_nom'] ?? '';

if ($fournisseurNomSelected === '' && $fournisseurIdSelected !== '' && !empty($fournisseurs)) {
    foreach ($fournisseurs as $f) {
        if ((string)$f['id_fournisseurs'] === (string)$fournisseurIdSelected) {
            $fournisseurNomSelected = $f['nom_fournisseurs'];
            break;
        }
    }
}
?>

<style>
.v-line {
  display: flex;
  flex-wrap: nowrap;
  gap: 10px;
  align-items: flex-end;
}

.v-col { flex: 1 1 auto; }
.v-col-fixed { flex: 0 0 auto; }

.v-fournisseur { flex: 1 1 420px; min-width: 260px; }
.v-date { width: 130px; }
.v-montant { width: 180px; }

@media (max-width: 768px) {
  .v-line { flex-wrap: wrap; }
  .v-fournisseur { flex: 1 1 100%; }
  .v-date, .v-montant { width: 100%; }
}
</style>

<div class="container mt-4" style="max-width: 950px;">
  <div class="d-flex justify-content-between align-items-center mb-3">
    <h4 class="mb-0">Nouveau versement fournisseur</h4>
    <a class="btn btn-secondary btn-sm" href="index.php?action=versements_fournisseurs">← Retour</a>
  </div>

  <?php if ($error): ?>
    <div class="alert alert-danger"><?= htmlspecialchars($error) ?></div>
  <?php endif; ?>

  <?php if ($mode === ''): ?>
    <div class="card">
      <div class="card-body p-3">
        <div class="mb-2 font-weight-bold">Choisir le type d’opération</div>

        <div class="row">
          <div class="col-md-12 mb-2">
            <a class="btn btn-light border w-100 text-left py-2"
               href="index.php?action=versements_fournisseurs/create&mode=especes">
              <div class="d-flex align-items-center">
                <i class="fas fa-money-bill-wave mr-2 text-success"></i>
                <div class="font-weight-bold" style="line-height:1.1; margin-left: 15px;">Décaissement espèces</div>
              </div>
            </a>
          </div>

          <div class="col-md-12 mb-2">
            <a class="btn btn-light border w-100 text-left py-2"
               href="index.php?action=versements_fournisseurs/create&mode=depot_especes_banque">
              <div class="d-flex align-items-center">
                <i class="fas fa-piggy-bank mr-2 text-secondary"></i>
                <div class="font-weight-bold" style="line-height:1.1; margin-left: 15px;">Dépôt espèces banque</div>
              </div>
            </a>
          </div>

          <div class="col-md-12 mb-2">
            <a class="btn btn-light border w-100 text-left py-2"
               href="index.php?action=versements_fournisseurs/create&mode=virement">
              <div class="d-flex align-items-center">
                <i class="fas fa-building-columns mr-2 text-primary"></i>
                <div class="font-weight-bold" style="line-height:1.1; margin-left: 15px;">Virement fournisseur</div>
              </div>
            </a>
          </div>

          <div class="col-md-12 mb-2">
            <a class="btn btn-light border w-100 text-left py-2"
               href="index.php?action=versements_fournisseurs/create&mode=cheque">
              <div class="d-flex align-items-center">
                <i class="fas fa-money-check mr-2 text-warning"></i>
                <div class="font-weight-bold" style="line-height:1.1; margin-left: 15px;">Chèque fournisseur</div>
              </div>
            </a>
          </div>

          <div class="col-md-12 mb-2">
            <a class="btn btn-light border w-100 text-left py-2"
               href="index.php?action=versements_fournisseurs/create&mode=mobile_money">
              <div class="d-flex align-items-center">
                <i class="fas fa-mobile-screen mr-2" style="color:#7c3aed;"></i>
                <div class="font-weight-bold" style="line-height:1.1; margin-left: 15px;">Mobile money</div>
              </div>
            </a>
          </div>
        </div>
      </div>
    </div>

  <?php else: ?>
    <?php
      $modeLabel = [
        'especes' => 'Décaissement espèces',
        'virement' => 'Virement fournisseur',
        'cheque' => 'Chèque fournisseur',
        'mobile_money' => 'Mobile money',
        'depot_especes_banque' => 'Dépôt espèces banque',
      ][$mode] ?? 'Versement fournisseur';

      $refLabel = 'Référence';
      $refPlaceholder = 'Ex: TRX-..., 123456';
      $field3Label = 'Caisse';

      if ($mode === 'cheque') {
        $refLabel = 'N° chèque';
        $refPlaceholder = 'Ex: 123456';
      } elseif ($mode === 'virement') {
        $refLabel = 'Référence virement';
        $refPlaceholder = 'Ex: VIRM-2026-...';
      } elseif ($mode === 'mobile_money') {
        $refLabel = 'Référence transaction';
        $refPlaceholder = 'Ex: OM-... / WAVE-...';
      } elseif ($mode === 'depot_especes_banque') {
        $refLabel = 'Référence dépôt (bordereau)';
        $refPlaceholder = 'Ex: BORD-...';
      }

      if (in_array($mode, ['virement','cheque','depot_especes_banque'], true)) {
        $field3Label = 'Banque';
      } elseif ($mode === 'mobile_money') {
        $field3Label = 'Mobile';
      }

      $opAbbr = function ($op) {
        $op = strtolower(trim((string)$op));
        return match ($op) {
          'orange' => 'OM',
          'mtn'    => 'MTN',
          'moov'   => 'MOOV',
          'wave'   => 'WAVE',
          default  => strtoupper($op),
        };
      };
    ?>

    <div class="card mb-3">
      <div class="card-body py-2 px-3">
        <div class="d-flex justify-content-between align-items-center">
          <div>
            <div class="font-weight-bold" style="line-height:1.1;"><?= htmlspecialchars($modeLabel) ?></div>
            <div class="text-muted small" style="line-height:1.1;">Renseigne les champs ci-dessous</div>
          </div>
          <a class="btn btn-sm btn-outline-secondary" href="index.php?action=versements_fournisseurs/create">Changer</a>
        </div>
      </div>
    </div>

    <div class="card">
      <div class="card-body p-3">
        <form method="post" action="index.php?action=versements_fournisseurs/create&mode=<?= urlencode($mode) ?>" id="vf-form" autocomplete="off">
          <?= $csrf_field ?? '' ?>
          <input type="hidden" name="mode" value="<?= htmlspecialchars($mode) ?>">

          <div class="v-line">
            <div class="form-group mb-2 v-col v-fournisseur">
              <label class="mb-1 font-weight-bold">Fournisseur *</label>

              <div class="autocomplete-wrapper" style="position:relative;">
                <input type="text"
                       class="form-control form-control-sm fournisseur-autocomplete"
                       id="fournisseur_nom"
                       name="fournisseur_nom"
                       placeholder="Rechercher un fournisseur..."
                       value="<?= htmlspecialchars($fournisseurNomSelected) ?>">

                <input type="hidden"
                       name="fournisseur_id"
                       id="fournisseur_id"
                       value="<?= htmlspecialchars($fournisseurIdSelected) ?>">

                <div class="autocomplete-results"
                     id="fournisseur-results"
                     style="display:none; position:absolute; z-index:2000; left:0; right:0;"></div>
              </div>
            </div>

            <div class="form-group mb-2 v-col-fixed v-date">
              <label class="mb-1 font-weight-bold">Date</label>
              <input type="date"
                     name="date"
                     class="form-control form-control-sm"
                     value="<?= htmlspecialchars($_POST['date'] ?? date('Y-m-d')) ?>">
            </div>

            <div class="form-group mb-2 v-col-fixed v-montant">
              <label class="mb-1 font-weight-bold">Montant *</label>
              <input type="text"
                     name="montant"
                     class="form-control form-control-sm"
                     placeholder="0,00"
                     value="<?= htmlspecialchars($_POST['montant'] ?? '') ?>"
                     required>
            </div>
          </div>

          <div class="v-line">
            <div class="form-group mb-2 v-col" style="flex:0 0 260px; margin-top: 30px;">
              <label class="mb-1 font-weight-bold"><?= htmlspecialchars($refLabel) ?></label>
              <input type="text"
                     name="reference"
                     class="form-control form-control-sm"
                     placeholder="<?= htmlspecialchars($refPlaceholder) ?>"
                     value="<?= htmlspecialchars($_POST['reference'] ?? '') ?>">
            </div>

            <div class="form-group mb-2 v-col">
              <label class="mb-1 font-weight-bold">Libellé</label>
              <input type="text"
                     name="note"
                     class="form-control form-control-sm"
                     placeholder="Ex: règlement fournisseur, avance, dépôt..."
                     value="<?= htmlspecialchars($_POST['note'] ?? '') ?>">
            </div>

            <div class="form-group mb-2 v-col-fixed" style="width: 220px;">
              <label class="mb-1 font-weight-bold"><?= htmlspecialchars($field3Label) ?></label>

              <select name="banque" class="form-control form-control-sm" required>
                <option value="">— Choisir —</option>

                <?php if ($mode === 'especes'): ?>
                  <?php foreach (($caisses ?? []) as $c): ?>
                    <option value="<?= htmlspecialchars($c['nom']) ?>" <?= (($_POST['banque'] ?? '') === $c['nom']) ? 'selected' : '' ?>>
                      <?= htmlspecialchars($c['nom']) ?>
                      <?php if (!empty($c['localisation'])): ?> (<?= htmlspecialchars($c['localisation']) ?>)<?php endif; ?>
                    </option>
                  <?php endforeach; ?>

                <?php elseif (in_array($mode, ['virement','cheque','depot_especes_banque'], true)): ?>
                  <?php foreach (($banques ?? []) as $b): ?>
                    <option value="<?= htmlspecialchars($b['nom']) ?>" <?= (($_POST['banque'] ?? '') === $b['nom']) ? 'selected' : '' ?>>
                      <?= htmlspecialchars($b['nom']) ?>
                      <?php if (!empty($b['localisation'])): ?> (<?= htmlspecialchars($b['localisation']) ?>)<?php endif; ?>
                    </option>
                  <?php endforeach; ?>

                <?php elseif ($mode === 'mobile_money'): ?>
                  <?php foreach (($mobiles ?? []) as $m): ?>
                    <?php
                      $abbr = $opAbbr($m['operateur'] ?? '');
                      $tel  = preg_replace('/\s+/', '', (string)($m['telephone'] ?? ''));
                      $value = trim($abbr . ' - ' . $tel);
                    ?>
                    <option value="<?= htmlspecialchars($value) ?>" <?= (($_POST['banque'] ?? '') === $value) ? 'selected' : '' ?>>
                      <?= htmlspecialchars($abbr) ?> - <?= htmlspecialchars($tel) ?>
                      <?php if (!empty($m['nom_compte'])): ?> (<?= htmlspecialchars($m['nom_compte']) ?>)<?php endif; ?>
                    </option>
                  <?php endforeach; ?>
                <?php endif; ?>
              </select>
            </div>
          </div>

          <?php if ($mode === 'cheque'): ?>
            <div class="v-line" style="margin-top: 10px;">
              <div class="form-group mb-2 v-col" style="max-width: 420px;">
                <label class="mb-1 font-weight-bold">Établissement payeur</label>
                <input type="text"
                       name="etablissement_payeur"
                       class="form-control form-control-sm"
                       placeholder="Ex: BOA, SGCI, BICICI..."
                       value="<?= htmlspecialchars($_POST['etablissement_payeur'] ?? '') ?>"
                       required>
                <small class="text-muted">Banque sur laquelle le chèque a été émis.</small>
              </div>
            </div>
          <?php endif; ?>

          <button type="submit" class="btn btn-primary btn-sm" style="float:right; margin-top: 30px;">VALIDER</button>
        </form>
      </div>
    </div>
  <?php endif; ?>
</div>

<?php include '../views/footer.php'; ?>

<script>
(function () {
  const form = document.getElementById('vf-form');
  const fournisseurInput = document.getElementById('fournisseur_nom');
  const fournisseurIdInput = document.getElementById('fournisseur_id');
  const fournisseurResults = document.getElementById('fournisseur-results');

  if (!fournisseurInput || !fournisseurIdInput || !fournisseurResults) {
    return;
  }

  let fournisseurTimer = null;

  function escapeHtml(value) {
    return String(value ?? '')
      .replace(/&/g, '&amp;')
      .replace(/</g, '&lt;')
      .replace(/>/g, '&gt;')
      .replace(/"/g, '&quot;')
      .replace(/'/g, '&#039;');
  }

  function hideResults(box) {
    box.style.display = 'none';
    box.innerHTML = '';
  }

  function showFournisseurResults(items) {
    if (!items || !items.length) {
      fournisseurResults.innerHTML = '<div class="list-group-item small text-muted">Aucun fournisseur trouvé</div>';
      fournisseurResults.style.display = 'block';
      return;
    }

    let html = '<div class="list-group">';
    items.forEach(item => {
      html += `
        <button type="button"
                class="list-group-item list-group-item-action"
                data-id="${item.id_fournisseurs}"
                data-name="${escapeHtml(item.nom_fournisseurs || '')}">
          ${escapeHtml(item.nom_fournisseurs || '')}
        </button>
      `;
    });
    html += '</div>';

    fournisseurResults.innerHTML = html;
    fournisseurResults.style.display = 'block';

    fournisseurResults.querySelectorAll('[data-id]').forEach(btn => {
      btn.addEventListener('mousedown', function (e) {
        e.preventDefault();
        fournisseurInput.value = this.dataset.name;
        fournisseurIdInput.value = this.dataset.id;
        hideResults(fournisseurResults);
      });
    });
  }

  fournisseurInput.addEventListener('input', function () {
    const term = fournisseurInput.value.trim();
    fournisseurIdInput.value = '';

    clearTimeout(fournisseurTimer);

    if (term.length < 1) {
      hideResults(fournisseurResults);
      return;
    }

    fournisseurTimer = setTimeout(() => {
      fetch('index.php?action=fournisseurs/search&term=' + encodeURIComponent(term), {
        headers: { 'X-Requested-With': 'XMLHttpRequest' }
      })
        .then(r => r.json())
        .then(data => showFournisseurResults(data))
        .catch(() => hideResults(fournisseurResults));
    }, 250);
  });

  fournisseurInput.addEventListener('blur', function () {
    setTimeout(() => hideResults(fournisseurResults), 150);
  });

  form?.addEventListener('submit', function (e) {
    const fournisseurNom = fournisseurInput.value.trim();
    const fournisseurId = fournisseurIdInput.value.trim();

    if (fournisseurNom !== '' && fournisseurId === '') {
      e.preventDefault();
      alert('Veuillez sélectionner un fournisseur dans la liste proposée.');
      fournisseurInput.focus();
    }
  });
})();
</script>