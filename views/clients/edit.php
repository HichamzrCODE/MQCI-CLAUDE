<<<<<<< HEAD
<?php include '../views/layout.php'; ?>

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
        <div class="form-group mb-4">
            <label for="telephone" class="form-label">Téléphone :</label>
            <input type="text" class="form-control" id="telephone" name="telephone" value="<?php echo htmlspecialchars($client['telephone']); ?>">
        </div>

        <div class="form-group mb-4">
    <label for="type_client" class="form-label">Type de client :</label>
    <select class="form-control" id="type_client" name="type_client" required>
        <option value="cash" <?= $client['type_client'] === 'cash' ? 'selected' : '' ?>>Cash</option>
        <option value="facture" <?= $client['type_client'] === 'facture' ? 'selected' : '' ?>>Facture</option>
    </select>
</div>
        <button type="submit" class="btn btn-primary w-100">Enregistrer les modifications</button>
    </form>
=======
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
        <?php endif; ?>

        <button type="submit" class="btn btn-primary w-100">Enregistrer les modifications</button>
    </form>
>>>>>>> 4f8dbbb6b83eb9c6f755d57287033c7da885a3b1
</div>