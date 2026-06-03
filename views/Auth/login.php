<?php include '..\views\layout.php'; ?>

<div class="container">
    <h1 style="margin-top:30px ;">Connexion</h1>
    <?php if (isset($error)): ?>
        <div class="alert alert-danger"><?php echo $error; ?></div>
    <?php endif; ?>
    
    <!-- ✅ Message de succès -->
    <?php if (isset($success)): ?>
        <div class="alert alert-success"><?php echo $success; ?></div>
    <?php endif; ?>
    
    <form method="post" action="index.php?action=login">
        <div class="form-group">
            <label for="username">Nom d'utilisateur:</label>
            <input type="text" class="form-control" id="username" name="username" required>
        </div>
        <div class="form-group">
            <label for="password">Mot de passe:</label>
            <input type="password" class="form-control" id="password" name="password" required>
        </div>
        
        <!-- ✅ AJOUTER CETTE LIGNE : Token CSRF -->
        <?php echo $csrf_field ?? ''; ?>
        
        <button type="submit" class="btn btn-primary">Se connecter</button>
    </form>
    
</div>