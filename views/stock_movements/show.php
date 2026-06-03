<?php include '../views/layout.php'; ?>

<div class="container mt-4" style="max-width:700px;">
    <div class="d-flex justify-content-between align-items-center mb-3">
        <h1 style="font-size:1.3rem;">📄 Détail du mouvement #<?= (int)$mouvement['id']; ?></h1>
        <div class="d-flex gap-2">
            <a href="index.php?action=stock_movements/historique&article_id=<?= (int)$mouvement['article_id']; ?>" class="btn btn-outline-info btn-sm">📋 Historique article</a>
            <a href="index.php?action=stock_movements" class="btn btn-secondary btn-sm">← Retour</a>
        </div>
    </div>

    <?php
        $typeBadges = [
            'entree'     => 'badge-success',
            'sortie'     => 'badge-danger',
            'ajustement' => 'badge-info',
            'retour'     => 'badge-warning',
            'transfert'  => 'badge-secondary',
        ];
        $typeLabels = [
            'entree'     => 'Entrée',
            'sortie'     => 'Sortie',
            'ajustement' => 'Ajustement',
            'retour'     => 'Retour',
            'transfert'  => 'Transfert',
        ];
        $badge = $typeBadges[$mouvement['type_mouvement']] ?? 'badge-secondary';
        $label = $typeLabels[$mouvement['type_mouvement']] ?? $mouvement['type_mouvement'];
    ?>

    <div class="card">
        <div class="card-header d-flex justify-content-between align-items-center">
            <span class="font-weight-bold">Mouvement de stock</span>
            <span class="badge <?= $badge; ?> px-3"><?= $label; ?></span>
        </div>
        <div class="card-body">
            <dl class="row mb-0">
                <dt class="col-sm-4">Article</dt>
                <dd class="col-sm-8" style="text-transform:uppercase;font-weight:600;">
                    <?= htmlspecialchars($mouvement['nom_art']); ?>
                    <?php if (!empty($mouvement['sku'])): ?>
                        <small class="text-muted font-weight-normal">(<?= htmlspecialchars($mouvement['sku']); ?>)</small>
                    <?php endif; ?>
                </dd>

                <dt class="col-sm-4">Dépôt</dt>
                <dd class="col-sm-8"><?= htmlspecialchars($mouvement['depot_nom']); ?></dd>

                <dt class="col-sm-4">Quantité</dt>
                <dd class="col-sm-8"><strong><?= (int)$mouvement['quantite']; ?></strong></dd>

                <?php if ($mouvement['quantite_avant'] !== null): ?>
                    <dt class="col-sm-4">Stock avant</dt>
                    <dd class="col-sm-8"><?= (int)$mouvement['quantite_avant']; ?></dd>
                <?php endif; ?>

                <?php if ($mouvement['quantite_apres'] !== null): ?>
                    <dt class="col-sm-4">Stock après</dt>
                    <dd class="col-sm-8"><strong><?= (int)$mouvement['quantite_apres']; ?></strong></dd>
                <?php endif; ?>

                <?php if (!empty($mouvement['reference'])): ?>
                    <dt class="col-sm-4">Référence</dt>
                    <dd class="col-sm-8"><code><?= htmlspecialchars($mouvement['reference']); ?></code></dd>
                <?php endif; ?>

                <?php if (!empty($mouvement['description'])): ?>
                    <dt class="col-sm-4">Description</dt>
                    <dd class="col-sm-8"><?= nl2br(htmlspecialchars($mouvement['description'])); ?></dd>
                <?php endif; ?>

                <dt class="col-sm-4">Utilisateur</dt>
                <dd class="col-sm-8"><?= htmlspecialchars($mouvement['user_nom'] ?? '-'); ?></dd>

                <dt class="col-sm-4">Date</dt>
                <dd class="col-sm-8"><?= htmlspecialchars(date('d/m/Y H:i:s', strtotime($mouvement['created_at']))); ?></dd>
            </dl>
        </div>
    </div>
</div>