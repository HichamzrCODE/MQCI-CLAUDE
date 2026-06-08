<?php include '../views/layout.php'; ?>

<style>
:root {
    --ff-border:#E4E8F0; --ff-primary:#2563EB; --ff-primary-light:#EFF6FF;
    --ff-success:#16A34A; --ff-danger:#DC2626; --ff-warning:#D97706;
    --ff-text:#1E293B; --ff-muted:#64748B; --ff-label:#475569;
    --ff-radius:10px; --ff-shadow:0 1px 4px rgba(0,0,0,.07);
}
.ff-page { max-width:1100px; margin:0 auto; padding:24px 16px 48px; }
.ff-topbar { display:flex; align-items:center; gap:10px; margin-bottom:20px; flex-wrap:wrap; }
.ff-title { font-size:1.2rem; font-weight:700; color:var(--ff-text); margin:0; }
.ff-count { font-size:.82rem; color:var(--ff-muted); background:#F1F5F9; padding:3px 10px; border-radius:99px; border:1px solid var(--ff-border); }
.ff-topbar-actions { margin-left:auto; }

.btn-ff { display:inline-flex; align-items:center; gap:5px; padding:7px 16px; border-radius:7px; font-size:.85rem; font-weight:600; text-decoration:none; border:none; cursor:pointer; transition:all .15s; }
.btn-ff-primary  { background:var(--ff-primary); color:#fff; }
.btn-ff-primary:hover { background:#1D4ED8; color:#fff; }
.btn-ff-secondary { background:#fff; color:var(--ff-muted); border:1.5px solid var(--ff-border); }
.btn-ff-secondary:hover { color:var(--ff-primary); border-color:var(--ff-primary); background:var(--ff-primary-light); }

/* Recherche */
.ff-search-bar { display:flex; gap:8px; margin-bottom:16px; align-items:center; flex-wrap:wrap; }
.ff-search {
    flex:1; min-width:220px; max-width:380px;
    padding:7px 12px 7px 34px; font-size:.88rem;
    border:1.5px solid var(--ff-border); border-radius:8px; outline:none;
    background:#fff url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='15' height='15' fill='%2364748B' viewBox='0 0 16 16'%3E%3Cpath d='M11.742 10.344a6.5 6.5 0 1 0-1.397 1.398h-.001l3.85 3.85a1 1 0 0 0 1.415-1.415l-3.867-3.833zm-5.242 1.156a5 5 0 1 1 0-10 5 5 0 0 1 0 10z'/%3E%3C/svg%3E") no-repeat 10px center;
    transition:border-color .15s, box-shadow .15s;
}
.ff-search:focus { border-color:var(--ff-primary); box-shadow:0 0 0 3px rgba(37,99,235,.1); }
.btn-search { padding:7px 16px; border-radius:7px; background:var(--ff-primary); color:#fff; border:none; font-size:.85rem; font-weight:600; cursor:pointer; transition:background .15s; }
.btn-search:hover { background:#1D4ED8; }

/* Card table */
.ff-card { background:#fff; border:1px solid var(--ff-border); border-radius:var(--ff-radius); box-shadow:var(--ff-shadow); overflow:hidden; }
.ff-table { width:100%; border-collapse:collapse; font-size:.87rem; }
.ff-table thead th {
    background:#F8FAFD; color:var(--ff-muted);
    font-size:.75rem; font-weight:700; text-transform:uppercase;
    letter-spacing:.06em; padding:9px 14px;
    border-bottom:1px solid var(--ff-border); text-align:left; white-space:nowrap;
}
.ff-table tbody tr { border-bottom:1px solid #F1F5F9; transition:background .1s; }
.ff-table tbody tr:last-child { border-bottom:none; }
.ff-table tbody tr:hover td { background:#FAFBFD; }
.ff-table td { padding:9px 14px; vertical-align:middle; color:var(--ff-text); }

/* Badges */
.badge-validated { background:#DCFCE7; color:#15803D; padding:2px 9px; border-radius:99px; font-size:.74rem; font-weight:700; white-space:nowrap; }
.badge-draft     { background:#FEF9C3; color:#854D0E; padding:2px 9px; border-radius:99px; font-size:.74rem; font-weight:700; white-space:nowrap; }

/* Boutons action */
.act-btn { display:inline-flex; align-items:center; justify-content:center; width:28px; height:28px; border-radius:6px; font-size:.82rem; text-decoration:none; border:1px solid var(--ff-border); background:#fff; transition:all .12s; cursor:pointer; }
.act-btn:hover { transform:translateY(-1px); }
.act-btn-view:hover    { background:var(--ff-primary-light); border-color:var(--ff-primary); }
.act-btn-edit { color:#D97706; }
.act-btn-edit:hover    { background:#FFF7ED; border-color:#D97706; }
.act-btn-valid { color:#16A34A; }
.act-btn-valid:hover   { background:#F0FDF4; border-color:#16A34A; }
.act-btn-del  { color:var(--ff-danger); }
.act-btn-del:hover     { background:#FEF2F2; border-color:var(--ff-danger); }

/* Pagination */
.ff-pagination { display:flex; gap:6px; align-items:center; margin-top:16px; flex-wrap:wrap; }
.ff-pag-btn { padding:5px 12px; border-radius:6px; font-size:.83rem; text-decoration:none; border:1.5px solid var(--ff-border); background:#fff; color:var(--ff-muted); transition:all .12s; }
.ff-pag-btn:hover { border-color:var(--ff-primary); color:var(--ff-primary); background:var(--ff-primary-light); }
.ff-pag-btn.active { background:var(--ff-primary); color:#fff; border-color:var(--ff-primary); pointer-events:none; }
.ff-pag-btn.disabled { opacity:.4; pointer-events:none; }

.ff-empty { padding:48px 0; text-align:center; color:var(--ff-muted); font-size:.95rem; }
.ff-empty-icon { font-size:2.2rem; margin-bottom:10px; }


</style>

<div class="ff-page">

    <div class="ff-topbar no-print">
        <h1 class="ff-title">Factures fournisseurs</h1>
        <span class="ff-count"><?= number_format($total ?? 0) ?> facture<?= ($total ?? 0) > 1 ? 's' : '' ?></span>
        <div class="ff-topbar-actions">
            <a href="index.php?action=factures_fournisseurs/create" class="btn-ff btn-ff-primary">+ Nouvelle facture</a>
        </div>
    </div>

    <!-- Recherche -->
    <form method="get" class="ff-search-bar">
        <input type="hidden" name="action" value="factures_fournisseurs">
        <input type="text" name="search_term" class="ff-search"
               placeholder="Rechercher par numéro, fournisseur, dépôt..."
               value="<?= htmlspecialchars($search_term ?? '') ?>">
        <button type="submit" class="btn-search">Rechercher</button>
        <?php if (!empty($search_term)): ?>
        <a href="index.php?action=factures_fournisseurs" class="btn-ff btn-ff-secondary">✕ Effacer</a>
        <?php endif; ?>
    </form>

    <!-- Table -->
    <div class="ff-card">
        <div style="overflow-x:auto;">
            <table class="ff-table">
                <thead>
                    <tr>
                        <th>Numéro</th>
                        <th>Date</th>
                        <th>Fournisseur</th>
                        <th>Dépôt</th>
                        <th>Statut</th>
                        <th style="text-align:right;">Total TTC</th>
                        <th style="text-align:center; width:120px;">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($factures)): ?>
                    <tr><td colspan="7">
                        <div class="ff-empty">
                            <div class="ff-empty-icon">🧾</div>
                            <?= !empty($search_term) ? 'Aucun résultat pour "'.htmlspecialchars($search_term).'"' : 'Aucune facture fournisseur.' ?>
                        </div>
                    </td></tr>
                    <?php else: ?>
                    <?php foreach ($factures as $ff):
                        $isValidated = ($ff['statut'] ?? 'draft') === 'validated';
                    ?>
                    <tr>
                        <td>
                            <a href="index.php?action=factures_fournisseurs/show&id=<?= (int)$ff['id'] ?>"
                               style="font-weight:700; color:var(--ff-primary); text-decoration:none;">
                                <?= htmlspecialchars($ff['numero']) ?>
                            </a>
                        </td>
                        <td style="color:var(--ff-muted); font-size:.85rem;">
                            <?= date('d/m/Y', strtotime($ff['date'])) ?>
                        </td>
                        <td><?= htmlspecialchars($ff['nom_fournisseurs']) ?></td>
                        <td style="font-size:.84rem; color:var(--ff-muted);">
                            <?= htmlspecialchars($ff['depot_nom']) ?>
                        </td>
                        <td>
                            <span class="<?= $isValidated ? 'badge-validated' : 'badge-draft' ?>">
                                <?= $isValidated ? '✅ Validée' : '⏳ Brouillon' ?>
                            </span>
                        </td>
                        <td style="text-align:right; font-weight:700;">
                            <?= number_format((float)$ff['total_ttc'], 0, ',', ' ') ?>
                        </td>
                        <td>
                            <div style="display:flex; gap:5px; justify-content:center;">
                                <a href="index.php?action=factures_fournisseurs/show&id=<?= (int)$ff['id'] ?>"
                                   class="act-btn act-btn-view" title="Voir">👁</a>
                                <a href="index.php?action=factures_fournisseurs/edit&id=<?= (int)$ff['id'] ?>"
                                   class="act-btn act-btn-edit" title="Modifier">✎</a>
                                <?php if (!$isValidated): ?>
                                <a href="index.php?action=factures_fournisseurs/validate&id=<?= (int)$ff['id'] ?>"
                                   class="act-btn act-btn-valid" title="Valider + CMP"
                                   onclick="return confirm('Valider cette facture ? Le stock sera incrémenté et le CMP recalculé.');">✔</a>
                                <?php endif; ?>
                                <a href="index.php?action=factures_fournisseurs/delete&id=<?= (int)$ff['id'] ?>"
                                   class="act-btn act-btn-del" title="Supprimer"
                                   onclick="return confirm('Supprimer cette facture fournisseur ?');">✕</a>
                            </div>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>

    <!-- Pagination -->
    <?php
    $totalPages  = max(1, (int)ceil(($total ?? 0) / ($parPage ?? 10)));
    $currentPage = max(1, (int)($page ?? 1));
    $search      = $search_term ?? '';
    ?>
    <?php if ($totalPages > 1): ?>
    <nav class="ff-pagination">
        <?php if ($currentPage > 1): ?>
        <a href="index.php?action=factures_fournisseurs&page=<?= $currentPage-1 ?>&search_term=<?= urlencode($search) ?>" class="ff-pag-btn">‹ Préc.</a>
        <?php else: ?>
        <span class="ff-pag-btn disabled">‹ Préc.</span>
        <?php endif; ?>

        <?php for ($p = max(1,$currentPage-2); $p <= min($totalPages,$currentPage+2); $p++): ?>
        <a href="index.php?action=factures_fournisseurs&page=<?= $p ?>&search_term=<?= urlencode($search) ?>"
           class="ff-pag-btn <?= $p === $currentPage ? 'active' : '' ?>"><?= $p ?></a>
        <?php endfor; ?>

        <?php if ($currentPage < $totalPages): ?>
        <a href="index.php?action=factures_fournisseurs&page=<?= $currentPage+1 ?>&search_term=<?= urlencode($search) ?>" class="ff-pag-btn">Suiv. ›</a>
        <?php else: ?>
        <span class="ff-pag-btn disabled">Suiv. ›</span>
        <?php endif; ?>

        <span style="font-size:.82rem; color:var(--ff-muted); margin-left:4px;">
            Page <?= $currentPage ?> / <?= $totalPages ?>
        </span>
    </nav>
    <?php endif; ?>
</div>

<?php include '../views/footer.php'; ?>
