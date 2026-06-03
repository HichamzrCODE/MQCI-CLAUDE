<?php include '..\views\layout.php'; 
$pageTitle = "Nouvelle Catégorie";
?>

<div class="container mt-4">
    <div class="row justify-content-center">
        <div class="col-md-6">
            <h2><i class="fas fa-plus-circle"></i> Nouvelle Catégorie</h2>

            <?php if ($error): ?>
                <div class="alert alert-danger alert-dismissible fade show" role="alert">
                    <i class="fas fa-exclamation-circle"></i> <?= htmlspecialchars($error) ?>
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
            <?php endif; ?>

            <form method="POST" class="card p-4">
                <div class="mb-3">
                    <label for="nom" class="form-label">Nom <span class="text-danger">*</span></label>
                    <input type="text" class="form-control" id="nom" name="nom" required>
                </div>

                <div class="mb-3">
                    <label for="description" class="form-label">Description</label>
                    <textarea class="form-control" id="description" name="description" rows="3" placeholder="Description optionnelle..."></textarea>
                </div>

                <div class="mb-3">
                    <label for="parent_id" class="form-label">Catégorie Parent (optionnelle)</label>
                    <select class="form-select" id="parent_id" name="parent_id">
                        <option value="">-- Aucune --</option>
                        <?php foreach ($parentCategories as $parent): ?>
                            <option value="<?= $parent['id'] ?>">
                                <?= htmlspecialchars($parent['nom']) ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <div class="d-flex gap-2">
                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-save"></i> Créer
                    </button>
                    <a href="index.php?action=categories" class="btn btn-secondary">
                        <i class="fas fa-times"></i> Annuler
                    </a>
                </div>
            </form>
        </div>
    </div>
</div>