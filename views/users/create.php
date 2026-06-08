<?php include '../views/layout.php'; ?>

<?php $pageTitle = "Créer un Utilisateur"; ?>

<div class="page-header">
    <h1><i class="fas fa-user-plus"></i> Créer un Utilisateur</h1>
    <p class="page-description">Ajouter un nouvel utilisateur au système</p>
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

                    <div class="mb-3">
                        <label for="username" class="form-label">Nom d'utilisateur <span class="text-danger">*</span></label>
                        <input type="text" class="form-control" id="username" name="username" value="<?= htmlspecialchars($data['username'] ?? '') ?>"required>
                        <small class="text-muted">Utilisé pour se connecter</small>
                    </div>

                    <div class="mb-3">
                        <label for="password" class="form-label">Mot de passe <span class="text-danger">*</span></label>
                        <input type="password" class="form-control" id="password" name="password" required>
                        <small class="text-muted">Minimum 6 caractères</small>
                    </div>

                    <div class="mb-3">
                        <label for="password_confirm" class="form-label">Confirmer le mot de passe <span class="text-danger">*</span></label>
                        <input type="password" class="form-control" id="password_confirm" name="password_confirm" required>
                    </div>

                    <hr>

                    <div class="mb-3">
                        <label for="nom" class="form-label">Nom <span class="text-danger">*</span></label>
                        <input type="text" class="form-control" id="nom" name="nom" value="<?= htmlspecialchars($data['nom'] ?? '') ?>" required>
                    </div>

                    <div class="mb-3">
                        <label for="prenom" class="form-label">Prénom <span class="text-danger">*</span></label>
                        <input type="text" class="form-control" id="prenom" name="prenom" value="<?= htmlspecialchars($data['prenom'] ?? '') ?>" required>
                    </div>

                    <div class="mb-3">
                        <label for="telephone" class="form-label">Téléphone <span class="text-danger">*</span></label>
                        <input type="tel" class="form-control" id="telephone" name="telephone" value="<?= htmlspecialchars($data['telephone'] ?? '') ?>" required>
                    </div>

                    <div class="mb-3">
                        <label for="succursale" class="form-label">Succursale </label>
                        <input type="text" class="form-control" id="succursale" name="succursale" value="<?= htmlspecialchars($data['succursale'] ?? '') ?>">
                    </div>

                    <div class="mb-3">
                        <label for="role" class="form-label">Rôle <span class="text-danger">*</span></label>
                        <select class="form-select" id="role" name="role" required>
                            <option value="user">Utilisateur</option>
                            <option value="manager">Manager</option>
                            <option value="admin">Admin</option>
                        </select>
                    </div>
                    <div class="mb-3">
    <label for="status" class="form-label">Statut <span class="text-danger">*</span></label>
    <select class="form-select" id="status" name="status" required>
        <option value="actif">Actif</option>
        <option value="inactif">Inactif</option>
    </select>
</div>

                    <div class="d-flex gap-2">
                        <button type="submit" class="btn btn-primary btn-lg">
                            <i class="fas fa-save"></i> Créer
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
                <h5 class="mb-0"><i class="fas fa-info-circle"></i> Informations</h5>
            </div>
            <div class="card-body">
                <h6>Rôles disponibles :</h6>
                <ul class="list-unstyled">
                    <li><span class="badge bg-info">Utilisateur</span> - Accès basique</li>
                    <li><span class="badge bg-warning">Manager</span> - Accès intermédiaire</li>
                    <li><span class="badge bg-danger">Admin</span> - Accès complet</li>
                </ul>
            </div>
        </div>
    </div>
</div>

<?php include '../views/footer.php'; ?>