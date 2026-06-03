<?php include '..\views\layout.php'; ?>

<div class="container">
    <h1 style="margin-TOP: 30px;">Créer une nouvelle voiture</h1>
    <?php if (isset($error)): ?>
        <div class="alert alert-danger"><?php echo htmlspecialchars($error); ?></div>
    <?php endif; ?>

    <form method="post" action="index.php?action=voiture/create">
        <div class="form-group">
            <label for="matricule">Matricule:</label>
            <input type="text" class="form-control" id="matricule" name="matricule" required>
        </div>
        <div class="form-group">
            <label for="chauffeur">Chauffeur:</label>
            <input type="text" class="form-control" id="chauffeur" name="chauffeur" required>
        </div>
        <div class="form-group">
            <label for="telephone_chauffeur">Téléphone du chauffeur:</label>
            <input type="text" class="form-control" id="telephone_chauffeur" name="telephone_chauffeur" required>
        </div>
        <button type="submit" class="btn btn-primary">Créer</button>
        <a href="index.php?action=voiture" class="btn btn-secondary">Annuler</a>
    </form>
</div>