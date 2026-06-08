<?php include '../views/layout.php'; ?>

<?php
$isValidated = (($facture['statut'] ?? 'draft') === 'validated');
$isAdmin     = (($_SESSION['role'] ?? '') === 'admin');
?>

<style>
:root {
    --ff-border:#E4E8F0; --ff-primary:#2563EB; --ff-primary-light:#EFF6FF;
    --ff-success:#16A34A; --ff-danger:#DC2626; --ff-warning:#D97706;
    --ff-text:#1E293B; --ff-muted:#64748B; --ff-label:#475569;
    --ff-radius:10px; --ff-shadow:0 1px 4px rgba(0,0,0,.07);
}
.ff-page { max-width:980px; margin:0 auto; padding:24px 16px 48px; }
.ff-topbar { display:flex; align-items:center; gap:10px; margin-bottom:24px; flex-wrap:wrap; }
.ff-title { font-size:1.15rem; font-weight:700; color:var(--ff-text); margin:0; }
.ff-topbar-actions { margin-left:auto; display:flex; gap:8px; }
.btn-ff { display:inline-flex; align-items:center; gap:5px; padding:7px 16px; border-radius:7px; font-size:.85rem; font-weight:600; text-decoration:none; border:none; cursor:pointer; transition:all .15s; }
.btn-ff-primary  { background:var(--ff-primary); color:#fff; }
.btn-ff-primary:hover  { background:#1D4ED8; color:#fff; }
.btn-ff-warning  { background:#D97706; color:#fff; }
.btn-ff-warning:hover  { background:#B45309; color:#fff; }
.btn-ff-secondary { background:#fff; color:var(--ff-muted); border:1.5px solid var(--ff-border); }
.btn-ff-secondary:hover { color:var(--ff-primary); border-color:var(--ff-primary); background:var(--ff-primary-light); }
.btn-ff-print { background:#fff; color:var(--ff-muted); border:1.5px solid var(--ff-border); }
.btn-ff-print:hover { background:#F8FAFD; }

.badge-validated { background:#DCFCE7; color:#15803D; padding:3px 10px; border-radius:99px; font-size:.78rem; font-weight:700; }
.badge-draft     { background:#FEF9C3; color:#854D0E; padding:3px 10px; border-radius:99px; font-size:.78rem; font-weight:700; }

.ff-grid { display:grid; grid-template-columns:1fr 1fr; gap:20px; margin-bottom:16px; align-items:start; }
@media (max-width:640px) { .ff-grid { grid-template-columns:1fr; } }

.ff-card { background:#fff; border:1px solid var(--ff-border); border-radius:var(--ff-radius); box-shadow:var(--ff-shadow); margin-bottom:16px; overflow:hidden; }
.ff-card-head { display:flex; align-items:center; gap:8px; padding:11px 18px; background:#F8FAFD; border-bottom:1px solid var(--ff-border); font-size:.8rem; font-weight:700; text-transform:uppercase; letter-spacing:.06em; color:var(--ff-label); }
.ff-card-head .icon { width:24px; height:24px; border-radius:6px; display:flex; align-items:center; justify-content:center; font-size:.9rem; background:var(--ff-primary-light); color:var(--ff-primary); }
.ff-card-body { padding:16px 18px; }

/* Infos */
.info-grid { display:grid; grid-template-columns:1fr 1fr; gap:10px; }
@media (max-width:500px) { .info-grid { grid-template-columns:1fr; } }
.info-card { padding:10px 14px; background:#F8FAFD; border-radius:8px; border:1px solid var(--ff-border); }
.info-label { font-size:.75rem; color:var(--ff-muted); margin-bottom:3px; }
.info-value { font-size:.92rem; font-weight:600; color:var(--ff-text); }

/* Totaux */
.totaux-grid { display:grid; grid-template-columns:repeat(3,1fr); gap:10px; }
@media (max-width:500px) { .totaux-grid { grid-template-columns:1fr; } }
.total-card { text-align:center; padding:12px 8px; background:#F8FAFD; border-radius:8px; border:1px solid var(--ff-border); }
.total-card.main { background:var(--ff-primary-light); border-color:#BFDBFE; }
.total-label { font-size:.74rem; color:var(--ff-muted); margin-bottom:4px; }
.total-val { font-size:1.05rem; font-weight:700; color:var(--ff-text); }
.total-card.main .total-val { color:var(--ff-primary); font-size:1.15rem; }

/* Table lignes */
.ff-table { width:100%; border-collapse:collapse; font-size:.87rem; }
.ff-table thead th { background:#F8FAFD; color:var(--ff-muted); font-size:.75rem; font-weight:700; text-transform:uppercase; letter-spacing:.06em; padding:8px 14px; border-bottom:1px solid var(--ff-border); text-align:left; white-space:nowrap; }
.ff-table tbody tr { border-bottom:1px solid #F1F5F9; transition:background .1s; }
.ff-table tbody tr:last-child { border-bottom:none; }
.ff-table tbody tr:hover td { background:#FAFBFD; }
.ff-table td { padding:9px 14px; vertical-align:middle; color:var(--ff-text); }
.ff-table tfoot td { background:#F8FAFD; font-weight:700; padding:10px 14px; border-top:2px solid var(--ff-border); font-size:.95rem; }

/* CMP info */
.cmp-info { background:#FFF7ED; border:1px solid #FDE68A; border-radius:8px; padding:11px 16px; font-size:.85rem; color:#92400E; display:flex; align-items:center; gap:8px; margin-bottom:16px; }


@media print {
    .no-print { display:none !important; }
    .ff-topbar-actions { display:none; }
    .ff-page { padding:0; }
    .ff-card { box-shadow:none; border:1px solid #ddd; }
    
    /* AMÉLIORATION IMPRESSION */
    body { background:#fff; margin:0; padding:0; }
    .ff-page { max-width:100%; margin:0; padding:20px; }
    .ff-grid { grid-template-columns:1fr; gap:15px; }
    .ff-card-head { background:#f5f5f5; border-bottom:2px solid #333; }
    .ff-card { page-break-inside:avoid; margin-bottom:20px; }
    .ff-table { font-size:.9rem; }
    .ff-table thead th { background:#f5f5f5; border:1px solid #999; }
    .ff-table td { border:1px solid #ddd; padding:8px; }
    .info-card { background:#fff; border:1px solid #ccc; }
    .total-card { background:#fff; border:1px solid #ccc; }
    .total-card.main { background:#f0f0f0; border:1px solid #999; }
    .cmp-info { display:none; }
    
    /* TITRE IMPRESSIF */
    .ff-title { font-size:1.4rem; margin-bottom:15px; }
    .badge-validated, .badge-draft { font-size:.9rem; padding:5px 12px; }
    
    /* MASQUER TOTAUX ET DEPOT */
    .ff-grid > .ff-card:nth-child(2) { display:none; }
    .info-card:nth-child(4) { display:none; }

 
}
</style>

<div class="ff-page">

    <!-- Topbar -->
    <div class="ff-topbar no-print">
        <a href="index.php?action=factures_fournisseurs" class="btn-ff btn-ff-secondary">← Liste</a>
        <h1 class="ff-title">Facture fournisseur</h1>
        <span class="<?= $isValidated ? 'badge-validated' : 'badge-draft' ?>">
            <?= $isValidated ? '✅ Validée' : '⏳ Brouillon' ?>
        </span>
        <div class="ff-topbar-actions">
            <a href="index.php?action=factures_fournisseurs/edit&id=<?= (int)$facture['id'] ?>"
               class="btn-ff btn-ff-primary">✎ Modifier</a>
            <?php if (!$isValidated): ?>
            <a href="index.php?action=factures_fournisseurs/validate&id=<?= (int)$facture['id'] ?>"
               class="btn-ff btn-ff-warning"
               onclick="return confirm('Valider cette facture ? Le stock sera incrémenté et le CMP recalculé.');">
                ✔ Valider
            </a>
            <?php endif; ?>
            <button class="btn-ff btn-ff-print" onclick="window.print()">🖨️ Imprimer</button>
        </div>
    </div>

    <?php if ($isValidated): ?>
    <div class="cmp-info no-print">
        📊 Cette facture est validée — le <strong>Coût Moyen Pondéré (CMP)</strong> des articles a été recalculé automatiquement à la validation.
    </div>
    <?php endif; ?>

    <div class="ff-grid">

        <!-- Informations -->
        <div class="ff-card">
            <div class="ff-card-head"><span class="icon">📋</span> Informations</div>
            <div class="ff-card-body">
                <div class="info-grid">
                    <div class="info-card">
                        <div class="info-label">Numéro</div>
                        <div class="info-value" style="font-family:monospace;"><?= htmlspecialchars($facture['numero']) ?></div>
                    </div>
                    <div class="info-card">
                        <div class="info-label">Date</div>
                        <div class="info-value"><?= date('d/m/Y', strtotime($facture['date'])) ?></div>
                    </div>
                    <div class="info-card">
                        <div class="info-label">Fournisseur</div>
                        <div class="info-value"><?= htmlspecialchars($facture['nom_fournisseurs']) ?></div>
                    </div>
                    <div class="info-card">
                        <div class="info-label">Dépôt</div>
                        <div class="info-value"><?= htmlspecialchars($facture['depot_nom']) ?></div>
                    </div>
                </div>
                <?php if (!empty($facture['notes'])): ?>
                <div style="margin-top:12px; font-size:.85rem; color:var(--ff-muted);">
                    <strong style="color:var(--ff-label);">Notes :</strong><br>
                    <?= nl2br(htmlspecialchars($facture['notes'])) ?>
                </div>
                <?php endif; ?>
            </div>
        </div>

        <!-- Totaux -->
        <div class="ff-card">
            <div class="ff-card-head"><span class="icon">💰</span> Totaux</div>
            <div class="ff-card-body">
                <div class="totaux-grid">
                    <div class="total-card">
                        <div class="total-label">Total HT</div>
                        <div class="total-val"><?= number_format((float)$facture['total_ht'], 0, ',', ' ') ?></div>
                    </div>
                    <div class="total-card">
                        <div class="total-label">TVA</div>
                        <div class="total-val"><?= number_format((float)$facture['total_tva'], 0, ',', ' ') ?></div>
                    </div>
                    <div class="total-card main">
                        <div class="total-label">Total TTC</div>
                        <div class="total-val"><?= number_format((float)$facture['total_ttc'], 0, ',', ' ') ?></div>
                    </div>
                </div>

                <?php if ($isValidated): ?>
                <div style="margin-top:14px; font-size:.82rem; color:var(--ff-muted);">
                    <div style="display:flex; justify-content:space-between; padding:4px 0; border-bottom:1px solid #F1F5F9;">
                        <span>Validée par</span>
                        <span style="font-weight:600;"><?= htmlspecialchars($facture['validated_by'] ?? '—') ?></span>
                    </div>
                    <div style="display:flex; justify-content:space-between; padding:4px 0;">
                        <span>Validée le</span>
                        <span style="font-weight:600;"><?= !empty($facture['validated_at']) ? date('d/m/Y H:i', strtotime($facture['validated_at'])) : '—' ?></span>
                    </div>
                </div>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <!-- Lignes articles -->
    <div class="ff-card">
        <div class="ff-card-head"><span class="icon">📦</span> Articles
            <span style="margin-left:auto; font-weight:400; font-size:.8rem; color:var(--ff-muted);">
                <?= count($lignes) ?> article<?= count($lignes) > 1 ? 's' : '' ?>
            </span>
        </div>
        <div style="overflow-x:auto;">
            <table class="ff-table">
                <thead>
                    <tr>
                        <th style="width:40px;">#</th>
                        <th>Article</th>
                        <th>Description</th>
                        <th style="width:100px; text-align:center;">Quantité</th>
                        <th style="width:130px; text-align:right;">PU Achat</th>
                        <th style="width:130px; text-align:right;">Total</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($lignes as $i => $ln): ?>
                    <tr>
                        <td style="color:var(--ff-muted); font-size:.82rem;"><?= $i + 1 ?></td>
                        <td>
                            <strong><?= htmlspecialchars($ln['nom_art'] ?? '') ?></strong>
                            <?php if (!empty($ln['sku'])): ?>
                            <br><span style="font-size:.75rem; color:var(--ff-muted); font-family:monospace;"><?= htmlspecialchars($ln['sku']) ?></span>
                            <?php endif; ?>
                        </td>
                        <td style="color:var(--ff-muted); font-size:.84rem;"><?= htmlspecialchars($ln['description'] ?? '') ?></td>
                        <td style="text-align:center; font-weight:600;"><?= (int)$ln['quantite'] ?></td>
                        <td style="text-align:right;"><?= number_format((float)$ln['prix_unitaire'], 0, ',', ' ') ?></td>
                        <td style="text-align:right; font-weight:700;"><?= number_format((float)$ln['total'], 0, ',', ' ') ?></td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
                <tfoot>
                    <tr>
                        <td colspan="5" style="text-align:right; color:var(--ff-label);">Total général</td>
                        <td style="text-align:right; color:var(--ff-primary); font-size:1.05rem;">
                            <?= number_format((float)$facture['total_ttc'], 0, ',', ' ') ?>
                        </td>
                    </tr>
                </tfoot>
            </table>
        </div>
    </div>

</div>

<?php include '../views/footer.php'; ?>
