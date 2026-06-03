<<<<<<< HEAD
<?php include '..\views\layout.php'; ?>

<?php
$sql_fournisseurs = "SELECT id_fournisseurs, nom_fournisseurs FROM fournisseurs ORDER BY nom_fournisseurs ASC";
try {
    $stmt_fournisseurs = $db->prepare($sql_fournisseurs);
    $stmt_fournisseurs->execute();
    $fournisseurs = $stmt_fournisseurs->fetchAll(PDO::FETCH_ASSOC);
} catch(PDOException $e) {
    die("Erreur lors de la récupération des fournisseurs: " . $e->getMessage());
}
?>

<div class="container">
    <h1 style="margin-top: 30px;">Créer un nouvel article</h1>

    <?php if (isset($data['error'])): ?>
        <div class="alert alert-danger">
            <?php echo htmlspecialchars($data['error']); ?>
        </div>
    <?php endif; ?>

    <form method="post" action="index.php?action=articles/create">
        <div class="form-group">
            <label for="nom_art">Nom de l'article:</label>
            <input type="text" class="form-control" id="nom_art" name="nom_art" style="text-transform: uppercase;" required>
        </div>

        <div class="form-group">
            <label for="pr">Prix de revient:</label>
            <input type="number" step="0.01" class="form-control" id="pr" name="pr" required>
        </div>

        <div class="form-group">
            <label for="fournisseur_id">Fournisseur:</label>
            <select class="form-control" id="fournisseur_id" name="fournisseur_id" required>
                <option value="">Sélectionner un fournisseur</option>
                <?php foreach ($fournisseurs as $fournisseur): ?>
                    <option value="<?php echo htmlspecialchars($fournisseur['id_fournisseurs']); ?>">
                        <?php echo htmlspecialchars($fournisseur['nom_fournisseurs']); ?>
                    </option>
                <?php endforeach; ?>
            </select>
        </div>

        <button type="submit" class="btn btn-primary">Créer</button>
        <a href="index.php?action=articles" class="btn btn-secondary">Annuler</a>
    </form>
</div>
=======
<?php include '../views/layout.php'; ?>

<div class="container mt-4" style="max-width:900px;">
    <h1 class="mb-3" style="font-size:1.3rem;">Créer un nouvel article</h1>

    <?php if (!empty($error)): ?>
        <div class="alert alert-danger"><?= htmlspecialchars($error); ?></div>
    <?php endif; ?>

    <form method="post" action="index.php?action=articles/create" enctype="multipart/form-data">
        <?= $csrf_field ?? ''; ?>

        <!-- Identification -->
        <div class="card mb-3">
            <div class="card-header font-weight-bold">Identification</div>
            <div class="card-body">
                <div class="form-row">
                    <div class="form-group col-md-8">
                        <label for="nom_art">Nom de l'article <span class="text-danger">*</span></label>
                        <input type="text" class="form-control" id="nom_art" name="nom_art"
                               value="<?= htmlspecialchars($_POST['nom_art'] ?? ''); ?>"
                               style="text-transform:uppercase;" required>
                    </div>
                    <div class="form-group col-md-4">
                        <label for="sku">SKU / Référence</label>
                        <input type="text" class="form-control" id="sku" name="sku"
                               value="<?= htmlspecialchars($_POST['sku'] ?? ''); ?>"
                               placeholder="Ex: REF-001">
                    </div>
                </div>
                <div class="form-row">
                    <div class="form-group col-md-6">
                        <label for="fournisseur_id">Fournisseur principal <span class="text-danger">*</span></label>
                        <select class="form-control" id="fournisseur_id" name="fournisseur_id" required>
                            <option value="">Sélectionner un fournisseur</option>
                            <?php foreach ($fournisseurs as $f): ?>
                                <option value="<?= htmlspecialchars($f['id_fournisseurs']); ?>"
                                    <?= (($_POST['fournisseur_id'] ?? '') == $f['id_fournisseurs']) ? 'selected' : ''; ?>>
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
                                <option value="<?= htmlspecialchars($f['id_fournisseurs']); ?>"
                                    <?= (($_POST['fournisseur_alternatif_id'] ?? '') == $f['id_fournisseurs']) ? 'selected' : ''; ?>>
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
                                <option value="<?= $cat['id']; ?>"
                                    <?= (($_POST['categorie_id'] ?? '') == $cat['id']) ? 'selected' : ''; ?>>
                                    <?= htmlspecialchars($cat['nom']); ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="form-group col-md-4">
                        <label for="statut">Statut</label>
                        <select class="form-control" id="statut" name="statut">
                            <option value="actif" <?= (($_POST['statut'] ?? 'actif') === 'actif') ? 'selected' : ''; ?>>Actif</option>
                            <option value="inactif" <?= (($_POST['statut'] ?? '') === 'inactif') ? 'selected' : ''; ?>>Inactif</option>
                            <option value="discontinued" <?= (($_POST['statut'] ?? '') === 'discontinued') ? 'selected' : ''; ?>>Arrêté</option>
                        </select>
                    </div>
                    <div class="form-group col-md-4">
                        <label for="unite_mesure">Unité de mesure</label>
                        <select class="form-control" id="unite_mesure" name="unite_mesure">
                            <?php foreach (['Piece', 'Kg', 'Litre', 'Mètre', 'Boîte', 'Carton'] as $u): ?>
                                <option value="<?= $u; ?>" <?= (($_POST['unite_mesure'] ?? 'Piece') === $u) ? 'selected' : ''; ?>><?= $u; ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                </div>
                <div class="form-group">
                    <label for="image">Image produit</label>
                    <input type="file" class="form-control-file" id="image" name="image" accept="image/jpeg,image/png,image/gif,image/webp">
                    <small class="text-muted">JPG, PNG, GIF, WEBP — max 2 Mo</small>
                </div>
            </div>
        </div>

        <!-- Tarification -->
        <div class="card mb-3">
            <div class="card-header font-weight-bold">Tarification</div>
            <div class="card-body">
                <div class="form-row">
                    <div class="form-group col-md-4">
                        <label for="prix_revient">Prix de revient <span class="text-danger">*</span></label>
                        <input type="number" step="0.01" min="0.01" class="form-control" id="prix_revient" name="pr"
                               value="<?= htmlspecialchars($_POST['prix_revient'] ?? ''); ?>" required>
                    </div>
                    <div class="form-group col-md-4">
                        <label for="prix_vente">Prix de vente</label>
                        <input type="number" step="0.01" min="0" class="form-control" id="prix_vente" name="prix_vente"
                               value="<?= htmlspecialchars($_POST['prix_vente'] ?? ''); ?>">
                    </div>
                    <div class="form-group col-md-4">
                        <label>Marge estimée</label>
                        <div class="form-control bg-light" id="marge-display">-</div>
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
                               value="<?= htmlspecialchars($_POST['stock_minimal'] ?? '0'); ?>">
                    </div>
                    <div class="form-group col-md-4">
                        <label for="stock_maximal">Stock maximal</label>
                        <input type="number" min="0" class="form-control" id="stock_maximal" name="stock_maximal"
                               value="<?= htmlspecialchars($_POST['stock_maximal'] ?? '0'); ?>">
                    </div>
                </div>
                <?php if (!empty($depots)): ?>
                    <hr>
                    <h6>Stock initial par dépôt</h6>
                    <div class="table-responsive">
                        <table class="table table-sm">
                            <thead><tr><th>Dépôt</th><th>Quantité initiale</th><th>Emplacement</th></tr></thead>
                            <tbody>
                            <?php foreach ($depots as $depot): ?>
                                <tr>
                                    <td><?= htmlspecialchars($depot['nom']); ?></td>
                                    <td style="width:140px;">
                                        <input type="number" min="0" class="form-control form-control-sm"
                                               name="depots[<?= $depot['id']; ?>][quantite]" value="0">
                                    </td>
                                    <td>
                                        <input type="text" class="form-control form-control-sm"
                                               name="depots[<?= $depot['id']; ?>][emplacement]"
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
                               value="<?= htmlspecialchars($_POST['poids_kg'] ?? ''); ?>">
                    </div>
                    <div class="form-group col-md-3">
                        <label for="longueur_cm">Longueur (cm)</label>
                        <input type="number" step="0.01" min="0" class="form-control" id="longueur_cm" name="longueur_cm"
                               value="<?= htmlspecialchars($_POST['longueur_cm'] ?? ''); ?>">
                    </div>
                    <div class="form-group col-md-3">
                        <label for="largeur_cm">Largeur (cm)</label>
                        <input type="number" step="0.01" min="0" class="form-control" id="largeur_cm" name="largeur_cm"
                               value="<?= htmlspecialchars($_POST['largeur_cm'] ?? ''); ?>">
                    </div>
                    <div class="form-group col-md-3">
                        <label for="hauteur_cm">Hauteur (cm)</label>
                        <input type="number" step="0.01" min="0" class="form-control" id="hauteur_cm" name="hauteur_cm"
                               value="<?= htmlspecialchars($_POST['hauteur_cm'] ?? ''); ?>">
                    </div>
                </div>
                <div class="form-row">
                    <div class="form-group col-md-4">
                        <label for="couleur">Couleur</label>
                        <input type="text" class="form-control" id="couleur" name="couleur"
                               value="<?= htmlspecialchars($_POST['couleur'] ?? ''); ?>">
                    </div>
                </div>
            </div>
        </div>

        <!-- Notes -->
        <div class="card mb-3">
            <div class="card-header font-weight-bold">Notes internes</div>
            <div class="card-body">
                <textarea class="form-control" id="notes_internes" name="notes_internes" rows="3"><?= htmlspecialchars($_POST['notes_internes'] ?? ''); ?></textarea>
            </div>
        </div>

        <div class="mb-4">
            <button type="submit" class="btn btn-success">✔ Créer l'article</button>
            <a href="index.php?action=articles" class="btn btn-secondary ml-2">Annuler</a>
        </div>
    </form>
</div>

<script>
(function() {
    function calcMarge() {
        var pr = parseFloat($('#prix_revient').val()) || 0;
        var pv = parseFloat($('#prix_vente').val()) || 0;
        if (pr > 0 && pv > 0) {
            var marge = ((pv - pr) / pr * 100).toFixed(1);
            var diff = (pv - pr).toLocaleString('fr-FR', {minimumFractionDigits:2, maximumFractionDigits:2});
            $('#marge-display').text(marge + '% (' + diff + ')');
            $('#marge-display').css('color', pv >= pr ? '#28a745' : '#dc3545');
        } else {
            $('#marge-display').text('-').css('color','');
        }
    }
    $('#prix_revient, #prix_vente').on('input', calcMarge);
    calcMarge();
})();
</script>
>>>>>>> 4f8dbbb6b83eb9c6f755d57287033c7da885a3b1
