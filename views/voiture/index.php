<<<<<<< HEAD
<?php include '..\views\layout.php'; ?>
<?php require_once __DIR__ . '/../../includes/permissions.php'; ?>

<div class="container" >
    <h1 style="margin-TOP: 30px;">LISTE DES VOITURES</h1>
    <?php if (hasPermission('voiture', 'create')): ?>
        <a href="index.php?action=voiture/create" class="btn btn-success mb-2">Ajouter une voiture</a>
    <?php endif; ?>
    <p style="text-align: right;">Nombre total de voitures : <?php echo htmlspecialchars($totalVoitures ?? 0); ?></p>

    <table class="table">
        <thead>
            <tr>
                <th>Matricule</th>
                <th>Chauffeur</th>
                <th>Téléphone</th>
                <?php if(hasPermission('voiture', 'edit') || hasPermission('voiture', 'delete')): ?>
                    <th>Actions</th>
                <?php endif; ?>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($voitures as $voiture): ?>
                <tr>
                    <td><?php echo htmlspecialchars($voiture['matricule']); ?></td>
                    <td><?php echo htmlspecialchars($voiture['chauffeur']); ?></td>
                    <td><?php echo htmlspecialchars($voiture['telephone_chauffeur']); ?></td>
                    <?php if(hasPermission('voiture', 'edit') || hasPermission('voiture', 'delete')): ?>
                    <td>
                        <?php if(hasPermission('voiture', 'edit')): ?>
                            <a href="index.php?action=voiture/edit&id=<?php echo $voiture['id']; ?>" class="btn btn-primary btn-sm">Modifier</a>
                        <?php endif; ?>
                        <?php if(hasPermission('voiture', 'delete')): ?>
                            <a href="index.php?action=voiture/delete&id=<?php echo $voiture['id']; ?>" class="btn btn-danger btn-sm" onclick="return confirm('Supprimer cette voiture ?');">X</a>
                        <?php endif; ?>
                    </td>
                    <?php endif; ?>
                </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
=======
<?php include '..\views\layout.php'; ?>
<?php require_once __DIR__ . '/../../includes/permissions.php'; ?>

<div class="container" >
    <h1 style="margin-TOP: 30px;">LISTE DES VOITURES</h1>
    <?php if (hasPermission('voiture', 'create')): ?>
        <a href="index.php?action=voiture/create" class="btn btn-success mb-2">Ajouter une voiture</a>
    <?php endif; ?>
    <p style="text-align: right;">Nombre total de voitures : <?php echo htmlspecialchars($totalVoitures ?? 0); ?></p>

    <table class="table">
        <thead>
            <tr>
                <th>Matricule</th>
                <th>Chauffeur</th>
                <th>Téléphone</th>
                <?php if(hasPermission('voiture', 'edit') || hasPermission('voiture', 'delete')): ?>
                    <th>Actions</th>
                <?php endif; ?>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($voitures as $voiture): ?>
                <tr>
                    <td><?php echo htmlspecialchars($voiture['matricule']); ?></td>
                    <td><?php echo htmlspecialchars($voiture['chauffeur']); ?></td>
                    <td><?php echo htmlspecialchars($voiture['telephone_chauffeur']); ?></td>
                    <?php if(hasPermission('voiture', 'edit') || hasPermission('voiture', 'delete')): ?>
                    <td>
                        <?php if(hasPermission('voiture', 'edit')): ?>
                            <a href="index.php?action=voiture/edit&id=<?php echo $voiture['id']; ?>" class="btn btn-primary btn-sm">Modifier</a>
                        <?php endif; ?>
                        <?php if(hasPermission('voiture', 'delete')): ?>
                            <a href="index.php?action=voiture/delete&id=<?php echo $voiture['id']; ?>" class="btn btn-danger btn-sm" onclick="return confirm('Supprimer cette voiture ?');">X</a>
                        <?php endif; ?>
                    </td>
                    <?php endif; ?>
                </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
>>>>>>> 4f8dbbb6b83eb9c6f755d57287033c7da885a3b1
</div>