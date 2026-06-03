<?php include '../views/layout.php'; ?>
<?php require_once __DIR__ . '/../../includes/permissions.php'; ?>

<div class="container" style="max-width: 540px;">
    <div class="d-flex justify-content-between align-items-center mt-4 mb-3">
        <a href="index.php?action=clients" class="btn btn-outline-secondary">&larr; Retour</a>
        <h1 class="mb-0" style="font-size: 1.5rem;">Modifier un client</h1>
    </div>
    <?php if (!empty($error)): ?>
        <div class="alert alert-danger"><?php echo htmlspecialchars($error); ?></div>
    <?php endif; ?>
    <form method="post" action="index.php?action=clients/edit&id=<?php echo $client['id_clients']; ?>" class="border rounded p-4 bg-white shadow-sm">
        <div class="form-group mb-3">
            <label for="nom" class="form-label">Nom :</label>
            <input type="text" class="form-control" id="nom" name="nom" value="<?php echo htmlspecialchars($client['nom']); ?>" required>
        </div>
        <div class="form-group mb-3">
            <label for="ville" class="form-label">Ville :</label>
            <input type="text" class="form-control" id="ville" name="ville" value="<?php echo htmlspecialchars($client['ville']); ?>">
        </div>
        <div class="form-group mb-3">
            <label for="telephone" class="form-label">Téléphone :</label>
            <input type="text" class="form-control" id="telephone" name="telephone" value="<?php echo htmlspecialchars($client['telephone']); ?>">
        </div>

        <div class="form-group mb-3">
    <label for="type_client" class="form-label">Type de client :</label>
    <select class="form-control" id="type_client" name="type_client" required>
        <option value="cash" <?= $client['type_client'] === 'cash' ? 'selected' : '' ?>>Cash</option>
        <option value="facture" <?= $client['type_client'] === 'facture' ? 'selected' : '' ?>>Facture</option>
    </select>
</div>

        <?php if (hasPermission('clients', 'edit')): ?>
        <div class="form-group mb-4">
            <label for="payment_delay" class="form-label">Délai de paiement (jours) :</label>
            <input type="number" class="form-control" id="payment_delay" name="payment_delay"
                   value="<?php echo (int)($client['payment_delay'] ?? 30); ?>" min="1" max="365">
        </div>

        <div class="form-group mb-3">
    <label class="form-label">AIRSI (automatique sur factures)</label>

    <div class="form-check mb-2">
        <input class="form-check-input" type="checkbox" id="apply_airsi" name="apply_airsi"
               <?= !empty($client['apply_airsi']) ? 'checked' : '' ?>>
        <label class="form-check-label" for="apply_airsi">
            Appliquer AIRSI pour ce client
        </label>
    </div>

    <label for="airsi_rate" class="form-label">Taux AIRSI (%)</label>
    <input type="number" class="form-control" id="airsi_rate" name="airsi_rate"
           value="<?= htmlspecialchars((string)($client['airsi_rate'] ?? 5)) ?>"
           step="0.01" min="0" max="100">
    <small class="text-muted">Par défaut: 5%</small>
</div>
        <?php endif; ?>

        <button type="submit" class="btn btn-primary w-100">Enregistrer les modifications</button>
    </form>
</div>