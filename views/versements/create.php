<?php
$pageTitle = "Nouveau Versement";
include '../views/layout.php';

$error = $error ?? null;
$mode = $_GET['mode'] ?? '';
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

.v-client { flex: 1 1 420px; min-width: 260px; }
.v-code { width: 90px; }
.v-date { width: 120px; }
.v-montant { width: 180px; }

/* Mobile: autoriser l'empilement (si tu veux) */
@media (max-width: 768px) {
  .v-line { flex-wrap: wrap; }
  .v-client { flex: 1 1 100%; }
  .v-code, .v-date, .v-montant { width: calc(33.33% - 7px); }
}</style>

<div class="container mt-4" style="max-width: 900px;">
  <div class="d-flex justify-content-between align-items-center mb-3">
    <h4 class="mb-0">Nouveau versement</h4>
    <a class="btn btn-secondary btn-sm" href="index.php?action=versements">← Retour</a>
  </div>

  <?php if ($error): ?>
    <div class="alert alert-danger"><?= htmlspecialchars($error) ?></div>
  <?php endif; ?>

  <?php if ($mode === ''): ?>
    <!-- ====== ÉTAPE 1 : CHOIX ====== -->
    <div class="card">
      <div class="card-body p-3">
        <div class="mb-2 font-weight-bold">Choisir le type d’opération</div>

        <div class="row">
          <div class="col-md-12 mb-2">
            <a class="btn btn-light border w-100 text-left py-2"
               href="index.php?action=versements/create&mode=especes">
              <div class="d-flex align-items-center">
                <i class="fas fa-money-bill-wave mr-2 text-success"></i>
                <div class="font-weight-bold" style="line-height:1.1; margin-left: 15px;">Encaissement espèces</div>
              </div>
            </a>
          </div>

          <div class="col-md-12 mb-2">
            <a class="btn btn-light border w-100 text-left py-2"
               href="index.php?action=versements/create&mode=depot_especes_banque">
              <div class="d-flex align-items-center">
                <i class="fas fa-piggy-bank mr-2 text-secondary"></i>
                <div class="font-weight-bold" style="line-height:1.1; margin-left: 15px;">Dépôt espèces banque</div>
              </div>
            </a>
          </div>

          <div class="col-md-12 mb-2">
            <a class="btn btn-light border w-100 text-left py-2"
               href="index.php?action=versements/create&mode=virement">
              <div class="d-flex align-items-center">
                <i class="fas fa-building-columns mr-2 text-primary"></i>
                <div class="font-weight-bold" style="line-height:1.1; margin-left: 15px;">Virement reçu</div>
              </div>
            </a>
          </div>

          <div class="col-md-12 mb-2">
            <a class="btn btn-light border w-100 text-left py-2"
               href="index.php?action=versements/create&mode=cheque">
              <div class="d-flex align-items-center">
                <i class="fas fa-money-check mr-2 text-warning"></i>
                <div class="font-weight-bold" style="line-height:1.1; margin-left: 15px;">Chèques reçus</div>
              </div>
            </a>
          </div>

          <div class="col-md-12 mb-2">
            <a class="btn btn-light border w-100 text-left py-2"
               href="index.php?action=versements/create&mode=mobile_money">
              <div class="d-flex align-items-center">
                <i class="fas fa-mobile-screen mr-2" style="color:#7c3aed;"></i>
                <div class="font-weight-bold" style="line-height:1.1; margin-left: 15px;">Mobile money</div>
              </div>
            </a>
          </div>
        </div><!-- /.row -->

      </div>
    </div>

  <?php else: ?>
    <!-- ====== ÉTAPE 2 : FORMULAIRE ====== -->
    <?php
      $modeLabel = [
        'especes' => 'Encaissement espèces',
        'virement' => 'Virement reçu',
        'cheque' => 'Chèque reçu',
        'mobile_money' => 'Mobile money',
        'depot_especes_banque' => 'Dépôt espèces banque',
      ][$mode] ?? 'Versement';

      $refLabel = 'Référence';
      $refPlaceholder = 'Ex: TRX-..., 123456';
      $field3Label = 'Caisse';
      $field3Placeholder = 'Ex: Caisse 1';

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
        $field3Placeholder = 'Ex: BOA, BICICI...';
      } elseif ($mode === 'mobile_money') {
        $field3Label = 'Mobile';
        $field3Placeholder = 'Ex: Orange Money, Wave...';
      }
    ?>

    <div class="card mb-3">
      <div class="card-body py-2 px-3">
        <div class="d-flex justify-content-between align-items-center">
          <div>
            <div class="font-weight-bold" style="line-height:1.1;"><?= htmlspecialchars($modeLabel) ?></div>
            <div class="text-muted small" style="line-height:1.1;">Renseigne les champs ci-dessous</div>
          </div>
          <a class="btn btn-sm btn-outline-secondary" href="index.php?action=versements/create">Changer</a>
        </div>
      </div>
    </div>

    <div class="container mt-4">
  <div class="card mx-auto" style="max-width: 950px;">
    <div class="card-body p-3">

    <form method="post" action="index.php?action=versements/create&mode=<?= urlencode($mode) ?>" autocomplete="off"><?= $csrf_field ?? '' ?>
      <input type="hidden" name="mode" value="<?= htmlspecialchars($mode) ?>">

      <!-- LIGNE 1 : CLIENT | CODE | DATE | MONTANT -->

      <div class="v-line">
  <div class="form-group mb-2 v-col v-client">
    <label class="mb-1 font-weight-bold">Client *</label>
    <div class="autocomplete-wrapper" style="position:relative;">
      <input type="text" class="form-control form-control-sm client-autocomplete" id="client_nom" placeholder="Rechercher un client...">
      <input type="hidden" name="client_id" id="client_id">
      <div class="autocomplete-results" style="display:none; position:absolute; z-index:2000; left:0; right:0;"></div>
    </div>
  </div>

  <div class="form-group mb-2 v-col-fixed v-code">
    <label class="mb-1 font-weight-bold">Code</label>
    <input type="text" class="form-control form-control-sm client-code" id="client_code" readonly placeholder="—">
  </div>

  <div class="form-group mb-2 v-col-fixed v-date">
    <label class="mb-1 font-weight-bold">Date</label>
    <input type="date" name="date" class="form-control form-control-sm" value="<?= htmlspecialchars(date('Y-m-d')) ?>">
  </div>

  <div class="form-group mb-2 v-col-fixed v-montant">
    <label class="mb-1 font-weight-bold">Montant *</label>
    <input type="text" name="montant" class="form-control form-control-sm" placeholder="0,00" required>
  </div>
</div>

      <!-- LIGNE 2 : REFERENCE | LIBELLÉ | CAISSE/BANQUE/MOBILE (colonne banque) -->
     <div class="v-line">
  <div class="form-group mb-2 v-col" style="flex:0 0 260px; margin-top: 30px;">
    <label class="mb-1 font-weight-bold"><?= htmlspecialchars($refLabel) ?></label>
    <input type="text" name="reference" class="form-control form-control-sm" placeholder="<?= htmlspecialchars($refPlaceholder) ?>">
  </div>

  <div class="form-group mb-2 v-col">
    <label class="mb-1 font-weight-bold">Libellé</label>
    <input type="text" name="note" class="form-control form-control-sm" placeholder="Ex: règlement facture..., avance..., dépôt...">
  </div>

  <div class="form-group mb-2 v-col-fixed" style="width: 200px;">
    <label class="mb-1 font-weight-bold"><?= htmlspecialchars($field3Label) ?></label>
    <?php
  // Helper opérateur => abréviation
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

<select name="banque" class="form-control form-control-sm" required>
  <option value="">— Choisir —</option>

  <?php if ($mode === 'especes'): ?>
    <?php foreach (($caisses ?? []) as $c): ?>
      <option value="<?= htmlspecialchars($c['nom']) ?>">
        <?= htmlspecialchars($c['nom']) ?>
        <?php if (!empty($c['localisation'])): ?> (<?= htmlspecialchars($c['localisation']) ?>)<?php endif; ?>
      </option>
    <?php endforeach; ?>

  <?php elseif (in_array($mode, ['virement','cheque','depot_especes_banque'], true)): ?>
    <?php foreach (($banques ?? []) as $b): ?>
      <option value="<?= htmlspecialchars($b['nom']) ?>">
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
      <option value="<?= htmlspecialchars($value) ?>">
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
      <input
        type="text"
        name="etablissement_payeur"
        class="form-control form-control-sm"
        placeholder="Ex: BOA, SGCI, BICICI..."
        required
      >
      <small class="text-muted">Banque sur laquelle le chèque a été émis.</small>
    </div>
  </div>
<?php endif; ?>


      <button type="submit" class="btn btn-primary btn-sm" style="float: right; margin-top: 30px;">VALIDER</button>
    </form>
  </div></div></div>
  <?php endif; ?>
</div>

<script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
<script src="/maqci/public/js/script.js"></script>