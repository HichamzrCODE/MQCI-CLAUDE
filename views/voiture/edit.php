<<<<<<< HEAD
<?php include '..\views\layout.php'; ?>

<div class="container">
    <h1 style="margin-TOP: 30px;">Modifier une voiture</h1>
    <?php if (isset($error)): ?>
        <div class="alert alert-danger"><?php echo htmlspecialchars($error); ?></div>
    <?php endif; ?>

    <form method="post" action="index.php?action=voiture/edit&id=<?php echo $voiture['id']; ?>">
        <div class="form-group">
            <label for="matricule">Matricule:</label>
            <input type="text" class="form-control" id="matricule" name="matricule" value="<?php echo htmlspecialchars($voiture['matricule']); ?>" required>
        </div>
        <div class="form-group">
            <label for="chauffeur">Chauffeur:</label>
            <input type="text" class="form-control" id="chauffeur" name="chauffeur" value="<?php echo htmlspecialchars($voiture['chauffeur']); ?>" required>
        </div>
        <div class="form-group">
            <label for="telephone_chauffeur">Téléphone du chauffeur:</label>
            <input type="text" class="form-control" id="telephone_chauffeur" name="telephone_chauffeur" value="<?php echo htmlspecialchars($voiture['telephone_chauffeur']); ?>" required>
        </div>
        <button type="submit" class="btn btn-primary">Modifier</button>
        <a href="index.php?action=voiture" class="btn btn-secondary">Annuler</a>
    </form>
=======
<?php include '..\views\layout.php'; ?>

<div class="container">
    <h1 style="margin-TOP: 30px;">Modifier une voiture</h1>
    <?php if (isset($error)): ?>
        <div class="alert alert-danger"><?php echo htmlspecialchars($error); ?></div>
    <?php endif; ?>

    <form method="post" action="index.php?action=voiture/edit&id=<?php echo $voiture['id']; ?>">
        <div class="form-group">
            <label for="matricule">Matricule:</label>
            <input type="text" class="form-control" id="matricule" name="matricule" value="<?php echo htmlspecialchars($voiture['matricule']); ?>" required>
        </div>
        <div class="form-group">
            <label for="chauffeur">Chauffeur:</label>
            <input type="text" class="form-control" id="chauffeur" name="chauffeur" value="<?php echo htmlspecialchars($voiture['chauffeur']); ?>" required>
        </div>
        <div class="form-group">
            <label for="telephone_chauffeur">Téléphone du chauffeur:</label>
            <input type="text" class="form-control" id="telephone_chauffeur" name="telephone_chauffeur" value="<?php echo htmlspecialchars($voiture['telephone_chauffeur']); ?>" required>
        </div>
        <button type="submit" class="btn btn-primary">Modifier</button>
        <a href="index.php?action=voiture" class="btn btn-secondary">Annuler</a>
    </form>
>>>>>>> 4f8dbbb6b83eb9c6f755d57287033c7da885a3b1
</div>