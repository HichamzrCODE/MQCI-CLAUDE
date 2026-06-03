<?php include '../views/layout.php'; ?>
<?php
$pageTitle    = $pageTitle ?? "Créer un Devis";
$errorFields  = $errorFields ?? [];
$devis        = $devis ?? [];
$error        = $error ?? null;
$numeroDevis  = $numeroDevis ?? '';
?>

<div class="container mt-4">

  <!-- Titre + boutons à droite -->
  <div class="d-flex justify-content-between align-items-center mb-3">
    <h4 class="mb-0">Créer un Devis</h4>

    <div class="d-flex" style="gap:8px;">
      <a class="btn btn-secondary btn-sm" href="index.php?action=devis">← Retour</a>
      <!-- form="devis-form" => le bouton soumet même s'il est déplacé -->
      <button type="submit" form="devis-form" class="btn btn-success btn-sm">Enregistrer</button>
    </div>
  </div>

  <?php if ($error): ?>
    <div class="alert alert-danger"><?= htmlspecialchars($error) ?></div>
  <?php endif; ?>

  <form method="post" action="index.php?action=devis/create" id="devis-form" autocomplete="off">

    <!-- TOP DEVIS (largeurs fixes) -->
    <div class="mb-3">

      <!-- Ligne 1 : Client / N° devis -->
      <div class="d-flex justify-content-between align-items-start flex-wrap" style="gap:16px;">
        <div style="width:300px; max-width:100%;">
          <div class="autocomplete-wrapper" style="position:relative;">
            <input type="text"
                   class="form-control form-control-sm client-autocomplete <?= isset($errorFields['client_id']) ? 'is-invalid' : '' ?>"
                   id="client_nom"
                   placeholder="Rechercher un client...">
            <input type="hidden" name="client_id" id="client_id" value="<?= htmlspecialchars($_POST['client_id'] ?? '') ?>">

            <div class="autocomplete-results"
                 style="display:none; position:absolute; z-index:2000; left:0; right:0;"></div>

            <?php if (isset($errorFields['client_id'])): ?>
              <div class="invalid-feedback d-block"><?= htmlspecialchars($errorFields['client_id']) ?></div>
            <?php endif; ?>
          </div>
        </div>

        <div style="width:240px; max-width:100%;">
          <input type="text"
                 class="form-control form-control-sm text-right"
                 id="numero" name="numero"
                 value="<?= htmlspecialchars($numeroDevis) ?>"
                 readonly
                 style="background:#f7f7f7;font-weight:bold;"
                 placeholder="Numéro devis">
        </div>
      </div>

      <!-- Ligne 2 : Référence / Date -->
      <div class="d-flex justify-content-between align-items-start flex-wrap mt-2" style="gap:16px;">
        <div style="width:300px; max-width:100%;">
          <input type="text"
                 class="form-control form-control-sm"
                 name="reference" id="reference"
                 placeholder="Référence (optionnel)"
                 value="<?= htmlspecialchars($_POST['reference'] ?? ($devis['reference'] ?? '')) ?>">
        </div>

        <div style="width:240px; max-width:100%;">
          <input type="date"
                 class="form-control form-control-sm text-right <?= isset($errorFields['date']) ? 'is-invalid' : '' ?>"
                 id="date" name="date"
                 value="<?= htmlspecialchars($_POST['date'] ?? date('Y-m-d')) ?>">

          <?php if (isset($errorFields['date'])): ?>
            <div class="invalid-feedback d-block text-left"><?= htmlspecialchars($errorFields['date']) ?></div>
          <?php endif; ?>
        </div>
      </div>

    </div>

    <!-- Table -->
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
          <tr id="ligne-vide-message">
            <td colspan="7" class="text-center text-muted" style="font-style:italic; background:#f7f7f7;">
              Cliquez sur <b>"Ajouter une ligne"</b> pour commencer votre devis.
            </td>
          </tr>
        </tbody>
      </table>
    </div>

    <button type="button" class="btn btn-primary" id="ajouter-ligne">Ajouter une ligne</button>

    <!-- Totaux à droite : HT / TVA / TTC -->
<div class="d-flex justify-content-end mt-1">
  <div style="width: 250px; max-width:100%;">
    <div class="form-group mb-0">
      <label class="mb-1 font-weight-bold">Total Général</label>
      <input type="text"
             class="form-control text-right"
             id="total-general" name="total_general"
             value="0"
             readonly>
    </div>
  </div>
</div>

  </form>
</div>

<!-- Modal recherche articles -->
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
<?php include '../views/footer.php'; ?>
<style>
  #devis-table { margin-bottom: 0; }

  #devis-table tr,
  #devis-table td,
  #devis-table th {
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