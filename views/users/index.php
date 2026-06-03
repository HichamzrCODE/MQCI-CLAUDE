<<<<<<< HEAD
<?php include '../views/layout.php'; ?>
<div class="container mt-4">
    <h2 style="margin-top: 30px;">Liste des utilisateurs</h2>
    <a href="index.php?action=users/create" class="btn btn-success mb-3">Créer un utilisateur</a>
    <table class="table table-striped">
        <thead>
            <tr>
                <th>ID</th><th>Username</th><th>Nom</th><th>Prénom</th><th>Téléphone</th><th>Succursale</th><th>Rôle</th><th>Connectés</th><th>Actions</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($users as $u): ?>
                <tr>
                    <td><?= $u['id_users'] ?></td>
                    <td><?= htmlspecialchars($u['username']) ?></td>
                    <td><?= htmlspecialchars($u['nom']) ?></td>
                    <td><?= htmlspecialchars($u['prenom']) ?></td>
                    <td><?= htmlspecialchars($u['telephone']) ?></td>
                    <td><?= htmlspecialchars($u['succursale']) ?></td>
                    <td><?= htmlspecialchars($u['role']) ?></td>
                    <td>
                        <?php
                            $expiration = 1800; // 30 minutes
                            $connected = $u['session_token'] && strtotime($u['last_login']) > (time() - $expiration);
                            echo $connected ? 'Oui' : 'Non';
                        ?>
                    </td>
                    <td>
                        <a href="index.php?action=users/edit&id=<?= $u['id_users'] ?>" class="btn btn-warning btn-sm">Modifier</a>
                        <?php if ($connected && $u['id_users'] != $_SESSION['user_id']): ?>
                            <a href="index.php?action=users/disconnect&id=<?= $u['id_users'] ?>"
                               class="btn btn-secondary btn-sm"
                               onclick="return confirm('Déconnecter cet utilisateur ?')">
                               Déconnecter
                            </a>
                        <?php endif; ?>
                        <a href="index.php?action=users/delete&id=<?= $u['id_users'] ?>"
                           class="btn btn-danger btn-sm"
                           onclick="return confirm('Supprimer cet utilisateur ?')">X</a>
                    </td>
                </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
=======
<?php include '../views/layout.php'; ?>
<div class="container mt-4">
    <h2 style="margin-top: 30px;">Liste des utilisateurs</h2>
    <a href="index.php?action=users/create" class="btn btn-success mb-3">Créer un utilisateur</a>
    <table class="table table-striped">
        <thead>
            <tr>
                <th>ID</th><th>Username</th><th>Nom</th><th>Prénom</th><th>Téléphone</th><th>Succursale</th><th>Rôle</th><th>Connectés</th><th>Actions</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($users as $u): ?>
                <tr>
                    <td><?= $u['id_users'] ?></td>
                    <td><?= htmlspecialchars($u['username']) ?></td>
                    <td><?= htmlspecialchars($u['nom']) ?></td>
                    <td><?= htmlspecialchars($u['prenom']) ?></td>
                    <td><?= htmlspecialchars($u['telephone']) ?></td>
                    <td><?= htmlspecialchars($u['succursale']) ?></td>
                    <td><?= htmlspecialchars($u['role']) ?></td>
                    <td>
                        <?php
                            $expiration = 1800; // 30 minutes
                            $connected = $u['session_token'] && strtotime($u['last_login']) > (time() - $expiration);
                            echo $connected ? 'Oui' : 'Non';
                        ?>
                    </td>
                    <td>
                        <a href="index.php?action=users/edit&id=<?= $u['id_users'] ?>" class="btn btn-warning btn-sm">Modifier</a>
                        <?php if ($connected && $u['id_users'] != $_SESSION['user_id']): ?>
                            <a href="index.php?action=users/disconnect&id=<?= $u['id_users'] ?>"
                               class="btn btn-secondary btn-sm"
                               onclick="return confirm('Déconnecter cet utilisateur ?')">
                               Déconnecter
                            </a>
                        <?php endif; ?>
                        <a href="index.php?action=users/delete&id=<?= $u['id_users'] ?>"
                           class="btn btn-danger btn-sm"
                           onclick="return confirm('Supprimer cet utilisateur ?')">X</a>
                    </td>
                </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
>>>>>>> 4f8dbbb6b83eb9c6f755d57287033c7da885a3b1
</div>