<?php include '..\views\layout.php'; ?>

<div class="container">
    <h1>Modifier un fournisseur</h1>
    <?php if (isset($error)): ?>
        <div class="alert alert-danger"><?php echo $error; ?></div>
    <?php endif; ?>
    <form method="post" action="index.php?action=fournisseurs/edit&id=<?php echo $fournisseur['id_fournisseurs']; ?>">
        <div class="form-group">
            <label for="nom">Nom:</label>
         <input type="text" class="form-control" id="nom_fournisseurs" name="nom_fournisseurs" value="<?php echo htmlspecialchars($fournisseur['nom_fournisseurs']); ?>">
        </div>
        <div class="form-group">
            <label for="email">Email :</label>
            <input type="email" class="form-control" id="email" name="email" value="<?php echo htmlspecialchars($fournisseur['email'] ?? ''); ?>">
        </div>
        <div class="form-group">
            <label for="telephone">Téléphone :</label>
            <input type="text" class="form-control" id="telephone" name="telephone" value="<?php echo htmlspecialchars($fournisseur['telephone'] ?? ''); ?>">
        </div>
        <button type="submit" class="btn btn-primary">Modifier</button>
    </form>
</div>
