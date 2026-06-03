
<?php $pageTitle = "Paramètres de l'Application"; include '../views/layout.php';?>

<div class="page-header">
    <h1><i class="fas fa-cog"></i> Paramètres de l'Application</h1>
    <p class="page-description">Configurez les paramètres généraux du système</p>
</div>

<?php if ($error): ?>
    <div class="alert alert-danger alert-dismissible fade show" role="alert">
        <i class="fas fa-exclamation-circle"></i> <?= htmlspecialchars($error) ?>
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
<?php endif; ?>

<?php if ($success): ?>
    <div class="alert alert-success alert-dismissible fade show" role="alert">
        <i class="fas fa-check-circle"></i> <?= htmlspecialchars($success) ?>
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
<?php endif; ?>

<div class="row">
    <div class="col-lg-8">
        <div class="card">
            <div class="card-header d-flex justify-content-between align-items-center">
  <span><i class="fas fa-sliders-h"></i> Configuration Générale</span>
  <div class="d-flex gap-2">
    <a href="index.php?action=settings/devis" class="btn btn-outline-primary btn-sm">
      <i class="fas fa-file-invoice"></i> Paramètres Devis
    </a>
  </div>
</div>
            <div class="card-body">
                <form method="POST" enctype="multipart/form-data">
                    <?= $csrf_field ?>

                    <div class="mb-4">
                        <label for="app_name" class="form-label">
                            <i class="fas fa-heading"></i> Nom de l'Application
                            <span class="text-danger">*</span>
                        </label>
                        <input 
                            type="text" 
                            class="form-control form-control-lg" 
                            id="app_name" 
                            name="app_name" 
                            value="<?= htmlspecialchars($settings['app_name'] ?? 'MAQCI') ?>" 
                            required
                            placeholder="Ex: MAQCI, SODIS, etc."
                        >
                        <small class="text-muted">Ce nom apparaîtra dans la barre de navigation</small>
                    </div>

                    <div class="mb-4">
                        <label for="app_icon" class="form-label">
                            <i class="fas fa-icons"></i> Icône (Font Awesome)
                        </label>
                        <div class="input-group">
                            <span class="input-group-text" id="icon-preview">
                                <i class="fas <?= htmlspecialchars($settings['app_icon'] ?? 'fa-cube') ?>"></i>
                            </span>
                            <input 
                                type="text" 
                                class="form-control" 
                                id="app_icon" 
                                name="app_icon" 
                                value="<?= htmlspecialchars($settings['app_icon'] ?? 'fa-cube') ?>"
                                placeholder="fa-cube"
                            >
                        </div>
                        <small class="text-muted d-block mt-2">
                            Utilisez les icônes Font Awesome : 
                            <a href="https://fontawesome.com/icons" target="_blank" class="text-decoration-none">
                                fontawesome.com/icons
                            </a>
                        </small>
                    </div>

                    <hr>

                    <div class="mb-4">
                        <label for="app_logo" class="form-label">
                            <i class="fas fa-image"></i> Logo de l'Application
                        </label>
                        <div class="mb-3">
                            <input 
                                type="file" 
                                class="form-control" 
                                id="app_logo" 
                                name="app_logo"
                                accept="image/jpeg,image/png,image/webp,image/svg+xml"
                            >
                            <small class="text-muted d-block mt-2">
                                Formats acceptés : JPG, PNG, WEBP, SVG (Max 2MB)
                                <br>
                                Taille recommandée : 256x256px (sera redimensionné automatiquement)
                            </small>
                        </div>
                    </div>

                    <div class="d-flex gap-2">
                        <button type="submit" class="btn btn-primary btn-lg">
                            <i class="fas fa-save"></i> Sauvegarder
                        </button>
                        <a href="index.php?action=home" class="btn btn-secondary btn-lg">
                            <i class="fas fa-times"></i> Annuler
                        </a>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <div class="col-lg-4">
        <div class="card">
            <div class="card-header">
                <i class="fas fa-eye"></i> Aperçu
            </div>
            <div class="card-body text-center">
                <?php if ($logoUrl): ?>
                    <img src="<?= htmlspecialchars($logoUrl) ?>" alt="Logo" style="width: 200px; height: 200px; object-fit: contain; margin: 20px 0; border-radius: 12px;">
                <?php else: ?>
                    <div style="font-size: 64px; margin: 20px 0; color: #2563eb;">
                        <i class="fas <?= htmlspecialchars($settings['app_icon'] ?? 'fa-cube') ?>"></i>
                    </div>
                <?php endif; ?>
                <h4 style="margin: 20px 0;">
                    <?= htmlspecialchars($settings['app_name'] ?? 'MAQCI') ?>
                </h4>
                <p class="text-muted text-sm">Cet aperçu montre comment votre application s'affichera</p>
            </div>
        </div>

        <div class="card mt-3">
            <div class="card-header">
                <i class="fas fa-info-circle"></i> À propos
            </div>
            <div class="card-body text-center">
                <p class="text-muted">
                    <small>
                        Développé par MQCI - <strong>HZR</strong><br>
                        <i class="fas fa-heart text-danger"></i><br>
                        © 2026 - Tous droits réservés
                    </small>
                </p>
            </div>
        </div>
    </div>
</div>

<script>
    // Aperçu en temps réel de l'icône
    document.getElementById('app_icon').addEventListener('input', function() {
        const icon = this.value || 'fa-cube';
        const preview = document.getElementById('icon-preview');
        preview.innerHTML = '<i class="fas ' + icon + '"></i>';
    });

    // Aperçu du logo avant upload
    document.getElementById('app_logo').addEventListener('change', function(e) {
        const file = e.target.files[0];
        if (file) {
            const reader = new FileReader();
            reader.onload = function(event) {
                // Le rendu se fera après la sauvegarde
                console.log('Fichier sélectionné : ' + file.name);
            };
            reader.readAsDataURL(file);
        }
    });
</script>