<?php include '../views/layout.php'; ?>
<?php require_once __DIR__ . '/../../includes/permissions.php'; ?>
<div class="container">
    <h1 style="margin-top: 30px;">Relevés Fournisseurs</h1>
    <?php if (hasPermission('fs', 'create')): ?>
        <a class="btn btn-primary my-3" href="index.php?action=fs/create">Créer un relevé fournisseur</a>
    <?php endif; ?>
    <table class="table table-bordered table-striped">
        <thead>
            <tr>
                <th>Fournisseur</th>
                <th>Solde</th>
                <th>Actions</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($releves as $r): ?>
                <tr>
                    <td><?= htmlspecialchars($r['nom_fournisseurs']) ?></td>
                    <td><?= number_format($r['total_general'], 0, ',', ' ') ?></td>
                    <td>
                        <?php if (hasPermission('fs', 'view')): ?>
                            <a href="index.php?action=fs/show&id=<?= $r['id'] ?>" class="btn btn-info btn-sm">Voir</a>
                        <?php endif; ?>
                        <?php if (hasPermission('fs', 'edit')): ?>
                            <a href="index.php?action=fs/edit&id=<?= $r['id'] ?>" class="btn btn-warning btn-sm">Modifier</a>
                        <?php endif; ?>
                        <?php if (hasPermission('fs', 'delete')): ?>
                            <a href="index.php?action=fs/delete&id=<?= $r['id'] ?>" class="btn btn-danger btn-sm" onclick="return confirm('Supprimer ce relevé ?')">X</a>
                        <?php endif;?>
                    </td>
                </tr>
            <?php endforeach;?>
        </tbody>
    </table>
</div>