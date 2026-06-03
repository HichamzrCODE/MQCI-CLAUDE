<?php include '../views/layout.php'; ?>

<div class="container mt-4" style="max-width:700px;">
    <div class="d-flex justify-content-between align-items-center mb-3">
        <h1 style="font-size:1.3rem;">🔧 Ajustement manuel de stock</h1>
        <a href="index.php?action=stock_movements" class="btn btn-secondary btn-sm">← Retour</a>
    </div>

    <?php if (!empty($error)): ?>
        <div class="alert alert-danger"><?= htmlspecialchars($error); ?></div>
    <?php endif; ?>

    <div class="alert alert-info">
        Cette page sert uniquement aux <strong>ajustements manuels de stock</strong> (inventaire, correction, régularisation).
        Les entrées et sorties normales sont générées automatiquement par les documents.
    </div>

    <div class="card">
        <div class="card-body">
            <form method="post" action="index.php?action=stock_movements/create">
                <?= $csrf_field ?? ''; ?>

                <?php
                    $preArticle = htmlspecialchars($_POST['article_id'] ?? $_GET['article_id'] ?? '');
                    $preDepot   = htmlspecialchars($_POST['depot_id'] ?? $_GET['depot_id'] ?? '');
                ?>

                <div class="form-group">
                    <label for="article_id">Article <span class="text-danger">*</span></label>
                    <select class="form-control" id="article_id" name="article_id" required>
                        <option value="">Sélectionner un article</option>
                        <?php foreach ($articles as $a): ?>
                            <option value="<?= $a['id_articles']; ?>"
                                <?= $preArticle == $a['id_articles'] ? 'selected' : ''; ?>>
                                <?= htmlspecialchars($a['nom_art']); ?>
                                <?php if (!empty($a['sku'])): ?>(<?= htmlspecialchars($a['sku']); ?>)<?php endif; ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <div class="form-group">
                    <label for="depot_id">Dépôt <span class="text-danger">*</span></label>
                    <select class="form-control" id="depot_id" name="depot_id" required>
                        <option value="">Sélectionner un dépôt</option>
                        <?php foreach ($depots as $d): ?>
                            <option value="<?= $d['id']; ?>"
                                <?= $preDepot == $d['id'] ? 'selected' : ''; ?>>
                                <?= htmlspecialchars($d['nom']); ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <div class="form-group">
                    <label for="quantite">Nouvelle quantité réelle dans ce dépôt <span class="text-danger">*</span></label>
                    <input type="number"
                           class="form-control"
                           id="quantite"
                           name="quantite"
                           min="0"
                           value="<?= htmlspecialchars($_POST['quantite'] ?? ''); ?>"
                           required>
                    <small class="form-text text-muted">
                        L’ajustement fixe directement la quantité du dépôt à cette valeur.
                    </small>
                </div>

                <div class="form-group">
                    <label for="reference">Référence</label>
                    <input type="text"
                           class="form-control"
                           id="reference"
                           name="reference"
                           maxlength="100"
                           value="<?= htmlspecialchars($_POST['reference'] ?? ''); ?>"
                           placeholder="Ex : INVENTAIRE-JUIN-2026">
                </div>

                <div class="form-group">
                    <label for="description">Motif / Notes</label>
                    <textarea class="form-control"
                              id="description"
                              name="description"
                              rows="3"
                              placeholder="Ex : Ajustement après inventaire physique"><?= htmlspecialchars($_POST['description'] ?? ''); ?></textarea>
                </div>

                <div class="d-flex gap-2">
                    <button type="submit" class="btn btn-success">Enregistrer</button>
                    <a href="index.php?action=stock_movements" class="btn btn-secondary">Annuler</a>
                </div>
            </form>
        </div>
    </div>
</div>