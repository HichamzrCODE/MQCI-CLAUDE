<?php include '..\views\layout.php'; 
$pageTitle = "Catégorie : " . htmlspecialchars($category['nom']);
?>

<div class="container-fluid mt-4">
    <div class="row mb-4">
        <div class="col-md-8">
            <h2><i class="fas fa-tag"></i> <?= htmlspecialchars($category['nom']) ?></h2>
            <p class="text-muted"><?= htmlspecialchars($category['description'] ?? 'Pas de description') ?></p>
        </div>
        <div class="col-md-4 text-end">
            <?php if (hasPermission('categories', 'edit')): ?>
                <a href="index.php?action=categories/edit&id=<?= $category['id'] ?>" class="btn btn-warning">
                    <i class="fas fa-edit"></i> Modifier
                </a>
            <?php endif; ?>
            <a href="index.php?action=categories" class="btn btn-secondary">
                <i class="fas fa-arrow-left"></i> Retour
            </a>
        </div>
    </div>

    <div class="row mb-4">
        <div class="col-md-12">
            <div class="card">
                <div class="card-body">
                    <p><strong>Parent :</strong> <?= htmlspecialchars($category['parent_nom'] ?? 'Aucun') ?></p>
                    <p><strong>Articles :</strong> <span class="badge bg-info"><?= $article_count ?></span></p>
                </div>
            </div>
        </div>
    </div>

    <h3>Articles dans cette catégorie</h3>
    <?php if (count($articles) > 0): ?>
        <div class="table-responsive">
            <table class="table table-hover">
                <thead class="table-light">
                    <tr>
                        <th>Nom</th>
                        <th>SKU</th>
                        <th>Prix Revient</th>
                        <th>Prix Vente</th>
                        <th>Statut</th>
                        <th class="text-end">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($articles as $article): ?>
                        <tr>
                            <td><strong><?= htmlspecialchars($article['nom_art']) ?></strong></td>
                            <td><?= htmlspecialchars($article['sku'] ?? '') ?></td>
                            <td><?= number_format($article['pr'], 2, ',', ' ') ?> F</td>
                            <td><?= number_format($article['prix_vente'], 2, ',', ' ') ?> F</td>
                            <td><span class="badge bg-<?= $article['statut'] === 'actif' ? 'success' : 'danger' ?>">
                                <?= $article['statut'] ?>
                            </span></td>
                            <td class="text-end">
                                <a href="index.php?action=articles/show&id=<?= $article['id_articles'] ?>" 
                                   class="btn btn-sm btn-info">
                                    <i class="fas fa-eye"></i>
                                </a>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    <?php else: ?>
        <div class="alert alert-info">Aucun article dans cette catégorie.</div>
    <?php endif; ?>
</div>