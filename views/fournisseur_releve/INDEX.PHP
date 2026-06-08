<?php
$pageTitle = "Suivi fournisseurs";
include '../views/layout.php';
$fournisseurs  = $fournisseurs  ?? [];
$totalFactures = $totalFactures ?? 0;
$totalVerse    = $totalVerse    ?? 0;
$totalSolde    = $totalSolde    ?? 0;

$perPage    = 20;
$page       = max(1, (int)($_GET['page'] ?? 1));
$search     = trim($_GET['q'] ?? '');

$filtered = array_filter($fournisseurs, function($f) use ($search) {
    if ($search && stripos($f['nom_fournisseurs'], $search) === false &&
        stripos($f['ville'] ?? '', $search) === false) return false;
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
?>

<style>
:root {
    --fr-border:#E4E8F0; --fr-primary:#2563EB; --fr-primary-light:#EFF6FF;
    --fr-success:#16A34A; --fr-danger:#DC2626; --fr-warning:#D97706;
    --fr-text:#1E293B; --fr-muted:#64748B;
    --fr-radius:10px; --fr-shadow:0 1px 4px rgba(0,0,0,.07);
}
.fr-page { max-width:1100px; margin:0 auto; padding:24px 16px 48px; }
.fr-topbar { display:flex; align-items:center; gap:10px; margin-bottom:20px; flex-wrap:wrap; }
.fr-title  { font-size:1.2rem; font-weight:700; color:var(--fr-text); margin:0; }

.fr-totaux { display:grid; grid-template-columns:repeat(3,1fr); gap:14px; margin-bottom:20px; }
@media(max-width:640px){ .fr-totaux{ grid-template-columns:1fr; } }
.fr-total-card { background:#fff; border:1px solid var(--fr-border); border-radius:var(--fr-radius); padding:14px 18px; box-shadow:var(--fr-shadow); }
.fr-total-label { font-size:.78rem; color:var(--fr-muted); text-transform:uppercase; letter-spacing:.05em; margin-bottom:6px; }
.fr-total-val   { font-size:1.3rem; font-weight:800; }

.fr-filters { display:flex; gap:10px; margin-bottom:16px; flex-wrap:wrap; align-items:center; }
.fr-search { flex:1; min-width:220px; max-width:360px; padding:7px 12px 7px 34px; font-size:.88rem; border:1.5px solid var(--fr-border); border-radius:8px; outline:none;
    background:#fff url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='15' height='15' fill='%2364748B' viewBox='0 0 16 16'%3E%3Cpath d='M11.742 10.344a6.5 6.5 0 1 0-1.397 1.398h-.001l3.85 3.85a1 1 0 0 0 1.415-1.415l-3.867-3.833zm-5.242 1.156a5 5 0 1 1 0-10 5 5 0 0 1 0 10z'/%3E%3C/svg%3E") no-repeat 10px center; }
.fr-search:focus { border-color:var(--fr-primary); box-shadow:0 0 0 3px rgba(37,99,235,.1); }
.btn-filter { padding:7px 16px; border-radius:7px; background:var(--fr-primary); color:#fff; border:none; font-size:.85rem; font-weight:600; cursor:pointer; }
.btn-reset  { padding:7px 14px; border-radius:7px; background:#fff; color:var(--fr-muted); border:1.5px solid var(--fr-border); font-size:.85rem; text-decoration:none; }

.fr-card  { background:#fff; border:1px solid var(--fr-border); border-radius:var(--fr-radius); box-shadow:var(--fr-shadow); overflow:hidden; }
.fr-table { width:100%; border-collapse:collapse; font-size:.88rem; }
.fr-table thead th { background:#F8FAFD; color:var(--fr-muted); font-size:.75rem; font-weight:700; text-transform:uppercase; letter-spacing:.06em; padding:9px 14px; border-bottom:1px solid var(--fr-border); text-align:left; white-space:nowrap; }
.fr-table tbody tr { border-bottom:1px solid #F1F5F9; transition:background .1s; }
.fr-table tbody tr:last-child { border-bottom:none; }
.fr-table tbody tr:hover td { background:#FAFBFD; }
.fr-table td { padding:9px 14px; vertical-align:middle; }
.fr-table tfoot td { background:#F8FAFD; font-weight:700; padding:10px 14px; border-top:2px solid var(--fr-border); }

.solde-pos { color:var(--fr-danger);  font-weight:700; }
.solde-neg { color:var(--fr-success); font-weight:700; }
.solde-zer { color:var(--fr-muted);   font-weight:600; }

.btn-releve { display:inline-flex; align-items:center; gap:4px; padding:5px 12px; border-radius:6px; font-size:.82rem; font-weight:600; text-decoration:none; background:var(--fr-primary-light); color:var(--fr-primary); border:1px solid #BFDBFE; transition:all .12s; }
.btn-releve:hover { background:var(--fr-primary); color:#fff; }

.bar-wrap { height:5px; background:#F1F5F9; border-radius:99px; margin-top:4px; overflow:hidden; }
.bar-fill { height:100%; border-radius:99px; }

.fr-pag { display:flex; gap:6px; align-items:center; padding:12px 18px; flex-wrap:wrap; }
.fr-pag-btn { padding:5px 12px; border-radius:6px; font-size:.83rem; text-decoration:none; border:1.5px solid var(--fr-border); background:#fff; color:var(--fr-muted); transition:all .12s; }
.fr-pag-btn:hover { border-color:var(--fr-primary); color:var(--fr-primary); background:var(--fr-primary-light); }
.fr-pag-btn.active { background:var(--fr-primary); color:#fff; border-color:var(--fr-primary); pointer-events:none; }
.fr-pag-btn.disabled { opacity:.4; pointer-events:none; }
</style>

<div class="fr-page">

    <div class="fr-topbar">
        <h1 class="fr-title">Suivi fournisseurs</h1>
        <span style="font-size:.82rem;color:var(--fr-muted);background:#F1F5F9;padding:3px 10px;border-radius:99px;border:1px solid var(--fr-border);">
            <?= $totalCount ?> fournisseur<?= $totalCount > 1 ? 's' : '' ?>
        </span>
    </div>

    <div class="fr-totaux">
        <div class="fr-total-card">
            <div class="fr-total-label">Total facturé</div>
            <div class="fr-total-val" style="color:var(--fr-danger);"><?= number_format($totalFacturesFil, 0, ',', ' ') ?></div>
        </div>
        <div class="fr-total-card">
            <div class="fr-total-label">Total versé</div>
            <div class="fr-total-val" style="color:var(--fr-success);"><?= number_format($totalVerseFil, 0, ',', ' ') ?></div>
        </div>
        <div class="fr-total-card">
            <div class="fr-total-label">Solde restant dû</div>
            <div class="fr-total-val" style="color:<?= $totalSoldeFil > 0 ? 'var(--fr-danger)' : 'var(--fr-success)' ?>;">
                <?= number_format($totalSoldeFil, 0, ',', ' ') ?>
            </div>
        </div>
    </div>

    <form method="get" class="fr-filters">
        <input type="hidden" name="action" value="fournisseur_releve/index">
        <input type="hidden" name="page" value="1">
        <input type="text" name="q" class="fr-search" placeholder="Rechercher un fournisseur..."
               value="<?= htmlspecialchars($search) ?>">
        <button type="submit" class="btn-filter">Filtrer</button>
        <?php if ($search): ?>
        <a href="index.php?action=fournisseur_releve/index" class="btn-reset">✕ Effacer</a>
        <?php endif; ?>
        <span style="font-size:.82rem;color:var(--fr-muted);margin-left:auto;">
            <?= $totalCount ?> résultat<?= $totalCount > 1 ? 's' : '' ?>
        </span>
    </form>

    <div class="fr-card">
        <div style="overflow-x:auto;">
            <table class="fr-table">
                <thead>
                    <tr>
                        <th>Fournisseur</th>
                        <th>Ville</th>
                        <th style="text-align:right;">Facturé TTC</th>
                        <th style="text-align:right;">Versé</th>
                        <th style="text-align:right;">Solde</th>
                        <th style="text-align:center;width:100px;">Relevé</th>
                    </tr>
                </thead>
                <tbody>
                <?php if (empty($paginated)): ?>
                <tr><td colspan="6" style="text-align:center;color:var(--fr-muted);padding:32px 0;">Aucun fournisseur trouvé.</td></tr>
                <?php endif; ?>
                <?php foreach ($paginated as $f):
                    $solde   = (float)$f['solde'];
                    $facture = (float)$f['total_facture'];
                    $verse   = (float)$f['total_verse'];
                    $soldeCls = $solde > 0 ? 'solde-pos' : ($solde < 0 ? 'solde-neg' : 'solde-zer');
                    $pct = $facture > 0 ? min(100, round($verse / $facture * 100)) : 0;
                    $barColor = $pct >= 100 ? 'var(--fr-success)' : ($pct >= 50 ? 'var(--fr-warning)' : 'var(--fr-danger)');
                ?>
                <tr>
                    <td>
                        <strong><?= htmlspecialchars($f['nom_fournisseurs']) ?></strong>
                        <?php if (!empty($f['telephone'])): ?>
                        <br><span style="font-size:.76rem;color:var(--fr-muted);"><?= htmlspecialchars($f['telephone']) ?></span>
                        <?php endif; ?>
                    </td>
                    <td style="color:var(--fr-muted);font-size:.84rem;"><?= htmlspecialchars($f['ville'] ?? '—') ?></td>
                    <td style="text-align:right;font-weight:600;">
                        <?= $facture > 0 ? number_format($facture, 0, ',', ' ') : '<span style="color:var(--fr-muted);">—</span>' ?>
                    </td>
                    <td style="text-align:right;">
                        <?= $verse > 0 ? number_format($verse, 0, ',', ' ') : '<span style="color:var(--fr-muted);">—</span>' ?>
                        <?php if ($facture > 0): ?>
                        <div class="bar-wrap">
                            <div class="bar-fill" style="width:<?= $pct ?>%;background:<?= $barColor ?>;"></div>
                        </div>
                        <?php endif; ?>
                    </td>
                    <td style="text-align:right;">
                        <span class="<?= $soldeCls ?>">
                            <?= number_format(abs($solde), 0, ',', ' ') ?>
                            <?php if ($solde > 0): ?><small style="font-weight:400;"> à payer</small>
                            <?php elseif ($solde < 0): ?><small style="font-weight:400;"> avance</small>
                            <?php else: ?><small style="font-weight:400;"> soldé</small>
                            <?php endif; ?>
                        </span>
                    </td>
                    <td style="text-align:center;">
                        <a href="index.php?action=fournisseur_releve/show&id=<?= (int)$f['id_fournisseurs'] ?>"
                           class="btn-releve">📄 Voir</a>
                    </td>
                </tr>
                <?php endforeach; ?>
                </tbody>
                <?php if (!empty($paginated)): ?>
                <tfoot>
                    <tr>
                        <td colspan="2">Total (page)</td>
                        <td style="text-align:right;color:var(--fr-danger);"><?= number_format(array_sum(array_column($paginated,'total_facture')), 0, ',', ' ') ?></td>
                        <td style="text-align:right;color:var(--fr-success);"><?= number_format(array_sum(array_column($paginated,'total_verse')), 0, ',', ' ') ?></td>
                        <td style="text-align:right;color:<?= $totalSoldeFil>0?'var(--fr-danger)':'var(--fr-success)' ?>;"><?= number_format(array_sum(array_column($paginated,'solde')), 0, ',', ' ') ?></td>
                        <td></td>
                    </tr>
                </tfoot>
                <?php endif; ?>
            </table>
        </div>

        <?php if ($totalPages > 1): ?>
        <nav class="fr-pag">
            <?php $baseUrl = 'index.php?action=fournisseur_releve/index' . ($search ? '&q='.urlencode($search) : ''); ?>
            <?php if ($page > 1): ?>
            <a href="<?= $baseUrl ?>&page=1" class="fr-pag-btn">&laquo;</a>
            <a href="<?= $baseUrl ?>&page=<?= $page-1 ?>" class="fr-pag-btn">‹ Préc.</a>
            <?php else: ?>
            <span class="fr-pag-btn disabled">&laquo;</span>
            <span class="fr-pag-btn disabled">‹ Préc.</span>
            <?php endif; ?>
            <?php for ($p = max(1,$page-2); $p <= min($totalPages,$page+2); $p++): ?>
            <a href="<?= $baseUrl ?>&page=<?= $p ?>" class="fr-pag-btn <?= $p===$page?'active':'' ?>"><?= $p ?></a>
            <?php endfor; ?>
            <?php if ($page < $totalPages): ?>
            <a href="<?= $baseUrl ?>&page=<?= $page+1 ?>" class="fr-pag-btn">Suiv. ›</a>
            <a href="<?= $baseUrl ?>&page=<?= $totalPages ?>" class="fr-pag-btn">&raquo;</a>
            <?php else: ?>
            <span class="fr-pag-btn disabled">Suiv. ›</span>
            <span class="fr-pag-btn disabled">&raquo;</span>
            <?php endif; ?>
            <span style="font-size:.82rem;color:var(--fr-muted);margin-left:4px;">
                Page <?= $page ?> / <?= $totalPages ?> &nbsp;·&nbsp; <?= $totalCount ?> fournisseur<?= $totalCount>1?'s':'' ?>
            </span>
        </nav>
        <?php endif; ?>
    </div>
</div>
