<<<<<<< HEAD
<?php include '../views/layout.php'; ?>
<div class="container" style="max-width: 760px; margin-top: 50px;">
    <div class="card shadow">
        <div class="card-header bg-primary text-white text-center">
            <h2 class="mb-0" style="font-size: 1.5rem;">Modifier l'utilisateur : <?= htmlspecialchars($user['prenom'].' '.$user['nom'].' ('.$user['username'].')') ?></h2>
        </div>
        <div class="card-body">
            <?php if (!empty($error)): ?>
                <div class="alert alert-danger"><?= $error ?></div>
            <?php endif; ?>
            <?php if (!empty($success)): ?>
                <div class="alert alert-success"><?= $success ?></div>
            <?php endif; ?>
            <form method="post" autocomplete="off">
                <div class="form-row">
                    <div class="form-group col-md-6">
                        <label for="nom">Nom</label>
                        <input type="text" id="nom" name="nom" class="form-control" value="<?= htmlspecialchars($user['nom']) ?>" required>
                    </div>
                    <div class="form-group col-md-6">
                        <label for="prenom">Prénom</label>
                        <input type="text" id="prenom" name="prenom" class="form-control" value="<?= htmlspecialchars($user['prenom']) ?>" required>
                    </div>
                </div>
                <div class="form-row">
                    <div class="form-group col-md-6">
                        <label for="telephone">Téléphone</label>
                        <input type="text" id="telephone" name="telephone" class="form-control" value="<?= htmlspecialchars($user['telephone']) ?>" required>
                    </div>
                    <div class="form-group col-md-6">
                        <label for="succursale">Succursale</label>
                        <input type="text" id="succursale" name="succursale" class="form-control" value="<?= htmlspecialchars($user['succursale']) ?>" required>
                    </div>
                </div>
                <div class="form-row">
                    <div class="form-group col-md-6">
                        <label for="role">Rôle</label>
                        <select id="role" name="role" class="form-control" required>
                            <option value="user" <?= $user['role']=='user'?'selected':'' ?>>Utilisateur</option>
                            <option value="manager" <?= $user['role']=='manager'?'selected':'' ?>>Manager</option>
                        </select>
                    </div>
                    <div class="form-group col-md-6">
                        <label for="password">Changer le mot de passe <span class="text-muted font-italic" style="font-size:85%;">(laisser vide pour ne pas modifier)</span></label>
                        <input type="password" id="password" name="password" class="form-control" autocomplete="new-password">
                    </div>
                </div>
                <div class="form-group text-center mt-4">
                    <button type="submit" class="btn btn-primary px-4">Enregistrer</button>
                    <a href="index.php?action=users" class="btn btn-secondary px-4 ml-2">Retour</a>
                </div>
            </form>
        </div>
    </div>
=======
<?php include '../views/layout.php'; ?>
<div class="container" style="max-width: 760px; margin-top: 50px;">
    <div class="card shadow">
        <div class="card-header bg-primary text-white text-center">
            <h2 class="mb-0" style="font-size: 1.5rem;">Modifier l'utilisateur : <?= htmlspecialchars($user['prenom'].' '.$user['nom'].' ('.$user['username'].')') ?></h2>
        </div>
        <div class="card-body">
            <?php if (!empty($error)): ?>
                <div class="alert alert-danger"><?= $error ?></div>
            <?php endif; ?>
            <?php if (!empty($success)): ?>
                <div class="alert alert-success"><?= $success ?></div>
            <?php endif; ?>
            <form method="post" autocomplete="off">
                <div class="form-row">
                    <div class="form-group col-md-6">
                        <label for="nom">Nom</label>
                        <input type="text" id="nom" name="nom" class="form-control" value="<?= htmlspecialchars($user['nom']) ?>" required>
                    </div>
                    <div class="form-group col-md-6">
                        <label for="prenom">Prénom</label>
                        <input type="text" id="prenom" name="prenom" class="form-control" value="<?= htmlspecialchars($user['prenom']) ?>" required>
                    </div>
                </div>
                <div class="form-row">
                    <div class="form-group col-md-6">
                        <label for="telephone">Téléphone</label>
                        <input type="text" id="telephone" name="telephone" class="form-control" value="<?= htmlspecialchars($user['telephone']) ?>" required>
                    </div>
                    <div class="form-group col-md-6">
                        <label for="succursale">Succursale</label>
                        <input type="text" id="succursale" name="succursale" class="form-control" value="<?= htmlspecialchars($user['succursale']) ?>" required>
                    </div>
                </div>
                <div class="form-row">
                    <div class="form-group col-md-6">
                        <label for="role">Rôle</label>
                        <select id="role" name="role" class="form-control" required>
                            <option value="user" <?= $user['role']=='user'?'selected':'' ?>>Utilisateur</option>
                            <option value="manager" <?= $user['role']=='manager'?'selected':'' ?>>Manager</option>
                        </select>
                    </div>
                    <div class="form-group col-md-6">
                        <label for="password">Changer le mot de passe <span class="text-muted font-italic" style="font-size:85%;">(laisser vide pour ne pas modifier)</span></label>
                        <input type="password" id="password" name="password" class="form-control" autocomplete="new-password">
                    </div>
                </div>
                <div class="form-group text-center mt-4">
                    <button type="submit" class="btn btn-primary px-4">Enregistrer</button>
                    <a href="index.php?action=users" class="btn btn-secondary px-4 ml-2">Retour</a>
                </div>
            </form>
        </div>
    </div>
>>>>>>> 4f8dbbb6b83eb9c6f755d57287033c7da885a3b1
</div>