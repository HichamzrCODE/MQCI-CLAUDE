<?php
$pageTitle = "Gestion des Utilisateurs";
include '../views/layout.php';

$users   = $users   ?? [];
$message = $message ?? ($_GET['message'] ?? null);
$error   = $error   ?? ($_GET['error']   ?? null);

// Seuil "En ligne" : 15 minutes
$ONLINE_THRESHOLD = 15 * 60;
?>

<div class="container mt-4">

    <div class="d-flex justify-content-between align-items-center mb-3">
        <h4 class="mb-0">Gestion des utilisateurs</h4>
        <a href="index.php?action=users/create" class="btn btn-primary btn-sm">
            + Nouvel utilisateur
        </a>
    </div>

    <?php if ($message): ?>
        <div class="alert alert-success alert-dismissible fade show">
            <?= htmlspecialchars($message) ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    <?php endif; ?>

    <?php if ($error): ?>
        <div class="alert alert-danger alert-dismissible fade show">
            <?= htmlspecialchars($error) ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    <?php endif; ?>

    <div class="card">
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-sm table-hover align-middle mb-0">
                    <thead class="table-light">
                        <tr>
                            <th>Utilisateur</th>
                            <th>Nom complet</th>
                            <th>Téléphone</th>
                            <th>Succursale</th>
                            <th>Rôle</th>
                            <th>Statut</th>
                            <th>Activité</th>
                            <th>En ligne</th>
                            <th class="text-end">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (!$users): ?>
                            <tr><td colspan="9" class="text-center text-muted py-3">Aucun utilisateur.</td></tr>
                        <?php endif; ?>

                        <?php foreach ($users as $u):
                            // Calcul "En ligne"
                            $hasToken    = !empty($u['session_token']);
                            $lastLogin   = $u['last_login'] ? strtotime($u['last_login']) : 0;
                            $isOnline    = $hasToken && $lastLogin > (time() - $ONLINE_THRESHOLD);
                            $isSelf      = ((int)$u['id_users'] === (int)$_SESSION['user_id']);

                            // Dernière activité
                            $lastActivity = $u['last_login']
                                ? date('d/m/Y H:i', strtotime($u['last_login']))
                                : 'Jamais';

                            // Badges rôle
                            $roleColors = ['admin' => 'danger', 'manager' => 'warning', 'user' => 'info'];
                            $roleColor  = $roleColors[$u['role']] ?? 'secondary';
                            $roleLabel  = ['admin' => 'Admin', 'manager' => 'Manager', 'user' => 'Utilisateur'];
                        ?>
                        <tr>
                            <td>
                                <strong><?= htmlspecialchars($u['username']) ?></strong>
                                <?php if ($isSelf): ?>
                                    <span class="badge bg-secondary ms-1" style="font-size:0.7rem;">Vous</span>
                                <?php endif; ?>
                            </td>
                            <td><?= htmlspecialchars($u['prenom'] . ' ' . $u['nom']) ?></td>
                            <td><?= htmlspecialchars($u['telephone']) ?></td>
                            <td><?= htmlspecialchars($u['succursale'] ?? '—') ?></td>
                            <td>
                                <span class="badge bg-<?= $roleColor ?>">
                                    <?= $roleLabel[$u['role']] ?? $u['role'] ?>
                                </span>
                            </td>
                            <td>
                                <?php if ($u['status'] === 'actif'): ?>
                                    <span class="badge bg-success">Actif</span>
                                <?php else: ?>
                                    <span class="badge bg-danger">Inactif</span>
                                <?php endif; ?>
                            </td>
                            <td style="font-size:0.82rem; color:#6c757d;">
                                <?= $lastActivity ?>
                            </td>
                            <td>
                                <?php if ($isOnline): ?>
                                    <span class="badge bg-success">
                                        🟢 En ligne
                                    </span>
                                <?php else: ?>
                                    <span class="badge bg-secondary">Hors ligne</span>
                                <?php endif; ?>
                            </td>
                            <td class="text-end">
                                <a href="index.php?action=users/edit&id=<?= (int)$u['id_users'] ?>"
                                   class="btn btn-warning btn-sm" title="Modifier">✏️</a>

                                <?php if ($isOnline && !$isSelf): ?>
                                    <a href="index.php?action=users/disconnect&id=<?= (int)$u['id_users'] ?>"
                                       class="btn btn-secondary btn-sm"
                                       title="Déconnecter"
                                       onclick="return confirm('Déconnecter <?= htmlspecialchars($u['username']) ?> ?')">
                                        🚪
                                    </a>
                                <?php endif; ?>

                                <?php if (!$isSelf): ?>
                                    <a href="index.php?action=users/delete&id=<?= (int)$u['id_users'] ?>"
                                       class="btn btn-danger btn-sm"
                                       title="Supprimer"
                                       onclick="return confirm('Supprimer <?= htmlspecialchars($u['username']) ?> définitivement ?')">
                                        🗑️
                                    </a>
                                <?php endif; ?>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <!-- Légende -->
    <div class="mt-2 text-muted" style="font-size:0.82rem;">
        🟢 En ligne = actif dans les 15 dernières minutes
    </div>
</div>
