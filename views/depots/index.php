<?php include '../views/layout.php'; ?>

<?php
$pageTitle = "Dépôts";
$pageDescription = "Gestion des dépôts de stockage";
?>

<div class="container-fluid mt-4">
    <div class="row mb-4">
        <div class="col-md-8">
            <h1><i class="fas fa-warehouse"></i> Dépôts</h1>
            <p class="text-muted">Total: <strong><?= $totalDepots ?></strong> dépôts</p>
        </div>
        <div class="col-md-4 text-end">
            <?php if (hasPermission('depots', 'create')): ?>
                <a href="index.php?action=depots/create" class="btn btn-primary">
                    <i class="fas fa-plus"></i> Nouveau Dépôt
                </a>
            <?php endif; ?>
        </div>
    </div>

    <!-- Recherche -->
    <div class="row mb-4">
        <div class="col-md-12">
            <input type="text" id="searchInput" class="form-control" placeholder="Rechercher un dépôt...">
        </div>
    </div>

    <!-- Message d'erreur -->
    <?php if (isset($_GET['error']) && $_GET['error'] === 'impossible_supprimer'): ?>
        <div class="alert alert-danger alert-dismissible fade show" role="alert">
            <i class="fas fa-exclamation-circle"></i> <strong>Impossible de supprimer !</strong> 
            Ce dépôt contient du stock.
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    <?php endif; ?>

    <!-- Tableau -->
    <div class="table-responsive">
        <table class="table table-hover">
            <thead class="table-light">
                <tr>
                    <th>Nom</th>
                    <th>Adresse</th>
                    <th>Ville</th>
                    <th>Téléphone</th>
                    <th>Statut</th>
                    <th class="text-center">Articles</th>
                    <th class="text-end">Actions</th>
                </tr>
            </thead>
            <tbody id="depotTable">
                <?php foreach ($depots as $depot): ?>
                    <tr>
                        <td><strong><?= htmlspecialchars($depot['nom']) ?></strong></td>
                        <td><?= htmlspecialchars($depot['adresse'] ?? '') ?></td>
                        <td><?= htmlspecialchars($depot['ville'] ?? '') ?></td>
                        <td><?= htmlspecialchars($depot['telephone'] ?? '') ?></td>
                        <td>
                            <?php if ($depot['statut'] === 'actif'): ?>
                                <span class="badge bg-success">Actif</span>
                            <?php else: ?>
                                <span class="badge bg-danger">Inactif</span>
                            <?php endif; ?>
                        </td>
                        <td class="text-center">
                            <span class="badge bg-info"><?= $depot['total_articles'] ?? 0 ?></span>
                        </td>
                        <td class="text-end">
                            <a href="index.php?action=depots/show&id=<?= $depot['id'] ?>" 
                               class="btn btn-sm btn-info" title="Voir">
                                <i class="fas fa-eye"></i>
                            </a>
                            <?php if (hasPermission('depots', 'edit')): ?>
                                <a href="index.php?action=depots/edit&id=<?= $depot['id'] ?>" 
                                   class="btn btn-sm btn-warning" title="Modifier">
                                    <i class="fas fa-edit"></i>
                                </a>
                            <?php endif; ?>
                            <?php if (hasPermission('depots', 'delete')): ?>
                                <a href="index.php?action=depots/delete&id=<?= $depot['id'] ?>" 
                                   class="btn btn-sm btn-danger" 
                                   onclick="return confirm('Êtes-vous sûr ?');" 
                                   title="Supprimer">
                                    <i class="fas fa-trash"></i>
                                </a>
                            <?php endif; ?>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</div>

<script>
document.getElementById('searchInput').addEventListener('keyup', function() {
    const term = this.value.trim();
    fetch('index.php?action=depots/search&term=' + encodeURIComponent(term))
        .then(response => response.json())
        .then(data => {
            const tbody = document.getElementById('depotTable');
            tbody.innerHTML = '';
            data.forEach(depot => {
                const statutBadge = depot.statut === 'actif' 
                    ? '<span class="badge bg-success">Actif</span>' 
                    : '<span class="badge bg-danger">Inactif</span>';
                
                const row = `
                    <tr>
                        <td><strong>${escapeHtml(depot.nom)}</strong></td>
                        <td>${escapeHtml(depot.adresse || '')}</td>
                        <td>${escapeHtml(depot.ville || '')}</td>
                        <td>${escapeHtml(depot.telephone || '')}</td>
                        <td>${statutBadge}</td>
                        <td class="text-center"><span class="badge bg-info">${depot.total_articles || 0}</span></td>
                        <td class="text-end">
                            <a href="index.php?action=depots/show&id=${depot.id}" class="btn btn-sm btn-info"><i class="fas fa-eye"></i></a>
                            ${depot.editable ? `<a href="index.php?action=depots/edit&id=${depot.id}" class="btn btn-sm btn-warning"><i class="fas fa-edit"></i></a>` : ''}
                            ${depot.deletable ? `<a href="index.php?action=depots/delete&id=${depot.id}" class="btn btn-sm btn-danger" onclick="return confirm('Êtes-vous sûr ?');"><i class="fas fa-trash"></i></a>` : ''}
                        </td>
                    </tr>
                `;
                tbody.innerHTML += row;
            });
        });
});

function escapeHtml(text) {
    const map = { '&': '&amp;', '<': '&lt;', '>': '&gt;', '"': '&quot;', "'": '&#039;' };
    return text.replace(/[&<>"']/g, m => map[m]);
}
</script>