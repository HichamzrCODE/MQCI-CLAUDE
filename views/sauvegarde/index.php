<?php
$pageTitle = "Sauvegardes";
include '../views/layout.php';
$backups    = $backups    ?? [];
$lastBackup = $lastBackup ?? null;
$dbSize     = $dbSize     ?? 'N/A';
$diskSpace  = $diskSpace  ?? [];
?>

<style>
:root {
    --sv-border:#E4E8F0; --sv-primary:#2563EB; --sv-primary-light:#EFF6FF;
    --sv-success:#16A34A; --sv-danger:#DC2626; --sv-warning:#D97706;
    --sv-text:#1E293B; --sv-muted:#64748B;
    --sv-radius:10px; --sv-shadow:0 1px 4px rgba(0,0,0,.07);
}
.sv-page { max-width:900px; margin:0 auto; padding:24px 16px 48px; }
.sv-topbar { display:flex; align-items:center; gap:10px; margin-bottom:24px; flex-wrap:wrap; }
.sv-title  { font-size:1.2rem; font-weight:700; color:var(--sv-text); margin:0; }

/* Stats */
.sv-stats { display:grid; grid-template-columns:repeat(3,1fr); gap:14px; margin-bottom:24px; }
@media(max-width:640px){ .sv-stats{ grid-template-columns:1fr; } }
.sv-stat { background:#fff; border:1px solid var(--sv-border); border-radius:var(--sv-radius); padding:14px 18px; box-shadow:var(--sv-shadow); }
.sv-stat-label { font-size:.76rem; color:var(--sv-muted); text-transform:uppercase; letter-spacing:.05em; margin-bottom:5px; }
.sv-stat-val   { font-size:1.15rem; font-weight:700; color:var(--sv-text); }

/* Barre disque */
.disk-bar-wrap { height:6px; background:#F1F5F9; border-radius:99px; margin-top:8px; overflow:hidden; }
.disk-bar      { height:100%; border-radius:99px; transition:width .4s; }

/* Bouton principal */
.btn-backup {
    display:inline-flex; align-items:center; gap:8px;
    padding:12px 24px; border-radius:8px; font-size:.95rem; font-weight:700;
    background:var(--sv-primary); color:#fff; border:none; cursor:pointer;
    transition:all .15s; margin-bottom:20px;
}
.btn-backup:hover { background:#1D4ED8; transform:translateY(-1px); }
.btn-backup:disabled { opacity:.6; cursor:default; transform:none; }

/* Card liste */
.sv-card { background:#fff; border:1px solid var(--sv-border); border-radius:var(--sv-radius); box-shadow:var(--sv-shadow); overflow:hidden; }
.sv-card-head { display:flex; align-items:center; justify-content:space-between; padding:12px 18px; background:#F8FAFD; border-bottom:1px solid var(--sv-border); font-size:.8rem; font-weight:700; text-transform:uppercase; letter-spacing:.06em; color:var(--sv-muted); }
.sv-table { width:100%; border-collapse:collapse; font-size:.88rem; }
.sv-table thead th { background:#F8FAFD; color:var(--sv-muted); font-size:.75rem; font-weight:700; text-transform:uppercase; letter-spacing:.06em; padding:9px 14px; border-bottom:1px solid var(--sv-border); text-align:left; white-space:nowrap; }
.sv-table tbody tr { border-bottom:1px solid #F1F5F9; transition:background .1s; }
.sv-table tbody tr:last-child { border-bottom:none; }
.sv-table tbody tr:hover td { background:#FAFBFD; }
.sv-table td { padding:10px 14px; vertical-align:middle; }

.btn-dl  { display:inline-flex; align-items:center; gap:4px; padding:5px 12px; border-radius:6px; font-size:.82rem; font-weight:600; text-decoration:none; background:var(--sv-primary-light); color:var(--sv-primary); border:1px solid #BFDBFE; cursor:pointer; transition:all .12s; }
.btn-dl:hover  { background:var(--sv-primary); color:#fff; }
.btn-del { display:inline-flex; align-items:center; gap:4px; padding:5px 12px; border-radius:6px; font-size:.82rem; font-weight:600; background:#FEF2F2; color:var(--sv-danger); border:1px solid #FECACA; cursor:pointer; transition:all .12s; }
.btn-del:hover { background:var(--sv-danger); color:#fff; }

.sv-empty { padding:40px 0; text-align:center; color:var(--sv-muted); }
.sv-empty-icon { font-size:2.5rem; margin-bottom:10px; }

/* Toast */
#sv-toast {
    display:none; position:fixed; bottom:24px; right:24px; z-index:9999;
    padding:12px 20px; border-radius:10px; font-size:.9rem; font-weight:600;
    box-shadow:0 4px 16px rgba(0,0,0,.18); min-width:260px;
}
#sv-toast.success { background:#DCFCE7; color:#15803D; border:1px solid #BBF7D0; }
#sv-toast.error   { background:#FEF2F2; color:var(--sv-danger); border:1px solid #FECACA; }

/* Spinner */
.spin { display:inline-block; animation:spin .8s linear infinite; }
@keyframes spin { to{ transform:rotate(360deg); } }
</style>

<div id="sv-toast"></div>

<div class="sv-page">

    <div class="sv-topbar">
        <h1 class="sv-title">🗄️ Sauvegardes</h1>
    </div>

    <!-- Stats -->
    <div class="sv-stats">
        <div class="sv-stat">
            <div class="sv-stat-label">Taille de la base</div>
            <div class="sv-stat-val"><?= htmlspecialchars($dbSize) ?></div>
        </div>
        <div class="sv-stat">
            <div class="sv-stat-label">Dernière sauvegarde</div>
            <div class="sv-stat-val" style="font-size:.95rem;">
                <?= $lastBackup ? htmlspecialchars($lastBackup['date']) : '<span style="color:var(--sv-warning);">Aucune</span>' ?>
            </div>
        </div>
        <div class="sv-stat">
            <div class="sv-stat-label">Espace disque</div>
            <div class="sv-stat-val" style="font-size:.9rem;">
                <?= htmlspecialchars($diskSpace['used'] ?? '—') ?> / <?= htmlspecialchars($diskSpace['total'] ?? '—') ?>
            </div>
            <?php if (!empty($diskSpace['percent'])): ?>
            <div class="disk-bar-wrap">
                <?php $pct = (float)$diskSpace['percent']; ?>
                <div class="disk-bar" style="width:<?= $pct ?>%;background:<?= $pct > 80 ? 'var(--sv-danger)' : ($pct > 60 ? 'var(--sv-warning)' : 'var(--sv-success)') ?>;"></div>
            </div>
            <div style="font-size:.74rem;color:var(--sv-muted);margin-top:4px;"><?= $pct ?>% utilisé — <?= htmlspecialchars($diskSpace['free']) ?> libre</div>
            <?php endif; ?>
        </div>
    </div>

    <!-- Bouton sauvegarde -->
    <button class="btn-backup" id="btn-backup">
        💾 Créer une sauvegarde maintenant
    </button>

    <!-- Info -->
    <div style="background:#EFF6FF; border:1px solid #BFDBFE; border-radius:8px; padding:12px 16px; margin-bottom:20px; font-size:.85rem; color:#1D4ED8;">
        ℹ️ Les sauvegardes sont au format <strong>.sql</strong> — importables sur n'importe quel serveur via <strong>phpMyAdmin</strong> ou ligne de commande (<code>mysql -u root nom_bdd &lt; fichier.sql</code>).
    </div>

    <!-- Liste -->
    <div class="sv-card">
        <div class="sv-card-head">
            <span>Sauvegardes disponibles</span>
            <span style="background:#E0E7FF;color:var(--sv-primary);padding:2px 10px;border-radius:99px;font-size:.78rem;font-weight:700;">
                <?= count($backups) ?> fichier<?= count($backups) > 1 ? 's' : '' ?>
            </span>
        </div>

        <?php if (empty($backups)): ?>
        <div class="sv-empty">
            <div class="sv-empty-icon">📂</div>
            Aucune sauvegarde — créez-en une maintenant.
        </div>
        <?php else: ?>
        <div style="overflow-x:auto;">
            <table class="sv-table" id="sv-table">
                <thead>
                    <tr>
                        <th>Fichier</th>
                        <th style="width:90px; text-align:right;">Taille</th>
                        <th style="width:160px;">Date</th>
                        <th style="width:140px; text-align:center;">Actions</th>
                    </tr>
                </thead>
                <tbody>
                <?php foreach ($backups as $b): ?>
                <tr id="row-<?= md5($b['filename']) ?>">
                    <td>
                        <span style="font-family:monospace; font-size:.84rem;">📄 <?= htmlspecialchars($b['filename']) ?></span>
                    </td>
                    <td style="text-align:right; color:var(--sv-muted);"><?= htmlspecialchars($b['size']) ?></td>
                    <td style="font-size:.84rem; color:var(--sv-muted);"><?= htmlspecialchars($b['date']) ?></td>
                    <td style="text-align:center;">
                        <div style="display:flex; gap:6px; justify-content:center;">
                            <a href="index.php?action=sauvegarde/download&file=<?= urlencode($b['filename']) ?>"
                               class="btn-dl" title="Télécharger">⬇ DL</a>
                            <button class="btn-del"
                                    onclick="deleteBk('<?= htmlspecialchars($b['filename'], ENT_QUOTES) ?>')"
                                    title="Supprimer">🗑</button>
                        </div>
                    </td>
                </tr>
                <?php endforeach; ?>
                </tbody>
            </table>
        </div>
        <?php endif; ?>
    </div>

    <!-- Instructions restauration -->
    <div style="background:#F8FAFD; border:1px solid var(--sv-border); border-radius:8px; padding:14px 18px; margin-top:16px; font-size:.85rem; color:var(--sv-muted);">
        <strong style="color:var(--sv-text);">Comment restaurer une sauvegarde ?</strong><br>
        1. Téléchargez le fichier <code>.sql</code><br>
        2. Ouvrez <strong>phpMyAdmin</strong> → sélectionnez votre base → onglet <strong>Importer</strong><br>
        3. Choisissez le fichier → cliquez <strong>Importer</strong><br>
        <em>Ou en ligne de commande :</em> <code>mysql -u root mqci &lt; backup_xxx.sql</code>
    </div>
</div>

<script>
(function () {
    const btnBackup = document.getElementById('btn-backup');

    function toast(msg, type = 'success') {
        const el = document.getElementById('sv-toast');
        el.textContent = (type === 'success' ? '✅ ' : '❌ ') + msg;
        el.className   = type;
        el.style.display = 'block';
        setTimeout(() => el.style.display = 'none', 4000);
    }

    // ── Créer une sauvegarde ──────────────────────────────────────
    btnBackup.addEventListener('click', function () {
        if (!confirm('Créer une nouvelle sauvegarde maintenant ?')) return;

        btnBackup.disabled   = true;
        btnBackup.innerHTML  = '<span class="spin">⏳</span> En cours...';

        fetch('index.php?action=sauvegarde/backup', { method: 'POST' })
            .then(r => r.json())
            .then(data => {
                if (data.success) {
                    toast('Sauvegarde créée : ' + data.filename);
                    setTimeout(() => location.reload(), 1500);
                } else {
                    toast(data.message || 'Erreur.', 'error');
                    btnBackup.disabled  = false;
                    btnBackup.innerHTML = '💾 Créer une sauvegarde maintenant';
                }
            })
            .catch(() => {
                toast('Erreur réseau.', 'error');
                btnBackup.disabled  = false;
                btnBackup.innerHTML = '💾 Créer une sauvegarde maintenant';
            });
    });

    // ── Supprimer une sauvegarde ──────────────────────────────────
    window.deleteBk = function (filename) {
        if (!confirm('Supprimer définitivement "' + filename + '" ?')) return;

        fetch('index.php?action=sauvegarde/delete&file=' + encodeURIComponent(filename), { method: 'POST' })
            .then(r => r.json())
            .then(data => {
                if (data.success) {
                    toast('Sauvegarde supprimée.');
                    const row = document.getElementById('row-' + md5(filename));
                    if (row) row.remove();
                    // Recharger si plus de lignes
                    if (!document.querySelector('#sv-table tbody tr')) location.reload();
                } else {
                    toast(data.message || 'Erreur.', 'error');
                }
            })
            .catch(() => toast('Erreur réseau.', 'error'));
    };

    // md5 simple (pour les IDs de ligne) — on utilise juste un hash côté PHP
    // ici on recharge simplement après suppression
})();
</script>
