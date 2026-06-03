<?php
$pageTitle = "Éditer un Devis";
include '../views/layout.php';

$errorFields = $errorFields ?? [];
$lignesDevis = $lignesDevis ?? [];
$clients = $clients ?? [];
$articles = $articles ?? [];
$devis = $devis ?? [];
$error = $error ?? null;

// Statut / verrouillage
$isValidated = (($devis['statut'] ?? 'draft') === 'validated');
$isAdmin = (($_SESSION['role'] ?? '') === 'admin');
$readonly = ($isValidated && !$isAdmin);

// Client actuel (autocomplete)
$currentClientId = $_POST['client_id'] ?? ($devis['client_id'] ?? '');
$currentClientName = '';
if ($currentClientId) {
  foreach ($clients as $client) {
    if ($client['id_clients'] == $currentClientId) {
      $currentClientName = $client['nom'];
      break;
    }
  }
}
?>

<style>
  #devis-table { margin-bottom: 0; }
  #devis-table tr, #devis-table td, #devis-table th {
    padding-top: 2px !important;
    padding-bottom: 2px !important;
    height: 28px !important;
    font-size: 13px !important;
    vertical-align: middle;
  }
  #devis-table input.form-control {
    padding: 2px 8px !important;
    height: 26px !important;
    font-size: 13px !important;
  }
  #devis-table .btn {
    padding: 2px 8px !important;
    font-size: 13px !important;
    height: 26px !important;
    line-height: 1.2 !important;
  }
  #devis-table .input-article { width: 270px !important; max-width: 350px !important; }
  #devis-table .input-description { width: 200px !important; min-width: 200px !important; max-width: 250px !important; }
  #devis-table .input-quantite { width: 70px !important; }
  #devis-table .input-prix { width: 130px !important; }

  #devis-table .prix-ht-input,
  #devis-table .prix-unitaire-input { text-align: right; }

  @media (max-width: 576px) {
    #devis-table .input-article,
    #devis-table .input-description {
      width: 100% !important; max-width: 100% !important; min-width: 0 !important;
    }
    .text-right { text-align: left !important; }
  }
</style>

<div class="container mt-4">
  <div class="d-flex justify-content-between align-items-center mb-2">
    <h4 class="mb-0">Éditer un Devis</h4>

    <div class="d-flex" style="gap:8px;">
      <a class="btn btn-secondary btn-sm" href="index.php?action=devis">← Retour</a>

      <?php if ($isValidated): ?>
        <a class="btn btn-warning btn-sm" href="index.php?action=livraisons/createFromDevis&devis_id=<?= (int)$devis['id'] ?>">
          Créer BL
        </a>
      <?php else: ?>
        <form method="post" action="index.php?action=devis/validate&id=<?= (int)$devis['id'] ?>" style="display:inline;">
          <button type="submit" class="btn btn-success btn-sm"
                  onclick="return confirm('Valider ce devis ? Après validation il sera transformable en BL.');">
            Valider
          </button>
        </form>
      <?php endif; ?>

      <a class="btn btn-outline-secondary btn-sm" href="index.php?action=devis/duplicate&id=<?= (int)$devis['id'] ?>">
        Dupliquer
      </a>

      <?php if (!$readonly): ?>
        <button type="submit" form="devis-form" class="btn btn-primary btn-sm">Enregistrer</button>
      <?php endif; ?>
    </div>
  </div>

  <div class="mb-2">
    <?php
      $statut = $devis['statut'] ?? 'draft';
      $badge = ($statut === 'validated') ? 'success' : 'secondary';
      $label = ($statut === 'validated') ? 'Validé' : 'Brouillon';
    ?>
    <span class="badge badge-<?= $badge ?>"><?= $label ?></span>

    <?php if ($readonly): ?>
      <span class="text-muted ml-2">Lecture seule (devis validé). Modifiable uniquement par admin.</span>
    <?php endif; ?>
  </div>

  <?php if ($error): ?>
    <div class="alert alert-danger"><?= htmlspecialchars($error) ?></div>
  <?php endif; ?>

  <form method="post"
        action="index.php?action=devis/update&id=<?= (int)$devis['id'] ?>"
        id="devis-form"
        autocomplete="off">

    <!-- TOP (comme create) : Client + N° + Réf + Date -->
    <div class="mb-3">
      <div class="d-flex justify-content-between align-items-start flex-wrap" style="gap:16px;">
        <div style="width:300px; max-width:100%;">
          <div class="autocomplete-wrapper" style="position:relative;">
            <input type="text"
                   class="form-control form-control-sm client-autocomplete <?= isset($errorFields['client_id']) ? 'is-invalid' : '' ?>"
                   id="client_nom"
                   placeholder="Rechercher un client..."
                   value="<?= htmlspecialchars($_POST['client_nom'] ?? $currentClientName) ?>"
                   <?= $readonly ? 'disabled' : '' ?>>
            <input type="hidden"
                   name="client_id"
                   id="client_id"
                   value="<?= htmlspecialchars($currentClientId) ?>">

            <div class="autocomplete-results" style="display:none; position:absolute; z-index:2000; left:0; right:0;"></div>

            <?php if (isset($errorFields['client_id'])): ?>
              <div class="invalid-feedback d-block"><?= htmlspecialchars($errorFields['client_id']) ?></div>
            <?php endif; ?>
          </div>
        </div>

        <div style="width:240px; max-width:100%;">
          <input type="text"
                 class="form-control form-control-sm text-right"
                 id="numero" name="numero"
                 value="<?= htmlspecialchars($devis['numero'] ?? '') ?>"
                 readonly
                 style="background:#f7f7f7;font-weight:bold;"
                 placeholder="Numéro devis">
        </div>
      </div>

      <div class="d-flex justify-content-between align-items-start flex-wrap mt-2" style="gap:16px;">
        <div style="width:300px; max-width:100%;">
          <input type="text"
                 class="form-control form-control-sm"
                 name="reference" id="reference"
                 placeholder="Référence (optionnel)"
                 value="<?= htmlspecialchars($_POST['reference'] ?? ($devis['reference'] ?? '')) ?>"
                 <?= $readonly ? 'disabled' : '' ?>>
        </div>

        <div style="width:240px; max-width:100%;">
          <input type="date"
                 class="form-control form-control-sm text-right <?= isset($errorFields['date']) ? 'is-invalid' : '' ?>"
                 id="date" name="date"
                 value="<?= htmlspecialchars($_POST['date'] ?? ($devis['date'] ?? date('Y-m-d'))) ?>"
                 <?= $readonly ? 'disabled' : '' ?>>

          <?php if (isset($errorFields['date'])): ?>
            <div class="invalid-feedback d-block text-left"><?= htmlspecialchars($errorFields['date']) ?></div>
          <?php endif; ?>
        </div>
      </div>
    </div>

    <!-- TABLE (comme create) -->
    <div class="table-responsive mb-2">
      <table class="table table-sm" id="devis-table">
        <thead>
          <tr>
            <th>Article</th>
            <th>Description</th>
            <th>Quantité</th>
            <th>PU HT</th>
            <th>PU TTC</th>
            <th class="text-right">Total TTC</th>
            <th>Action</th>
          </tr>
        </thead>

        <tbody>
          <?php if (empty($lignesDevis)): ?>
            <tr id="ligne-vide-message">
              <td colspan="7" class="text-center text-muted" style="font-style:italic; background:#f7f7f7;">
                Cliquez sur <b>"Ajouter une ligne"</b> pour commencer votre devis.
              </td>
            </tr>
          <?php else: ?>
            <?php foreach ($lignesDevis as $index => $ligne):
              $inputIndex = isset($ligne['id']) ? 'id_' . $ligne['id'] : 'new_' . $index;

              $puTtc = (float)($ligne['prix_unitaire'] ?? 0);
              $puHt = ($puTtc > 0) ? ($puTtc / 1.18) : 0;

              // ✅ PR TTC stocké dans devis_lignes.pr_ref_ttc
              $prRefTtc = (float)($ligne['pr_ref_ttc'] ?? 0);
            ?>
              <tr class="ligne-article"
                  data-ligne-id="<?= htmlspecialchars($inputIndex) ?>"
                  <?php if (isset($ligne['id'])): ?>data-ligne-db-id="<?= (int)$ligne['id'] ?>"<?php endif; ?>>

                <td>
                  <div class="autocomplete-wrapper" style="position:relative;">
                    <input type="text"
                           class="form-control article-autocomplete input-article <?= isset($errorFields["articles_{$inputIndex}_article_id"]) ? 'is-invalid' : '' ?>"
                           placeholder="Rechercher un article"
                           value="<?= htmlspecialchars($ligne['nom_art'] ?? '') ?>"
                           <?= $readonly ? 'disabled' : '' ?>>

                    <input type="hidden"
                           class="article-id"
                           name="articles[<?= $inputIndex ?>][article_id]"
                           value="<?= htmlspecialchars($ligne['article_id'] ?? '') ?>">

                    <div class="autocomplete-results"></div>

                    <?php if (isset($errorFields["articles_{$inputIndex}_article_id"])): ?>
                      <div class="invalid-feedback d-block"><?= htmlspecialchars($errorFields["articles_{$inputIndex}_article_id"]) ?></div>
                    <?php endif; ?>
                  </div>
                </td>

                <td>
                  <input type="text"
                         class="form-control input-description"
                         name="articles[<?= $inputIndex ?>][description]"
                         placeholder="Description (optionnel)"
                         value="<?= htmlspecialchars($ligne['description'] ?? '') ?>"
                         <?= $readonly ? 'disabled' : '' ?>>
                </td>

                <td>
                  <input type="number"
                         class="form-control quantite-input input-quantite <?= isset($errorFields["articles_{$inputIndex}_quantite"]) ? 'is-invalid' : '' ?>"
                         min="1"
                         name="articles[<?= $inputIndex ?>][quantite]"
                         value="<?= htmlspecialchars($ligne['quantite'] ?? '1') ?>"
                         <?= $readonly ? 'disabled' : '' ?>>

                  <?php if (isset($errorFields["articles_{$inputIndex}_quantite"])): ?>
                    <div class="invalid-feedback d-block"><?= htmlspecialchars($errorFields["articles_{$inputIndex}_quantite"]) ?></div>
                  <?php endif; ?>
                </td>

                <td>
                  <input type="text"
                         class="form-control prix-ht-input input-prix"
                         value="<?= htmlspecialchars(number_format($puHt, 2, ',', ' ')) ?>"
                         placeholder="HT"
                         <?= $readonly ? 'disabled' : '' ?>>
                </td>

                <td>
                  <div class="input-group input-group-sm">
                    <input type="text"
                           class="form-control prix-unitaire-input input-prix <?= isset($errorFields["articles_{$inputIndex}_prix_unitaire"]) ? 'is-invalid' : '' ?>"
                           name="articles[<?= $inputIndex ?>][prix_unitaire]"
                           value="<?= htmlspecialchars(number_format($puTtc, 2, ',', ' ')) ?>"
                           data-pr="<?= htmlspecialchars($prRefTtc) ?>"
                           <?= $readonly ? 'disabled' : '' ?>>
                  </div>

                  <?php if (isset($errorFields["articles_{$inputIndex}_prix_unitaire"])): ?>
                    <div class="invalid-feedback d-block"><?= htmlspecialchars($errorFields["articles_{$inputIndex}_prix_unitaire"]) ?></div>
                  <?php endif; ?>
                </td>

                <td class="total-ligne text-right" data-total="<?= htmlspecialchars($ligne['total'] ?? 0) ?>">
                  <?= number_format((float)($ligne['total'] ?? 0), 2, ',', ' ') ?>
                </td>

                <td>
                  <?php if (!$readonly): ?>
                    <button type="button" class="btn btn-danger remove-ligne" data-ligne-id="<?= htmlspecialchars($inputIndex) ?>">X</button>
                  <?php else: ?>
                    <span class="text-muted">—</span>
                  <?php endif; ?>
                </td>

              </tr>
            <?php endforeach; ?>
          <?php endif; ?>
        </tbody>
      </table>
    </div>

    <?php if (!$readonly): ?>
      <button type="button" class="btn btn-primary" id="ajouter-ligne">Ajouter une ligne</button>
    <?php endif; ?>

    <!-- Total général (comme create) -->
    <div class="d-flex justify-content-end mt-3">
      <div style="width:250px; max-width:100%;">
        <div class="form-group mb-0">
          <label class="mb-1 font-weight-bold">Total Général</label>
          <input type="text"
                 class="form-control text-right"
                 id="total-general"
                 name="total_general"
                 value="<?= htmlspecialchars(number_format((float)($devis['total'] ?? 0), 2, ',', ' ')) ?>"
                 readonly>
        </div>
      </div>
    </div>

  </form>
</div>

<!-- Modal recherche articles (même que create) -->
<div class="modal fade" id="articlesModal" tabindex="-1" role="dialog" aria-hidden="true">
  <div class="modal-dialog modal-lg" role="document" style="max-width:650px;">
    <div class="modal-content">
      <div class="modal-header py-2">
        <h5 class="modal-title">Recherche avancée d'articles</h5>
        <button type="button" class="close" data-dismiss="modal" aria-label="Fermer" style="font-size:1.6rem;">
          <span aria-hidden="true">&times;</span>
        </button>
      </div>
      <div class="modal-body p-3">
        <input type="text" id="modal-article-search" class="form-control mb-2" placeholder="Rechercher un article...">
        <div id="modal-articles-list" style="max-height:340px; overflow-y:auto;"></div>
      </div>
      <div class="modal-footer py-2">
        <button type="button" class="btn btn-secondary" data-dismiss="modal">Fermer</button>
      </div>
    </div>
  </div>
</div>

<!-- Scripts (une seule version, ordre correct pour tooltips) -->
<script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/popper.js@1.16.1/dist/umd/popper.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@4.6.2/dist/js/bootstrap.min.js"></script>

<?php if (!$readonly): ?>
  <script src="/maqci/public/js/script.js"></script>
  <script src="/maqci/public/js/devis-lignes.js"></script>
  <script src="/maqci/public/js/devis-validation.js"></script>
  <script src="/maqci/public/js/unsaved-warning.js"></script>
  <script src="/maqci/public/js/articles-modal.js"></script>
<?php endif; ?>

<script>
  // couleurs alternées (ton code)
  function alternerCouleursDevis() {
    $('#devis-table tbody tr.ligne-article').each(function(i) {
      var color = (i % 2 === 0) ? '#DEE4FF' : '#fff';
      $(this).css('background-color', color);
    });
  }
  $(document).ready(function() {
    alternerCouleursDevis();
    $('#ajouter-ligne').on('click', function() {
      setTimeout(alternerCouleursDevis, 10);
    });
    $(document).on('click', '.remove-ligne', function() {
      setTimeout(alternerCouleursDevis, 10);
    });
  });
</script>