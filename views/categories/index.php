<?php include '..\views\layout.php'; 
$pageTitle = "Catégories";
$pageDescription = "Gestion des catégories d'articles";
?>

<div class="container-fluid mt-4">
    <div class="row mb-4">
        <div class="col-md-8">
            <h1><i class="fas fa-tags"></i> Catégories</h1>
            <p class="text-muted">Total: <strong><?= $totalCategories ?></strong> catégories</p>
        </div>
        <div class="col-md-4 text-end">
            <?php if (hasPermission('categories', 'create')): ?>
                <a href="index.php?action=categories/create" class="btn btn-primary">
                    <i class="fas fa-plus"></i> Nouvelle Catégorie
                </a>
            <?php endif; ?>
        </div>
    </div>

    <!-- Recherche -->
    <div class="row mb-4">
        <div class="col-md-12">
            <input type="text" id="searchInput" class="form-control" placeholder="Rechercher une catégorie...">
        </div>
    </div>

    <!-- Message d'erreur -->
    <?php if (isset($_GET['error']) && $_GET['error'] === 'impossible_supprimer'): ?>
        <div class="alert alert-danger alert-dismissible fade show" role="alert">
            <i class="fas fa-exclamation-circle"></i> <strong>Impossible de supprimer !</strong> 
            Cette catégorie contient des articles ou des sous-catégories.
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    <?php endif; ?>

    <!-- Tableau -->
    <div class="table-responsive">
        <table class="table table-hover">
            <thead class="table-light">
                <tr>
                    <th>Nom</th>
                    <th>Parent</th>
                    <th>Description</th>
                    <th class="text-center">Articles</th>
                    <th class="text-end">Actions</th>
                </tr>
            </thead>
            <tbody id="categoryTable">
                <?php foreach ($categories as $cat): ?>
                    <tr>
                        <td><strong><?= htmlspecialchars($cat['nom']) ?></strong></td>
                        <td><?= htmlspecialchars($cat['parent_nom'] ?? 'Aucun') ?></td>
                        <td>
                            <small class="text-muted">
                                <?= htmlspecialchars(substr($cat['description'] ?? '', 0, 50)) ?>
                                <?= strlen($cat['description'] ?? '') > 50 ? '...' : '' ?>
                            </small>
                        </td>
                        <td class="text-center">
                            <span class="badge bg-info"><?= $cat['article_count'] ?? 0 ?></span>
                        </td>
                        <td class="text-end">
                            <a href="index.php?action=categories/show&id=<?= $cat['id'] ?>" 
                               class="btn btn-sm btn-info" title="Voir">
                                <i class="fas fa-eye"></i>
                            </a>
                            <?php if (hasPermission('categories', 'edit')): ?>
                                <a href="index.php?action=categories/edit&id=<?= $cat['id'] ?>" 
                                   class="btn btn-sm btn-warning" title="Modifier">
                                    <i class="fas fa-edit"></i>
                                </a>
                            <?php endif; ?>
                            <?php if (hasPermission('categories', 'delete')): ?>
                                <a href="index.php?action=categories/delete&id=<?= $cat['id'] ?>" 
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
    fetch('index.php?action=categories/search&term=' + encodeURIComponent(term))
        .then(response => response.json())
        .then(data => {
            const tbody = document.getElementById('categoryTable');
            tbody.innerHTML = '';
            data.forEach(cat => {
                const row = `
                    <tr>
                        <td><strong>${escapeHtml(cat.nom)}</strong></td>
                        <td>${escapeHtml(cat.parent_nom || 'Aucun')}</td>
                        <td><small class="text-muted">${escapeHtml((cat.description || '').substring(0, 50))}</small></td>
                        <td class="text-center"><span class="badge bg-info">${cat.article_count || 0}</span></td>
                        <td class="text-end">
                            <a href="index.php?action=categories/show&id=${cat.id}" class="btn btn-sm btn-info"><i class="fas fa-eye"></i></a>
                            ${cat.editable ? `<a href="index.php?action=categories/edit&id=${cat.id}" class="btn btn-sm btn-warning"><i class="fas fa-edit"></i></a>` : ''}
                            ${cat.deletable ? `<a href="index.php?action=categories/delete&id=${cat.id}" class="btn btn-sm btn-danger" onclick="return confirm('Êtes-vous sûr ?');"><i class="fas fa-trash"></i></a>` : ''}
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