<<<<<<< HEAD
<?php include '../views/layout.php'; ?>
<?php
$errorFields = $errorFields ?? [];
$lignesDevis = $lignesDevis ?? [];
$clients = $clients ?? [];
$articles = $articles ?? [];
$devis = $devis ?? [];
$error = $error ?? null;

// Pour la valeur du client autocomplete
$currentClientId = $_POST['client_id'] ?? $devis['client_id'] ?? '';
$currentClientName = '';
if ($currentClientId) {
    foreach ($clients as $client) {
        if ($client['id_clients'] == $currentClientId) {
            $currentClientName = $client['nom'];
            break;
        }
    }
}

// Fonction PHP pour normaliser le prix (pour affichage éventuel côté PHP)
function normalize_price($prix) {
    $prix = preg_replace('/[\s\xA0]/u', '', $prix);
    $prix = str_replace(',', '.', $prix);
    return $prix;
}
?>
<style>
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
        width: 220px !important;
        min-width: 160px !important;
        max-width: 350px !important;
    }
    #devis-table .input-description {
        width: 200px !important;
        min-width: 160px !important;
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
        .form-row .col-md-2 { width: 100% !important; margin-bottom: 10px; }
        .text-right { text-align: left !important; }
    }
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

<div class="container mt-4">
    <h4>Éditer un Devis</h4>
    <?php if ($error): ?>
        <div class="alert alert-danger"><?= $error ?></div>
    <?php endif; ?>
    <form method="post" action="index.php?action=devis/update&id=<?= $devis['id'] ?>" id="devis-form" autocomplete="off">
        <div class="form-row mb-1">
            <!-- Client autocomplete à gauche -->
            <div class="col-md-4 mb-2">
                <div class="autocomplete-wrapper">
                    <input type="text"
                        class="form-control form-control-sm client-autocomplete <?= isset($errorFields['client_id']) ? 'is-invalid' : '' ?>"
                        id="client_nom"
                        placeholder="Rechercher un client..."
                        value="<?= htmlspecialchars($_POST['client_nom'] ?? $currentClientName) ?>">
                    <input type="hidden" name="client_id" id="client_id"
                        value="<?= htmlspecialchars($currentClientId) ?>">
                    <div class="autocomplete-results" style="display:none; position:absolute; z-index:1000;"></div>
                    <?php if (isset($errorFields['client_id'])): ?>
                        <div class="invalid-feedback"><?= $errorFields['client_id'] ?></div>
                    <?php endif; ?>
                </div>
                <!-- Référence sous le client -->
                <input type="text" class="form-control form-control-sm mt-2" name="reference" id="reference"
                       placeholder="Référence (optionnel)"
                       value="<?= htmlspecialchars($_POST['reference'] ?? $devis['reference'] ?? '') ?>">
            </div>
            <!-- Numéro devis et date à droite, empilés -->
            <div class="col-md-2 offset-md-4 mb-2 text-right">
                <div class="form-group mb-1">
                    <input type="text" class="form-control form-control-sm text-right"
                           id="numero" name="numero"
                           value="<?= htmlspecialchars($devis['numero'] ?? '') ?>"
                           readonly
                           style="background:#f7f7f7;font-weight:bold;"
                           placeholder="Numéro devis">
                </div>
                <div class="form-group mb-0">
                    <input type="date"
                        class="form-control form-control-sm text-right <?= isset($errorFields['date']) ? 'is-invalid' : '' ?>"
                        id="date" name="date"
                        value="<?= htmlspecialchars($_POST['date'] ?? ($devis['date'] ?? date('Y-m-d'))) ?>">
                    <?php if (isset($errorFields['date'])): ?>
                        <div class="invalid-feedback text-left"><?= $errorFields['date'] ?></div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
        <div class="table-responsive">
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
                    <?php foreach ($lignesDevis as $index => $ligne):
                        $inputIndex = isset($ligne['id']) ? 'id_' . $ligne['id'] : 'new_' . $index;
                    ?>
                    <tr class="ligne-article" data-ligne-id="<?= htmlspecialchars($inputIndex) ?>" <?php if (isset($ligne['id'])): ?>data-ligne-db-id="<?= (int)$ligne['id'] ?>"<?php endif; ?>>
                        <td>
                            <div class="autocomplete-wrapper">
                                <input type="text"
                                    class="form-control article-autocomplete input-article <?= isset($errorFields["articles_{$inputIndex}_article_id"]) ? 'is-invalid' : '' ?>"
                                    placeholder="Rechercher un article"
                                    value="<?= htmlspecialchars($ligne['nom_art'] ?? '') ?>">
                                <input type="hidden" class="article-id" name="articles[<?= $inputIndex ?>][article_id]"
                                    value="<?= htmlspecialchars($ligne['article_id'] ?? '') ?>">
                                <div class="autocomplete-results"></div>
                                <?php if (isset($errorFields["articles_{$inputIndex}_article_id"])): ?>
                                    <div class="invalid-feedback"><?= $errorFields["articles_{$inputIndex}_article_id"] ?></div>
                                <?php endif; ?>
                            </div>
                        </td>
                        <td>
                            <input type="text"
                                class="form-control input-description"
                                name="articles[<?= $inputIndex ?>][description]"
                                placeholder="Description (optionnel)"
                                value="<?= htmlspecialchars($ligne['description'] ?? '') ?>">
                        </td>
                        <td>
                            <input type="number"
                                class="form-control quantite-input input-quantite <?= isset($errorFields["articles_{$inputIndex}_quantite"]) ? 'is-invalid' : '' ?>"
                                min="1"
                                name="articles[<?= $inputIndex ?>][quantite]"
                                value="<?= htmlspecialchars($ligne['quantite'] ?? '1') ?>">
                            <?php if (isset($errorFields["articles_{$inputIndex}_quantite"])): ?>
                                <div class="invalid-feedback"><?= $errorFields["articles_{$inputIndex}_quantite"] ?></div>
                            <?php endif; ?>
                        </td>
                        <td>
                            <input type="text"
                                class="form-control prix-unitaire-input input-prix <?= isset($errorFields["articles_{$inputIndex}_prix_unitaire"]) ? 'is-invalid' : '' ?>"
                                name="articles[<?= $inputIndex ?>][prix_unitaire]"
                                value="<?= htmlspecialchars(number_format($ligne['prix_unitaire'], 0, ',', ' ')) ?>">
                            <?php if (isset($errorFields["articles_{$inputIndex}_prix_unitaire"])): ?>
                                <div class="invalid-feedback"><?= $errorFields["articles_{$inputIndex}_prix_unitaire"] ?></div>
                            <?php endif; ?>
                        </td>
                        <td class="total-ligne" data-total="<?= htmlspecialchars($ligne['total'] ?? '0') ?>">
                            <?= number_format($ligne['total'], 0, ',', ' ') ?>
                        </td>
                        <td>
                            <button type="button" class="btn btn-danger remove-ligne" data-ligne-id="<?= htmlspecialchars($inputIndex) ?>">X</button>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
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
<script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
<script src="/s6/public/js/script.js"></script>
<script src="/s6/public/js/devis-lignes.js"></script>
<script src="/s6/public/js/devis-validation.js"></script>
<script src="/s6/public/js/unsaved-warning.js"></script>
<script src="/s6/public/js/articles-modal.js"></script>
<script src="https://cdn.jsdelivr.net/npm/popper.js@1.16.1/dist/umd/popper.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@4.6.2/dist/js/bootstrap.min.js"></script>
<script>
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
$lignesDevis = $lignesDevis ?? [];
$clients = $clients ?? [];
$articles = $articles ?? [];
$devis = $devis ?? [];
$error = $error ?? null;

// Pour la valeur du client autocomplete
$currentClientId = $_POST['client_id'] ?? $devis['client_id'] ?? '';
$currentClientName = '';
if ($currentClientId) {
    foreach ($clients as $client) {
        if ($client['id_clients'] == $currentClientId) {
            $currentClientName = $client['nom'];
            break;
        }
    }
}

// Fonction PHP pour normaliser le prix (pour affichage éventuel côté PHP)
function normalize_price($prix) {
    $prix = preg_replace('/[\s\xA0]/u', '', $prix);
    $prix = str_replace(',', '.', $prix);
    return $prix;
}
?>
<style>
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
        width: 220px !important;
        min-width: 160px !important;
        max-width: 350px !important;
    }
    #devis-table .input-description {
        width: 200px !important;
        min-width: 160px !important;
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
        .form-row .col-md-2 { width: 100% !important; margin-bottom: 10px; }
        .text-right { text-align: left !important; }
    }
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

<div class="container mt-4">
    <h4>Éditer un Devis</h4>
    <?php if ($error): ?>
        <div class="alert alert-danger"><?= $error ?></div>
    <?php endif; ?>
    <form method="post" action="index.php?action=devis/update&id=<?= $devis['id'] ?>" id="devis-form" autocomplete="off">
        <div class="form-row mb-1">
            <!-- Client autocomplete à gauche -->
            <div class="col-md-4 mb-2">
                <div class="autocomplete-wrapper">
                    <input type="text"
                        class="form-control form-control-sm client-autocomplete <?= isset($errorFields['client_id']) ? 'is-invalid' : '' ?>"
                        id="client_nom"
                        placeholder="Rechercher un client..."
                        value="<?= htmlspecialchars($_POST['client_nom'] ?? $currentClientName) ?>">
                    <input type="hidden" name="client_id" id="client_id"
                        value="<?= htmlspecialchars($currentClientId) ?>">
                    <div class="autocomplete-results" style="display:none; position:absolute; z-index:1000;"></div>
                    <?php if (isset($errorFields['client_id'])): ?>
                        <div class="invalid-feedback"><?= $errorFields['client_id'] ?></div>
                    <?php endif; ?>
                </div>
                <!-- Référence sous le client -->
                <input type="text" class="form-control form-control-sm mt-2" name="reference" id="reference"
                       placeholder="Référence (optionnel)"
                       value="<?= htmlspecialchars($_POST['reference'] ?? $devis['reference'] ?? '') ?>">
            </div>
            <!-- Numéro devis et date à droite, empilés -->
            <div class="col-md-2 offset-md-4 mb-2 text-right">
                <div class="form-group mb-1">
                    <input type="text" class="form-control form-control-sm text-right"
                           id="numero" name="numero"
                           value="<?= htmlspecialchars($devis['numero'] ?? '') ?>"
                           readonly
                           style="background:#f7f7f7;font-weight:bold;"
                           placeholder="Numéro devis">
                </div>
                <div class="form-group mb-0">
                    <input type="date"
                        class="form-control form-control-sm text-right <?= isset($errorFields['date']) ? 'is-invalid' : '' ?>"
                        id="date" name="date"
                        value="<?= htmlspecialchars($_POST['date'] ?? ($devis['date'] ?? date('Y-m-d'))) ?>">
                    <?php if (isset($errorFields['date'])): ?>
                        <div class="invalid-feedback text-left"><?= $errorFields['date'] ?></div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
        <div class="table-responsive">
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
                    <?php foreach ($lignesDevis as $index => $ligne):
                        $inputIndex = isset($ligne['id']) ? 'id_' . $ligne['id'] : 'new_' . $index;
                    ?>
                    <tr class="ligne-article" data-ligne-id="<?= htmlspecialchars($inputIndex) ?>" <?php if (isset($ligne['id'])): ?>data-ligne-db-id="<?= (int)$ligne['id'] ?>"<?php endif; ?>>
                        <td>
                            <div class="autocomplete-wrapper">
                                <input type="text"
                                    class="form-control article-autocomplete input-article <?= isset($errorFields["articles_{$inputIndex}_article_id"]) ? 'is-invalid' : '' ?>"
                                    placeholder="Rechercher un article"
                                    value="<?= htmlspecialchars($ligne['nom_art'] ?? '') ?>">
                                <input type="hidden" class="article-id" name="articles[<?= $inputIndex ?>][article_id]"
                                    value="<?= htmlspecialchars($ligne['article_id'] ?? '') ?>">
                                <div class="autocomplete-results"></div>
                                <?php if (isset($errorFields["articles_{$inputIndex}_article_id"])): ?>
                                    <div class="invalid-feedback"><?= $errorFields["articles_{$inputIndex}_article_id"] ?></div>
                                <?php endif; ?>
                            </div>
                        </td>
                        <td>
                            <input type="text"
                                class="form-control input-description"
                                name="articles[<?= $inputIndex ?>][description]"
                                placeholder="Description (optionnel)"
                                value="<?= htmlspecialchars($ligne['description'] ?? '') ?>">
                        </td>
                        <td>
                            <input type="number"
                                class="form-control quantite-input input-quantite <?= isset($errorFields["articles_{$inputIndex}_quantite"]) ? 'is-invalid' : '' ?>"
                                min="1"
                                name="articles[<?= $inputIndex ?>][quantite]"
                                value="<?= htmlspecialchars($ligne['quantite'] ?? '1') ?>">
                            <?php if (isset($errorFields["articles_{$inputIndex}_quantite"])): ?>
                                <div class="invalid-feedback"><?= $errorFields["articles_{$inputIndex}_quantite"] ?></div>
                            <?php endif; ?>
                        </td>
                        <td>
                            <input type="text"
                                class="form-control prix-unitaire-input input-prix <?= isset($errorFields["articles_{$inputIndex}_prix_unitaire"]) ? 'is-invalid' : '' ?>"
                                name="articles[<?= $inputIndex ?>][prix_unitaire]"
                                value="<?= htmlspecialchars(number_format($ligne['prix_unitaire'], 0, ',', ' ')) ?>">
                            <?php if (isset($errorFields["articles_{$inputIndex}_prix_unitaire"])): ?>
                                <div class="invalid-feedback"><?= $errorFields["articles_{$inputIndex}_prix_unitaire"] ?></div>
                            <?php endif; ?>
                        </td>
                        <td class="total-ligne" data-total="<?= htmlspecialchars($ligne['total'] ?? '0') ?>">
                            <?= number_format($ligne['total'], 0, ',', ' ') ?>
                        </td>
                        <td>
                            <button type="button" class="btn btn-danger remove-ligne" data-ligne-id="<?= htmlspecialchars($inputIndex) ?>">X</button>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
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
<script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
<script src="/s6/public/js/script.js"></script>
<script src="/s6/public/js/devis-lignes.js"></script>
<script src="/s6/public/js/devis-validation.js"></script>
<script src="/s6/public/js/unsaved-warning.js"></script>
<script src="/s6/public/js/articles-modal.js"></script>
<script src="https://cdn.jsdelivr.net/npm/popper.js@1.16.1/dist/umd/popper.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@4.6.2/dist/js/bootstrap.min.js"></script>
<script>
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