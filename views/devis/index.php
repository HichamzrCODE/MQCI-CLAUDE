<<<<<<< HEAD
<?php include '../views/layout.php'; ?>

<style>
    #devis-table tr,
    #devis-table td,
    #devis-table th {
        padding-top: 4px !important;
        padding-bottom: 4px !important;
        height: 32px !important;
        vertical-align: middle !important;
        font-size: 14px;
    }
    #devis-table td, #devis-table th {
        line-height: 1.0 !important;
    }
</style>

<div class="container mt-4">
    <h1>Liste des Devis</h1>

    <!-- Barre de recherche SERVER-SIDE -->
    <form method="get" action="index.php" class="form-inline mb-3">
        <input type="hidden" name="action" value="devis">
        <input type="text" name="search_term" id="devis-search" class="form-control mr-2" placeholder="Rechercher devis (numéro, client, total)..." value="<?= htmlspecialchars($data['search_term'] ?? '') ?>">
        <button type="submit" class="btn btn-primary">Rechercher</button>
        <a href="index.php?action=devis/create" class="btn btn-success ml-2">Créer un Devis</a>
    </form>

    <table class="table table-hover" id="devis-table">
        <thead>
            <tr>
                <th>Numéro</th>
                <th>Client</th>
                <th>Référence</th>
                <th>Date</th>
                <th>Total</th>
                <th>Actions</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($data['devis'] as $devisItem): ?>
                <tr>
                    <td><?= htmlspecialchars($devisItem['numero']) ?></td>
                    <td><?= htmlspecialchars($devisItem['nom_client']) ?></td>
                    <td><?= htmlspecialchars($devisItem['reference'] ?? '') ?></td>
                    <td><?= (new DateTime($devisItem['date']))->format("d/m/Y") ?></td>
                    <td><?= number_format($devisItem['total'], 0, '', ' ') ?></td>
                    <td>
                        <a href="index.php?action=devis/detail&id=<?= $devisItem['id'] ?>" class="btn btn-info btn-sm">Détail</a>
                        <a href="index.php?action=devis/edit&id=<?= $devisItem['id'] ?>" class="btn btn-primary btn-sm">Modifier</a>
                        <?php if (isset($_SESSION['role']) && $_SESSION['role'] === 'admin'): ?>
                            <a href="index.php?action=devis/delete&id=<?= $devisItem['id'] ?>" class="btn btn-danger btn-sm">X</a>
                        <?php endif; ?>
                    </td>
                </tr>
            <?php endforeach; ?>
        </tbody>
    </table>

    <!-- Pagination -->
    <?php
    $parPage = $data['parPage'] ?? 10;
    $total = $data['total'] ?? 0;
    $page = $data['page'] ?? 1;
    $nbPages = max(1, ceil($total / $parPage));
    $searchTerm = $data['search_term'] ?? '';

    // Limiter le nombre de liens de pagination affichés
    $maxLinks = 10; // maximum de numéros de page affichés
    ?>
    <?php if ($nbPages > 1): ?>
        <?php
            $qs = $searchTerm ? '&search_term=' . urlencode($searchTerm) : '';
            $maxLinksToShow = min($maxLinks, $nbPages);
            $half = (int)floor($maxLinksToShow / 2);

            $start = $page - $half;
            if ($start < 1) $start = 1;

            $end = $start + $maxLinksToShow - 1;
            if ($end > $nbPages) {
                $end = $nbPages;
                $start = max(1, $end - $maxLinksToShow + 1);
            }
        ?>
        <nav>
            <ul class="pagination justify-content-center mt-4">
                <?php if ($page > 1): ?>
                    <li class="page-item">
                        <a class="page-link" href="?action=devis<?= $qs ?>&page=<?= $page - 1 ?>">Précédent</a>
                    </li>
                <?php endif; ?>

                <?php if ($start > 1): ?>
                    <li class="page-item"><a class="page-link" href="?action=devis<?= $qs ?>&page=1">1</a></li>
                    <?php if ($start > 2): ?>
                        <li class="page-item disabled"><span class="page-link">…</span></li>
                    <?php endif; ?>
                <?php endif; ?>

                <?php for ($i = $start; $i <= $end; $i++): ?>
                    <li class="page-item<?= $i === $page ? ' active' : '' ?>">
                        <a class="page-link" href="?action=devis<?= $qs ?>&page=<?= $i ?>"><?= $i ?></a>
                    </li>
                <?php endfor; ?>

                <?php if ($end < $nbPages): ?>
                    <?php if ($end < $nbPages - 1): ?>
                        <li class="page-item disabled"><span class="page-link">…</span></li>
                    <?php endif; ?>
                    <li class="page-item"><a class="page-link" href="?action=devis<?= $qs ?>&page=<?= $nbPages ?>"><?= $nbPages ?></a></li>
                <?php endif; ?>

                <?php if ($page < $nbPages): ?>
                    <li class="page-item">
                        <a class="page-link" href="?action=devis<?= $qs ?>&page=<?= $page + 1 ?>">Suivant</a>
                    </li>
                <?php endif; ?>
            </ul>
        </nav>
    <?php endif; ?>
</div>

<script>
    // Confirmation avant suppression d'un devis
    $(document).on('click', 'a.btn-danger', function(e) {
        if ($(this).attr('href') && $(this).attr('href').indexOf('action=devis/delete') !== -1) {
            if (!confirm('Êtes-vous sûr de vouloir supprimer ce devis ? Cette action est irréversible.')) {
                e.preventDefault();
            }
        }
    });
=======
<?php include '../views/layout.php'; ?>

<style>
    #devis-table tr,
    #devis-table td,
    #devis-table th {
        padding-top: 4px !important;
        padding-bottom: 4px !important;
        height: 32px !important;
        vertical-align: middle !important;
        font-size: 14px;
    }
    #devis-table td, #devis-table th {
        line-height: 1.0 !important;
    }
</style>

<div class="container mt-4">
    <h1>Liste des Devis</h1>

    <!-- Barre de recherche SERVER-SIDE -->
    <form method="get" action="index.php" class="form-inline mb-3">
        <input type="hidden" name="action" value="devis">
        <input type="text" name="search_term" id="devis-search" class="form-control mr-2" placeholder="Rechercher devis (numéro, client, total)..." value="<?= htmlspecialchars($data['search_term'] ?? '') ?>">
        <button type="submit" class="btn btn-primary">Rechercher</button>
        <a href="index.php?action=devis/create" class="btn btn-success ml-2">Créer un Devis</a>
    </form>

    <table class="table table-hover" id="devis-table">
        <thead>
            <tr>
                <th>Numéro</th>
                <th>Client</th>
                <th>Référence</th>
                <th>Date</th>
                <th>Total</th>
                <th>Actions</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($data['devis'] as $devisItem): ?>
                <tr>
                    <td><?= htmlspecialchars($devisItem['numero']) ?></td>
                    <td><?= htmlspecialchars($devisItem['nom_client']) ?></td>
                    <td><?= htmlspecialchars($devisItem['reference'] ?? '') ?></td>
                    <td><?= (new DateTime($devisItem['date']))->format("d/m/Y") ?></td>
                    <td><?= number_format($devisItem['total'], 0, '', ' ') ?></td>
                    <td>
                        <a href="index.php?action=devis/detail&id=<?= $devisItem['id'] ?>" class="btn btn-info btn-sm">Détail</a>
                        <a href="index.php?action=devis/edit&id=<?= $devisItem['id'] ?>" class="btn btn-primary btn-sm">Modifier</a>
                        <?php if (isset($_SESSION['role']) && $_SESSION['role'] === 'admin'): ?>
                            <a href="index.php?action=devis/delete&id=<?= $devisItem['id'] ?>" class="btn btn-danger btn-sm">X</a>
                        <?php endif; ?>
                    </td>
                </tr>
            <?php endforeach; ?>
        </tbody>
    </table>

    <!-- Pagination -->
    <?php
    $parPage = $data['parPage'] ?? 10;
    $total = $data['total'] ?? 0;
    $page = $data['page'] ?? 1;
    $nbPages = max(1, ceil($total / $parPage));
    $searchTerm = $data['search_term'] ?? '';

    // Limiter le nombre de liens de pagination affichés
    $maxLinks = 10; // maximum de numéros de page affichés
    ?>
    <?php if ($nbPages > 1): ?>
        <?php
            $qs = $searchTerm ? '&search_term=' . urlencode($searchTerm) : '';
            $maxLinksToShow = min($maxLinks, $nbPages);
            $half = (int)floor($maxLinksToShow / 2);

            $start = $page - $half;
            if ($start < 1) $start = 1;

            $end = $start + $maxLinksToShow - 1;
            if ($end > $nbPages) {
                $end = $nbPages;
                $start = max(1, $end - $maxLinksToShow + 1);
            }
        ?>
        <nav>
            <ul class="pagination justify-content-center mt-4">
                <?php if ($page > 1): ?>
                    <li class="page-item">
                        <a class="page-link" href="?action=devis<?= $qs ?>&page=<?= $page - 1 ?>">Précédent</a>
                    </li>
                <?php endif; ?>

                <?php if ($start > 1): ?>
                    <li class="page-item"><a class="page-link" href="?action=devis<?= $qs ?>&page=1">1</a></li>
                    <?php if ($start > 2): ?>
                        <li class="page-item disabled"><span class="page-link">…</span></li>
                    <?php endif; ?>
                <?php endif; ?>

                <?php for ($i = $start; $i <= $end; $i++): ?>
                    <li class="page-item<?= $i === $page ? ' active' : '' ?>">
                        <a class="page-link" href="?action=devis<?= $qs ?>&page=<?= $i ?>"><?= $i ?></a>
                    </li>
                <?php endfor; ?>

                <?php if ($end < $nbPages): ?>
                    <?php if ($end < $nbPages - 1): ?>
                        <li class="page-item disabled"><span class="page-link">…</span></li>
                    <?php endif; ?>
                    <li class="page-item"><a class="page-link" href="?action=devis<?= $qs ?>&page=<?= $nbPages ?>"><?= $nbPages ?></a></li>
                <?php endif; ?>

                <?php if ($page < $nbPages): ?>
                    <li class="page-item">
                        <a class="page-link" href="?action=devis<?= $qs ?>&page=<?= $page + 1 ?>">Suivant</a>
                    </li>
                <?php endif; ?>
            </ul>
        </nav>
    <?php endif; ?>
</div>

<script>
    // Confirmation avant suppression d'un devis
    $(document).on('click', 'a.btn-danger', function(e) {
        if ($(this).attr('href') && $(this).attr('href').indexOf('action=devis/delete') !== -1) {
            if (!confirm('Êtes-vous sûr de vouloir supprimer ce devis ? Cette action est irréversible.')) {
                e.preventDefault();
            }
        }
    });
>>>>>>> 4f8dbbb6b83eb9c6f755d57287033c7da885a3b1
</script>