<?php $pageTitle="Liste des Devis"; include '../views/layout.php'; ?>

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
    #devis-table td, #devis-table th { line-height: 1.0 !important; }

    /* Mini bonus: colonne actions compacte */
    #devis-table th.actions-col,
    #devis-table td.actions-col{
        width: 1%;
        white-space: nowrap;
    }
</style>

<div class="container mt-4">
    <h1>Liste des Devis</h1>

    <!-- Barre de recherche SERVER-SIDE -->
    <form method="get" action="index.php" class="form-inline mb-3">
        <input type="hidden" name="action" value="devis">
        <input type="text" name="search_term" id="devis-search" class="form-control mr-2"
               placeholder="Rechercher devis (numéro, client, total)..."
               value="<?= htmlspecialchars($data['search_term'] ?? '') ?>">
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
                <th>Statut</th>
                <th class="actions-col text-end">Actions</th>
            </tr>
        </thead>

        <tbody>
            <?php foreach (($data['devis'] ?? []) as $devisItem): ?>
                <?php
                    $statut = $devisItem['statut'] ?? 'draft';
                    $isValidated = ($statut === 'validated');
                    $badge = $isValidated ? 'success' : 'secondary';
                    $label = $isValidated ? 'Validé' : 'Brouillon';
                    $isAdmin = (($_SESSION['role'] ?? '') === 'admin');
                ?>
                <tr>
                    <td><?= htmlspecialchars($devisItem['numero']) ?></td>
                    <td><?= htmlspecialchars($devisItem['nom_client']) ?></td>
                    <td><?= htmlspecialchars($devisItem['reference'] ?? '') ?></td>
                    <td><?= (new DateTime($devisItem['date']))->format("d/m/Y") ?></td>
                    <td><?= number_format((float)$devisItem['total'], 0, '', ' ') ?></td>

                    <td>
                        <span class="badge bg-<?= $badge ?>"><?= $label ?></span>
                    </td>

                    <td class="actions-col text-end">
                        <div class="btn-group" role="group" aria-label="Actions devis">

                            <!-- Détail -->
                            <a href="index.php?action=devis/detail&id=<?= (int)$devisItem['id'] ?>"
                               class="btn btn-sm btn-outline-info"
                               data-bs-toggle="tooltip" data-bs-title="Détail">
                                <i class="fas fa-eye"></i>
                            </a>

                            <!-- Modifier (draft ou admin) -->
                            <?php if (!$isValidated || $isAdmin): ?>
                                <a href="index.php?action=devis/edit&id=<?= (int)$devisItem['id'] ?>"
                                   class="btn btn-sm btn-outline-primary"
                                   data-bs-toggle="tooltip" data-bs-title="Modifier">
                                    <i class="fas fa-edit"></i>
                                </a>
                            <?php endif; ?>

                            <!-- Dupliquer -->
                            <a href="index.php?action=devis/duplicate&id=<?= (int)$devisItem['id'] ?>"
                               class="btn btn-sm btn-outline-secondary"
                               data-bs-toggle="tooltip" data-bs-title="Dupliquer">
                                <i class="fas fa-clone"></i>
                            </a>

                            <!-- Valider (BL auto) -->
                            <?php if (!$isValidated): ?>
                                <form method="post"
                                      action="index.php?action=devis/validate&id=<?= (int)$devisItem['id'] ?>"
                                      class="d-inline"
                                      onsubmit="return confirm('Valider ce devis ? Un BL sera créé automatiquement.');">
                                    <button type="submit"
                                            class="btn btn-sm btn-outline-success"
                                            data-bs-toggle="tooltip"
                                            data-bs-title="Valider (création BL auto)">
                                        <i class="fas fa-check"></i>
                                    </button>
                                </form>
                            <?php endif; ?>

                            <!-- Supprimer (admin) -->
                            <?php if ($isAdmin): ?>
                                <a href="index.php?action=devis/delete&id=<?= (int)$devisItem['id'] ?>"
                                   class="btn btn-sm btn-outline-danger js-delete-devis"
                                   data-bs-toggle="tooltip" data-bs-title="Supprimer">
                                    <i class="fas fa-trash"></i>
                                </a>
                            <?php endif; ?>

                        </div>
                    </td>
                </tr>
            <?php endforeach; ?>
        </tbody>
    </table>

    <!-- Pagination -->
    <?php
        $parPage    = $data['parPage'] ?? 10;
        $total      = $data['total'] ?? 0;
        $page       = $data['page'] ?? 1;
        $nbPages    = max(1, (int)ceil($total / $parPage));
        $searchTerm = $data['search_term'] ?? '';

        $maxLinks = 10;
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
/* Confirmation suppression devis (ciblée) */
$(document).on('click', 'a.js-delete-devis', function(e) {
  if (!confirm('Êtes-vous sûr de vouloir supprimer ce devis ? Cette action est irréversible.')) {
    e.preventDefault();
  }
});

/* Tooltips Bootstrap 5 */
document.addEventListener('DOMContentLoaded', function () {
  if (typeof bootstrap === 'undefined') return;
  document.querySelectorAll('[data-bs-toggle="tooltip"]').forEach(function (el) {
    new bootstrap.Tooltip(el, { trigger: 'hover focus' });
  });
});
</script>