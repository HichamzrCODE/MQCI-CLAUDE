<<<<<<< HEAD
<?php include '..\views\layout.php'; ?>

<div class="container">
    <h1 style="margin-top: 30PX;">Ajouter un fournisseur</h1>
    <?php if (isset($error)): ?>
        <div class="alert alert-danger"><?php echo $error; ?></div>
    <?php endif; ?>
    <form method="post" action="index.php?action=fournisseurs/create">
        <div class="form-group">
            <label for="nom">Nom:</label>
            <input type="text" class="form-control" id="nom_fournisseurs" name="nom_fournisseurs">
        </div>
       
        <button type="submit" class="btn btn-primary">Ajouter</button>
    </form>
=======
<?php include '..\views\layout.php'; ?>

<div class="container">
    <h1 style="margin-top: 30PX;">Ajouter un fournisseur</h1>
    <?php if (isset($error)): ?>
        <div class="alert alert-danger"><?php echo $error; ?></div>
    <?php endif; ?>
    <form method="post" action="index.php?action=fournisseurs/create">
        <div class="form-group">
            <label for="nom">Nom:</label>
            <input type="text" class="form-control" id="nom_fournisseurs" name="nom_fournisseurs">
        </div>
        <div class="form-group">
            <label for="email">Email :</label>
            <input type="email" class="form-control" id="email" name="email">
        </div>
        <div class="form-group">
            <label for="telephone">Téléphone :</label>
            <input type="text" class="form-control" id="telephone" name="telephone">
        </div>
        <button type="submit" class="btn btn-primary">Ajouter</button>
    </form>
>>>>>>> 4f8dbbb6b83eb9c6f755d57287033c7da885a3b1
</div>