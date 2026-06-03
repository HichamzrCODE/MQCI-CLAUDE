<<<<<<< HEAD
<?php include '../views/layout.php'; ?>
<?php
$errorFields = $errorFields ?? [];
$clients = $clients ?? [];
$articles = $articles ?? [];
$devis = $devis ?? [];
$error = $error ?? null;
?>
<div class="container mt-4">
    <h4>Créer un Devis</h4>
    <?php if ($error): ?>
        <div class="alert alert-danger"><?= $error ?></div>
    <?php endif; ?>
    <form method="post" action="index.php?action=devis/create" id="devis-form" autocomplete="off">
        <div class="form-row mb-1">
            <!-- Client à gauche -->
            <div class="col-md-4 mb-2">
                <div class="autocomplete-wrapper">
                    <input type="text" class="form-control form-control-sm client-autocomplete <?= isset($errorFields['id_clients']) ? 'is-invalid' : '' ?>" id="client_nom" placeholder="Rechercher un client...">
                    <input type="hidden" name="client_id" id="client_id" value="<?= htmlspecialchars($_POST['client_id'] ?? '') ?>">
                    <div class="autocomplete-results" style="display:none; position:absolute; z-index:1000;"></div>
                    <?php if (isset($errorFields['client_id'])): ?>
                        <div class="invalid-feedback"><?= $errorFields['client_id'] ?></div>
                    <?php endif; ?>
                </div>
                <!-- Référence sous Client -->
                <input type="text" class="form-control form-control-sm mt-2" name="reference" id="reference" placeholder="Référence (optionnel)" value="<?= htmlspecialchars($_POST['reference'] ?? $devis['reference'] ?? '') ?>">
            </div>
            <!-- Numéro devis et date à droite, empilés -->
            <div class="col-md-2 offset-md-4 mb-2 text-right">
                <div class="form-group mb-1">
                    <input type="text" class="form-control form-control-sm text-right" 
                        id="numero" name="numero"
                        value="<?= htmlspecialchars($numeroDevis ?? '') ?>" 
                        readonly 
                        style="background:#f7f7f7;font-weight:bold;" 
                        placeholder="Numéro devis">
                </div>
                <div class="form-group mb-0">
                    <input type="date"
                        class="form-control form-control-sm text-right <?= isset($errorFields['date']) ? 'is-invalid' : '' ?>"
                        id="date" name="date"
                        value="<?= htmlspecialchars($_POST['date'] ?? date('Y-m-d')) ?>">
                    <?php if (isset($errorFields['date'])): ?>
                        <div class="invalid-feedback text-left"><?= $errorFields['date'] ?></div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
        <div class="table-responsive mb-2">
            <table class="table" id="devis-table">
                <thead>
                    <tr>
                        <th>Article</th>
                        <th>Description</th>
                        <th>Quantité</th>
                        <th>Prix Unitaire</th>
                        <th>Total</th>
                        <th>Action</th>
                    </tr>
                </thead>
                <tbody>
                    <tr id="ligne-vide-message">
                        <td colspan="6" class="text-center text-muted" style="font-style:italic; background:#f7f7f7;">
                            Cliquez sur <b>"Ajouter une ligne"</b> pour commencer votre devis.
                        </td>
                    </tr>
                </tbody>


<!-- Modal de recherche avancée d'articles -->
<div class="modal fade" id="articlesModal" tabindex="-1" role="dialog" aria-labelledby="articlesModalLabel" aria-hidden="true">
  <div class="modal-dialog modal-lg" role="document" style="max-width:650px;">
    <div class="modal-content">
      <div class="modal-header py-2">
        <h5 class="modal-title" id="articlesModalLabel">Recherche avancée d'articles</h5>
        <button type="button" class="close" data-dismiss="modal" aria-label="Fermer" style="font-size:1.6rem;">
          <span aria-hidden="true">&times;</span>
        </button>
      </div>
      <div class="modal-body p-3">
        <input type="text" id="modal-article-search" class="form-control mb-2" placeholder="Rechercher un article...">
        <div id="modal-articles-list" style="max-height:340px; overflow-y:auto;">
          <!-- Résultats AJAX ici -->
        </div>
      </div>
      <div class="modal-footer py-2">
        <button type="button" class="btn btn-secondary" data-dismiss="modal">Fermer</button>
      </div>
    </div>
  </div>
</div>

            </table>
        </div>
        <div class="mb-4"></div>
        <button type="button" class="btn btn-primary" id="ajouter-ligne">Ajouter une ligne</button>
        <div class="form-row justify-content-end align-items-center mt-4">
            <div class="form-group mr-3 mb-0">
                <label for="total-general" class="mb-1 font-weight-bold">Total général</label>
                <input type="text" class="form-control" id="total-general" name="total_general"
                    value="<?= number_format($devis['total'] ?? 0, 2, ',', ' ') ?>" style="width: 150px;" readonly>
            </div>
            <button type="submit" class="btn btn-success btn-lg">Enregistrer le Devis</button>
        </div>
    </form>
</div>
<style>
    #devis-table {margin-bottom: 0;}
    #ajouter-ligne {margin-top: 10px;}
    /* Compactage du tableau */
    #devis-table tr,
    #devis-table td,
    #devis-table th {
        padding-top: 2px !important;
        padding-bottom: 2px !important;
        height: 28px !important;
        font-size: 13px !important;
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
    #devis-table .input-article {
        width: 270px !important;
        min-width: 25px !important;
        max-width: 350px !important;
    }
    #devis-table .input-description {
        width: 200px !important;
        min-width: 200px !important;
        max-width: 250px !important;
    }
    #devis-table .input-quantite {
        width: 60px !important;
        min-width: 40px !important;
        max-width: 80px !important;
    }
    #devis-table .input-prix {
        width: 90px !important;
        min-width: 60px !important;
        max-width: 120px !important;
    }
    @media (max-width: 576px) {
        #devis-table .input-article,
        #devis-table .input-description {
            width: 100% !important;
            min-width: 0 !important;
            max-width: 100% !important;
        }
        .form-row .col-md-4,
        .form-row .col-md-5,
        .form-row .col-md-3 {width: 100% !important; margin-bottom: 10px;}
        .text-right { text-align: left !important; }
    }
    /* Champs client/date/référence plus compacts */
    .form-row .form-control-sm {
        font-size: 14px;
        padding: 4px 8px;
        height: 32px;
    }

    #modal-articles-list .list-group-item {
    padding-top: 0.55em;
    padding-bottom: 0.55em;
    font-size: 14px;
    border-bottom: 1px solid #eee;
    cursor: pointer;
}
#modal-articles-list .list-group-item .badge {
    font-size: 12px;
}
@media (max-width: 576px) {
    #modal-articles-list .article-info {
        flex-direction: column !important;
        align-items: flex-start !important;
    }
}
</style>
<script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
<script src="/s6/public/js/script.js"></script>
<script src="/s6/public/js/devis-lignes.js"></script>
<script src="/s6/public/js/devis-validation.js"></script>
<script src="/s6/public/js/unsaved-warning.js"></script>
<script src="/s6/public/js/articles-modal.js"></script>
<script src="https://cdn.jsdelivr.net/npm/popper.js@1.16.1/dist/umd/popper.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@4.6.2/dist/js/bootstrap.min.js"></script>
<script>
// Alternance JS fiable sur chaque ligne article
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
=======
<?php include '../views/layout.php'; ?>
<?php
$errorFields = $errorFields ?? [];
$clients = $clients ?? [];
$articles = $articles ?? [];
$devis = $devis ?? [];
$error = $error ?? null;
?>
<div class="container mt-4">
    <h4>Créer un Devis</h4>
    <?php if ($error): ?>
        <div class="alert alert-danger"><?= $error ?></div>
    <?php endif; ?>
    <form method="post" action="index.php?action=devis/create" id="devis-form" autocomplete="off">
        <div class="form-row mb-1">
            <!-- Client à gauche -->
            <div class="col-md-4 mb-2">
                <div class="autocomplete-wrapper">
                    <input type="text" class="form-control form-control-sm client-autocomplete <?= isset($errorFields['id_clients']) ? 'is-invalid' : '' ?>" id="client_nom" placeholder="Rechercher un client...">
                    <input type="hidden" name="client_id" id="client_id" value="<?= htmlspecialchars($_POST['client_id'] ?? '') ?>">
                    <div class="autocomplete-results" style="display:none; position:absolute; z-index:1000;"></div>
                    <?php if (isset($errorFields['client_id'])): ?>
                        <div class="invalid-feedback"><?= $errorFields['client_id'] ?></div>
                    <?php endif; ?>
                </div>
                <!-- Référence sous Client -->
                <input type="text" class="form-control form-control-sm mt-2" name="reference" id="reference" placeholder="Référence (optionnel)" value="<?= htmlspecialchars($_POST['reference'] ?? $devis['reference'] ?? '') ?>">
            </div>
            <!-- Numéro devis et date à droite, empilés -->
            <div class="col-md-2 offset-md-4 mb-2 text-right">
                <div class="form-group mb-1">
                    <input type="text" class="form-control form-control-sm text-right" 
                        id="numero" name="numero"
                        value="<?= htmlspecialchars($numeroDevis ?? '') ?>" 
                        readonly 
                        style="background:#f7f7f7;font-weight:bold;" 
                        placeholder="Numéro devis">
                </div>
                <div class="form-group mb-0">
                    <input type="date"
                        class="form-control form-control-sm text-right <?= isset($errorFields['date']) ? 'is-invalid' : '' ?>"
                        id="date" name="date"
                        value="<?= htmlspecialchars($_POST['date'] ?? date('Y-m-d')) ?>">
                    <?php if (isset($errorFields['date'])): ?>
                        <div class="invalid-feedback text-left"><?= $errorFields['date'] ?></div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
        <div class="table-responsive mb-2">
            <table class="table" id="devis-table">
                <thead>
                    <tr>
                        <th>Article</th>
                        <th>Description</th>
                        <th>Quantité</th>
                        <th>Prix Unitaire</th>
                        <th>Total</th>
                        <th>Action</th>
                    </tr>
                </thead>
                <tbody>
                    <tr id="ligne-vide-message">
                        <td colspan="6" class="text-center text-muted" style="font-style:italic; background:#f7f7f7;">
                            Cliquez sur <b>"Ajouter une ligne"</b> pour commencer votre devis.
                        </td>
                    </tr>
                </tbody>


<!-- Modal de recherche avancée d'articles -->
<div class="modal fade" id="articlesModal" tabindex="-1" role="dialog" aria-labelledby="articlesModalLabel" aria-hidden="true">
  <div class="modal-dialog modal-lg" role="document" style="max-width:650px;">
    <div class="modal-content">
      <div class="modal-header py-2">
        <h5 class="modal-title" id="articlesModalLabel">Recherche avancée d'articles</h5>
        <button type="button" class="close" data-dismiss="modal" aria-label="Fermer" style="font-size:1.6rem;">
          <span aria-hidden="true">&times;</span>
        </button>
      </div>
      <div class="modal-body p-3">
        <input type="text" id="modal-article-search" class="form-control mb-2" placeholder="Rechercher un article...">
        <div id="modal-articles-list" style="max-height:340px; overflow-y:auto;">
          <!-- Résultats AJAX ici -->
        </div>
      </div>
      <div class="modal-footer py-2">
        <button type="button" class="btn btn-secondary" data-dismiss="modal">Fermer</button>
      </div>
    </div>
  </div>
</div>

            </table>
        </div>
        <div class="mb-4"></div>
        <button type="button" class="btn btn-primary" id="ajouter-ligne">Ajouter une ligne</button>
        <div class="form-row justify-content-end align-items-center mt-4">
            <div class="form-group mr-3 mb-0">
                <label for="total-general" class="mb-1 font-weight-bold">Total général</label>
                <input type="text" class="form-control" id="total-general" name="total_general"
                    value="<?= number_format($devis['total'] ?? 0, 2, ',', ' ') ?>" style="width: 150px;" readonly>
            </div>
            <button type="submit" class="btn btn-success btn-lg">Enregistrer le Devis</button>
        </div>
    </form>
</div>
<style>
    #devis-table {margin-bottom: 0;}
    #ajouter-ligne {margin-top: 10px;}
    /* Compactage du tableau */
    #devis-table tr,
    #devis-table td,
    #devis-table th {
        padding-top: 2px !important;
        padding-bottom: 2px !important;
        height: 28px !important;
        font-size: 13px !important;
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
    #devis-table .input-article {
        width: 270px !important;
        min-width: 25px !important;
        max-width: 350px !important;
    }
    #devis-table .input-description {
        width: 200px !important;
        min-width: 200px !important;
        max-width: 250px !important;
    }
    #devis-table .input-quantite {
        width: 60px !important;
        min-width: 40px !important;
        max-width: 80px !important;
    }
    #devis-table .input-prix {
        width: 90px !important;
        min-width: 60px !important;
        max-width: 120px !important;
    }
    @media (max-width: 576px) {
        #devis-table .input-article,
        #devis-table .input-description {
            width: 100% !important;
            min-width: 0 !important;
            max-width: 100% !important;
        }
        .form-row .col-md-4,
        .form-row .col-md-5,
        .form-row .col-md-3 {width: 100% !important; margin-bottom: 10px;}
        .text-right { text-align: left !important; }
    }
    /* Champs client/date/référence plus compacts */
    .form-row .form-control-sm {
        font-size: 14px;
        padding: 4px 8px;
        height: 32px;
    }

    #modal-articles-list .list-group-item {
    padding-top: 0.55em;
    padding-bottom: 0.55em;
    font-size: 14px;
    border-bottom: 1px solid #eee;
    cursor: pointer;
}
#modal-articles-list .list-group-item .badge {
    font-size: 12px;
}
@media (max-width: 576px) {
    #modal-articles-list .article-info {
        flex-direction: column !important;
        align-items: flex-start !important;
    }
}
</style>
<script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
<script src="/s6/public/js/script.js"></script>
<script src="/s6/public/js/devis-lignes.js"></script>
<script src="/s6/public/js/devis-validation.js"></script>
<script src="/s6/public/js/unsaved-warning.js"></script>
<script src="/s6/public/js/articles-modal.js"></script>
<script src="https://cdn.jsdelivr.net/npm/popper.js@1.16.1/dist/umd/popper.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@4.6.2/dist/js/bootstrap.min.js"></script>
<script>
// Alternance JS fiable sur chaque ligne article
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
>>>>>>> 4f8dbbb6b83eb9c6f755d57287033c7da885a3b1
</script>