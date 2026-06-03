<?php $pageTitle = "Paramètres - Devis"; include '../views/layout.php'; ?>

<div class="page-header">
    <h1><i class="fas fa-file-invoice"></i> Paramètres Devis</h1>
    <p class="page-description">Personnalisez l’en-tête et le pied de page des devis</p>
</div>

<?php if (!empty($error)): ?>
    <div class="alert alert-danger alert-dismissible fade show" role="alert">
        <i class="fas fa-exclamation-circle"></i> <?= htmlspecialchars($error) ?>
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
<?php endif; ?>

<?php if (!empty($success)): ?>
    <div class="alert alert-success alert-dismissible fade show" role="alert">
        <i class="fas fa-check-circle"></i> <?= htmlspecialchars($success) ?>
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
<?php endif; ?>

<div class="row">
    <div class="col-lg-8">
        <div class="card">
            <div class="card-header">
                <i class="fas fa-pen"></i> Contenu du Devis
            </div>
            <div class="card-body">
                <form method="POST">
                    <?= $csrf_field ?>

                    <div class="mb-3">
                        <label class="form-label">En-tête — Ligne 1</label>
                        <input type="text"
                               class="form-control"
                               name="devis_header_line1"
                               value="<?= htmlspecialchars($settings['devis_header_line1'] ?? '') ?>"
                               placeholder="Ex: Succursale 06 - ...">
                    </div>

                    <div class="mb-3">
                        <label class="form-label">En-tête — Ligne 2</label>
                        <input type="text"
                               class="form-control"
                               name="devis_header_line2"
                               value="<?= htmlspecialchars($settings['devis_header_line2'] ?? '') ?>"
                               placeholder="Ex: Téléphones ...">
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Pied de page (multi-lignes)</label>
                        <textarea class="form-control"
                                  name="devis_footer_text"
                                  rows="6"
                                  placeholder="RCCM..., banques..., etc."><?= htmlspecialchars($settings['devis_footer_text'] ?? '') ?></textarea>
                        <small class="text-muted">Les retours à la ligne seront conservés à l’impression.</small>
                    </div>

                    <div class="d-flex gap-2">
                        <button type="submit" class="btn btn-primary">
                            <i class="fas fa-save"></i> Sauvegarder
                        </button>
                        <a href="index.php?action=settings" class="btn btn-secondary">
                            Retour
                        </a>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Aperçu rapide -->
    <div class="col-lg-4">
        <div class="card">
            <div class="card-header">
                <i class="fas fa-eye"></i> Aperçu (texte)
            </div>
            <div class="card-body">
                <div class="mb-3">
                    <div class="fw-bold">En-tête</div>
                    <div class="text-muted" style="font-size:12px;">
                        <?= htmlspecialchars($settings['devis_header_line1'] ?? '') ?><br>
                        <?= htmlspecialchars($settings['devis_header_line2'] ?? '') ?>
                    </div>
                </div>
                <div>
                    <div class="fw-bold">Pied de page</div>
                    <div class="text-muted" style="font-size:12px; white-space:pre-wrap;">
                        <?= htmlspecialchars($settings['devis_footer_text'] ?? '') ?>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>