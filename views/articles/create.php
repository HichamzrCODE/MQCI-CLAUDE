<?php include '../views/layout.php'; ?>

<style>
/* ─── Variables ───────────────────────────────────────────────── */
:root {
    --art-bg:       #F7F8FC;
    --art-card:     #FFFFFF;
    --art-border:   #E4E8F0;
    --art-primary:  #2563EB;
    --art-primary-light: #EFF6FF;
    --art-success:  #16A34A;
    --art-danger:   #DC2626;
    --art-text:     #1E293B;
    --art-muted:    #64748B;
    --art-label:    #475569;
    --art-radius:   10px;
    --art-shadow:   0 1px 4px rgba(0,0,0,.07);
}

/* ─── Layout ──────────────────────────────────────────────────── */
.art-page { max-width: 980px; margin: 0 auto; padding: 24px 16px 48px; }

.art-topbar {
    display: flex;
    align-items: center;
    gap: 14px;
    margin-bottom: 28px;
}
.art-back {
    display: inline-flex;
    align-items: center;
    gap: 6px;
    font-size: .85rem;
    color: var(--art-muted);
    text-decoration: none;
    padding: 5px 10px;
    border: 1px solid var(--art-border);
    border-radius: 6px;
    background: #fff;
    transition: all .15s;
}
.art-back:hover { color: var(--art-primary); border-color: var(--art-primary); background: var(--art-primary-light); }
.art-title { font-size: 1.25rem; font-weight: 700; color: var(--art-text); margin: 0; }

/* ─── Grid 2 colonnes ─────────────────────────────────────────── */
.art-grid {
    display: grid;
    grid-template-columns: 1fr 340px;
    gap: 20px;
    align-items: start;
}
@media (max-width: 768px) { .art-grid { grid-template-columns: 1fr; } }

/* ─── Section card ────────────────────────────────────────────── */
.art-section {
    background: var(--art-card);
    border: 1px solid var(--art-border);
    border-radius: var(--art-radius);
    box-shadow: var(--art-shadow);
    margin-bottom: 16px;
    overflow: hidden;
}
.art-section-head {
    display: flex;
    align-items: center;
    gap: 9px;
    padding: 12px 18px;
    background: #F8FAFD;
    border-bottom: 1px solid var(--art-border);
    font-size: .82rem;
    font-weight: 700;
    text-transform: uppercase;
    letter-spacing: .06em;
    color: var(--art-label);
}
.art-section-head .icon {
    width: 26px; height: 26px;
    border-radius: 6px;
    display: flex; align-items: center; justify-content: center;
    font-size: .95rem;
    background: var(--art-primary-light);
    color: var(--art-primary);
}
.art-section-body { padding: 18px; }

/* ─── Champs ──────────────────────────────────────────────────── */
.art-field { margin-bottom: 14px; }
.art-field:last-child { margin-bottom: 0; }
.art-label {
    display: block;
    font-size: .8rem;
    font-weight: 600;
    color: var(--art-label);
    margin-bottom: 5px;
    letter-spacing: .02em;
}
.art-label .req { color: var(--art-danger); margin-left: 2px; }
.art-input, .art-select, .art-textarea {
    width: 100%;
    padding: 8px 11px;
    font-size: .9rem;
    color: var(--art-text);
    background: #fff;
    border: 1.5px solid var(--art-border);
    border-radius: 7px;
    transition: border-color .15s, box-shadow .15s;
    outline: none;
}
.art-input:focus, .art-select:focus, .art-textarea:focus {
    border-color: var(--art-primary);
    box-shadow: 0 0 0 3px rgba(37,99,235,.1);
}
.art-hint { font-size: .76rem; color: var(--art-muted); margin-top: 4px; }
.art-row { display: grid; gap: 12px; }
.art-row.col2 { grid-template-columns: 1fr 1fr; }
.art-row.col3 { grid-template-columns: 1fr 1fr 1fr; }
.art-row.col4 { grid-template-columns: 1fr 1fr 1fr 1fr; }
@media (max-width: 600px) {
    .art-row.col2, .art-row.col3, .art-row.col4 { grid-template-columns: 1fr; }
}

/* ─── Prix badge marge ────────────────────────────────────────── */
.marge-badge {
    padding: 8px 12px;
    border-radius: 7px;
    font-size: .88rem;
    font-weight: 700;
    background: #F1F5F9;
    color: var(--art-muted);
    border: 1.5px solid var(--art-border);
    display: flex;
    align-items: center;
    gap: 6px;
    min-height: 38px;
}
.marge-badge.pos { background: #F0FDF4; color: var(--art-success); border-color: #BBF7D0; }
.marge-badge.neg { background: #FEF2F2; color: var(--art-danger); border-color: #FECACA; }

/* ─── Table stock dépôt ───────────────────────────────────────── */
.depot-table { width: 100%; border-collapse: collapse; font-size: .87rem; }
.depot-table th {
    background: #F8FAFD;
    font-weight: 600;
    font-size: .78rem;
    text-transform: uppercase;
    letter-spacing: .05em;
    color: var(--art-muted);
    padding: 7px 10px;
    border-bottom: 1px solid var(--art-border);
    text-align: left;
}
.depot-table td { padding: 7px 10px; border-bottom: 1px solid #F1F5F9; }
.depot-table tr:last-child td { border-bottom: none; }
.depot-table input { border-radius: 6px; font-size: .86rem; }

/* ─── Upload image ────────────────────────────────────────────── */
.img-drop {
    border: 2px dashed var(--art-border);
    border-radius: 8px;
    padding: 22px;
    text-align: center;
    cursor: pointer;
    transition: all .15s;
    position: relative;
}
.img-drop:hover, .img-drop.drag { border-color: var(--art-primary); background: var(--art-primary-light); }
.img-drop input[type=file] { position: absolute; inset: 0; opacity: 0; cursor: pointer; width: 100%; }
.img-drop-icon { font-size: 1.8rem; margin-bottom: 6px; }
.img-drop-text { font-size: .82rem; color: var(--art-muted); }
.img-preview { display: none; max-height: 140px; border-radius: 6px; margin-top: 8px; }

/* ─── Actions ─────────────────────────────────────────────────── */
.art-actions {
    display: flex;
    gap: 10px;
    padding: 16px 18px;
    background: #F8FAFD;
    border-top: 1px solid var(--art-border);
}
.btn-create {
    padding: 9px 22px;
    background: var(--art-primary);
    color: #fff;
    border: none;
    border-radius: 7px;
    font-size: .9rem;
    font-weight: 600;
    cursor: pointer;
    transition: background .15s, transform .1s;
}
.btn-create:hover { background: #1D4ED8; transform: translateY(-1px); }
.btn-cancel {
    padding: 9px 18px;
    background: #fff;
    color: var(--art-muted);
    border: 1.5px solid var(--art-border);
    border-radius: 7px;
    font-size: .9rem;
    text-decoration: none;
    transition: all .15s;
    display: inline-flex;
    align-items: center;
}
.btn-cancel:hover { color: var(--art-danger); border-color: var(--art-danger); }

/* ─── Alerte ──────────────────────────────────────────────────── */
.art-alert {
    background: #FEF2F2;
    border: 1px solid #FECACA;
    color: var(--art-danger);
    border-radius: 8px;
    padding: 11px 16px;
    margin-bottom: 18px;
    font-size: .88rem;
    display: flex;
    align-items: center;
    gap: 8px;
}

/* ─── Info panel ──────────────────────────────────────────────── */
.info-pill {
    display: flex;
    align-items: center;
    gap: 8px;
    padding: 9px 12px;
    border-radius: 8px;
    margin-bottom: 8px;
    font-size: .84rem;
    font-weight: 500;
}
.pill-user  { background:#EFF6FF; color:#1D4ED8; }
.pill-mgr   { background:#FFF7ED; color:#C2410C; }
.pill-admin { background:#FEF2F2; color:#991B1B; }
</style>

<div class="art-page">

    <!-- Topbar -->
    <div class="art-topbar">
        <a href="index.php?action=articles" class="art-back">← Articles</a>
        <h1 class="art-title">Nouvel article</h1>
    </div>

    <?php if (!empty($error)): ?>
    <div class="art-alert">⚠️ <?= htmlspecialchars($error) ?></div>
    <?php endif; ?>

    <form method="post" action="index.php?action=articles/create" enctype="multipart/form-data">
        <?= $csrf_field ?? '' ?>

        <div class="art-grid">

            <!-- ── Colonne principale ─────────────────────────────── -->
            <div>

                <!-- Identification -->
                <div class="art-section">
                    <div class="art-section-head">
                        <span class="icon">📦</span> Identification
                    </div>
                    <div class="art-section-body">
                        <div class="art-row col2">
                            <div class="art-field">
                                <label class="art-label">Nom de l'article <span class="req">*</span></label>
                                <input type="text" class="art-input" name="nom_art"
                                       value="<?= htmlspecialchars($_POST['nom_art'] ?? '') ?>"
                                       style="text-transform:uppercase;" required placeholder="Ex: CIMENT CPA 42.5">
                            </div>
                            <div class="art-field">
                                <label class="art-label">SKU / Référence</label>
                                <input type="text" class="art-input" name="sku"
                                       value="<?= htmlspecialchars($_POST['sku'] ?? '') ?>"
                                       placeholder="Ex: REF-001">
                            </div>
                        </div>

                        <div class="art-row col2">
                            <div class="art-field">
                                <label class="art-label">Fournisseur principal <span class="req">*</span></label>
                                <select class="art-select" name="fournisseur_id" required>
                                    <option value="">— Sélectionner —</option>
                                    <?php foreach ($fournisseurs as $f): ?>
                                    <option value="<?= $f['id_fournisseurs'] ?>"
                                        <?= (($_POST['fournisseur_id'] ?? '') == $f['id_fournisseurs']) ? 'selected' : '' ?>>
                                        <?= htmlspecialchars($f['nom_fournisseurs']) ?>
                                    </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                            <div class="art-field">
                                <label class="art-label">Fournisseur alternatif</label>
                                <select class="art-select" name="fournisseur_alternatif_id">
                                    <option value="">— Aucun —</option>
                                    <?php foreach ($fournisseurs as $f): ?>
                                    <option value="<?= $f['id_fournisseurs'] ?>"
                                        <?= (($_POST['fournisseur_alternatif_id'] ?? '') == $f['id_fournisseurs']) ? 'selected' : '' ?>>
                                        <?= htmlspecialchars($f['nom_fournisseurs']) ?>
                                    </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                        </div>

                        <div class="art-row col3">
                            <div class="art-field">
                                <label class="art-label">Catégorie</label>
                                <select class="art-select" name="categorie_id">
                                    <option value="">— Aucune —</option>
                                    <?php foreach ($categories as $cat): ?>
                                    <option value="<?= $cat['id'] ?>"
                                        <?= (($_POST['categorie_id'] ?? '') == $cat['id']) ? 'selected' : '' ?>>
                                        <?= htmlspecialchars($cat['nom']) ?>
                                    </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                            <div class="art-field">
                                <label class="art-label">Unité de mesure</label>
                                <select class="art-select" name="unite_mesure">
                                    <?php foreach (['Piece','Kg','Litre','Mètre','Boîte','Carton'] as $u): ?>
                                    <option value="<?= $u ?>" <?= (($_POST['unite_mesure'] ?? 'Piece') === $u) ? 'selected' : '' ?>>
                                        <?= $u ?>
                                    </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                            <div class="art-field">
                                <label class="art-label">Statut</label>
                                <select class="art-select" name="statut">
                                    <option value="actif"         <?= (($_POST['statut'] ?? 'actif') === 'actif')         ? 'selected' : '' ?>>Actif</option>
                                    <option value="inactif"       <?= (($_POST['statut'] ?? '') === 'inactif')            ? 'selected' : '' ?>>Inactif</option>
                                    <option value="discontinued"  <?= (($_POST['statut'] ?? '') === 'discontinued')       ? 'selected' : '' ?>>Arrêté</option>
                                </select>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Tarification -->
                <div class="art-section">
                    <div class="art-section-head">
                        <span class="icon">💰</span> Tarification
                    </div>
                    <div class="art-section-body">
                        <div class="art-row col4">
                            <div class="art-field">
                                <label class="art-label">Prix de revient <span class="req">*</span></label>
                                <input type="number" step="0.01" min="0.01" class="art-input" id="js-pr" name="pr"
                                       value="<?= htmlspecialchars($_POST['pr'] ?? '') ?>" required placeholder="0">
                            </div>
                            <div class="art-field">
                                <label class="art-label">Prix détail</label>
                                <input type="number" step="0.01" min="0" class="art-input" id="js-pd" name="prix_detail"
                                       value="<?= htmlspecialchars($_POST['prix_detail'] ?? '') ?>" placeholder="0">
                            </div>
                            <div class="art-field">
                                <label class="art-label">Prix semi-gros</label>
                                <input type="number" step="0.01" min="0" class="art-input" name="prix_semi_gros"
                                       value="<?= htmlspecialchars($_POST['prix_semi_gros'] ?? '') ?>" placeholder="0">
                            </div>
                            <div class="art-field">
                                <label class="art-label">Prix gros</label>
                                <input type="number" step="0.01" min="0" class="art-input" name="prix_gros"
                                       value="<?= htmlspecialchars($_POST['prix_gros'] ?? '') ?>" placeholder="0">
                            </div>
                        </div>

                        <!-- Marge calculée -->
                        <div class="art-row col2" style="margin-top:4px;">
                            <div class="art-field">
                                <label class="art-label">Marge estimée (sur prix détail)</label>
                                <div class="marge-badge" id="js-marge">—</div>
                            </div>
                            <div class="art-field" style="display:none;">
                                <!-- Champ prix_vente conservé pour compatibilité -->
                                <input type="number" step="0.01" name="prix_vente" id="js-pv"
                                       value="<?= htmlspecialchars($_POST['prix_vente'] ?? '') ?>">
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Stock & Dépôts -->
                <div class="art-section">
                    <div class="art-section-head">
                        <span class="icon">🏭</span> Stock & Dépôts
                    </div>
                    <div class="art-section-body">
                        <div class="art-row col2" style="margin-bottom:14px;">
                            <div class="art-field">
                                <label class="art-label">Stock minimal d'alerte</label>
                                <input type="number" min="0" class="art-input" name="stock_minimal"
                                       value="<?= htmlspecialchars($_POST['stock_minimal'] ?? '0') ?>">
                            </div>
                            <div class="art-field">
                                <label class="art-label">Stock maximal</label>
                                <input type="number" min="0" class="art-input" name="stock_maximal"
                                       value="<?= htmlspecialchars($_POST['stock_maximal'] ?? '0') ?>">
                            </div>
                        </div>

                        <?php if (!empty($depots)): ?>
                        <div style="overflow-x:auto;">
                            <table class="depot-table">
                                <thead>
                                    <tr>
                                        <th>Dépôt</th>
                                        <th style="width:120px;">Qté initiale</th>
                                        <th>Emplacement</th>
                                    </tr>
                                </thead>
                                <tbody>
                                <?php foreach ($depots as $depot): ?>
                                <tr>
                                    <td><?= htmlspecialchars($depot['nom']) ?></td>
                                    <td>
                                        <input type="number" min="0" class="art-input"
                                               name="depots[<?= $depot['id'] ?>][quantite]" value="0">
                                    </td>
                                    <td>
                                        <input type="text" class="art-input"
                                               name="depots[<?= $depot['id'] ?>][emplacement]"
                                               placeholder="Ex: A-12-3">
                                    </td>
                                </tr>
                                <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                        <?php endif; ?>
                    </div>
                </div>

                <!-- Caractéristiques physiques -->
                <div class="art-section">
                    <div class="art-section-head">
                        <span class="icon">📐</span> Caractéristiques physiques
                        <span style="margin-left:auto; font-size:.75rem; font-weight:400; color:var(--art-muted);">Optionnel</span>
                    </div>
                    <div class="art-section-body">
                        <div class="art-row col4">
                            <div class="art-field">
                                <label class="art-label">Poids (kg)</label>
                                <input type="number" step="0.001" min="0" class="art-input" name="poids_kg"
                                       value="<?= htmlspecialchars($_POST['poids_kg'] ?? '') ?>" placeholder="0.000">
                            </div>
                            <div class="art-field">
                                <label class="art-label">Longueur (cm)</label>
                                <input type="number" step="0.01" min="0" class="art-input" name="longueur_cm"
                                       value="<?= htmlspecialchars($_POST['longueur_cm'] ?? '') ?>" placeholder="0">
                            </div>
                            <div class="art-field">
                                <label class="art-label">Largeur (cm)</label>
                                <input type="number" step="0.01" min="0" class="art-input" name="largeur_cm"
                                       value="<?= htmlspecialchars($_POST['largeur_cm'] ?? '') ?>" placeholder="0">
                            </div>
                            <div class="art-field">
                                <label class="art-label">Hauteur (cm)</label>
                                <input type="number" step="0.01" min="0" class="art-input" name="hauteur_cm"
                                       value="<?= htmlspecialchars($_POST['hauteur_cm'] ?? '') ?>" placeholder="0">
                            </div>
                        </div>
                        <div class="art-field" style="margin-top:4px;">
                            <label class="art-label">Couleur</label>
                            <input type="text" class="art-input" name="couleur"
                                   value="<?= htmlspecialchars($_POST['couleur'] ?? '') ?>" placeholder="Ex: Rouge, Bleu...">
                        </div>
                    </div>
                </div>

                <!-- Notes -->
                <div class="art-section">
                    <div class="art-section-head">
                        <span class="icon">📝</span> Notes internes
                    </div>
                    <div class="art-section-body">
                        <textarea class="art-textarea" name="notes_internes" rows="3"
                                  placeholder="Remarques, conditions particulières..."><?= htmlspecialchars($_POST['notes_internes'] ?? '') ?></textarea>
                    </div>
                </div>

                <!-- Actions -->
                <div class="art-actions" style="border-radius:var(--art-radius); border:1px solid var(--art-border);">
                    <button type="submit" class="btn-create">✔ Créer l'article</button>
                    <a href="index.php?action=articles" class="btn-cancel">Annuler</a>
                </div>
            </div>

            <!-- ── Colonne latérale ───────────────────────────────── -->
            <div>

                <!-- Image -->
                <div class="art-section">
                    <div class="art-section-head">
                        <span class="icon">🖼️</span> Image produit
                    </div>
                    <div class="art-section-body">
                        <div class="img-drop" id="js-drop">
                            <input type="file" name="image" accept="image/jpeg,image/png,image/gif,image/webp" id="js-file">
                            <div class="img-drop-icon">📤</div>
                            <div class="img-drop-text">Glisser-déposer ou cliquer<br>
                                <span style="font-size:.75rem;">JPG, PNG, GIF, WEBP — max 2 Mo</span>
                            </div>
                            <img src="" alt="" class="img-preview" id="js-preview">
                        </div>
                    </div>
                </div>

                <!-- Récapitulatif tarifs -->
                <div class="art-section">
                    <div class="art-section-head">
                        <span class="icon">📊</span> Récapitulatif tarifs
                    </div>
                    <div class="art-section-body" style="padding:12px;">
                        <table style="width:100%; font-size:.85rem; border-collapse:collapse;">
                            <tr>
                                <td style="padding:5px 0; color:var(--art-muted);">Prix de revient</td>
                                <td style="text-align:right; font-weight:600;" id="sum-pr">—</td>
                            </tr>
                            <tr>
                                <td style="padding:5px 0; color:var(--art-muted);">Prix détail</td>
                                <td style="text-align:right; font-weight:600;" id="sum-pd">—</td>
                            </tr>
                            <tr>
                                <td style="padding:5px 0; color:var(--art-muted);">Prix semi-gros</td>
                                <td style="text-align:right; font-weight:600;" id="sum-psg">—</td>
                            </tr>
                            <tr>
                                <td style="padding:5px 0; color:var(--art-muted);">Prix gros</td>
                                <td style="text-align:right; font-weight:600;" id="sum-pg">—</td>
                            </tr>
                            <tr style="border-top:1px solid var(--art-border);">
                                <td style="padding:8px 0 4px; font-weight:700;">Marge détail</td>
                                <td style="text-align:right; font-weight:700;" id="sum-marge">—</td>
                            </tr>
                        </table>
                    </div>
                </div>

                <!-- Rôles info -->
                <div class="art-section">
                    <div class="art-section-head">
                        <span class="icon">ℹ️</span> Rôles & accès
                    </div>
                    <div class="art-section-body" style="padding:12px;">
                        <div class="info-pill pill-user">👤 Utilisateur — consultation & création</div>
                        <div class="info-pill pill-mgr">🔑 Manager — édition complète</div>
                        <div class="info-pill pill-admin">⚡ Admin — accès total</div>
                    </div>
                </div>

            </div>
        </div>
    </form>
</div>

<script>
(function () {
    // ── Calcul marge ──────────────────────────────────────────────
    const pr   = document.getElementById('js-pr');
    const pd   = document.getElementById('js-pd');
    const pv   = document.getElementById('js-pv');
    const psg  = document.querySelector('[name=prix_semi_gros]');
    const pg   = document.querySelector('[name=prix_gros]');
    const marge= document.getElementById('js-marge');

    const sumPr  = document.getElementById('sum-pr');
    const sumPd  = document.getElementById('sum-pd');
    const sumPsg = document.getElementById('sum-psg');
    const sumPg  = document.getElementById('sum-pg');
    const sumM   = document.getElementById('sum-marge');

    function fmt(v) {
        if (!v || isNaN(v)) return '—';
        return Number(v).toLocaleString('fr-FR', {minimumFractionDigits:0, maximumFractionDigits:0});
    }

    function calc() {
        const vPr = parseFloat(pr.value)  || 0;
        const vPd = parseFloat(pd.value)  || 0;

        // Sync prix_vente (compatibilité)
        if (pv) pv.value = pd.value;

        // Marge
        if (vPr > 0 && vPd > 0) {
            const pct  = ((vPd - vPr) / vPr * 100).toFixed(1);
            const diff = (vPd - vPr).toLocaleString('fr-FR', {minimumFractionDigits:0});
            marge.textContent = pct + '% (' + diff + ')';
            marge.className = 'marge-badge ' + (vPd >= vPr ? 'pos' : 'neg');
            sumM.textContent = pct + '%';
            sumM.style.color = vPd >= vPr ? 'var(--art-success)' : 'var(--art-danger)';
        } else {
            marge.textContent = '—';
            marge.className = 'marge-badge';
            sumM.textContent = '—';
            sumM.style.color = '';
        }

        // Récap
        sumPr.textContent  = fmt(pr.value);
        sumPd.textContent  = fmt(pd.value);
        sumPsg.textContent = fmt(psg ? psg.value : 0);
        sumPg.textContent  = fmt(pg  ? pg.value  : 0);
    }

    [pr, pd, psg, pg].forEach(el => el && el.addEventListener('input', calc));
    calc();

    // ── Preview image ─────────────────────────────────────────────
    const drop    = document.getElementById('js-drop');
    const fileIn  = document.getElementById('js-file');
    const preview = document.getElementById('js-preview');

    fileIn.addEventListener('change', function () {
        const file = this.files[0];
        if (!file) return;
        if (file.size > 2 * 1024 * 1024) {
            alert('Image trop lourde (max 2 Mo).');
            this.value = '';
            return;
        }
        const reader = new FileReader();
        reader.onload = e => {
            preview.src = e.target.result;
            preview.style.display = 'block';
        };
        reader.readAsDataURL(file);
    });

    drop.addEventListener('dragover',  e => { e.preventDefault(); drop.classList.add('drag'); });
    drop.addEventListener('dragleave', ()  => drop.classList.remove('drag'));
    drop.addEventListener('drop', e => {
        e.preventDefault();
        drop.classList.remove('drag');
        fileIn.files = e.dataTransfer.files;
        fileIn.dispatchEvent(new Event('change'));
    });
})();
</script>
