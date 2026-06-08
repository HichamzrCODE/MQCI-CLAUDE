<?php
$pageTitle = "Suivi clients";
include '../views/layout.php';
$clients       = $clients       ?? [];
$totalFactures = $totalFactures ?? 0;
$totalVerse    = $totalVerse    ?? 0;
$totalSolde    = $totalSolde    ?? 0;

// Pagination
$perPage     = 20;
$page        = max(1, (int)($_GET['page'] ?? 1));
$search      = trim($_GET['q'] ?? '');
$typeFilter  = $_GET['type'] ?? '';

// Filtrage
$filtered = array_filter($clients, function($c) use ($search, $typeFilter) {
    if ($search && stripos($c['nom'], $search) === false &&
        stripos($c['ville'] ?? '', $search) === false) return false;
    if ($typeFilter && $c['type_client'] !== $typeFilter) return false;
    return true;
});
$filtered    = array_values($filtered);
$totalCount  = count($filtered);
$totalPages  = max(1, (int)ceil($totalCount / $perPage));
$page        = min($page, $totalPages);
$paginated   = array_slice($filtered, ($page - 1) * $perPage, $perPage);

$totalFacturesFil = array_sum(array_column($filtered, 'total_facture'));
$totalVerseFil    = array_sum(array_column($filtered, 'total_verse'));
$totalSoldeFil    = array_sum(array_column($filtered, 'solde'));

$role     = $_SESSION['role'] ?? 'user';
$canSeePR = ($role !== 'user');
?>

<style>
:root {
    --cr-border:#E4E8F0; --cr-primary:#2563EB; --cr-primary-light:#EFF6FF;
    --cr-success:#16A34A; --cr-danger:#DC2626; --cr-warning:#D97706;
    --cr-text:#1E293B; --cr-muted:#64748B;
    --cr-radius:10px; --cr-shadow:0 1px 4px rgba(0,0,0,.07);
}
.cr-page { max-width:1100px; margin:0 auto; padding:24px 16px 48px; }
.cr-topbar { display:flex; align-items:center; gap:10px; margin-bottom:20px; flex-wrap:wrap; }
.cr-title  { font-size:1.2rem; font-weight:700; color:var(--cr-text); margin:0; }

.cr-totaux { display:grid; grid-template-columns:repeat(3,1fr); gap:14px; margin-bottom:20px; }
@media(max-width:640px){ .cr-totaux{ grid-template-columns:1fr; } }
.cr-total-card { background:#fff; border:1px solid var(--cr-border); border-radius:var(--cr-radius); padding:14px 18px; box-shadow:var(--cr-shadow); }
.cr-total-label { font-size:.78rem; color:var(--cr-muted); text-transform:uppercase; letter-spacing:.05em; margin-bottom:6px; }
.cr-total-val   { font-size:1.3rem; font-weight:800; }

.cr-filters { display:flex; gap:10px; margin-bottom:16px; flex-wrap:wrap; align-items:center; }
.cr-search { flex:1; min-width:220px; max-width:360px; padding:7px 12px 7px 34px; font-size:.88rem; border:1.5px solid var(--cr-border); border-radius:8px; outline:none;
    background:#fff url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='15' height='15' fill='%2364748B' viewBox='0 0 16 16'%3E%3Cpath d='M11.742 10.344a6.5 6.5 0 1 0-1.397 1.398h-.001l3.85 3.85a1 1 0 0 0 1.415-1.415l-3.867-3.833zm-5.242 1.156a5 5 0 1 1 0-10 5 5 0 0 1 0 10z'/%3E%3C/svg%3E") no-repeat 10px center; }
.cr-search:focus { border-color:var(--cr-primary); box-shadow:0 0 0 3px rgba(37,99,235,.1); }
.cr-select { padding:7px 12px; border:1.5px solid var(--cr-border); border-radius:8px; font-size:.88rem; outline:none; background:#fff; }
.cr-select:focus { border-color:var(--cr-primary); }
.btn-filter { padding:7px 16px; border-radius:7px; background:var(--cr-primary); color:#fff; border:none; font-size:.85rem; font-weight:600; cursor:pointer; }
.btn-reset  { padding:7px 14px; border-radius:7px; background:#fff; color:var(--cr-muted); border:1.5px solid var(--cr-border); font-size:.85rem; text-decoration:none; }

.cr-card  { background:#fff; border:1px solid var(--cr-border); border-radius:var(--cr-radius); box-shadow:var(--cr-shadow); overflow:hidden; }
.cr-table { width:100%; border-collapse:collapse; font-size:.88rem; }
.cr-table thead th { background:#F8FAFD; color:var(--cr-muted); font-size:.75rem; font-weight:700; text-transform:uppercase; letter-spacing:.06em; padding:9px 14px; border-bottom:1px solid var(--cr-border); text-align:left; white-space:nowrap; }
.cr-table tbody tr { border-bottom:1px solid #F1F5F9; transition:background .1s; }
.cr-table tbody tr:last-child { border-bottom:none; }
.cr-table tbody tr:hover td { background:#FAFBFD; }
.cr-table td { padding:9px 14px; vertical-align:middle; }
.cr-table tfoot td { background:#F8FAFD; font-weight:700; padding:10px 14px; border-top:2px solid var(--cr-border); }

.solde-pos { color:var(--cr-danger);  font-weight:700; }
.solde-neg { color:var(--cr-success); font-weight:700; }
.solde-zer { color:var(--cr-muted);   font-weight:600; }

.badge-facture { background:#DCFCE7; color:#15803D; padding:2px 8px; border-radius:99px; font-size:.72rem; font-weight:700; }
.badge-cash    { background:#FEF9C3; color:#854D0E; padding:2px 8px; border-radius:99px; font-size:.72rem; font-weight:700; }

.btn-releve { display:inline-flex; align-items:center; gap:4px; padding:5px 12px; border-radius:6px; font-size:.82rem; font-weight:600; text-decoration:none; background:var(--cr-primary-light); color:var(--cr-primary); border:1px solid #BFDBFE; transition:all .12s; }
.btn-releve:hover { background:var(--cr-primary); color:#fff; }

.bar-wrap { height:5px; background:#F1F5F9; border-radius:99px; margin-top:4px; overflow:hidden; }
.bar-fill { height:100%; border-radius:99px; }

.cr-pag { display:flex; gap:6px; align-items:center; padding:12px 18px; flex-wrap:wrap; }
.cr-pag-btn { padding:5px 12px; border-radius:6px; font-size:.83rem; text-decoration:none; border:1.5px solid var(--cr-border); background:#fff; color:var(--cr-muted); transition:all .12s; }
.cr-pag-btn:hover { border-color:var(--cr-primary); color:var(--cr-primary); background:var(--cr-primary-light); }
.cr-pag-btn.active { background:var(--cr-primary); color:#fff; border-color:var(--cr-primary); pointer-events:none; }
.cr-pag-btn.disabled { opacity:.4; pointer-events:none; }
</style>

<div class="cr-page">

    <div class="cr-topbar">
        <h1 class="cr-title">Suivi clients</h1>
        <span style="font-size:.82rem;color:var(--cr-muted);background:#F1F5F9;padding:3px 10px;border-radius:99px;border:1px solid var(--cr-border);">
            <?= $totalCount ?> client<?= $totalCount > 1 ? 's' : '' ?>
        </span>
    </div>

    <!-- Totaux globaux (sur la sélection filtrée) -->
    <div class="cr-totaux">
        <div class="cr-total-card">
            <div class="cr-total-label">Total facturé</div>
            <div class="cr-total-val" style="color:var(--cr-danger);"><?= number_format($totalFacturesFil, 0, ',', ' ') ?></div>
        </div>
        <div class="cr-total-card">
            <div class="cr-total-label">Total versé</div>
            <div class="cr-total-val" style="color:var(--cr-success);"><?= number_format($totalVerseFil, 0, ',', ' ') ?></div>
        </div>
        <div class="cr-total-card">
            <div class="cr-total-label">Solde restant dû</div>
            <div class="cr-total-val" style="color:<?= $totalSoldeFil > 0 ? 'var(--cr-danger)' : 'var(--cr-success)' ?>;">
                <?= number_format($totalSoldeFil, 0, ',', ' ') ?>
            </div>
        </div>
    </div>

    <!-- Filtres -->
    <form method="get" class="cr-filters">
        <input type="hidden" name="action" value="client_releve/index">
        <input type="hidden" name="page" value="1">
        <input type="text" name="q" class="cr-search" placeholder="Rechercher un client..."
               value="<?= htmlspecialchars($search) ?>">
        <select name="type" class="cr-select">
            <option value="">Tous les types</option>
            <option value="facture" <?= $typeFilter === 'facture' ? 'selected' : '' ?>>Entreprise</option>
            <option value="cash"    <?= $typeFilter === 'cash'    ? 'selected' : '' ?>>Cash</option>
        </select>
        <button type="submit" class="btn-filter">Filtrer</button>
        <?php if ($search || $typeFilter): ?>
        <a href="index.php?action=client_releve/index" class="btn-reset">✕ Effacer</a>
        <?php endif; ?>
        <span style="font-size:.82rem;color:var(--cr-muted);margin-left:auto;">
            <?= $totalCount ?> résultat<?= $totalCount > 1 ? 's' : '' ?>
        </span>
    </form>

    <!-- Table -->
    <div class="cr-card">
        <div style="overflow-x:auto;">
            <table class="cr-table">
                <thead>
                    <tr>
                        <th>Client</th>
                        <th>Type</th>
                        <th>Ville</th>
                        <?php if ($canSeePR): ?>
                        <th style="text-align:right;">Facturé</th>
                        <th style="text-align:right;">Versé</th>
                        <th style="text-align:right;">Solde</th>
                        <?php endif; ?>
                        <th style="text-align:center;width:100px;">Relevé</th>
                    </tr>
                </thead>
                <tbody>
                <?php if (empty($paginated)): ?>
                <tr><td colspan="<?= $canSeePR ? 7 : 4 ?>" style="text-align:center;color:var(--cr-muted);padding:32px 0;">Aucun client trouvé.</td></tr>
                <?php endif; ?>
                <?php foreach ($paginated as $c):
                    $solde   = (float)$c['solde'];
                    $facture = (float)$c['total_facture'];
                    $verse   = (float)$c['total_verse'];
                    $soldeCls = $solde > 0 ? 'solde-pos' : ($solde < 0 ? 'solde-neg' : 'solde-zer');
                    $pct = $facture > 0 ? min(100, round($verse / $facture * 100)) : 0;
                    $barColor = $pct >= 100 ? 'var(--cr-success)' : ($pct >= 50 ? 'var(--cr-warning)' : 'var(--cr-danger)');
                ?>
                <tr>
                    <td>
                        <strong><?= htmlspecialchars($c['nom']) ?></strong>
                        <?php if (!empty($c['telephone'])): ?>
                        <br><span style="font-size:.76rem;color:var(--cr-muted);"><?= htmlspecialchars($c['telephone']) ?></span>
                        <?php endif; ?>
                    </td>
                    <td>
                        <?php if ($c['type_client'] === 'facture'): ?>
                        <span class="badge-facture">Entreprise</span>
                        <?php else: ?>
                        <span class="badge-cash">Cash</span>
                        <?php endif; ?>
                    </td>
                    <td style="color:var(--cr-muted);font-size:.84rem;"><?= htmlspecialchars($c['ville'] ?? '—') ?></td>
                    <?php if ($canSeePR): ?>
                    <td style="text-align:right;font-weight:600;">
                        <?= $facture > 0 ? number_format($facture, 0, ',', ' ') : '<span style="color:var(--cr-muted);">—</span>' ?>
                    </td>
                    <td style="text-align:right;">
                        <?= $verse > 0 ? number_format($verse, 0, ',', ' ') : '<span style="color:var(--cr-muted);">—</span>' ?>
                        <?php if ($facture > 0): ?>
                        <div class="bar-wrap">
                            <div class="bar-fill" style="width:<?= $pct ?>%;background:<?= $barColor ?>;"></div>
                        </div>
                        <?php endif; ?>
                    </td>
                    <td style="text-align:right;">
                        <span class="<?= $soldeCls ?>">
                            <?= number_format(abs($solde), 0, ',', ' ') ?>
                            <?php if ($solde > 0): ?><small style="font-weight:400;"> dû</small>
                            <?php elseif ($solde < 0): ?><small style="font-weight:400;"> avance</small>
                            <?php else: ?><small style="font-weight:400;"> soldé</small>
                            <?php endif; ?>
                        </span>
                    </td>
                    <?php endif; ?>
                    <td style="text-align:center;">
                        <a href="index.php?action=client_releve/show&id=<?= (int)$c['id_clients'] ?>"
                           class="btn-releve">📄 Voir</a>
                    </td>
                </tr>
                <?php endforeach; ?>
                </tbody>
                <?php if ($canSeePR && !empty($paginated)): ?>
                <tfoot>
                    <tr>
                        <td colspan="3">Total (page)</td>
                        <td style="text-align:right;color:var(--cr-danger);"><?= number_format(array_sum(array_column($paginated,'total_facture')), 0, ',', ' ') ?></td>
                        <td style="text-align:right;color:var(--cr-success);"><?= number_format(array_sum(array_column($paginated,'total_verse')), 0, ',', ' ') ?></td>
                        <td style="text-align:right;color:<?= $totalSoldeFil > 0 ? 'var(--cr-danger)' : 'var(--cr-success)' ?>;"><?= number_format(array_sum(array_column($paginated,'solde')), 0, ',', ' ') ?></td>
                        <td></td>
                    </tr>
                </tfoot>
                <?php endif; ?>
            </table>
        </div>

        <!-- Pagination -->
        <?php if ($totalPages > 1): ?>
        <nav class="cr-pag">
            <?php
            $baseUrl = 'index.php?action=client_releve/index'
                . ($search     ? '&q='    . urlencode($search)     : '')
                . ($typeFilter ? '&type=' . urlencode($typeFilter) : '');
            ?>
            <?php if ($page > 1): ?>
            <a href="<?= $baseUrl ?>&page=1" class="cr-pag-btn">&laquo;</a>
            <a href="<?= $baseUrl ?>&page=<?= $page-1 ?>" class="cr-pag-btn">‹ Préc.</a>
            <?php else: ?>
            <span class="cr-pag-btn disabled">&laquo;</span>
            <span class="cr-pag-btn disabled">‹ Préc.</span>
            <?php endif; ?>

            <?php for ($p = max(1,$page-2); $p <= min($totalPages,$page+2); $p++): ?>
            <a href="<?= $baseUrl ?>&page=<?= $p ?>"
               class="cr-pag-btn <?= $p===$page?'active':'' ?>"><?= $p ?></a>
            <?php endfor; ?>

            <?php if ($page < $totalPages): ?>
            <a href="<?= $baseUrl ?>&page=<?= $page+1 ?>" class="cr-pag-btn">Suiv. ›</a>
            <a href="<?= $baseUrl ?>&page=<?= $totalPages ?>" class="cr-pag-btn">&raquo;</a>
            <?php else: ?>
            <span class="cr-pag-btn disabled">Suiv. ›</span>
            <span class="cr-pag-btn disabled">&raquo;</span>
            <?php endif; ?>

            <span style="font-size:.82rem;color:var(--cr-muted);margin-left:4px;">
                Page <?= $page ?> / <?= $totalPages ?> &nbsp;·&nbsp; <?= $totalCount ?> client<?= $totalCount>1?'s':'' ?>
            </span>
        </nav>
        <?php endif; ?>
    </div>
</div>
