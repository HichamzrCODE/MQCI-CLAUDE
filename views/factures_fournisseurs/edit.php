<?php include '../views/layout.php'; ?>
<?php
$errorFields = $errorFields ?? [];
$error       = $error       ?? null;
$isValidated = (($facture['statut'] ?? 'draft') === 'validated');
$isAdmin     = (($_SESSION['role'] ?? '') === 'admin');
$readonly    = ($isValidated && !$isAdmin);
$lignes      = $lignes      ?? [];
$fournisseurs= $fournisseurs ?? [];
$depots      = $depots      ?? [];
?>

<style>
:root {
    --ff-border:#E4E8F0; --ff-primary:#2563EB; --ff-primary-light:#EFF6FF;
    --ff-danger:#DC2626; --ff-warning:#D97706;
    --ff-text:#1E293B; --ff-muted:#64748B; --ff-label:#475569;
    --ff-radius:10px; --ff-shadow:0 1px 4px rgba(0,0,0,.07);
}
.ff-page { max-width:1100px; margin:0 auto; padding:24px 16px 48px; }
.ff-topbar { display:flex; align-items:center; gap:10px; margin-bottom:24px; flex-wrap:wrap; }
.ff-title  { font-size:1.2rem; font-weight:700; color:var(--ff-text); margin:0; }
.ff-badge-num { font-size:.82rem; color:var(--ff-muted); background:#F1F5F9; padding:3px 10px; border-radius:99px; border:1px solid var(--ff-border); font-family:monospace; }
.ff-topbar-actions { margin-left:auto; display:flex; gap:8px; }
.btn-ff           { display:inline-flex; align-items:center; gap:5px; padding:8px 18px; border-radius:7px; font-size:.88rem; font-weight:600; text-decoration:none; border:none; cursor:pointer; transition:all .15s; }
.btn-ff-primary   { background:var(--ff-primary); color:#fff; }
.btn-ff-primary:hover { background:#1D4ED8; color:#fff; }
.btn-ff-warning   { background:#D97706; color:#fff; }
.btn-ff-warning:hover { background:#B45309; color:#fff; }
.btn-ff-secondary { background:#fff; color:var(--ff-muted); border:1.5px solid var(--ff-border); }
.btn-ff-secondary:hover { color:var(--ff-primary); border-color:var(--ff-primary); background:var(--ff-primary-light); }
.badge-validated { background:#DCFCE7; color:#15803D; padding:3px 10px; border-radius:99px; font-size:.78rem; font-weight:700; }
.badge-draft     { background:#FEF9C3; color:#854D0E; padding:3px 10px; border-radius:99px; font-size:.78rem; font-weight:700; }

.ff-card      { background:#fff; border:1px solid var(--ff-border); border-radius:var(--ff-radius); box-shadow:var(--ff-shadow); margin-bottom:16px; }
.ff-card-head { display:flex; align-items:center; gap:8px; padding:11px 18px; background:#F8FAFD; border-bottom:1px solid var(--ff-border); font-size:.8rem; font-weight:700; text-transform:uppercase; letter-spacing:.06em; color:var(--ff-label); border-radius:var(--ff-radius) var(--ff-radius) 0 0; }
.ff-card-head .icon { width:24px; height:24px; border-radius:6px; display:flex; align-items:center; justify-content:center; font-size:.9rem; background:var(--ff-primary-light); color:var(--ff-primary); }
.ff-card-body { padding:16px 18px; }

.ff-grid  { display:grid; grid-template-columns:1fr 1fr 1fr; gap:14px; }
.ff-grid-2{ display:grid; grid-template-columns:1fr 1fr; gap:14px; }
@media(max-width:640px){ .ff-grid,.ff-grid-2{ grid-template-columns:1fr; } }

.ff-label { display:block; font-size:.78rem; font-weight:600; color:var(--ff-label); margin-bottom:4px; }
.ff-input,.ff-select,.ff-textarea {
    width:100%; padding:7px 10px; font-size:.88rem; color:var(--ff-text);
    background:#fff; border:1.5px solid var(--ff-border); border-radius:7px;
    outline:none; transition:border-color .15s,box-shadow .15s; box-sizing:border-box;
}
.ff-input:focus,.ff-select:focus,.ff-textarea:focus { border-color:var(--ff-primary); box-shadow:0 0 0 3px rgba(37,99,235,.1); }
.ff-input[readonly],.ff-input:disabled,.ff-select:disabled,.ff-textarea[readonly] { background:#F8FAFD; color:var(--ff-muted); cursor:default; }

/* Dropdown global dans body */
#ac-global-box {
    display:none; position:fixed; z-index:99999;
    background:#fff; border:1.5px solid var(--ff-primary);
    border-radius:8px; box-shadow:0 4px 24px rgba(0,0,0,.18);
    max-height:240px; overflow-y:auto; min-width:200px;
}
#ac-global-box .ac-item { padding:9px 14px; cursor:pointer; font-size:.87rem; color:var(--ff-text); border-bottom:1px solid #F1F5F9; display:block; text-decoration:none; }
#ac-global-box .ac-item:last-child { border-bottom:none; }
#ac-global-box .ac-item:hover,
#ac-global-box .ac-item.ac-active { background:var(--ff-primary-light); color:var(--ff-primary); }
#ac-global-box .ac-empty { padding:9px 14px; color:var(--ff-muted); font-size:.85rem; font-style:italic; }

.ff-table { width:100%; border-collapse:collapse; font-size:.87rem; }
.ff-table thead th { background:#F8FAFD; color:var(--ff-muted); font-size:.75rem; font-weight:700; text-transform:uppercase; letter-spacing:.06em; padding:8px 10px; border-bottom:1px solid var(--ff-border); text-align:left; white-space:nowrap; }
.ff-table tbody tr { border-bottom:1px solid #F1F5F9; }
.ff-table tbody tr:last-child { border-bottom:none; }
.ff-table td { padding:7px 8px; vertical-align:middle; }
.ff-table .ff-input { padding:6px 8px; font-size:.86rem; }

.pr-badge { display:inline-block; font-size:.74rem; padding:2px 7px; border-radius:99px; background:#FFF7ED; color:var(--ff-warning); border:1px solid #FDE68A; margin-top:3px; }
.ff-total-bar { display:flex; justify-content:flex-end; padding:12px 18px; background:#F8FAFD; border-top:1px solid var(--ff-border); }
.ff-total-val { font-size:1.15rem; font-weight:800; color:var(--ff-primary); }
.ff-tva-line  { font-size:.82rem; color:var(--ff-muted); margin-top:2px; }

.btn-add-line { display:inline-flex; align-items:center; gap:6px; padding:7px 14px; border-radius:7px; font-size:.84rem; font-weight:600; background:var(--ff-primary-light); color:var(--ff-primary); border:1.5px dashed var(--ff-primary); cursor:pointer; transition:all .15s; margin:10px 0; }
.btn-add-line:hover { background:var(--ff-primary); color:#fff; }
.btn-del-line { display:inline-flex; align-items:center; justify-content:center; width:28px; height:28px; border-radius:6px; background:#FEF2F2; color:var(--ff-danger); border:1px solid #FECACA; cursor:pointer; }
.btn-del-line:hover { background:var(--ff-danger); color:#fff; }

.ff-alert      { background:#FEF2F2; border:1px solid #FECACA; color:var(--ff-danger); border-radius:8px; padding:10px 14px; margin-bottom:14px; font-size:.87rem; }
.ff-alert-info { background:#EFF6FF; border:1px solid #BFDBFE; color:#1D4ED8; border-radius:8px; padding:10px 14px; margin-bottom:14px; font-size:.87rem; }
</style>

<div id="ac-global-box"></div>

<div class="ff-page">
    <div class="ff-topbar">
        <a href="index.php?action=factures_fournisseurs" class="btn-ff btn-ff-secondary">← Retour</a>
        <h1 class="ff-title">Facture fournisseur</h1>
        <span class="ff-badge-num"><?= htmlspecialchars($facture['numero']) ?></span>
        <span class="<?= $isValidated ? 'badge-validated' : 'badge-draft' ?>">
            <?= $isValidated ? '✅ Validée' : '⏳ Brouillon' ?>
        </span>
        <div class="ff-topbar-actions">
            <?php if (!$readonly): ?>
            <button type="submit" form="ff-form" class="btn-ff btn-ff-primary">💾 Enregistrer</button>
            <?php endif; ?>
            <?php if (!$isValidated): ?>
            <a href="index.php?action=factures_fournisseurs/validate&id=<?= (int)$facture['id'] ?>"
               class="btn-ff btn-ff-warning"
               onclick="return confirm('Valider ? Le stock sera incrémenté et le CMP recalculé.');">
                ✔ Valider
            </a>
            <?php endif; ?>
        </div>
    </div>

    <?php if ($error): ?><div class="ff-alert">⚠️ <?= htmlspecialchars($error) ?></div><?php endif; ?>
    <?php if ($readonly): ?><div class="ff-alert-info">ℹ️ Facture validée — lecture seule. CMP mis à jour automatiquement.</div><?php endif; ?>

    <form method="post" action="index.php?action=factures_fournisseurs/update&id=<?= (int)$facture['id'] ?>" id="ff-form" autocomplete="off">

        <div class="ff-card">
            <div class="ff-card-head"><span class="icon">📋</span> En-tête</div>
            <div class="ff-card-body">
                <div class="ff-grid">
                    <div>
                        <label class="ff-label">Fournisseur</label>
                        <select name="fournisseur_id" class="ff-select" <?= $readonly ? 'disabled' : '' ?>>
                            <option value="">— Sélectionner —</option>
                            <?php foreach ($fournisseurs as $f): ?>
                            <option value="<?= (int)$f['id_fournisseurs'] ?>"
                                <?= ((int)$facture['fournisseur_id']===(int)$f['id_fournisseurs']) ? 'selected' : '' ?>>
                                <?= htmlspecialchars($f['nom_fournisseurs']) ?>
                            </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div>
                        <label class="ff-label">Dépôt</label>
                        <select name="depot_id" class="ff-select" <?= $readonly ? 'disabled' : '' ?>>
                            <option value="">— Sélectionner —</option>
                            <?php foreach ($depots as $d): ?>
                            <option value="<?= (int)$d['id'] ?>"
                                <?= ((int)$facture['depot_id']===(int)$d['id']) ? 'selected' : '' ?>>
                                <?= htmlspecialchars($d['nom']) ?>
                            </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div>
                        <label class="ff-label">Date</label>
                        <input type="date" name="date" class="ff-input"
                               value="<?= htmlspecialchars($facture['date']) ?>"
                               <?= $readonly ? 'readonly' : '' ?>>
                    </div>
                </div>
                <div class="ff-grid-2" style="margin-top:12px;">
                    <div>
                        <label class="ff-label">Numéro</label>
                        <input type="text" class="ff-input" readonly
                               value="<?= htmlspecialchars($facture['numero']) ?>"
                               style="font-family:monospace;font-weight:700;">
                    </div>
                    <div>
                        <label class="ff-label">Notes</label>
                        <textarea name="notes" class="ff-textarea" rows="2"
                                  <?= $readonly ? 'readonly' : '' ?>><?= htmlspecialchars($facture['notes'] ?? '') ?></textarea>
                    </div>
                </div>
            </div>
        </div>

        <div class="ff-card">
            <div class="ff-card-head"><span class="icon">📦</span> Articles</div>
            <div style="overflow-x:auto;">
                <table class="ff-table">
                    <thead>
                        <tr>
                            <th style="min-width:260px;">Article <?= !$readonly ? '<small style="font-weight:400;opacity:.7;">↑↓ Entrée Tab</small>' : '' ?></th>
                            <th style="min-width:160px;">Description</th>
                            <th style="width:100px;">Quantité</th>
                            <th style="width:160px;">PU Achat TTC <?= !$readonly ? '<small style="font-weight:400;opacity:.7;">PR auto</small>' : '' ?></th>
                            <th style="width:130px;text-align:right;">Total TTC</th>
                            <?php if (!$readonly): ?><th style="width:40px;"></th><?php endif; ?>
                        </tr>
                    </thead>
                    <tbody id="ff-tbody"></tbody>
                </table>
            </div>
            <?php if (!$readonly): ?>
            <div style="padding:10px 18px;border-top:1px solid var(--ff-border);">
                <button type="button" class="btn-add-line" id="ajouter-ligne">+ Ajouter une ligne</button>
            </div>
            <?php endif; ?>
            <div class="ff-total-bar">
                <div style="text-align:right;">
                    <div style="display:flex;align-items:center;gap:12px;">
                        <span style="font-size:.88rem;font-weight:600;color:var(--ff-label);">Total TTC</span>
                        <span class="ff-total-val" id="total-ttc">0</span>
                    </div>
                    <div class="ff-tva-line">HT : <span id="total-ht">0</span> &nbsp;|&nbsp; TVA 18% : <span id="total-tva">0</span></div>
                </div>
            </div>
        </div>

    </form>
</div>

<?php include '../views/footer.php'; ?>

<script>
(function () {
    const lignesInit = <?= json_encode($lignes ?? [], JSON_UNESCAPED_UNICODE) ?>;
    const tbody      = document.getElementById('ff-tbody');
    const TVA        = 1.18;
    const READONLY   = <?= $readonly ? 'true' : 'false' ?>;
    let rowIndex     = 0;

    function esc(v)      { return String(v??'').replace(/&/g,'&amp;').replace(/</g,'&lt;').replace(/>/g,'&gt;').replace(/"/g,'&quot;'); }
    function parseNum(v) { return parseFloat(String(v??'').replace(/[\s\u00A0]/g,'').replace(',','.')) || 0; }
    function fmt(v)      { return Number(v||0).toLocaleString('fr-FR',{maximumFractionDigits:0}); }

    function updateTotals() {
        let ttc = 0;
        tbody.querySelectorAll('tr').forEach(row => {
            const t = parseNum(row.querySelector('.q-input')?.value) * parseNum(row.querySelector('.p-input')?.value);
            const c = row.querySelector('.t-cell'); if(c) c.textContent = fmt(Math.round(t));
            ttc += t;
        });
        document.getElementById('total-ttc').textContent = fmt(Math.round(ttc));
        document.getElementById('total-ht').textContent  = fmt(Math.round(ttc / TVA));
        document.getElementById('total-tva').textContent = fmt(Math.round(ttc - ttc / TVA));
    }

    // ── Dropdown global ───────────────────────────────────────────
    const BOX = document.getElementById('ac-global-box');
    let currentInput=null, currentHidden=null, currentExtra=null;
    let currentItems=[], activeIdx=-1, fetchTimer=null;

    function positionBox() {
        if (!currentInput) return;
        const r = currentInput.getBoundingClientRect();
        const h = BOX.offsetHeight || 10;
        BOX.style.left  = r.left + 'px';
        BOX.style.width = r.width + 'px';
        const spaceAbove = r.top;
        const spaceBelow = window.innerHeight - r.bottom;
        BOX.style.top = (spaceAbove >= h + 8 || spaceAbove > spaceBelow)
            ? (r.top - h - 4) + 'px'
            : (r.bottom + 4) + 'px';
    }

    function setActive(idx) {
        currentItems.forEach((el,i) => el.classList.toggle('ac-active', i===idx));
        activeIdx = idx;
        if (currentItems[idx]) currentItems[idx].scrollIntoView({block:'nearest'});
    }

    function hideBox() { BOX.style.display='none'; currentItems=[]; activeIdx=-1; }

    function showResults(data, input, hidden, extra) {
        currentInput=input; currentHidden=hidden; currentExtra=extra;
        BOX.innerHTML=''; activeIdx=-1;
        if (!data.length) {
            BOX.innerHTML='<div class="ac-empty">Aucun résultat</div>';
            BOX.style.display='block'; requestAnimationFrame(positionBox);
            currentItems=[]; return;
        }
        data.slice(0,10).forEach(d => {
            const a=document.createElement('a'); a.href='#'; a.className='ac-item';
            a.textContent=d._label; a.dataset.id=d._id; a._data=d;
            a.addEventListener('mousedown', e => { e.preventDefault(); pickItem(a); });
            BOX.appendChild(a);
        });
        currentItems=Array.from(BOX.querySelectorAll('.ac-item'));
        BOX.style.display='block'; requestAnimationFrame(positionBox);
    }

    function pickItem(item) {
        if (!currentInput) return;
        currentInput.value  = item.textContent.trim();
        currentHidden.value = item.dataset.id;
        currentExtra && currentExtra(item._data);
        hideBox();
    }

    window.addEventListener('scroll', positionBox, {passive:true});
    window.addEventListener('resize', positionBox);
    document.addEventListener('mousedown', e => { if (!BOX.contains(e.target) && e.target!==currentInput) hideBox(); });

    function bindAC(input, hidden, url, labelFn, idFn, extraFn) {
        input.addEventListener('input', function() {
            hidden.value=''; clearTimeout(fetchTimer);
            const term=input.value.trim();
            if (!term) { hideBox(); return; }
            fetchTimer=setTimeout(() =>
                fetch(url+encodeURIComponent(term)).then(r=>r.json()).then(data => {
                    data.forEach(d => { d._label=labelFn(d); d._id=idFn(d); });
                    showResults(data, input, hidden, extraFn);
                }).catch(hideBox)
            , 220);
        });

        input.addEventListener('keydown', function(e) {
            const open = BOX.style.display==='block' && currentInput===input && currentItems.length;
            if (e.key==='ArrowDown') { e.preventDefault(); if(open) setActive(Math.min(activeIdx+1,currentItems.length-1)); }
            else if (e.key==='ArrowUp')  { e.preventDefault(); if(open) setActive(Math.max(activeIdx-1,0)); }
            else if (e.key==='Enter')    { if(open && activeIdx>=0){ e.preventDefault(); pickItem(currentItems[activeIdx]); } }
            else if (e.key==='Tab') {
                if (open) {
                    const target = activeIdx>=0 ? currentItems[activeIdx] : currentItems[0];
                    if (target) {
                        currentInput.value  = target.textContent.trim();
                        currentHidden.value = target.dataset.id;
                        currentExtra && currentExtra(target._data);
                    }
                    hideBox();
                }
            }
            else if (e.key==='Escape') { hideBox(); }
        });

        input.addEventListener('blur', () => setTimeout(hideBox, 180));
    }

    function addRow(data={}) {
        const idx=rowIndex++, pr=parseNum(data.pr||0);
        const tr=document.createElement('tr');

        tr.innerHTML = READONLY ? `
            <td>
                <div class="ff-input" style="background:#F8FAFD;">${esc(data.nom_art||'')}</div>
                <input type="hidden" name="articles[${idx}][article_id]" value="${esc(data.article_id||'')}">
                ${pr>0?`<span class="pr-badge">PR TTC : ${fmt(Math.round(pr))}</span>`:''}
            </td>
            <td><input type="text" class="ff-input" name="articles[${idx}][description]" value="${esc(data.description||'')}" readonly></td>
            <td><input type="number" class="ff-input q-input" name="articles[${idx}][quantite]" value="${esc(data.quantite||'1')}" readonly></td>
            <td><input type="text" class="ff-input p-input" name="articles[${idx}][prix_unitaire]" value="${esc(data.prix_unitaire||'0')}" readonly></td>
            <td class="t-cell" style="text-align:right;font-weight:700;">0</td>
        ` : `
            <td style="min-width:260px;">
                <input type="text" class="ff-input article-ac" autocomplete="off"
                       placeholder="Taper pour rechercher..."
                       value="${esc(data.nom_art||data.article_nom||'')}">
                <input type="hidden" name="articles[${idx}][article_id]" class="article-id" value="${esc(data.article_id||'')}">
                <div class="pr-hint" style="display:${pr>0?'block':'none'};">
                    <span class="pr-badge">PR TTC : <span class="pr-val">${pr>0?fmt(Math.round(pr)):'—'}</span></span>
                </div>
            </td>
            <td><input type="text" class="ff-input" name="articles[${idx}][description]" value="${esc(data.description||'')}"></td>
            <td><input type="number" min="1" step="1" class="ff-input q-input" name="articles[${idx}][quantite]" value="${esc(data.quantite||'1')}"></td>
            <td><input type="text" class="ff-input p-input" name="articles[${idx}][prix_unitaire]" value="${esc(data.prix_unitaire||(pr>0?fmt(Math.round(pr)):'0'))}" placeholder="Prix TTC"></td>
            <td class="t-cell" style="text-align:right;font-weight:700;">0</td>
            <td><button type="button" class="btn-del-line">✕</button></td>
        `;

        if (!READONLY) {
            tr.querySelector('.btn-del-line').addEventListener('click', () => { tr.remove(); updateTotals(); });
            tr.querySelector('.q-input').addEventListener('input', updateTotals);
            tr.querySelector('.p-input').addEventListener('input', updateTotals);

            const artAC  = tr.querySelector('.article-ac');
            const artHid = tr.querySelector('.article-id');
            const pIn    = tr.querySelector('.p-input');
            const prHint = tr.querySelector('.pr-hint');
            const prVal  = tr.querySelector('.pr-val');

            bindAC(artAC, artHid, 'index.php?action=articles/search&term=',
                d => d.nom_art||'', d => d.id_articles||'',
                d => {
                    const prTtc = parseFloat(d.pr||0);
                    pIn.value = prTtc>0 ? fmt(Math.round(prTtc)) : '0';
                    if (prTtc>0) { prVal.textContent=fmt(Math.round(prTtc)); prHint.style.display='block'; }
                    else { prHint.style.display='none'; }
                    updateTotals();
                }
            );
        }

        tbody.appendChild(tr);
        updateTotals();
        // ✅ Focus sur le champ article de la nouvelle ligne
        if (!READONLY) tr.querySelector('.article-ac')?.focus();
    }

    if (!READONLY && document.getElementById('ajouter-ligne')) {
        document.getElementById('ajouter-ligne').addEventListener('click', () => {
            const last=tbody.querySelector('tr:last-child');
            if (last) {
                const id=last.querySelector('.article-id')?.value.trim()||'';
                const q=parseNum(last.querySelector('.q-input')?.value);
                if (!id||q<=0) { alert("Complétez la ligne en cours."); last.querySelector('.article-ac')?.focus(); return; }
            }
            addRow();
        });
    }

    lignesInit.length ? lignesInit.forEach(l=>addRow(l)) : addRow();
    updateTotals();
})();
</script>
