<<<<<<< HEAD
<?php include '..\views\layout.php'; ?>

<div class="container" style="max-width:900px;">
    <h1 class="mt-4 mb-3">Détails du client</h1>
    <div class="card mb-4 shadow-sm">
        <div class="card-body">
            <div class="row mb-2 align-items-center">
                <div class="col-md-6">
                    <h5 class="card-title mb-2"><?= htmlspecialchars($client['nom']) ?></h5>
                    <span class="badge bg-secondary"><?= htmlspecialchars($client['ville']) ?></span>
                </div>
                <div class="col-md-6 text-md-end">
                    <span class="text-muted"><i class="bi bi-telephone"></i> <?= htmlspecialchars($client['telephone']) ?></span>
                </div>
                <div class="col-md-6 text-md-end">
                    <span class="text-muted"><i class="bi bi-telephone"></i> <?= htmlspecialchars($client['type_client']) ?></span>
                </div>
            </div>
            <a href="index.php?action=clients" class="btn btn-outline-secondary btn-sm">Retour à la liste</a>
        </div>
    </div>

    <hr>
    <div class="mb-3 d-flex flex-column flex-md-row align-items-md-center justify-content-between">
        <h4 class="mb-2 mb-md-0">Articles déjà eu en devis</h4>
        <input type="text" id="search-article-input" class="form-control form-control-sm" placeholder="🔎 Rechercher un article, devis, ou prix..." style="max-width:260px;">
    </div>
    <?php if (!empty($articles_devis)): ?>
    <div style="overflow-x:auto;">
        <table id="articles-devis-table" class="table table-bordered table-striped table-sm" style="font-size: 0.97rem; background: #f9f9f9;">
            <thead style="background: #F4FAF2;">
                <tr>
                    <th>Article</th>
                    <th>Prix de vente</th>
                    <th>Numéro devis</th>
                    <th>Date</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($articles_devis as $ad): ?>
                <tr>
                    <td><?= htmlspecialchars($ad['article']) ?></td>
                    <td><?= number_format($ad['prix_unitaire'], 0, ',', ' ') ?></td>
                    <td><?= htmlspecialchars($ad['devis_numero']) ?></td>
                    <td><?= date('d/m/Y', strtotime($ad['devis_date'])) ?></td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
    <?php else: ?>
        <p class="text-muted mt-3">Aucun article trouvé dans les devis pour ce client.</p>
    <?php endif; ?>
</div>

<style>
.card-title { font-size: 1.34rem; }
#search-article-input { margin-bottom:5px; }
.table th, .table td { vertical-align: middle; white-space: nowrap; }
@media (max-width: 600px) {
    .card-title { font-size: 1.07rem; }
    #search-article-input { font-size:0.95rem;}
}
</style>

<script>
// Filtrage instantané de la table articles devisés
document.getElementById('search-article-input').addEventListener('input', function() {
    const value = this.value.toLowerCase().trim();
    const rows = document.querySelectorAll('#articles-devis-table tbody tr');
    rows.forEach(row => {
        let show = false;
        row.querySelectorAll('td').forEach(td => {
            if (td.textContent.toLowerCase().indexOf(value) !== -1) show = true;
        });
        row.style.display = show ? '' : 'none';
    });
});
=======
<?php include '..\views\layout.php'; ?>

<div class="container" style="max-width:900px;">
    <h1 class="mt-4 mb-3">Détails du client</h1>
    <div class="card mb-4 shadow-sm">
        <div class="card-body">
            <div class="row mb-2 align-items-center">
                <div class="col-md-6">
                    <h5 class="card-title mb-2"><?= htmlspecialchars($client['nom']) ?></h5>
                    <span class="badge bg-secondary"><?= htmlspecialchars($client['ville']) ?></span>
                </div>
                <div class="col-md-6 text-md-end">
                    <span class="text-muted"><i class="bi bi-telephone"></i> <?= htmlspecialchars($client['telephone']) ?></span>
                </div>
                <div class="col-md-6 text-md-end">
                    <span class="text-muted"><i class="bi bi-telephone"></i> <?= htmlspecialchars($client['type_client']) ?></span>
                </div>
            </div>
            <a href="index.php?action=clients" class="btn btn-outline-secondary btn-sm">Retour à la liste</a>
        </div>
    </div>

    <hr>
    <div class="mb-3 d-flex flex-column flex-md-row align-items-md-center justify-content-between">
        <h4 class="mb-2 mb-md-0">Articles déjà eu en devis</h4>
        <input type="text" id="search-article-input" class="form-control form-control-sm" placeholder="🔎 Rechercher un article, devis, ou prix..." style="max-width:260px;">
    </div>
    <?php if (!empty($articles_devis)): ?>
    <div style="overflow-x:auto;">
        <table id="articles-devis-table" class="table table-bordered table-striped table-sm" style="font-size: 0.97rem; background: #f9f9f9;">
            <thead style="background: #F4FAF2;">
                <tr>
                    <th>Article</th>
                    <th>Prix de vente</th>
                    <th>Numéro devis</th>
                    <th>Date</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($articles_devis as $ad): ?>
                <tr>
                    <td><?= htmlspecialchars($ad['article']) ?></td>
                    <td><?= number_format($ad['prix_unitaire'], 0, ',', ' ') ?></td>
                    <td><?= htmlspecialchars($ad['devis_numero']) ?></td>
                    <td><?= date('d/m/Y', strtotime($ad['devis_date'])) ?></td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
    <?php else: ?>
        <p class="text-muted mt-3">Aucun article trouvé dans les devis pour ce client.</p>
    <?php endif; ?>
</div>

<style>
.card-title { font-size: 1.34rem; }
#search-article-input { margin-bottom:5px; }
.table th, .table td { vertical-align: middle; white-space: nowrap; }
@media (max-width: 600px) {
    .card-title { font-size: 1.07rem; }
    #search-article-input { font-size:0.95rem;}
}
</style>

<script>
// Filtrage instantané de la table articles devisés
document.getElementById('search-article-input').addEventListener('input', function() {
    const value = this.value.toLowerCase().trim();
    const rows = document.querySelectorAll('#articles-devis-table tbody tr');
    rows.forEach(row => {
        let show = false;
        row.querySelectorAll('td').forEach(td => {
            if (td.textContent.toLowerCase().indexOf(value) !== -1) show = true;
        });
        row.style.display = show ? '' : 'none';
    });
});
>>>>>>> 4f8dbbb6b83eb9c6f755d57287033c7da885a3b1
</script>