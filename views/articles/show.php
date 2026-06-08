<?php
$pageTitle = "Détail article";
include '../views/layout.php';

$article        = $article        ?? [];
$stockDepots    = $stockDepots    ?? [];
$prixHistorique = $prixHistorique ?? [];

$role     = $_SESSION['role'] ?? 'user';
$canSeePR = ($role !== 'user');

$pr  = (float)($article['pr'] ?? 0);
$pd  = (float)($article['prix_detail'] ?? $article['prix_vente'] ?? 0);
$psg = isset($article['prix_semi_gros']) && $article['prix_semi_gros'] !== '' ? (float)$article['prix_semi_gros'] : null;
$pg  = isset($article['prix_gros'])      && $article['prix_gros']      !== '' ? (float)$article['prix_gros']      : null;
$marge = ($pr > 0 && $pd > 0) ? round(($pd - $pr) / $pr * 100, 1) : null;

$statutColors = ['actif'=>'#16A34A','inactif'=>'#D97706','discontinued'=>'#64748B'];
$statutColor  = $statutColors[$article['statut'] ?? 'actif'] ?? '#64748B';
$qt   = (int)($article['quantite_totale'] ?? 0);
$smin = (int)($article['stock_minimal'] ?? 0);
$smax = (int)($article['stock_maximal'] ?? 0);
?>

<style>
:root {
    --art-border:#E4E8F0; --art-primary:#2563EB; --art-primary-light:#EFF6FF;
    --art-success:#16A34A; --art-danger:#DC2626; --art-warning:#D97706;
    --art-text:#1E293B; --art-muted:#64748B; --art-label:#475569;
    --art-radius:10px; --art-shadow:0 1px 4px rgba(0,0,0,.07);
}
.art-page { max-width:980px; margin:0 auto; padding:24px 16px 48px; }
.art-topbar { display:flex; align-items:center; gap:10px; margin-bottom:24px; flex-wrap:wrap; }
.art-back { display:inline-flex; align-items:center; gap:6px; font-size:.85rem; color:var(--art-muted); text-decoration:none; padding:5px 10px; border:1px solid var(--art-border); border-radius:6px; background:#fff; transition:all .15s; }
.art-back:hover { color:var(--art-primary); border-color:var(--art-primary); background:var(--art-primary-light); }
.art-title { font-size:1.25rem; font-weight:700; color:var(--art-text); margin:0; text-transform:uppercase; }
.statut-pill { display:inline-block; padding:3px 10px; border-radius:99px; font-size:.8rem; font-weight:700; color:#fff; }
.art-actions-top { margin-left:auto; display:flex; gap:8px; }
.btn-edit { padding:7px 16px; background:var(--art-primary); color:#fff; border:none; border-radius:7px; font-size:.88rem; font-weight:600; text-decoration:none; display:inline-flex; align-items:center; gap:5px; transition:background .15s; }
.btn-edit:hover { background:#1D4ED8; color:#fff; }

.art-grid { display:grid; grid-template-columns:1fr 300px; gap:20px; align-items:start; }
@media (max-width:768px) { .art-grid { grid-template-columns:1fr; } }

.art-section { background:#fff; border:1px solid var(--art-border); border-radius:var(--art-radius); box-shadow:var(--art-shadow); margin-bottom:16px; overflow:hidden; }
.art-section-head { display:flex; align-items:center; gap:9px; padding:11px 18px; background:#F8FAFD; border-bottom:1px solid var(--art-border); font-size:.8rem; font-weight:700; text-transform:uppercase; letter-spacing:.06em; color:var(--art-label); }
.art-section-head .icon { width:24px; height:24px; border-radius:6px; display:flex; align-items:center; justify-content:center; font-size:.9rem; background:var(--art-primary-light); color:var(--art-primary); }
.art-section-body { padding:16px 18px; }

.info-grid { display:grid; grid-template-columns:1fr 1fr; gap:10px; }
@media (max-width:600px) { .info-grid { grid-template-columns:1fr; } }
.info-card { padding:10px 14px; background:#F8FAFD; border-radius:8px; border:1px solid var(--art-border); }
.info-card-label { font-size:.76rem; color:var(--art-muted); margin-bottom:3px; }
.info-card-value { font-size:.93rem; font-weight:600; color:var(--art-text); }

/* Tarifs — nombre de colonnes selon rôle */
.tarif-grid-admin { display:grid; grid-template-columns:repeat(4,1fr); gap:10px; }
.tarif-grid-user  { display:grid; grid-template-columns:repeat(3,1fr); gap:10px; }
@media (max-width:600px) { .tarif-grid-admin,.tarif-grid-user { grid-template-columns:1fr 1fr; } }
.tarif-card { text-align:center; padding:12px 8px; background:#F8FAFD; border-radius:8px; border:1px solid var(--art-border); }
.tarif-label { font-size:.76rem; color:var(--art-muted); margin-bottom:4px; }
.tarif-value { font-size:1.05rem; font-weight:700; color:var(--art-text); }
.tarif-card.highlight { background:var(--art-primary-light); border-color:#BFDBFE; }
.tarif-card.highlight .tarif-value { color:var(--art-primary); }

.marge-strip { margin-top:12px; padding:9px 14px; border-radius:8px; font-size:.88rem; font-weight:600; display:flex; align-items:center; gap:8px; }
.marge-strip.pos { background:#F0FDF4; color:var(--art-success); border:1px solid #BBF7D0; }
.marge-strip.neg { background:#FEF2F2; color:var(--art-danger); border:1px solid #FECACA; }
.marge-strip.na  { background:#F1F5F9; color:var(--art-muted); border:1px solid var(--art-border); }

.stock-bar-wrap { height:8px; background:#F1F5F9; border-radius:99px; overflow:hidden; margin:4px 0 8px; }
.stock-bar { height:100%; border-radius:99px; }
.depot-table { width:100%; border-collapse:collapse; font-size:.86rem; }
.depot-table th { background:#F8FAFD; font-weight:600; font-size:.76rem; text-transform:uppercase; letter-spacing:.05em; color:var(--art-muted); padding:7px 12px; border-bottom:1px solid var(--art-border); text-align:left; }
.depot-table td { padding:7px 12px; border-bottom:1px solid #F8FAFD; }
.depot-table tr:last-child td { border-bottom:none; }
.depot-table tr:hover td { background:#FAFBFD; }
.hist-table { width:100%; border-collapse:collapse; font-size:.83rem; }
.hist-table th { background:#F8FAFD; font-size:.75rem; text-transform:uppercase; letter-spacing:.05em; color:var(--art-muted); padding:6px 10px; border-bottom:1px solid var(--art-border); text-align:left; }
.hist-table td { padding:6px 10px; border-bottom:1px solid #F8FAFD; }
.hist-table tr:last-child td { border-bottom:none; }
.side-row { display:flex; justify-content:space-between; align-items:center; padding:7px 0; border-bottom:1px solid #F1F5F9; font-size:.85rem; }
.side-row:last-child { border-bottom:none; }
.side-label { color:var(--art-muted); }
.side-val { font-weight:600; }
.alert-stock { background:#FEF9C3; border:1px solid #FDE047; color:#854D0E; border-radius:8px; padding:9px 14px; font-size:.84rem; display:flex; align-items:center; gap:8px; margin-top:10px; }

/* Badge accès restreint */
.restricted-badge { display:inline-flex; align-items:center; gap:5px; padding:6px 12px; background:#F8FAFD; border:1px dashed var(--art-border); border-radius:8px; font-size:.8rem; color:var(--art-muted); }
</style>

<div class="art-page">

    <div class="art-topbar">
        <a href="index.php?action=articles" class="art-back">← Articles</a>
        <h1 class="art-title"><?= htmlspecialchars($article['nom_art'] ?? '') ?></h1>
        <span class="statut-pill" style="background:<?= $statutColor ?>;"><?= ucfirst($article['statut'] ?? 'actif') ?></span>
        <div class="art-actions-top">
            <?php if (function_exists('hasPermission') && hasPermission('articles', 'edit')): ?>
            <a href="index.php?action=articles/edit&id=<?= (int)$article['id_articles'] ?>" class="btn-edit">✎ Modifier</a>
            <?php endif; ?>
        </div>
    </div>

    <div class="art-grid">

        <!-- Colonne principale -->
        <div>

            <!-- Infos générales -->
            <div class="art-section">
                <div class="art-section-head"><span class="icon">📦</span> Informations générales</div>
                <div class="art-section-body">
                    <div class="info-grid">
                        <div class="info-card">
                            <div class="info-card-label">SKU / Référence</div>
                            <div class="info-card-value"><?= htmlspecialchars($article['sku'] ?? '—') ?></div>
                        </div>
                        <div class="info-card">
                            <div class="info-card-label">Unité de mesure</div>
                            <div class="info-card-value"><?= htmlspecialchars($article['unite_mesure'] ?? 'Pièce') ?></div>
                        </div>
                        <div class="info-card">
                            <div class="info-card-label">Fournisseur principal</div>
                            <div class="info-card-value"><?= htmlspecialchars($article['nom_fournisseurs'] ?? '—') ?></div>
                        </div>
                        <?php if (!empty($article['nom_fournisseur_alt'])): ?>
                        <div class="info-card">
                            <div class="info-card-label">Fournisseur alternatif</div>
                            <div class="info-card-value"><?= htmlspecialchars($article['nom_fournisseur_alt']) ?></div>
                        </div>
                        <?php endif; ?>
                        <?php if (!empty($article['nom_categorie'])): ?>
                        <div class="info-card">
                            <div class="info-card-label">Catégorie</div>
                            <div class="info-card-value"><?= htmlspecialchars($article['nom_categorie']) ?></div>
                        </div>
                        <?php endif; ?>
                        <?php if (!empty($article['couleur'])): ?>
                        <div class="info-card">
                            <div class="info-card-label">Couleur</div>
                            <div class="info-card-value"><?= htmlspecialchars($article['couleur']) ?></div>
                        </div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>

            <!-- Tarification -->
            <div class="art-section">
                <div class="art-section-head"><span class="icon">💰</span> Tarification</div>
                <div class="art-section-body">
                    <div class="<?= $canSeePR ? 'tarif-grid-admin' : 'tarif-grid-user' ?>">

                        <?php if ($canSeePR): ?>
                        <!-- Prix de revient — managers/admins uniquement -->
                        <div class="tarif-card highlight">
                            <div class="tarif-label">Prix de revient</div>
                            <div class="tarif-value"><?= $pr ? number_format($pr,0,',',' ') : '—' ?></div>
                        </div>
                        <?php endif; ?>

                        <div class="tarif-card">
                            <div class="tarif-label">Prix détail</div>
                            <div class="tarif-value"><?= $pd ? number_format($pd,0,',',' ') : '—' ?></div>
                        </div>
                        <div class="tarif-card">
                            <div class="tarif-label">Prix semi-gros</div>
                            <div class="tarif-value"><?= $psg !== null ? number_format($psg,0,',',' ') : '—' ?></div>
                        </div>
                        <div class="tarif-card">
                            <div class="tarif-label">Prix gros</div>
                            <div class="tarif-value"><?= $pg !== null ? number_format($pg,0,',',' ') : '—' ?></div>
                        </div>
                    </div>

                    <?php if ($canSeePR): ?>
                        <?php if ($marge !== null): ?>
                        <div class="marge-strip <?= $marge >= 0 ? 'pos' : 'neg' ?>">
                            <?= $marge >= 0 ? '📈' : '📉' ?>
                            Marge sur prix détail : <strong><?= $marge ?>%</strong>
                            (<?= number_format($pd - $pr, 0, ',', ' ') ?> / unité)
                        </div>
                        <?php else: ?>
                        <div class="marge-strip na">ℹ️ Marge non calculable — prix de revient ou prix détail manquant</div>
                        <?php endif; ?>
                    <?php else: ?>
                        <!-- Message pour les users -->
                        <div style="margin-top:10px;">
                            <span class="restricted-badge">🔒 Prix de revient et marge réservés aux managers</span>
                        </div>
                    <?php endif; ?>
                </div>
            </div>

            <!-- Stock par dépôt -->
            <div class="art-section">
                <div class="art-section-head">
                    <span class="icon">🏭</span> Stock par dépôt
                    <span style="margin-left:auto; font-weight:700; color:var(--art-primary); font-size:.9rem;">Total : <?= $qt ?></span>
                </div>
                <div class="art-section-body" style="padding:0;">
                    <?php if (empty($stockDepots)): ?>
                    <p style="text-align:center; color:var(--art-muted); padding:20px 0;">Aucun stock enregistré.</p>
                    <?php else: ?>
                    <table class="depot-table">
                        <thead>
                            <tr>
                                <th>Dépôt</th>
                                <th>Quantité</th>
                                <th>En transit</th>
                                <th>Bloquée</th>
                                <th>Emplacement</th>
                            </tr>
                        </thead>
                        <tbody>
                        <?php foreach ($stockDepots as $sd): ?>
                        <tr>
                            <td>
                                <strong><?= htmlspecialchars($sd['depot_nom']) ?></strong>
                                <?php if (!empty($sd['depot_ville'])): ?>
                                <br><span style="font-size:.76rem; color:var(--art-muted);"><?= htmlspecialchars($sd['depot_ville']) ?></span>
                                <?php endif; ?>
                            </td>
                            <td><strong style="font-size:1rem;"><?= (int)$sd['quantite'] ?></strong></td>
                            <td style="color:var(--art-muted);"><?= (int)$sd['quantite_en_transit'] ?></td>
                            <td style="color:var(--art-muted);"><?= (int)$sd['quantite_bloquee'] ?></td>
                            <td style="font-size:.82rem;"><?= htmlspecialchars($sd['emplacement'] ?? '—') ?></td>
                        </tr>
                        <?php endforeach; ?>
                        </tbody>
                    </table>
                    <?php endif; ?>
                </div>
            </div>

            <!-- Historique prix — managers/admins uniquement -->
            <?php if ($canSeePR && !empty($prixHistorique)): ?>
            <div class="art-section">
                <div class="art-section-head"><span class="icon">📋</span> Historique des prix</div>
                <div class="art-section-body" style="padding:0; overflow-x:auto;">
                    <table class="hist-table">
                        <thead>
                            <tr>
                                <th>Date</th><th>PR avant</th><th>PR après</th>
                                <th>PV avant</th><th>PV après</th><th>Par</th><th>Raison</th>
                            </tr>
                        </thead>
                        <tbody>
                        <?php foreach ($prixHistorique as $h): ?>
                        <tr>
                            <td style="white-space:nowrap;"><?= date('d/m/Y H:i', strtotime($h['changed_at'])) ?></td>
                            <td><?= $h['prix_revient_ancien'] !== null ? number_format($h['prix_revient_ancien'],0,',',' ') : '—' ?></td>
                            <td style="font-weight:600;"><?= $h['prix_revient_nouveau'] !== null ? number_format($h['prix_revient_nouveau'],0,',',' ') : '—' ?></td>
                            <td><?= $h['prix_vente_ancien'] !== null ? number_format($h['prix_vente_ancien'],0,',',' ') : '—' ?></td>
                            <td style="font-weight:600;"><?= $h['prix_vente_nouveau'] !== null ? number_format($h['prix_vente_nouveau'],0,',',' ') : '—' ?></td>
                            <td><?= htmlspecialchars($h['changed_by_name'] ?? '—') ?></td>
                            <td style="color:var(--art-muted); font-size:.82rem;"><?= htmlspecialchars($h['raison'] ?? '—') ?></td>
                        </tr>
                        <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>
            <?php endif; ?>

            <!-- Notes internes -->
            <?php if (!empty($article['notes_internes'])): ?>
            <div class="art-section">
                <div class="art-section-head"><span class="icon">📝</span> Notes internes</div>
                <div class="art-section-body">
                    <p style="margin:0; white-space:pre-line; color:var(--art-text); font-size:.9rem;">
                        <?= htmlspecialchars($article['notes_internes']) ?>
                    </p>
                </div>
            </div>
            <?php endif; ?>

        </div>

        <!-- Colonne latérale -->
        <div>

            <?php if (!empty($article['image_path'])): ?>
            <div class="art-section">
                <div class="art-section-head"><span class="icon">🖼️</span> Image</div>
                <div class="art-section-body" style="text-align:center; padding:14px;">
                    <img src="<?= BASE_URL ?>/public/<?= htmlspecialchars($article['image_path']) ?>"
                         alt="Image article"
                         style="max-height:200px; max-width:100%; border-radius:8px; border:1px solid var(--art-border);">
                </div>
            </div>
            <?php endif; ?>

            <!-- Stock seuils -->
            <div class="art-section">
                <div class="art-section-head"><span class="icon">📊</span> Seuils de stock</div>
                <div class="art-section-body">
                    <div class="side-row">
                        <span class="side-label">Stock actuel</span>
                        <span class="side-val" style="font-size:1.1rem; color:var(--art-primary);"><?= $qt ?></span>
                    </div>
                    <div class="side-row">
                        <span class="side-label">Stock minimal</span>
                        <span class="side-val"><?= $smin ?></span>
                    </div>
                    <div class="side-row">
                        <span class="side-label">Stock maximal</span>
                        <span class="side-val"><?= $smax ?: '—' ?></span>
                    </div>
                    <?php if ($smax > 0): ?>
                    <div class="stock-bar-wrap" style="margin-top:10px;">
                        <?php
                        $pct = min(100, round($qt / $smax * 100));
                        $barColor = $qt < $smin ? '#DC2626' : ($pct < 30 ? '#D97706' : '#16A34A');
                        ?>
                        <div class="stock-bar" style="width:<?= $pct ?>%; background:<?= $barColor ?>;"></div>
                    </div>
                    <div style="font-size:.76rem; color:var(--art-muted); text-align:right;"><?= $pct ?>% du max</div>
                    <?php endif; ?>
                    <?php if ($smin > 0 && $qt < $smin): ?>
                    <div class="alert-stock">⚠️ Stock sous le minimum ! (<?= $qt ?> / <?= $smin ?>)</div>
                    <?php endif; ?>
                </div>
            </div>

            <!-- Dimensions -->
            <?php if (!empty($article['poids_kg']) || !empty($article['longueur_cm'])): ?>
            <div class="art-section">
                <div class="art-section-head"><span class="icon">📐</span> Dimensions</div>
                <div class="art-section-body">
                    <?php if (!empty($article['poids_kg'])): ?>
                    <div class="side-row"><span class="side-label">Poids</span><span class="side-val"><?= htmlspecialchars($article['poids_kg']) ?> kg</span></div>
                    <?php endif; ?>
                    <?php if (!empty($article['longueur_cm'])): ?>
                    <div class="side-row">
                        <span class="side-label">L × l × H</span>
                        <span class="side-val" style="font-size:.82rem;">
                            <?= htmlspecialchars($article['longueur_cm'] ?? '—') ?> ×
                            <?= htmlspecialchars($article['largeur_cm']  ?? '—') ?> ×
                            <?= htmlspecialchars($article['hauteur_cm']  ?? '—') ?> cm
                        </span>
                    </div>
                    <?php endif; ?>
                </div>
            </div>
            <?php endif; ?>

            <!-- Traçabilité -->
            <div class="art-section">
                <div class="art-section-head"><span class="icon">🕒</span> Traçabilité</div>
                <div class="art-section-body">
                    <?php if (!empty($article['created_by_name'])): ?>
                    <div class="side-row"><span class="side-label">Créé par</span><span class="side-val"><?= htmlspecialchars($article['created_by_name']) ?></span></div>
                    <?php endif; ?>
                    <?php if (!empty($article['created_at'])): ?>
                    <div class="side-row"><span class="side-label">Créé le</span><span><?= date('d/m/Y', strtotime($article['created_at'])) ?></span></div>
                    <?php endif; ?>
                    <?php if (!empty($article['updated_by_name'])): ?>
                    <div class="side-row"><span class="side-label">Modifié par</span><span class="side-val"><?= htmlspecialchars($article['updated_by_name']) ?></span></div>
                    <?php endif; ?>
                    <?php if (!empty($article['updated_at'])): ?>
                    <div class="side-row"><span class="side-label">Modifié le</span><span><?= date('d/m/Y H:i', strtotime($article['updated_at'])) ?></span></div>
                    <?php endif; ?>
                </div>
            </div>

        </div>
    </div>
</div>
