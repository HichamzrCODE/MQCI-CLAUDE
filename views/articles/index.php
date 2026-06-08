<?php include '../views/layout.php';

$role      = $_SESSION['role'] ?? 'user';
$canSeePR  = ($role !== 'user'); // Prix revient + marge réservés aux managers/admins
?>

<style>
:root {
    --art-border:        #E4E8F0;
    --art-primary:       #2563EB;
    --art-primary-light: #EFF6FF;
    --art-success:       #16A34A;
    --art-danger:        #DC2626;
    --art-warning:       #D97706;
    --art-text:          #1E293B;
    --art-muted:         #64748B;
    --art-radius:        10px;
    --art-shadow:        0 1px 4px rgba(0,0,0,.07);
}
.art-page { max-width: 1200px; margin: 0 auto; padding: 24px 16px 48px; }
.art-topbar { display:flex; align-items:center; gap:10px; margin-bottom:20px; flex-wrap:wrap; }
.art-title { font-size:1.2rem; font-weight:700; color:var(--art-text); margin:0; }
.art-count { font-size:.82rem; color:var(--art-muted); background:#F1F5F9; padding:3px 10px; border-radius:99px; border:1px solid var(--art-border); }
.art-topbar-actions { margin-left:auto; display:flex; gap:8px; flex-wrap:wrap; }
.btn-action { display:inline-flex; align-items:center; gap:5px; padding:7px 14px; border-radius:7px; font-size:.83rem; font-weight:600; text-decoration:none; border:none; cursor:pointer; transition:all .15s; }
.btn-primary-a { background:var(--art-primary); color:#fff; }
.btn-primary-a:hover { background:#1D4ED8; color:#fff; }
.btn-outline-a { background:#fff; color:var(--art-muted); border:1.5px solid var(--art-border); }
.btn-outline-a:hover { border-color:var(--art-primary); color:var(--art-primary); background:var(--art-primary-light); }

.art-search-bar { display:flex; align-items:center; gap:10px; margin-bottom:16px; flex-wrap:wrap; }
.art-search {
    flex:1; min-width:240px; max-width:420px;
    padding:8px 14px 8px 36px; font-size:.9rem;
    border:1.5px solid var(--art-border); border-radius:8px; outline:none;
    background:#fff url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='16' height='16' fill='%2364748B' viewBox='0 0 16 16'%3E%3Cpath d='M11.742 10.344a6.5 6.5 0 1 0-1.397 1.398h-.001l3.85 3.85a1 1 0 0 0 1.415-1.415l-3.867-3.833zm-5.242 1.156a5 5 0 1 1 0-10 5 5 0 0 1 0 10z'/%3E%3C/svg%3E") no-repeat 11px center;
    transition:border-color .15s, box-shadow .15s;
}
.art-search:focus { border-color:var(--art-primary); box-shadow:0 0 0 3px rgba(37,99,235,.1); }

.art-card { background:#fff; border:1px solid var(--art-border); border-radius:var(--art-radius); box-shadow:var(--art-shadow); overflow:hidden; }
.art-table { width:100%; border-collapse:collapse; font-size:.88rem; }
.art-table thead th {
    background:#F8FAFD; color:var(--art-muted);
    font-size:.76rem; font-weight:700; text-transform:uppercase;
    letter-spacing:.06em; padding:10px 14px;
    border-bottom:1px solid var(--art-border);
    white-space:nowrap; text-align:left;
}
.art-table tbody tr { border-bottom:1px solid #F1F5F9; transition:background .1s; }
.art-table tbody tr:last-child { border-bottom:none; }
.art-table tbody tr:hover td { background:#FAFBFD; }
.art-table tbody tr.alt td { background:#FAFBFD; }
.art-table tbody tr.alt:hover td { background:#F3F4F6; }
.art-table td { padding:9px 14px; vertical-align:middle; color:var(--art-text); }

.art-nom { font-weight:600; text-transform:uppercase; font-size:.88rem; }
.art-sku { font-size:.76rem; color:var(--art-muted); margin-top:1px; font-family:monospace; }
.prix-val { font-weight:700; font-size:.92rem; }
.prix-muted { color:var(--art-muted); }
.stock-val { font-weight:700; font-size:.95rem; cursor:pointer; text-decoration:none; color:var(--art-text); }
.stock-val:hover { color:var(--art-primary); }
.stock-alert { color:var(--art-danger) !important; }
.badge-actif        { background:#DCFCE7; color:#15803D; }
.badge-inactif      { background:#FEF9C3; color:#854D0E; }
.badge-discontinued { background:#F1F5F9; color:var(--art-muted); }
.status-badge { display:inline-block; padding:2px 8px; border-radius:99px; font-size:.72rem; font-weight:700; letter-spacing:.03em; }
.act-btn { display:inline-flex; align-items:center; justify-content:center; width:28px; height:28px; border-radius:6px; font-size:.85rem; text-decoration:none; border:1px solid var(--art-border); background:#fff; transition:all .12s; cursor:pointer; }
.act-btn:hover { transform:translateY(-1px); }
.act-btn-view:hover  { background:var(--art-primary-light); border-color:var(--art-primary); }
.act-btn-edit { color:var(--art-warning); }
.act-btn-edit:hover  { background:#FFF7ED; border-color:var(--art-warning); }
.act-btn-del  { color:var(--art-danger); }
.act-btn-del:hover   { background:#FEF2F2; border-color:var(--art-danger); }
.art-empty { padding:48px 0; text-align:center; color:var(--art-muted); font-size:.95rem; }
.art-empty-icon { font-size:2.5rem; margin-bottom:10px; }
.row-discontinued td { opacity:.55; }

/* Modal */
.art-modal-overlay { display:none; position:fixed; inset:0; background:rgba(0,0,0,.35); z-index:1000; align-items:center; justify-content:center; }
.art-modal-overlay.open { display:flex; }
.art-modal { background:#fff; border-radius:12px; width:520px; max-width:95vw; box-shadow:0 8px 32px rgba(0,0,0,.18); overflow:hidden; }
.art-modal-head { display:flex; align-items:center; justify-content:space-between; padding:14px 18px; border-bottom:1px solid var(--art-border); font-weight:700; font-size:.95rem; }
.art-modal-close { background:none; border:none; font-size:1.2rem; color:var(--art-muted); cursor:pointer; line-height:1; }
.art-modal-close:hover { color:var(--art-danger); }
.art-modal-body { padding:16px 18px; }
.modal-depot-table { width:100%; border-collapse:collapse; font-size:.86rem; }
.modal-depot-table th { background:#F8FAFD; font-size:.75rem; text-transform:uppercase; letter-spacing:.05em; color:var(--art-muted); padding:6px 10px; border-bottom:1px solid var(--art-border); text-align:left; }
.modal-depot-table td { padding:7px 10px; border-bottom:1px solid #F8FAFD; }
.modal-depot-table tr:last-child td { border-bottom:none; }
</style>

<div class="art-page">

    <div class="art-topbar">
        <h1 class="art-title">Articles</h1>
        <span class="art-count"><?= number_format($totalArticles ?? 0) ?> article<?= ($totalArticles ?? 0) > 1 ? 's' : '' ?></span>
        <div class="art-topbar-actions">
            <?php if (hasPermission('articles', 'create')): ?>
            <a href="index.php?action=articles/create" class="btn-action btn-primary-a">+ Nouveau</a>
            <?php endif; ?>
            <?php if (hasPermission('articles', 'view')): ?>
            <a href="index.php?action=articles/export" class="btn-action btn-outline-a">⬇ CSV</a>
            <?php endif; ?>
            <?php if (hasPermission('articles', 'create')): ?>
            <a href="index.php?action=articles/import" class="btn-action btn-outline-a">⬆ Import</a>
            <?php endif; ?>
            <?php if (hasPermission('articles', 'view')): ?>
            <a href="index.php?action=depots" class="btn-action btn-outline-a">🏭 Dépôts</a>
            <?php endif; ?>
        </div>
    </div>

    <div class="art-search-bar">
        <input type="text" id="article-search" class="art-search" placeholder="Rechercher par SKU, nom, fournisseur...">
        <span id="search-count" style="font-size:.82rem; color:var(--art-muted);"></span>
    </div>

    <div class="art-card">
        <div style="overflow-x:auto;">
            <table class="art-table" id="articles-table">
                <thead>
                    <tr>
                        <th style="min-width:180px;">Article</th>
                        <th style="width:80px; text-align:center;">Qté</th>
                        <th style="width:100px; text-align:right;">Prix vente</th>
                        <?php if ($canSeePR): ?>
                        <th style="width:100px; text-align:right;">Prix rev.</th>
                        <th style="width:90px; text-align:right;">Marge</th>
                        <?php endif; ?>
                        <th style="min-width:120px;">Fournisseur</th>
                        <th style="width:80px;">Statut</th>
                        <th style="width:90px; text-align:center;">Actions</th>
                    </tr>
                </thead>
                <tbody>
                <?php
                $alt = false;
                foreach ($articles as $article):
                    $pr     = (float)($article['prix_revient_display'] ?? $article['prix_revient'] ?? $article['pr'] ?? 0);
                    $pv     = (float)($article['prix_vente'] ?? 0);
                    $qt     = (int)($article['quantite_totale'] ?? 0);
                    $statut = $article['statut'] ?? 'actif';
                    $smin   = (int)($article['stock_minimal'] ?? 0);
                    $alert  = $smin > 0 && $qt < $smin;
                    $marge  = ($pr > 0 && $pv > 0) ? round(($pv - $pr) / $pr * 100, 1) : null;
                    $rowCls = $alt ? 'alt' : '';
                    if ($statut === 'discontinued') $rowCls .= ' row-discontinued';
                ?>
                <tr class="<?= $rowCls ?>">
                    <td>
                        <div class="art-nom"><?= htmlspecialchars($article['nom_art']) ?></div>
                        <?php if (!empty($article['sku'])): ?>
                        <div class="art-sku"><?= htmlspecialchars($article['sku']) ?></div>
                        <?php endif; ?>
                    </td>
                    <td style="text-align:center;">
                        <a href="#" class="stock-val <?= $alert ? 'stock-alert' : '' ?>"
                           data-id="<?= (int)$article['id_articles'] ?>"
                           data-nom="<?= htmlspecialchars($article['nom_art']) ?>">
                            <?= $qt ?><?= $alert ? ' ⚠' : '' ?>
                        </a>
                    </td>
                    <td style="text-align:right;">
                        <?php if ($pv): ?>
                        <span class="prix-val"><?= number_format($pv, 0, ',', ' ') ?></span>
                        <?php else: ?><span class="prix-muted">—</span><?php endif; ?>
                    </td>
                    <?php if ($canSeePR): ?>
                    <td style="text-align:right;">
                        <span class="prix-val" style="color:var(--art-muted);">
                            <?= $pr ? number_format($pr, 0, ',', ' ') : '—' ?>
                        </span>
                    </td>
                    <td style="text-align:right;">
                        <?php if ($marge !== null): ?>
                        <span style="font-weight:700; font-size:.88rem; color:<?= $marge >= 0 ? 'var(--art-success)' : 'var(--art-danger)' ?>;">
                            <?= $marge ?>%
                        </span>
                        <?php else: ?><span class="prix-muted">—</span><?php endif; ?>
                    </td>
                    <?php endif; ?>
                    <td style="font-size:.84rem; color:var(--art-muted);"><?= htmlspecialchars($article['nom_fournisseurs'] ?? '—') ?></td>
                    <td>
                        <?php
                        $badgeClass = ['actif'=>'badge-actif','inactif'=>'badge-inactif','discontinued'=>'badge-discontinued'][$statut] ?? '';
                        $badgeLabel = ['actif'=>'Actif','inactif'=>'Inactif','discontinued'=>'Arrêté'][$statut] ?? $statut;
                        ?>
                        <span class="status-badge <?= $badgeClass ?>"><?= $badgeLabel ?></span>
                    </td>
                    <td>
                        <div style="display:flex; gap:5px; justify-content:center;">
                            <?php if (hasPermission('articles', 'view')): ?>
                            <a href="index.php?action=articles/show&id=<?= (int)$article['id_articles'] ?>" class="act-btn act-btn-view" title="Voir">👁</a>
                            <?php endif; ?>
                            <?php if (hasPermission('articles', 'edit')): ?>
                            <a href="index.php?action=articles/edit&id=<?= (int)$article['id_articles'] ?>" class="act-btn act-btn-edit" title="Modifier">✎</a>
                            <?php endif; ?>
                            <?php if (hasPermission('articles', 'delete')): ?>
                            <a href="index.php?action=articles/delete&id=<?= (int)$article['id_articles'] ?>" class="act-btn act-btn-del" title="Supprimer" onclick="return confirm('Supprimer cet article ?')">✕</a>
                            <?php endif; ?>
                        </div>
                    </td>
                </tr>
                <?php $alt = !$alt; endforeach; ?>
                <?php if (empty($articles)): ?>
                <tr><td colspan="<?= $canSeePR ? 8 : 6 ?>">
                    <div class="art-empty"><div class="art-empty-icon">📦</div>Aucun article trouvé.</div>
                </td></tr>
                <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<!-- Modal stock -->
<div class="art-modal-overlay" id="stockOverlay">
    <div class="art-modal">
        <div class="art-modal-head">
            <span id="modalTitle">Stock par dépôt</span>
            <button class="art-modal-close" id="modalClose">✕</button>
        </div>
        <div class="art-modal-body" id="modalBody">
            <div style="text-align:center;padding:20px;color:var(--art-muted);">Chargement...</div>
        </div>
    </div>
</div>

<script>
(function () {
    const CAN_SEE_PR = <?= $canSeePR ? 'true' : 'false' ?>;
    const COLS = CAN_SEE_PR ? 8 : 6;

    function fmt(v) { const n=parseFloat(v); return isNaN(n)?'—':n.toLocaleString('fr-FR',{maximumFractionDigits:0}); }
    function esc(s) { return String(s).replace(/&/g,'&amp;').replace(/</g,'&lt;').replace(/>/g,'&gt;').replace(/"/g,'&quot;'); }

    // ── Recherche ─────────────────────────────────────────────────
    let timer;
    document.getElementById('article-search').addEventListener('input', function(){
        clearTimeout(timer);
        const term = this.value.trim();
        timer = setTimeout(() => {
            fetch('index.php?action=articles/search&term=' + encodeURIComponent(term))
                .then(r => r.json())
                .then(data => {
                    const tbody = document.querySelector('#articles-table tbody');
                    tbody.innerHTML = '';
                    const sc = document.getElementById('search-count');
                    if (!data.length) {
                        tbody.innerHTML = `<tr><td colspan="${COLS}"><div class="art-empty"><div class="art-empty-icon">🔍</div>Aucun résultat.</div></td></tr>`;
                        sc.textContent = '0 résultat';
                        return;
                    }
                    sc.textContent = data.length + ' résultat' + (data.length > 1 ? 's' : '');
                    let alt = false;
                    data.forEach(a => { tbody.insertAdjacentHTML('beforeend', buildRow(a, alt)); alt = !alt; });
                }).catch(()=>{});
        }, 250);
    });

    function buildRow(a, alt) {
        const pr   = parseFloat(a.prix_revient_display || a.prix_revient || a.pr || 0);
        const pv   = parseFloat(a.prix_vente || 0);
        const qt   = parseInt(a.quantite_totale) || 0;
        const st   = a.statut || 'actif';
        const smin = parseInt(a.stock_minimal) || 0;
        const alrt = smin > 0 && qt < smin;
        const marge= (pr > 0 && pv > 0) ? ((pv-pr)/pr*100).toFixed(1) : null;
        const badgeMap = {actif:'badge-actif',inactif:'badge-inactif',discontinued:'badge-discontinued'};
        const badgeLbl = {actif:'Actif',inactif:'Inactif',discontinued:'Arrêté'};

        let prCols = '';
        if (CAN_SEE_PR) {
            prCols = `<td style="text-align:right;"><span class="prix-val" style="color:var(--art-muted);">${pr ? fmt(pr) : '—'}</span></td>
            <td style="text-align:right;">${marge !== null ? `<span style="font-weight:700;font-size:.88rem;color:${parseFloat(marge)>=0?'var(--art-success)':'var(--art-danger)'}">${marge}%</span>` : '<span class="prix-muted">—</span>'}</td>`;
        }

        let actions = '';
        if (a.viewable)  actions += `<a href="index.php?action=articles/show&id=${a.id_articles}" class="act-btn act-btn-view" title="Voir">👁</a>`;
        if (a.editable)  actions += `<a href="index.php?action=articles/edit&id=${a.id_articles}" class="act-btn act-btn-edit" title="Modifier">✎</a>`;
        if (a.deletable) actions += `<a href="index.php?action=articles/delete&id=${a.id_articles}" class="act-btn act-btn-del" title="Supprimer" onclick="return confirm('Supprimer ?')">✕</a>`;

        return `<tr class="${alt?'alt':''} ${st==='discontinued'?'row-discontinued':''}">
            <td><div class="art-nom">${esc(a.nom_art)}</div>${a.sku?`<div class="art-sku">${esc(a.sku)}</div>`:''}</td>
            <td style="text-align:center;"><a href="#" class="stock-val ${alrt?'stock-alert':''}" data-id="${a.id_articles}" data-nom="${esc(a.nom_art)}">${qt}${alrt?' ⚠':''}</a></td>
            <td style="text-align:right;">${pv?`<span class="prix-val">${fmt(pv)}</span>`:'<span class="prix-muted">—</span>'}</td>
            ${prCols}
            <td style="font-size:.84rem;color:var(--art-muted);">${esc(a.nom_fournisseurs||'—')}</td>
            <td><span class="status-badge ${badgeMap[st]||''}">${badgeLbl[st]||st}</span></td>
            <td><div style="display:flex;gap:5px;justify-content:center;">${actions}</div></td>
        </tr>`;
    }

    // ── Modal stock ───────────────────────────────────────────────
    const overlay  = document.getElementById('stockOverlay');
    const modalClose = document.getElementById('modalClose');
    const modalTitle = document.getElementById('modalTitle');
    const modalBody  = document.getElementById('modalBody');

    document.addEventListener('click', function(e){
        const btn = e.target.closest('.stock-val');
        if (!btn) return;
        e.preventDefault();
        modalTitle.textContent = 'Stock — ' + (btn.dataset.nom || '');
        modalBody.innerHTML = '<div style="text-align:center;padding:20px;color:var(--art-muted);">Chargement...</div>';
        overlay.classList.add('open');
        fetch('index.php?action=articles/stock-par-depot&id=' + btn.dataset.id)
            .then(r => r.json())
            .then(data => {
                if (!data || !data.length) { modalBody.innerHTML='<p style="text-align:center;color:var(--art-muted);padding:20px 0;">Aucun stock.</p>'; return; }
                let html = '<table class="modal-depot-table"><thead><tr><th>Dépôt</th><th>Qté</th><th>Transit</th><th>Bloquée</th><th>Emplacement</th></tr></thead><tbody>';
                data.forEach(s => {
                    html += `<tr>
                        <td><strong>${esc(s.depot_nom)}</strong>${s.depot_ville?`<br><span style="font-size:.76rem;color:var(--art-muted);">${esc(s.depot_ville)}</span>`:''}</td>
                        <td><strong style="font-size:1rem;">${parseInt(s.quantite)||0}</strong></td>
                        <td style="color:var(--art-muted);">${parseInt(s.quantite_en_transit)||0}</td>
                        <td style="color:var(--art-muted);">${parseInt(s.quantite_bloquee)||0}</td>
                        <td style="font-size:.82rem;">${s.emplacement?esc(s.emplacement):'—'}</td>
                    </tr>`;
                });
                html += '</tbody></table>';
                modalBody.innerHTML = html;
            })
            .catch(()=>{ modalBody.innerHTML='<p style="color:var(--art-danger);text-align:center;">Erreur.</p>'; });
    });

    modalClose.addEventListener('click', () => overlay.classList.remove('open'));
    overlay.addEventListener('click', e => { if(e.target===overlay) overlay.classList.remove('open'); });
    document.addEventListener('keydown', e => { if(e.key==='Escape') overlay.classList.remove('open'); });
})();
</script>
