<?php include '../views/layout.php'; ?>

<?php
$pageTitle = "Modifier Dépôt : " . htmlspecialchars($depot['nom']);
?>

<div class="container mt-4">
    <div class="row justify-content-center">
        <div class="col-md-6">
            <h2><i class="fas fa-edit"></i> Modifier Dépôt</h2>

            <?php if ($error): ?>
                <div class="alert alert-danger alert-dismissible fade show" role="alert">
                    <i class="fas fa-exclamation-circle"></i> <?= htmlspecialchars($error) ?>
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
            <?php endif; ?>

            <form method="POST" class="card p-4">
                <?= $csrf_field ?>

                <div class="mb-3">
                    <label for="nom" class="form-label">Nom <span class="text-danger">*</span></label>
                    <input type="text" class="form-control" id="nom" name="nom" 
                           value="<?= htmlspecialchars($depot['nom'] ?? '') ?>" required>
                </div>

                <div class="mb-3">
                    <label for="adresse" class="form-label">Adresse <span class="text-danger">*</span></label>
                    <input type="text" class="form-control" id="adresse" name="adresse" 
                           value="<?= htmlspecialchars($depot['adresse'] ?? '') ?>" required>
                </div>

                <div class="mb-3">
                    <label for="ville" class="form-label">Ville <span class="text-danger">*</span></label>
                    <input type="text" class="form-control" id="ville" name="ville" 
                           value="<?= htmlspecialchars($depot['ville'] ?? '') ?>" required>
                </div>

                <div class="mb-3">
                    <label for="responsable_id" class="form-label">Responsable</label>
                    <select class="form-control" id="responsable_id" name="responsable_id">
                        <option value="">-- Sélectionner un responsable --</option>
                        <?php foreach ($responsables as $resp): ?>
                            <option value="<?= $resp['id_users'] ?>"
                                    <?= ($depot['responsable_id'] ?? '') == $resp['id_users'] ? 'selected' : '' ?>>
                                <?= htmlspecialchars($resp['username']) ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <div class="mb-3">
                    <label for="telephone" class="form-label">Téléphone</label>
                    <input type="tel" class="form-control" id="telephone" name="telephone"
                           value="<?= htmlspecialchars($depot['telephone'] ?? '') ?>">
                </div>

                <div class="mb-3">
                    <label for="email" class="form-label">Email</label>
                    <input type="email" class="form-control" id="email" name="email"
                           value="<?= htmlspecialchars($depot['email'] ?? '') ?>">
                </div>

                <div class="mb-3">
                    <label for="statut" class="form-label">Statut <span class="text-danger">*</span></label>
                    <select class="form-control" id="statut" name="statut" required>
                        <option value="actif" <?= ($depot['statut'] ?? '') === 'actif' ? 'selected' : '' ?>>
                            Actif
                        </option>
                        <option value="inactif" <?= ($depot['statut'] ?? '') === 'inactif' ? 'selected' : '' ?>>
                            Inactif
                        </option>
                    </select>
                </div>

                <div class="d-flex gap-2">
                    <button type="submit" class="btn btn-warning">
                        <i class="fas fa-save"></i> Modifier
                    </button>
                    <a href="index.php?action=depots" class="btn btn-secondary">
                        <i class="fas fa-times"></i> Annuler
                    </a>
                </div>
            </form>
        </div>
    </div>
</div>