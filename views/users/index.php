<?php include '../views/layout.php'; ?>

<?php $pageTitle = "Gestion des Utilisateurs"; ?>

<div class="page-header">
    <h1><i class="fas fa-users-cog"></i> Gestion des Utilisateurs</h1>
    <p class="page-description">Administrez les utilisateurs du système</p>
</div>

<?php if (isset($_GET['message'])): ?>
    <div class="alert alert-success alert-dismissible fade show" role="alert">
        <i class="fas fa-check-circle"></i> <?= htmlspecialchars($_GET['message']) ?>
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
<?php endif; ?>

<div class="row mb-3">
    <div class="col-md-6">
        <h4>Liste des utilisateurs</h4>
    </div>
    <div class="col-md-6 text-end">
        <a href="index.php?action=users/create" class="btn btn-primary">
            <i class="fas fa-plus"></i> Nouvel Utilisateur
        </a>
    </div>
</div>

<div class="card">
    <div class="card-body">
        <?php if (count($users) > 0): ?>
            <div class="table-responsive">
                <table class="table table-hover">
                    <thead class="table-light">
                        <tr>
                            <th>ID</th>
                            <th>Username</th>
                            <th>Nom Complet</th>
                            <th>Téléphone</th>
                            <th>Succursale</th>
                            <th>Rôle</th>
                            <th>Statut</th>
                            <th>Connecté</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($users as $u): ?>
                            <tr>
                                <td>
                                    <span class="badge bg-secondary"><?= $u['id_users'] ?></span>
                                </td>
                                <td>
                                    <strong><?= htmlspecialchars($u['username']) ?></strong>
                                </td>
                                <td>
                                    <?= htmlspecialchars($u['prenom'] . ' ' . $u['nom']) ?>
                                </td>
                                <td>
                                    <i class="fas fa-phone"></i> <?= htmlspecialchars($u['telephone']) ?>
                                </td>
                                <td>
                                    <?= htmlspecialchars($u['succursale']) ?>
                                </td>
                                <td>
                                    <?php
                                        $roleBadge = [
                                            'admin' => 'danger',
                                            'manager' => 'warning',
                                            'user' => 'info'
                                        ];
                                        $color = $roleBadge[$u['role']] ?? 'secondary';
                                    ?>
                                    <span class="badge bg-<?= $color ?>">
                                        <?= ucfirst($u['role']) ?>
                                    </span>
                                </td>
                                <td>
                                    <?php if ($u['status'] === 'actif'): ?>
                                        <span class="badge bg-success">Actif</span>
                                    <?php else: ?>
                                        <span class="badge bg-danger">Inactif</span>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <?php
                                        $lastLogin = $u['last_login'] ? strtotime($u['last_login']) : 0;
                                        $expiration = 30 * 60;
                                        $isConnected = $u['session_token'] && $lastLogin > (time() - $expiration);
                                    ?>
                                    <?php if ($isConnected): ?>
                                        <span class="badge bg-success">
                                            <i class="fas fa-circle"></i> En ligne
                                        </span>
                                    <?php else: ?>
                                        <span class="badge bg-secondary">Hors ligne</span>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <div class="btn-group btn-group-sm" role="group">
                                        <a href="index.php?action=users/edit&id=<?= $u['id_users'] ?>" 
                                           class="btn btn-warning" title="Modifier">
                                            <i class="fas fa-edit"></i>
                                        </a>
                                        <?php if ($isConnected && $u['id_users'] != $_SESSION['user_id']): ?>
                                            <a href="index.php?action=users/disconnect&id=<?= $u['id_users'] ?>"
                                               class="btn btn-secondary" title="Déconnecter"
                                               onclick="return confirm('Déconnecter cet utilisateur ?')">
                                                <i class="fas fa-sign-out-alt"></i>
                                            </a>
                                        <?php endif; ?>
                                        <?php if ($u['id_users'] != $_SESSION['user_id']): ?>
                                            <a href="index.php?action=users/delete&id=<?= $u['id_users'] ?>"
                                               class="btn btn-danger" title="Supprimer"
                                               onclick="return confirm('Supprimer cet utilisateur ?')">
                                                <i class="fas fa-trash"></i>
                                            </a>
                                        <?php endif; ?>
                                    </div>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        <?php else: ?>
            <div class="alert alert-info">
                <i class="fas fa-info-circle"></i> Aucun utilisateur trouvé.
            </div>
        <?php endif; ?>
    </div>
</div>

<?php include '../views/footer.php'; ?>