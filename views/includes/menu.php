<nav class="navbar navbar-expand-lg navbar-light bg-light">
    <a class="navbar-brand" href="index.php">Mon Application</a>
    <button class="navbar-toggler" type="button" data-toggle="collapse" data-target="#navbarNav" aria-controls="navbarNav" aria-expanded="false" aria-label="Toggle navigation">
        <span class="navbar-toggler-icon"></span>
    </button>
    <div class="collapse navbar-collapse" id="navbarNav">
        <ul class="navbar-nav">
            <?php if (isset($_SESSION['user_id'])): ?>
                <li class="nav-item">
                    <a class="nav-link" href="index.php?action=dashboard">Tableau de bord</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" href="index.php?action=clients">Clients</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" href="index.php?action=fournisseurs">Fournisseurs</a>
                </li>
                 <li class="nav-item">
                    <a class="nav-link" href="index.php?action=fournisseurs">Articles</a>
                </li>               
                <li class="nav-item">
                    <a class="nav-link" href="index.php?action=devis">Devis</a>
                </li>
                <!-- Ajoutez d'autres liens de menu ici -->
                <li class="nav-item">
                    <a class="nav-link" href="index.php?action=logout">Déconnexion</a>
                </li>
            <?php else: ?>
                <li class="nav-item">
                    <a class="nav-link" href="index.php?action=login">Connexion</a>
                </li>
            <?php endif; ?>
        </ul>
    </div>
</nav>