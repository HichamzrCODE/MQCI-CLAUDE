<?php include '../views/layout.php'; ?>

<div class="container mt-4">
    <div class="row justify-content-center">
        <div class="col-md-8">
            <h2><i class="fas fa-plus-circle"></i> Nouveau Transfert de Stock</h2>

            <?php if ($error): ?>
                <div class="alert alert-danger alert-dismissible fade show">
                    <i class="fas fa-exclamation-circle"></i> <?= htmlspecialchars($error) ?>
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
            <?php endif; ?>

            <form method="POST" class="card p-4">
                <?= $csrf_field ?>

                <div class="mb-3">
                    <label for="depot_source_id" class="form-label">Dépôt Source <span class="text-danger">*</span></label>
                    <select class="form-select" id="depot_source_id" name="depot_source_id" required>
                        <option value="">-- Sélectionner le dépôt source --</option>
                        <?php foreach ($depots as $depot): ?>
                            <option value="<?= $depot['id'] ?>"
                                <?= ($_POST['depot_source_id'] ?? '') == $depot['id'] ? 'selected' : '' ?>>
                                <?= htmlspecialchars($depot['nom']) ?>
                                <?php if (!empty($depot['ville'])): ?>(<?= htmlspecialchars($depot['ville']) ?>)<?php endif; ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <div class="mb-3">
                    <label for="depot_destination_id" class="form-label">Dépôt Destination <span class="text-danger">*</span></label>
                    <select class="form-select" id="depot_destination_id" name="depot_destination_id" required>
                        <option value="">-- Sélectionner le dépôt destination --</option>
                        <?php foreach ($depots as $depot): ?>
                            <option value="<?= $depot['id'] ?>"
                                <?= ($_POST['depot_destination_id'] ?? '') == $depot['id'] ? 'selected' : '' ?>>
                                <?= htmlspecialchars($depot['nom']) ?>
                                <?php if (!empty($depot['ville'])): ?>(<?= htmlspecialchars($depot['ville']) ?>)<?php endif; ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <div class="mb-3">
                    <label for="date_transfert" class="form-label">Date de Transfert <span class="text-danger">*</span></label>
                    <input type="date" class="form-control" id="date_transfert" name="date_transfert" required
                           value="<?= htmlspecialchars($_POST['date_transfert'] ?? date('Y-m-d')) ?>">
                </div>

                <div class="mb-3">
                    <label for="description" class="form-label">Description</label>
                    <textarea class="form-control" id="description" name="description" rows="3"><?= htmlspecialchars($_POST['description'] ?? '') ?></textarea>
                </div>

                <div class="d-flex gap-2">
                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-save"></i> Créer le transfert
                    </button>
                    <a href="index.php?action=transferts_stock" class="btn btn-secondary">
                        <i class="fas fa-times"></i> Annuler
                    </a>
                </div>
            </form>
        </div>
    </div>
</div>