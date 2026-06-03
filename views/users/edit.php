<?php include '../views/layout.php'; ?>

<?php $pageTitle = "Éditer " . htmlspecialchars($user['prenom'] . ' ' . $user['nom']); ?>

<div class="page-header">
    <h1><i class="fas fa-user-edit"></i> Éditer l'Utilisateur</h1>
    <p class="page-description"><?= htmlspecialchars($user['prenom'] . ' ' . $user['nom']) ?> (<?= htmlspecialchars($user['username']) ?>)</p>
</div>

<?php if ($error): ?>
    <div class="alert alert-danger alert-dismissible fade show" role="alert">
        <i class="fas fa-exclamation-circle"></i> <?= htmlspecialchars($error) ?>
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
<?php endif; ?>

<?php if ($success): ?>
    <div class="alert alert-success alert-dismissible fade show" role="alert">
        <i class="fas fa-check-circle"></i> <?= htmlspecialchars($success) ?>
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
<?php endif; ?>

<div class="row">
    <div class="col-lg-6">
        <div class="card">
            <div class="card-header">
                <h5 class="mb-0">Informations de l'Utilisateur</h5>
            </div>
            <div class="card-body">
                <form method="POST">
                    <?= $csrf_field ?>

                    <div class="alert alert-info">
                        <i class="fas fa-info-circle"></i> 
                        <strong>Username :</strong> <?= htmlspecialchars($user['username']) ?> (non modifiable)
                    </div>

                    <div class="mb-3">
                        <label for="nom" class="form-label">Nom <span class="text-danger">*</span></label>
                        <input type="text" class="form-control" id="nom" name="nom" value="<?= htmlspecialchars($user['nom']) ?>" required>
                    </div>

                    <div class="mb-3">
                        <label for="prenom" class="form-label">Prénom <span class="text-danger">*</span></label>
                        <input type="text" class="form-control" id="prenom" name="prenom" value="<?= htmlspecialchars($user['prenom']) ?>" required>
                    </div>

                    <div class="mb-3">
                        <label for="telephone" class="form-label">Téléphone <span class="text-danger">*</span></label>
                        <input type="tel" class="form-control" id="telephone" name="telephone" value="<?= htmlspecialchars($user['telephone']) ?>" required>
                    </div>

                    <div class="mb-3">
                        <label for="succursale" class="form-label">Succursale <span class="text-danger">*</span></label>
                        <input type="text" class="form-control" id="succursale" name="succursale" value="<?= htmlspecialchars($user['succursale']) ?>" required>
                    </div>

                    <hr>

                    <div class="mb-3">
                        <label for="role" class="form-label">Rôle <span class="text-danger">*</span></label>
                        <select class="form-select" id="role" name="role" required>
                            <option value="user" <?= $user['role'] === 'user' ? 'selected' : '' ?>>Utilisateur</option>
                            <option value="manager" <?= $user['role'] === 'manager' ? 'selected' : '' ?>>Manager</option>
                            <option value="admin" <?= $user['role'] === 'admin' ? 'selected' : '' ?>>Admin</option>
                        </select>
                    </div>

                    <div class="mb-3">
                        <label for="status" class="form-label">Statut <span class="text-danger">*</span></label>
                        <select class="form-select" id="status" name="status" required>
                            <option value="actif" <?= $user['status'] === 'actif' ? 'selected' : '' ?>>Actif</option>
                            <option value="inactif" <?= $user['status'] === 'inactif' ? 'selected' : '' ?>>Inactif</option>
                        </select>
                    </div>

                    <hr>

                    <div class="mb-3">
                        <label for="password" class="form-label">Nouveau mot de passe</label>
                        <input type="password" class="form-control" id="password" name="password" placeholder="Laisser vide pour ne pas changer">
                        <small class="text-muted">Minimum 6 caractères</small>
                    </div>

                    <div class="d-flex gap-2">
                        <button type="submit" class="btn btn-primary btn-lg">
                            <i class="fas fa-save"></i> Mettre à jour
                        </button>
                        <a href="index.php?action=users" class="btn btn-secondary btn-lg">
                            <i class="fas fa-times"></i> Annuler
                        </a>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <div class="col-lg-6">
        <div class="card">
            <div class="card-header">
                <h5 class="mb-0"><i class="fas fa-history"></i> Historique</h5>
            </div>
            <div class="card-body">
                <p>
                    <strong>Créé le :</strong><br>
                    <?= date('d/m/Y H:i', strtotime($user['created_at'])) ?>
                </p>
                <p>
                    <strong>Dernière connexion :</strong><br>
                    <?= $user['last_login'] ? date('d/m/Y H:i', strtotime($user['last_login'])) : 'Jamais' ?>
                </p>
            </div>
        </div>
    </div>
</div>

<?php include '../views/footer.php'; ?>