<<<<<<< HEAD
<?php include '..\views\layout.php'; ?>

<div class="container">
    <h1 style="margin-top: 30px;">Ajouter un client</h1>
    <?php if (isset($error)): ?>
        <div class="alert alert-danger"><?php echo $error; ?></div>
    <?php endif; ?>
    <form method="post" action="index.php?action=clients/create">
        <div class="form-group">
            <label for="nom">Nom:</label>
            <input type="text" class="form-control" id="nom" name="nom">
        </div>
        <div class="form-group">
            <label for="ville">Ville:</label>
            <input type="text" class="form-control" id="ville" name="ville">
        </div>
        <div class="form-group">
            <label for="telephone">Téléphone:</label>
            <input type="text" class="form-control" id="telephone" name="telephone">
        </div>

        <div class="form-group">
            <label for="type_client">Type de client :</label>
            <select class="form-control" id="type_client" name="type_client" required>
            <option value="cash">Cash</option>
            <option value="facture">Entreprise (Facture)</option>
         </select>
       </div>
        <button type="submit" class="btn btn-primary">Ajouter</button>
    </form>
=======
<?php include '..\views\layout.php'; ?>
<?php require_once __DIR__ . '/../../includes/permissions.php'; ?>

<div class="container">
    <h1 style="margin-top: 30px;">Ajouter un client</h1>
    <?php if (isset($error)): ?>
        <div class="alert alert-danger"><?php echo $error; ?></div>
    <?php endif; ?>
    <form method="post" action="index.php?action=clients/create">
        <div class="form-group">
            <label for="nom">Nom:</label>
            <input type="text" class="form-control" id="nom" name="nom">
        </div>
        <div class="form-group">
            <label for="ville">Ville:</label>
            <input type="text" class="form-control" id="ville" name="ville">
        </div>
        <div class="form-group">
            <label for="telephone">Téléphone:</label>
            <input type="text" class="form-control" id="telephone" name="telephone">
        </div>

        <div class="form-group">
            <label for="type_client">Type de client :</label>
            <select class="form-control" id="type_client" name="type_client" required>
            <option value="cash">Cash</option>
            <option value="facture">Entreprise (Facture)</option>
         </select>
       </div>

        <?php if (hasPermission('clients', 'edit')): ?>
        <div class="form-group">
            <label for="payment_delay">Délai de paiement (jours) :</label>
            <input type="number" class="form-control" id="payment_delay" name="payment_delay" value="30" min="1" max="365">
        </div>
        <?php endif; ?>

        <button type="submit" class="btn btn-primary">Ajouter</button>
    </form>
>>>>>>> 4f8dbbb6b83eb9c6f755d57287033c7da885a3b1
</div>