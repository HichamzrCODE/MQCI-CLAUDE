<?php include '../views/layout.php'; ?>

<style>
    .stock-page-title {
        font-size: 1.45rem;
        font-weight: 700;
        margin-bottom: 0;
    }

    .stock-toolbar .btn {
        border-radius: 10px;
    }

    .stock-card {
        border: 0;
        border-radius: 14px;
        box-shadow: 0 4px 18px rgba(0,0,0,0.06);
    }

    .stock-card .card-header {
        background: #fff;
        border-bottom: 1px solid #eef1f4;
        font-weight: 600;
    }

    .stock-summary {
        font-size: 0.95rem;
        color: #6c757d;
    }

    .stock-summary strong {
        color: #212529;
    }

    .table-stock thead th {
        background: #f8fafc;
        border-top: 0 !important;
        border-bottom: 1px solid #e9ecef !important;
        font-size: 0.84rem;
        text-transform: uppercase;
        letter-spacing: .03em;
        color: #6c757d;
        white-space: nowrap;
    }

    .table-stock tbody td {
        vertical-align: middle;
    }

    .article-name {
        font-weight: 600;
        text-transform: uppercase;
        color: #212529;
    }

    .article-sku {
        font-size: 0.78rem;
        color: #6c757d;
    }

    .qty-badge {
        display: inline-block;
        min-width: 62px;
        text-align: center;
        padding: 4px 10px;
        border-radius: 999px;
        font-weight: 700;
        font-size: 0.85rem;
    }

    .qty-entree,
    .qty-retour {
        background: #d1fae5;
        color: #065f46;
    }

    .qty-sortie,
    .qty-transfert {
        background: #fee2e2;
        color: #991b1b;
    }

    .qty-ajustement {
        background: #dbeafe;
        color: #1e40af;
    }

    .mini-value {
        font-weight: 600;
        color: #495057;
    }

    .ref-code {
        background: #f1f3f5;
        color: #495057;
        padding: 3px 8px;
        border-radius: 8px;
        font-size: 0.8rem;
        display: inline-block;
    }

    .action-eye {
        border-radius: 8px;
    }

    .empty-state {
        padding: 35px 10px;
        color: #6c757d;
    }

    .filter-label {
        font-size: 0.8rem;
        font-weight: 600;
        color: #6c757d;
        margin-bottom: 6px;
    }

    .badge-soft {
        padding: 6px 10px;
        border-radius: 999px;
        font-size: 0.78rem;
        font-weight: 600;
    }

    .badge-entree { background:#d1fae5; color:#065f46; }
    .badge-sortie { background:#fee2e2; color:#991b1b; }
    .badge-ajustement { background:#dbeafe; color:#1e40af; }
    .badge-retour { background:#fef3c7; color:#92400e; }
    .badge-transfert { background:#e5e7eb; color:#374151; }

    @media (max-width: 768px) {
        .stock-toolbar {
            width: 100%;
        }

        .stock-toolbar .btn {
            width: 100%;
        }

        .stock-page-title {
            font-size: 1.2rem;
        }
    }
</style>

<div class="container-fluid mt-4">
    <div class="d-flex justify-content-between align-items-center mb-3 flex-wrap gap-2">
        <div>
            <h1 class="stock-page-title">📦 Mouvements de stock</h1>
            <div class="stock-summary">
                Suivi des entrées, sorties et ajustements de stock.
            </div>
        </div>

        <div class="d-flex gap-2 flex-wrap stock-toolbar">
            <?php if (hasPermission('stock_movements', 'create')): ?>
                <a href="index.php?action=stock_movements/create" class="btn btn-success btn-sm">
                    + Ajustement
                </a>
            <?php endif; ?>
            <a href="index.php?action=stock_movements/alerts" class="btn btn-warning btn-sm">⚠ Alertes</a>
            <a href="index.php?action=stock_movements/seuils" class="btn btn-outline-secondary btn-sm">⚙ Seuils</a>
        </div>
    </div>

<div class="card stock-card mb-3">
    <div class="card-header">Filtres</div>
    <div class="card-body py-3">
        <form method="get" action="index.php">
            <input type="hidden" name="action" value="stock_movements">

            <?php
                $selectedArticleId = $filters['article_id'] ?? '';
                $selectedArticleNom = '';

                if (!empty($selectedArticleId) && !empty($articles)) {
                    foreach ($articles as $a) {
                        if ((string)$a['id_articles'] === (string)$selectedArticleId) {
                            $selectedArticleNom = $a['nom_art'];
                            break;
                        }
                    }
                }
            ?>

            <div class="row align-items-end">
                <div class="col-md-3">
                    <div class="form-group mb-0">
                        <label class="filter-label">Article</label>
                        <div style="position:relative;">
                            <input type="text"
                                   id="article_autocomplete"
                                   name="article_nom"
                                   class="form-control form-control-sm"
                                   placeholder="Rechercher un article..."
                                   value="<?= htmlspecialchars($selectedArticleNom); ?>"
                                   autocomplete="off">

                            <input type="hidden"
                                   id="article_id"
                                   name="article_id"
                                   value="<?= htmlspecialchars($selectedArticleId); ?>">

                            <div id="article-results"
                                 class="list-group"
                                 style="position:absolute; left:0; right:0; top:100%; z-index:2000; display:none; max-height:240px; overflow:auto;"></div>
                        </div>
                    </div>
                </div>

                <div class="col-md-2">
                    <div class="form-group mb-0">
                        <label class="filter-label">Dépôt</label>
                        <select name="depot_id" class="form-control form-control-sm">
                            <option value="">Tous les dépôts</option>
                            <?php foreach ($depots as $d): ?>
                                <option value="<?= $d['id']; ?>"
                                    <?= ($filters['depot_id'] ?? '') == $d['id'] ? 'selected' : ''; ?>>
                                    <?= htmlspecialchars($d['nom']); ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                </div>

                <div class="col-md-2">
                    <div class="form-group mb-0">
                        <label class="filter-label">Type</label>
                        <select name="type_mouvement" class="form-control form-control-sm">
                            <option value="">Tous les types</option>
                            <?php foreach ([
                                'entree' => 'Entrée',
                                'sortie' => 'Sortie',
                                'ajustement' => 'Ajustement',
                                'retour' => 'Retour',
                                'transfert' => 'Transfert'
                            ] as $val => $label): ?>
                                <option value="<?= $val; ?>" <?= ($filters['type_mouvement'] ?? '') === $val ? 'selected' : ''; ?>>
                                    <?= $label; ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                </div>

                <div class="col-md-2">
                    <div class="form-group mb-0">
                        <label class="filter-label">Du</label>
                        <input type="date"
                               name="date_debut"
                               class="form-control form-control-sm"
                               value="<?= htmlspecialchars($filters['date_debut'] ?? ''); ?>">
                    </div>
                </div>

                <div class="col-md-2">
                    <div class="form-group mb-0">
                        <label class="filter-label">Au</label>
                        <input type="date"
                               name="date_fin"
                               class="form-control form-control-sm"
                               value="<?= htmlspecialchars($filters['date_fin'] ?? ''); ?>">
                    </div>
                </div>

                <div class="col-md-1">
                    <div class="form-group mb-0">
                        <button type="submit" class="btn btn-primary btn-sm w-100">Filtrer</button>
                    </div>
                </div>
            </div>
        </form>
    </div>
</div>

    <div class="d-flex justify-content-between align-items-center mb-2 flex-wrap gap-2">
        <p class="stock-summary mb-0">
            Total : <strong><?= (int)$total; ?></strong> mouvement(s)
        </p>
    </div>

    <div class="card stock-card">
        <div class="card-header">Liste des mouvements</div>
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-sm table-hover mb-0 table-stock">
                    <thead>
                        <tr>
                            <th>#</th>
                            <th>Date</th>
                            <th>Article</th>
                            <th>Dépôt</th>
                            <th>Type</th>
                            <th>Qté</th>
                            <th>Avant</th>
                            <th>Après</th>
                            <th>Référence</th>
                            <th>Utilisateur</th>
                            <th class="text-center">Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (empty($mouvements)): ?>
                            <tr>
                                <td colspan="11" class="text-center empty-state">
                                    Aucun mouvement trouvé.
                                </td>
                            </tr>
                        <?php endif; ?>

                        <?php foreach ($mouvements as $m): ?>
                            <?php
                                $typeClasses = [
                                    'entree'     => 'badge-entree',
                                    'sortie'     => 'badge-sortie',
                                    'ajustement' => 'badge-ajustement',
                                    'retour'     => 'badge-retour',
                                    'transfert'  => 'badge-transfert',
                                ];
                                $typeLabels = [
                                    'entree'     => 'Entrée',
                                    'sortie'     => 'Sortie',
                                    'ajustement' => 'Ajustement',
                                    'retour'     => 'Retour',
                                    'transfert'  => 'Transfert',
                                ];
                                $qtyClasses = [
                                    'entree'     => 'qty-entree',
                                    'sortie'     => 'qty-sortie',
                                    'ajustement' => 'qty-ajustement',
                                    'retour'     => 'qty-entree',
                                    'transfert'  => 'qty-sortie',
                                ];

                                $badge = $typeClasses[$m['type_mouvement']] ?? 'badge-transfert';
                                $label = $typeLabels[$m['type_mouvement']] ?? ($m['type_mouvement'] ?? '-');
                                $qtyClass = $qtyClasses[$m['type_mouvement']] ?? 'qty-ajustement';
                            ?>
                            <tr>
                                <td><span class="mini-value"><?= (int)$m['id']; ?></span></td>
                                <td style="white-space:nowrap;">
                                    <?= htmlspecialchars(date('d/m/Y H:i', strtotime($m['created_at']))); ?>
                                </td>
                                <td>
                                    <div class="article-name"><?= htmlspecialchars($m['nom_art'] ?? '-'); ?></div>
                                    <?php if (!empty($m['sku'])): ?>
                                        <div class="article-sku"><?= htmlspecialchars($m['sku']); ?></div>
                                    <?php endif; ?>
                                </td>
                                <td><?= htmlspecialchars($m['depot_nom'] ?? '-'); ?></td>
                                <td>
                                    <span class="badge-soft <?= $badge; ?>">
                                        <?= $label; ?>
                                    </span>
                                </td>
                                <td>
                                    <span class="qty-badge <?= $qtyClass; ?>">
                                        <?= (int)$m['quantite']; ?>
                                    </span>
                                </td>
                                <td class="text-muted">
                                    <?= $m['quantite_avant'] !== null ? (int)$m['quantite_avant'] : '-'; ?>
                                </td>
                                <td>
                                    <strong><?= $m['quantite_apres'] !== null ? (int)$m['quantite_apres'] : '-'; ?></strong>
                                </td>
                                <td>
                                    <?php if (!empty($m['reference'])): ?>
                                        <span class="ref-code"><?= htmlspecialchars($m['reference']); ?></span>
                                    <?php else: ?>
                                        <span class="text-muted">—</span>
                                    <?php endif; ?>
                                </td>
                                <td><?= htmlspecialchars($m['user_nom'] ?? '-'); ?></td>
                                <td class="text-center">
                                    <a href="index.php?action=stock_movements/show&id=<?= $m['id']; ?>"
                                       class="btn btn-outline-info btn-sm action-eye"
                                       title="Détails">
                                        👁
                                    </a>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <?php if ($total > $limit): ?>
        <nav class="mt-3">
            <ul class="pagination pagination-sm justify-content-end">
                <?php $pages = ceil($total / $limit); ?>
                <?php for ($p = 1; $p <= $pages; $p++): ?>
                    <?php
                        $q = array_merge($filters, ['page' => $p]);
                        $qs = http_build_query(array_merge(['action' => 'stock_movements'], $q));
                    ?>
                    <li class="page-item <?= $p === $page ? 'active' : ''; ?>">
                        <a class="page-link" href="index.php?<?= $qs; ?>"><?= $p; ?></a>
                    </li>
                <?php endfor; ?>
            </ul>
        </nav>
    <?php endif; ?>
</div>

<script>
(function () {
    const input = document.getElementById('article_autocomplete');
    const hidden = document.getElementById('article_id');
    const resultsBox = document.getElementById('article-results');

    if (!input || !hidden || !resultsBox) return;

    let timer = null;

    function escapeHtml(str) {
        return String(str ?? '')
            .replace(/&/g, '&amp;')
            .replace(/</g, '&lt;')
            .replace(/>/g, '&gt;')
            .replace(/"/g, '&quot;')
            .replace(/'/g, '&#039;');
    }

    function hideResults() {
        resultsBox.style.display = 'none';
        resultsBox.innerHTML = '';
    }

    function renderResults(items) {
        if (!Array.isArray(items) || items.length === 0) {
            resultsBox.innerHTML = '<div class="list-group-item small text-muted">Aucun article trouvé</div>';
            resultsBox.style.display = 'block';
            return;
        }

        let html = '';
        items.slice(0, 10).forEach(item => {
            html += `
                <button type="button"
                        class="list-group-item list-group-item-action article-result-item"
                        data-id="${item.id_articles}"
                        data-name="${escapeHtml(item.nom_art || '')}">
                    <div style="font-weight:600;">${escapeHtml(item.nom_art || '')}</div>
                    ${item.sku ? `<small class="text-muted">${escapeHtml(item.sku)}</small>` : ''}
                </button>
            `;
        });

        resultsBox.innerHTML = html;
        resultsBox.style.display = 'block';

        resultsBox.querySelectorAll('.article-result-item').forEach(btn => {
            btn.addEventListener('mousedown', function (e) {
                e.preventDefault();
                input.value = this.dataset.name || '';
                hidden.value = this.dataset.id || '';
                hideResults();
            });
        });
    }

    input.addEventListener('input', function () {
        const term = input.value.trim();
        hidden.value = '';

        clearTimeout(timer);

        if (term.length < 2) {
            hideResults();
            return;
        }

        timer = setTimeout(function () {
            fetch('index.php?action=articles/search&term=' + encodeURIComponent(term), {
                headers: { 'X-Requested-With': 'XMLHttpRequest' }
            })
            .then(r => r.json())
            .then(data => renderResults(data))
            .catch(() => hideResults());
        }, 250);
    });

    input.addEventListener('blur', function () {
        setTimeout(hideResults, 150);
    });

    input.addEventListener('keydown', function (e) {
        if (e.key === 'Escape') {
            hideResults();
        }
    });

    const form = input.closest('form');
    if (form) {
        form.addEventListener('submit', function () {
            if (input.value.trim() === '') {
                hidden.value = '';
            }
        });
    }
})();
</script>