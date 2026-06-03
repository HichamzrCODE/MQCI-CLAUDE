<?php include __DIR__ . '/../layout.php'; ?>
<div class="container mt-4">
    <h3 style="margin-top: 30px;">Sauvegarde de la base de données</h3>
    <form method="post" action="index.php?action=sauvegarde/backup">
        <button type="submit" class="btn btn-success mb-3">Créer une nouvelle sauvegarde</button>
        <span class="text-muted ml-2">Fichiers stockés dans <code>/sauvegardes/</code></span>
    </form>
    <hr>
    <h5>Liste des sauvegardes existantes</h5>
    <table class="table table-striped">
        <thead>
            <tr>
                <th>Fichier</th>
                <th>Date</th>
                <th>Taille</th>
                <th>Action</th>
            </tr>
        </thead>
        <tbody>
        <?php if (empty($saves)) : ?>
            <tr><td colspan="4" class="text-center text-muted">Aucune sauvegarde trouvée.</td></tr>
        <?php else: ?>
            <?php foreach ($saves as $save): ?>
                <tr>
                    <td><?= htmlspecialchars($save['name']) ?></td>
                    <td><?= htmlspecialchars($save['date']) ?></td>
                    <td><?= round($save['size']/1024, 1) ?> Ko</td>
                    <td>
                        <a href="index.php?action=sauvegarde/download&file=<?= urlencode($save['name']) ?>" class="btn btn-primary btn-sm">Télécharger</a>
                    </td>
                </tr>
            <?php endforeach; ?>
        <?php endif ?>
        </tbody>
    </table>
</div>