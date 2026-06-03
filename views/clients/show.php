<?php include '../views/layout.php'; ?>
<?php require_once __DIR__ . '/../../includes/permissions.php'; ?>

<?php
$client = $client ?? null;
$documents = $documents ?? [];
$dernier_prix_articles = $dernier_prix_articles ?? [];
$finance = $finance ?? [
    'total_factures' => 0,
    'total_factures_payees' => 0,
    'total_factures_echues' => 0,
    'total_factures_non_payees_pas_echues' => 0
];

if (!$client) {
    echo "<div class='container mt-4'><div class='alert alert-danger'>Client introuvable.</div></div>";
    return;
}

$typeLabel = ($client['type_client'] ?? '') === 'facture' ? 'Entreprise (Facture)' : 'Cash';
$statutText = (($client['type_client'] ?? '') === 'facture') ? 'Compte entreprise' : 'Client cash';

$airsiText = !empty($client['apply_airsi'])
    ? 'Oui (' . number_format((float)($client['airsi_rate'] ?? 5), 2, ',', ' ') . '%)'
    : 'Non';
?>

<div class="container mt-4" style="max-width: 1100px;">

    <!-- Header -->
    <div class="d-flex justify-content-between align-items-center mb-3">
        <div style="font-size:1.1rem;">
            <strong>#<?= (int)$client['id_clients'] ?></strong>
            <span class="ml-2"><?= htmlspecialchars($client['nom'] ?? '') ?></span>
        </div>

        <div class="d-flex align-items-center" style="gap:8px;">
            <?php if (hasPermission('clients', 'edit')): ?>
                <a href="index.php?action=clients/edit&id=<?= (int)$client['id_clients'] ?>"
                   class="btn btn-primary btn-sm">Modifier</a>
            <?php endif; ?>
            <a href="index.php?action=clients" class="btn btn-outline-secondary btn-sm">Retour</a>
            <a href="index.php?action=client_releve/show&id=<?= (int)$client['id_clients'] ?>" class="btn btn-success btn-sm ms-1">📄 Relevé</a>
        </div>
    </div>

    <!-- Accordéon HTML natif -->
    <div class="acc-root">

        <!-- CLIENT -->
        <details class="acc" open>
            <summary class="acc-summary">
                <span class="acc-title">
                    <i class="fas fa-user mr-2"></i> Client
                </span>
                <span class="acc-hint">Infos générales</span>
            </summary>

            <div class="acc-body">
                <div class="client-grid">
                    <div class="client-field">
                        <div class="client-label">Type</div>
                        <div class="client-value"><?= htmlspecialchars($typeLabel) ?></div>
                    </div>

                    <div class="client-field">
                        <div class="client-label">Statut</div>
                        <div class="client-value"><?= htmlspecialchars($statutText) ?></div>
                    </div>

                    <div class="client-field">
                        <div class="client-label">Adresse / Ville</div>
                        <div class="client-value"><?= htmlspecialchars($client['ville'] ?? '—') ?></div>
                    </div>

                    <div class="client-field">
                        <div class="client-label">Téléphone</div>
                        <div class="client-value"><?= htmlspecialchars($client['telephone'] ?? '—') ?></div>
                    </div>

                    <div class="client-field">
                        <div class="client-label">Délai paiement</div>
                        <div class="client-value"><?= (int)($client['payment_delay'] ?? 30) ?> jours</div>
                    </div>

                    <div class="client-field">
                        <div class="client-label">Tarif</div>
                        <div class="client-value"><?= htmlspecialchars($client['tarif'] ?? '—') ?></div>
                    </div>

                    <div class="client-field">
                        <div class="client-label">AIRSI</div>
                        <div class="client-value"><?= htmlspecialchars($airsiText) ?></div>
                    </div>
                </div>
            </div>
        </details>

        <!-- FINANCIERES -->
        <details class="acc">
            <summary class="acc-summary">
                <span class="acc-title">
                    <i class="fas fa-coins mr-2"></i> Financières
                </span>
                <span class="acc-hint">Totaux factures</span>
            </summary>

            <div class="acc-body">
                <div class="row">
                    <div class="col-12 col-md-6 mb-3">
                        <div class="metric">
                            <div class="metric-label">Total factures</div>
                            <div class="metric-value"><?= number_format((float)$finance['total_factures'], 0, ',', ' ') ?> fr</div>
                        </div>
                    </div>

                    <div class="col-12 col-md-6 mb-3">
                        <div class="metric">
                            <div class="metric-label">Total factures payées</div>
                            <div class="metric-value"><?= number_format((float)$finance['total_factures_payees'], 0, ',', ' ') ?> fr</div>
                        </div>
                    </div>

                    <div class="col-12 col-md-6 mb-3">
                        <div class="metric">
                            <div class="metric-label">Factures arrivées à échéance (impayées)</div>
                            <div class="metric-value"><?= number_format((float)$finance['total_factures_echues'], 0, ',', ' ') ?> fr</div>
                        </div>
                    </div>

                    <div class="col-12 col-md-6 mb-3">
                        <div class="metric">
                            <div class="metric-label">Factures non payées (pas échues)</div>
                            <div class="metric-value"><?= number_format((float)$finance['total_factures_non_payees_pas_echues'], 0, ',', ' ') ?> fr</div>
                        </div>
                    </div>
                </div>

                <div class="text-muted small">
                    Note: “payées” dépend d’un statut <code>paid</code> (ou d’un module règlements à ajouter plus tard).
                </div>
            </div>
        </details>

        <!-- DOCUMENTS -->
        <details class="acc">
            <summary class="acc-summary">
                <span class="acc-title">
                    <i class="fas fa-file-alt mr-2"></i> Documents
                </span>
                <span class="acc-hint">5 derniers devis/factures</span>
            </summary>

            <div class="acc-body">
                <?php if (empty($documents)): ?>
                    <div class="text-muted">Aucun document (devis/facture) trouvé.</div>
                <?php else: ?>
                    <div class="table-responsive">
                        <table class="table table-sm table-hover align-middle">
                            <thead class="thead-light">
                                <tr>
                                    <th>N°</th>
                                    <th>Type</th>
                                    <th>Date</th>
                                    <th>Statut</th>
                                    <th class="text-right">Total</th>
                                    <th class="text-right">Action</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($documents as $d): ?>
                                    <?php
                                    $t = $d['type_doc'] ?? 'devis';
                                    $typeDocLabel = ($t === 'facture') ? 'Facture' : 'Devis';
                                    $total = (float)($d['total_ttc'] ?? $d['total'] ?? 0);

                                    $url = ($t === 'facture')
                                        ? ("index.php?action=factures/show&id=" . (int)$d['id'])
                                        : ("index.php?action=devis/detail&id=" . (int)$d['id']);
                                    ?>
                                    <tr>
                                        <td class="font-weight-bold"><?= htmlspecialchars($d['numero'] ?? '') ?></td>
                                        <td><?= htmlspecialchars($typeDocLabel) ?></td>
                                        <td><?= htmlspecialchars($d['date'] ?? '') ?></td>
                                        <td><?= htmlspecialchars($d['statut'] ?? '') ?></td>
                                        <td class="text-right"><?= number_format($total, 0, ',', ' ') ?> fr</td>
                                        <td class="text-right">
                                            <a class="btn btn-outline-primary btn-sm" href="<?= htmlspecialchars($url) ?>">Ouvrir</a>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                <?php endif; ?>
            </div>
        </details>

        <!-- ARTICLES -->
        <details class="acc">
            <summary class="acc-summary">
                <span class="acc-title">
                    <i class="fas fa-box mr-2"></i> Articles
                </span>
                <span class="acc-hint">Dernier prix par article</span>
            </summary>

            <div class="acc-body">
                <div class="d-flex justify-content-between align-items-center mb-2">
                    <div class="text-muted small">Priorité factures, sinon devis</div>
                    <input type="text" id="article-search" class="form-control form-control-sm"
                           placeholder="Rechercher..." style="max-width:240px;">
                </div>

                <?php if (empty($dernier_prix_articles)): ?>
                    <div class="text-muted">Aucun article trouvé.</div>
                <?php else: ?>
                    <div class="table-responsive">
                        <table class="table table-sm table-bordered" id="articles-table">
                            <thead class="thead-light">
                                <tr>
                                    <th>Article</th>
                                    <th class="text-right">PU</th>
                                    <th>Doc</th>
                                    <th>Type</th>
                                    <th>Date</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($dernier_prix_articles as $row): ?>
                                    <tr>
                                        <td><?= htmlspecialchars($row['article'] ?? '') ?></td>
                                        <td class="text-right"><?= number_format((float)($row['prix_unitaire'] ?? 0), 0, ',', ' ') ?></td>
                                        <td><?= htmlspecialchars($row['doc_numero'] ?? '') ?></td>
                                        <td><?= htmlspecialchars(($row['type_doc'] ?? '') === 'facture' ? 'Facture' : 'Devis') ?></td>
                                        <td><?= !empty($row['doc_date']) ? date('d/m/Y', strtotime($row['doc_date'])) : '—' ?></td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                <?php endif; ?>
            </div>
        </details>

    </div>
</div>

<style>
/* Conteneur */
.acc-root{
    display: flex;
    flex-direction: column;
    gap: 12px;
}

/* Card */
.acc{
    border: 1px solid #e5e7eb;
    border-radius: 12px;
    background: #fff;
    overflow: hidden;
}

/* Summary = header cliquable */
.acc-summary{
    list-style: none;
    cursor: pointer;
    user-select: none;
    padding: 12px 14px;
    background: #f3f7ff; /* fond clair */
    border-bottom: 1px solid #e5e7eb;

    display: flex;
    align-items: center;
    justify-content: space-between;
    gap: 12px;
}
.acc-summary::-webkit-details-marker{ display:none; }

.acc-title{
    font-weight: 700;
    color: #1f2937;
    display: inline-flex;
    align-items: center;
}
.acc-hint{
    color: #6b7280;
    font-size: .85rem;
}

/* Icône chevron à droite */
.acc-summary::after{
    content: "▾";
    color: #6b7280;
    font-size: 1rem;
    transition: transform .15s ease;
}
.acc[open] > .acc-summary::after{
    transform: rotate(-180deg);
}

/* Body */
.acc-body{
    padding: 14px;
}

/* Client section: grille propre, pas en gras */
.client-grid{
    display: grid;
    grid-template-columns: repeat(2, minmax(0, 1fr));
    gap: 10px 12px;
}
@media (max-width: 768px){
    .client-grid{ grid-template-columns: 1fr; }
}
.client-field{
    border: 1px solid #eef2f7;
    border-radius: 10px;
    padding: 10px 12px;
    background: #fff;
}
.client-label{
    color: #6b7280;
    font-size: .82rem;
    margin-bottom: 3px;
}
.client-value{
    color: #111827;
    font-size: .95rem;
    font-weight: 400; /* pas gras */
}

/* Metrics cards */
.metric{
    border: 1px solid #e5e7eb;
    border-radius: 10px;
    padding: 12px;
    background: #ffffff;
}
.metric-label{
    color: #6b7280;
    font-size: .85rem;
    margin-bottom: 2px;
}
.metric-value{
    font-size: 1.15rem;
    font-weight: 800;
    color: #111827;
}
</style>

<script>
(function(){
    var input = document.getElementById('article-search');
    if (!input) return;

    input.addEventListener('input', function(){
        var v = (this.value || '').toLowerCase().trim();
        var rows = document.querySelectorAll('#articles-table tbody tr');
        rows.forEach(function(tr){
            tr.style.display = tr.textContent.toLowerCase().indexOf(v) !== -1 ? '' : 'none';
        });
    });
})();
</script>