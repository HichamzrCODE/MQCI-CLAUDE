<?php
$pageTitle = "Modifier l'article";
include '../views/layout.php';

$article = $article ?? [];
$fournisseurs = $fournisseurs ?? [];
$categories = $categories ?? [];
$depots = $depots ?? [];
$stockDepots = $stockDepots ?? [];
$error = $error ?? null;

// Indexer le stock existant par depot_id
$stockIndex = [];
foreach ($stockDepots as $sd) {
    $stockIndex[$sd['depot_id']] = $sd;
}
?>

<div class="container mt-4" style="max-width:900px;">
    <h1 class="mb-3" style="font-size:1.3rem;">Modifier l'article</h1>

    <?php if (!empty($error)): ?>
        <div class="alert alert-danger"><?= htmlspecialchars($error); ?></div>
    <?php endif; ?>

    <form method="post" action="index.php?action=articles/edit&id=<?= (int)$article['id_articles']; ?>" enctype="multipart/form-data">
        <?= $csrf_field ?? ''; ?>

        <!-- Identification -->
        <div class="card mb-3">
            <div class="card-header font-weight-bold">Identification</div>
            <div class="card-body">
                <div class="form-row">
                    <div class="form-group col-md-8">
                        <label for="nom_art">Nom de l'article <span class="text-danger">*</span></label>
                        <input type="text" class="form-control" id="nom_art" name="nom_art"
                               value="<?= htmlspecialchars($_POST['nom_art'] ?? $article['nom_art'] ?? ''); ?>"
                               style="text-transform:uppercase;" required>
                    </div>
                    <div class="form-group col-md-4">
                        <label for="sku">SKU / Référence</label>
                        <input type="text" class="form-control" id="sku" name="sku"
                               value="<?= htmlspecialchars($_POST['sku'] ?? ($article['sku'] ?? '')); ?>">
                    </div>
                </div>

                <div class="form-row">
                    <div class="form-group col-md-6">
                        <label for="fournisseur_id">Fournisseur principal <span class="text-danger">*</span></label>
                        <select class="form-control" id="fournisseur_id" name="fournisseur_id" required>
                            <option value="">Sélectionner un fournisseur</option>
                            <?php foreach ($fournisseurs as $f): ?>
                                <option value="<?= (int)$f['id_fournisseurs']; ?>"
                                    <?= (($_POST['fournisseur_id'] ?? $article['fournisseur_id'] ?? '') == $f['id_fournisseurs']) ? 'selected' : ''; ?>>
                                    <?= htmlspecialchars($f['nom_fournisseurs']); ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <div class="form-group col-md-6">
                        <label for="fournisseur_alternatif_id">Fournisseur alternatif</label>
                        <select class="form-control" id="fournisseur_alternatif_id" name="fournisseur_alternatif_id">
                            <option value="">Aucun</option>
                            <?php foreach ($fournisseurs as $f): ?>
                                <option value="<?= (int)$f['id_fournisseurs']; ?>"
                                    <?= (($_POST['fournisseur_alternatif_id'] ?? ($article['fournisseur_alternatif_id'] ?? '')) == $f['id_fournisseurs']) ? 'selected' : ''; ?>>
                                    <?= htmlspecialchars($f['nom_fournisseurs']); ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                </div>

                <div class="form-row">
                    <div class="form-group col-md-4">
                        <label for="categorie_id">Catégorie</label>
                        <select class="form-control" id="categorie_id" name="categorie_id">
                            <option value="">Aucune</option>
                            <?php foreach ($categories as $cat): ?>
                                <option value="<?= (int)$cat['id']; ?>"
                                    <?= (($_POST['categorie_id'] ?? ($article['categorie_id'] ?? '')) == $cat['id']) ? 'selected' : ''; ?>>
                                    <?= htmlspecialchars($cat['nom']); ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <div class="form-group col-md-4">
                        <label for="statut">Statut</label>
                        <select class="form-control" id="statut" name="statut">
                            <?php $st = $_POST['statut'] ?? ($article['statut'] ?? 'actif'); ?>
                            <option value="actif" <?= ($st === 'actif') ? 'selected' : ''; ?>>Actif</option>
                            <option value="inactif" <?= ($st === 'inactif') ? 'selected' : ''; ?>>Inactif</option>
                            <option value="discontinued" <?= ($st === 'discontinued') ? 'selected' : ''; ?>>Arrêté</option>
                        </select>
                    </div>

                    <div class="form-group col-md-4">
                        <label for="unite_mesure">Unité de mesure</label>
                        <?php $um = $_POST['unite_mesure'] ?? ($article['unite_mesure'] ?? 'Piece'); ?>
                        <select class="form-control" id="unite_mesure" name="unite_mesure">
                            <?php foreach (['Piece', 'Kg', 'Litre', 'Mètre', 'Boîte', 'Carton'] as $u): ?>
                                <option value="<?= $u; ?>" <?= ($um === $u) ? 'selected' : ''; ?>><?= $u; ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                </div>

                <!-- Image actuelle -->
                <?php if (!empty($article['image_path'])): ?>
                    <div class="mb-2">
                        <label>Image actuelle :</label><br>
                        <img src="<?= BASE_URL ?>/public/<?= htmlspecialchars($article['image_path']); ?>"
                             alt="Image"
                             style="max-height:120px;border:1px solid #ddd;padding:4px;">
                    </div>
                <?php endif; ?>

                <div class="form-group">
                    <label for="image">Nouvelle image (optionnel)</label>
                    <input type="file" class="form-control-file" id="image" name="image" accept="image/jpeg,image/png,image/gif,image/webp">
                    <small class="text-muted">JPG, PNG, GIF, WEBP — max 2 Mo</small>
                </div>
            </div>
        </div>

        <!-- Tarification (ALIGNÉE avec create) -->
        <div class="card mb-3">
            <div class="card-header font-weight-bold">Tarification</div>
            <div class="card-body">

                <div class="form-row">
                    <div class="form-group col-md-3">
                        <label for="prix_revient">Prix de revient <span class="text-danger">*</span></label>
                        <input type="number" step="0.01" min="0.01" class="form-control" id="prix_revient" name="pr"
                               value="<?= htmlspecialchars($_POST['pr'] ?? ($article['pr'] ?? '')); ?>" required>
                    </div>

                    <div class="form-group col-md-3">
                        <label for="prix_detail">Prix détail</label>
                        <input type="number" step="0.01" min="0" class="form-control" id="prix_detail" name="prix_detail"
                               value="<?= htmlspecialchars($_POST['prix_detail'] ?? ($article['prix_detail'] ?? '')); ?>">
                        <small class="text-muted">Si vide, on utilise “Prix de vente”.</small>
                    </div>

                    <div class="form-group col-md-3">
                        <label for="prix_semi_gros">Prix semi-gros</label>
                        <input type="number" step="0.01" min="0" class="form-control" id="prix_semi_gros" name="prix_semi_gros"
                               value="<?= htmlspecialchars($_POST['prix_semi_gros'] ?? ($article['prix_semi_gros'] ?? '')); ?>">
                    </div>

                    <div class="form-group col-md-3">
                        <label for="prix_gros">Prix gros</label>
                        <input type="number" step="0.01" min="0" class="form-control" id="prix_gros" name="prix_gros"
                               value="<?= htmlspecialchars($_POST['prix_gros'] ?? ($article['prix_gros'] ?? '')); ?>">
                    </div>
                </div>

                <div class="form-row">
                    <div class="form-group col-md-3">
                        <label for="prix_vente">Prix de vente (compatibilité)</label>
                        <input type="number" step="0.01" min="0" class="form-control" id="prix_vente" name="prix_vente"
                               value="<?= htmlspecialchars($_POST['prix_vente'] ?? ($article['prix_vente'] ?? '')); ?>">
                        <small class="text-muted">Utilisé comme fallback si prix détail vide.</small>
                    </div>

                    <div class="form-group col-md-3">
                        <label>Marge estimée (sur prix détail)</label>
                        <div class="form-control bg-light" id="marge-display">-</div>
                    </div>

                    <div class="form-group col-md-6">
                        <label for="raison_changement_prix">Raison du changement de prix (si applicable)</label>
                        <input type="text" class="form-control" id="raison_changement_prix" name="raison_changement_prix"
                               value="<?= htmlspecialchars($_POST['raison_changement_prix'] ?? ''); ?>"
                               placeholder="Ex: Mise à jour tarif fournisseur">
                    </div>
                </div>

            </div>
        </div>

        <!-- Stock -->
        <div class="card mb-3">
            <div class="card-header font-weight-bold">Paramètres de stock</div>
            <div class="card-body">
                <div class="form-row">
                    <div class="form-group col-md-4">
                        <label for="stock_minimal">Stock minimal d'alerte</label>
                        <input type="number" min="0" class="form-control" id="stock_minimal" name="stock_minimal"
                               value="<?= htmlspecialchars($_POST['stock_minimal'] ?? ($article['stock_minimal'] ?? '0')); ?>">
                    </div>
                    <div class="form-group col-md-4">
                        <label for="stock_maximal">Stock maximal</label>
                        <input type="number" min="0" class="form-control" id="stock_maximal" name="stock_maximal"
                               value="<?= htmlspecialchars($_POST['stock_maximal'] ?? ($article['stock_maximal'] ?? '0')); ?>">
                    </div>
                </div>

                <?php if (!empty($depots)): ?>
                    <hr>
                    <h6>Stock par dépôt</h6>

                    <div class="table-responsive">
                        <table class="table table-sm">
                            <thead><tr><th>Dépôt</th><th>Quantité</th><th>Emplacement</th></tr></thead>
                            <tbody>
                            <?php foreach ($depots as $depot): ?>
                                <?php $existing = $stockIndex[$depot['id']] ?? null; ?>
                                <tr>
                                    <td><?= htmlspecialchars($depot['nom']); ?></td>
                                    <td style="width:140px;">
                                        <input type="number" min="0" class="form-control form-control-sm"
                                               name="depots[<?= (int)$depot['id']; ?>][quantite]"
                                               value="<?= htmlspecialchars((string)($_POST['depots'][$depot['id']]['quantite'] ?? ($existing['quantite'] ?? 0))); ?>">
                                    </td>
                                    <td>
                                        <input type="text" class="form-control form-control-sm"
                                               name="depots[<?= (int)$depot['id']; ?>][emplacement]"
                                               value="<?= htmlspecialchars($_POST['depots'][$depot['id']]['emplacement'] ?? ($existing['emplacement'] ?? '')); ?>"
                                               placeholder="Ex: A-12-3">
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                <?php endif; ?>
            </div>
        </div>

        <!-- Caractéristiques physiques -->
        <div class="card mb-3">
            <div class="card-header font-weight-bold">Caractéristiques physiques</div>
            <div class="card-body">
                <div class="form-row">
                    <div class="form-group col-md-3">
                        <label for="poids_kg">Poids (kg)</label>
                        <input type="number" step="0.001" min="0" class="form-control" id="poids_kg" name="poids_kg"
                               value="<?= htmlspecialchars($_POST['poids_kg'] ?? ($article['poids_kg'] ?? '')); ?>">
                    </div>
                    <div class="form-group col-md-3">
                        <label for="longueur_cm">Longueur (cm)</label>
                        <input type="number" step="0.01" min="0" class="form-control" id="longueur_cm" name="longueur_cm"
                               value="<?= htmlspecialchars($_POST['longueur_cm'] ?? ($article['longueur_cm'] ?? '')); ?>">
                    </div>
                    <div class="form-group col-md-3">
                        <label for="largeur_cm">Largeur (cm)</label>
                        <input type="number" step="0.01" min="0" class="form-control" id="largeur_cm" name="largeur_cm"
                               value="<?= htmlspecialchars($_POST['largeur_cm'] ?? ($article['largeur_cm'] ?? '')); ?>">
                    </div>
                    <div class="form-group col-md-3">
                        <label for="hauteur_cm">Hauteur (cm)</label>
                        <input type="number" step="0.01" min="0" class="form-control" id="hauteur_cm" name="hauteur_cm"
                               value="<?= htmlspecialchars($_POST['hauteur_cm'] ?? ($article['hauteur_cm'] ?? '')); ?>">
                    </div>
                </div>

                <div class="form-row">
                    <div class="form-group col-md-4">
                        <label for="couleur">Couleur</label>
                        <input type="text" class="form-control" id="couleur" name="couleur"
                               value="<?= htmlspecialchars($_POST['couleur'] ?? ($article['couleur'] ?? '')); ?>">
                    </div>
                </div>
            </div>
        </div>

        <!-- Notes -->
        <div class="card mb-3">
            <div class="card-header font-weight-bold">Notes internes</div>
            <div class="card-body">
                <textarea class="form-control" id="notes_internes" name="notes_internes" rows="3"><?= htmlspecialchars($_POST['notes_internes'] ?? ($article['notes_internes'] ?? '')); ?></textarea>
            </div>
        </div>

        <div class="mb-4">
            <button type="submit" class="btn btn-primary">✔ Enregistrer les modifications</button>
            <a href="index.php?action=articles/show&id=<?= (int)$article['id_articles']; ?>" class="btn btn-info ml-2">Voir détail</a>
            <a href="index.php?action=articles" class="btn btn-secondary ml-2">Annuler</a>
        </div>
    </form>
</div>

<script>
(function() {
    function calcMarge() {
        var pr = parseFloat(document.getElementById('prix_revient').value) || 0;

        var pv = parseFloat(document.getElementById('prix_detail').value);
        if (isNaN(pv)) pv = parseFloat(document.getElementById('prix_vente').value) || 0;

        var el = document.getElementById('marge-display');
        if (pr > 0 && pv > 0) {
            var marge = ((pv - pr) / pr * 100).toFixed(1);
            var diff = (pv - pr).toLocaleString('fr-FR', {minimumFractionDigits:2, maximumFractionDigits:2});
            el.textContent = marge + '% (' + diff + ')';
            el.style.color = (pv >= pr) ? '#28a745' : '#dc3545';
        } else {
            el.textContent = '-';
            el.style.color = '';
        }
    }

    ['prix_revient','prix_detail','prix_vente'].forEach(function(id) {
        var input = document.getElementById(id);
        if (input) input.addEventListener('input', calcMarge);
    });
    calcMarge();
})();
</script>
<script>
(function () {
  const prixDetail = document.getElementById('prix_detail');
  const prixVente  = document.getElementById('prix_vente');
  if (!prixDetail || !prixVente) return;

  // au chargement: si prix_vente vide et prix_detail rempli => copie
  if (prixVente.value === '' && prixDetail.value !== '') {
    prixVente.value = prixDetail.value;
  }

  // à chaque saisie: prix_vente suit prix_detail (sync totale)
  prixDetail.addEventListener('input', function () {
    prixVente.value = prixDetail.value;
  });
})();
</script>