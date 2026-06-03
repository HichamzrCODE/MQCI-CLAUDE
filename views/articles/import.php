<?php include '../views/layout.php'; ?>

<div class="container mt-4" style="max-width:600px;">
    <h1 class="mb-3" style="font-size:1.3rem;">Importer des articles (CSV)</h1>

    <?php if (!empty($error)): ?>
        <div class="alert alert-danger"><?= htmlspecialchars($error); ?></div>
    <?php endif; ?>
    <?php if (!empty($success)): ?>
        <div class="alert alert-success"><?= htmlspecialchars($success); ?></div>
    <?php endif; ?>

    <div class="card mb-3">
        <div class="card-header font-weight-bold">Format attendu du CSV</div>
        <div class="card-body">
            <p class="text-muted" style="font-size:0.9rem;">
                Le fichier CSV doit utiliser le <strong>point-virgule (;)</strong> comme séparateur.<br>
                La première ligne (en-tête) est ignorée.<br>
                Colonnes attendues : <code>ID ; SKU ; Nom ; Prix Revient ; Prix Vente ; Quantité ; Statut ; Unité ; Stock min ; Stock max ; Poids ; Couleur ; Notes ; Fournisseur</code>
            </p>
            <a href="index.php?action=articles/export" class="btn btn-outline-secondary btn-sm">⬇ Télécharger un exemple (export actuel)</a>
        </div>
    </div>

    <form method="post" action="index.php?action=articles/import" enctype="multipart/form-data">
        <?= $csrf_field ?? ''; ?>
        <div class="form-group">
            <label for="csv_file">Fichier CSV <span class="text-danger">*</span></label>
            <input type="file" class="form-control-file" id="csv_file" name="csv_file" accept=".csv" required>
            <small class="text-muted">Taille max : 5 Mo</small>
        </div>
        <button type="submit" class="btn btn-primary">⬆ Importer</button>
        <a href="index.php?action=articles" class="btn btn-secondary ml-2">Annuler</a>
    </form>
</div>
