<?php include '../views/layout.php'; ?>
<?php
$errorFields = $errorFields ?? [];
$fournisseurs = $fournisseurs ?? [];
$depots = $depots ?? [];
$articles = $articles ?? [];
$error = $error ?? null;
?>
<style>
    .table th, .table td {
        vertical-align: middle;
        white-space: nowrap;
        padding: 6px 8px;
        font-size: 0.95rem;
    }
    .table th {
        background: #f0f4f8;
        font-weight: 600;
        color: #2c3e50;
    }
    .table tbody tr:hover {
        background: #e8f0f7;
    }
    .form-control-sm, .form-select-sm {
        font-size: 0.9rem;
        padding: 0.4rem 0.6rem;
        height: auto;
    }
    .autocomplete-wrapper {
        position: relative;
    }
    .autocomplete-results {
        position: absolute;
        background: white;
        border: 1px solid #ddd;
        border-radius: 4px;
        max-height: 200px;
        overflow-y: auto;
        z-index: 1000;
        width: 100%;
        box-shadow: 0 2px 8px rgba(0,0,0,0.1);
        display: none;
        top: 100%;
        left: 0;
    }
    .autocomplete-results.show {
        display: block;
    }
    .autocomplete-item {
        padding: 10px 12px;
        cursor: pointer;
        border-bottom: 1px solid #f0f0f0;
        font-size: 0.9rem;
        display: block;
        text-decoration: none;
        color: #333;
        background: white;
        user-select: none;
    }
    .autocomplete-item:hover,
    .autocomplete-item.active {
        background: #f0f4f8 !important;
        color: #2c3e50 !important;
    }
    h4 {
        color: #2c3e50;
        margin-bottom: 1.5rem;
        font-weight: 600;
    }
    .alert {
        margin-bottom: 1rem;
    }
    .container {
        margin-top: -20px !important;
    }
    .form-label {
        font-size: 0.9rem;
        margin-bottom: 0.35rem;
        font-weight: 500;
    }
</style>

<div class="container mt-4">
    <h4><i class="fas fa-inbox"></i> Réception Fournisseur</h4>
    
    <?php if ($error): ?>
        <div class="alert alert-danger"><?= htmlspecialchars($error) ?></div>
    <?php endif; ?>

    <form method="post" action="index.php?action=reception_fournisseur/create" id="reception-form" autocomplete="off">
        
        <div class="row mb-3">
            <div class="col-md-3 mb-2">
                <label class="form-label">Fournisseur <span class="text-danger">*</span></label>
                <div class="autocomplete-wrapper">
                    <input type="text" class="form-control form-control-sm" id="fournisseur_search" placeholder="Chercher..." autocomplete="off">
                    <input type="hidden" name="fournisseur_id" id="fournisseur_id" value="">
                    <div class="autocomplete-results" id="fournisseur_results"></div>
                </div>
            </div>
            <div class="col-md-3 mb-2">
                <label class="form-label">Dépôt <span class="text-danger">*</span></label><br>
                <select name="depot_id" id="depot_id" class="form-select form-select-sm" required>
                    <option value="">-- Sélectionnez --</option>
                    <?php foreach ($depots as $d): ?>
                        <option value="<?= $d['id'] ?>"><?= htmlspecialchars($d['nom']) ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="col-md-3 mb-2">
                <label class="form-label">Numéro Facture</label>
                <input type="text" class="form-control form-control-sm" name="numero_facture" placeholder="Facture" value="<?= htmlspecialchars($_POST['numero_facture'] ?? '') ?>">
            </div>
            <div class="col-md-3 mb-2">
                <label class="form-label">Date Réception <span class="text-danger">*</span></label>
                <input type="date" class="form-control form-control-sm" name="date_reception" value="<?= htmlspecialchars($_POST['date_reception'] ?? date('Y-m-d')) ?>" required>
            </div>
        </div>

        <div class="table-responsive mb-3">
            <table class="table table-bordered table-striped" id="reception-table">
                <thead>
                    <tr>
                        <th style="width: 35%;">Article</th>
                        <th style="width: 20%; text-align: right;">Quantité</th>
                        <th style="width: 25%; text-align: right;">Prix de Revient</th>
                        <th style="width: 15%; text-align: right;">Total</th>
                        <th style="width: 5%; text-align: center;">Action</th>
                    </tr>
                </thead>
                <tbody id="lignes-container">
                    <tr id="ligne-vide-message">
                        <td colspan="5" class="text-center text-muted" style="font-style: italic; background: #f9f9f9; padding: 20px;">
                            Cliquez sur <b>"Ajouter une ligne"</b> pour commencer la réception.
                        </td>
                    </tr>
                </tbody>
            </table>
        </div>

        <div class="mb-3">
            <button type="button" class="btn btn-sm btn-success" id="add-ligne-btn">
                <i class="fas fa-plus"></i> Ajouter une ligne
            </button>
        </div>

        <div class="row">
            <div class="col-md-6 offset-md-6">
                <div class="card">
                    <div class="card-body p-3">
                        <div class="row mb-2">
                            <div class="col-6 text-right"><strong>Total Articles:</strong></div>
                            <div class="col-6 text-right"><span id="total-montant">0.00</span> F</div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="mt-3" style="text-align: right;">
            <a href="index.php?action=stock_movements" class="btn btn-secondary btn-sm">
                <i class="fas fa-arrow-left"></i> Annuler
            </a>
            <button type="submit" class="btn btn-primary btn-sm">
                <i class="fas fa-save"></i> Enregistrer
            </button>
        </div>
    </form>
</div>

<script>
const fournisseursData = <?= json_encode($fournisseurs) ?>;
let ligneCount = 0;
let activeInput = null;

// ===== AUTOCOMPLETE FOURNISSEUR =====
const fournisseurSearch = document.getElementById('fournisseur_search');
const fournisseurResults = document.getElementById('fournisseur_results');
const fournisseurId = document.getElementById('fournisseur_id');
const depotId = document.getElementById('depot_id');

fournisseurSearch.addEventListener('input', function(e) {
    activeInput = this;
    const value = this.value.toLowerCase().trim();
    fournisseurResults.innerHTML = '';
    
    if (value.length < 1) {
        fournisseurResults.classList.remove('show');
        return;
    }
    
    const filtered = fournisseursData.filter(f =>
        f.nom_fournisseurs.toLowerCase().includes(value)
    );
    
    if (filtered.length === 0) {
        fournisseurResults.classList.remove('show');
        return;
    }
    
    filtered.forEach(f => {
        const div = document.createElement('div');
        div.className = 'autocomplete-item';
        div.textContent = f.nom_fournisseurs;
        div.dataset.id = f.id_fournisseurs;
        
        div.addEventListener('mousedown', function(e) {
            e.preventDefault();
            fournisseurSearch.value = f.nom_fournisseurs;
            fournisseurId.value = f.id_fournisseurs;
            fournisseurResults.classList.remove('show');
            activeInput = null;
        });
        
        fournisseurResults.appendChild(div);
    });
    
    fournisseurResults.classList.add('show');
});

// ===== AUTOCOMPLETE ARTICLES =====
function setupArticleAutocomplete(input, resultsDiv) {
    input.addEventListener('input', function(e) {
        activeInput = this;
        const fournisseurIdVal = fournisseurId.value;
        
        if (!fournisseurIdVal) {
            alert('Sélectionnez un fournisseur d\'abord');
            input.value = '';
            return;
        }

        const value = this.value.toLowerCase().trim();
        resultsDiv.innerHTML = '';
        
        if (value.length < 1) {
            resultsDiv.classList.remove('show');
            return;
        }
        
        // Requête AJAX pour récupérer les articles du fournisseur
        fetch(`index.php?action=reception_fournisseur/getArticlesByFournisseur&fournisseur_id=${fournisseurIdVal}&search=${encodeURIComponent(value)}`)
            .then(r => r.json())
            .then(articles => {
                resultsDiv.innerHTML = '';
                
                if (articles.length === 0) {
                    resultsDiv.classList.remove('show');
                    return;
                }
                
                articles.forEach(article => {
                    const div = document.createElement('div');
                    div.className = 'autocomplete-item';
                    div.innerHTML = `<strong>${article.nom_art}</strong> (${article.sku || 'N/A'}) - ${parseFloat(article.pr).toFixed(2)} F`;
                    div.dataset.id = article.id_articles;
                    div.dataset.nom = article.nom_art;
                    div.dataset.prix = article.pr;
                    
                    div.addEventListener('mousedown', function(e) {
                        e.preventDefault();
                        const row = input.closest('tr');
                        row.querySelector('[name*="[article_id]"]').value = article.id_articles;
                        row.querySelector('[name*="[prix_revient]"]').value = parseFloat(article.pr).toFixed(2);
                        row.querySelector('.prix-revient-display').textContent = parseFloat(article.pr).toFixed(2);
                        
                        resultsDiv.classList.remove('show');
                        input.value = article.nom_art;
                        
                        const quantiteInput = row.querySelector('[name*="[quantite]"]');
                        quantiteInput.focus();
                        updateRowTotal(row);
                        activeInput = null;
                    });
                    
                    resultsDiv.appendChild(div);
                });
                
                resultsDiv.classList.add('show');
            })
            .catch(e => {
                console.error('Erreur:', e);
                resultsDiv.classList.remove('show');
            });
    });
}

// Fermer tous les autocomplete si clic EN DEHORS
document.addEventListener('click', function(e) {
    if (!e.target.closest('.autocomplete-wrapper') && !e.target.closest('.autocomplete-item')) {
        document.querySelectorAll('.autocomplete-results').forEach(div => {
            div.classList.remove('show');
        });
        activeInput = null;
    }
});

// ===== GESTION LIGNES =====
function addLigne() {
    const container = document.getElementById('lignes-container');
    const empty = document.getElementById('ligne-vide-message');
    if (empty) empty.remove();
    
    const row = document.createElement('tr');
    row.innerHTML = `
        <td>
            <div class="autocomplete-wrapper" style="position: relative;">
                <input type="hidden" name="lignes[${ligneCount}][article_id]" value="">
                <input type="text" class="form-control form-control-sm article-search" placeholder="Chercher..." autocomplete="off">
                <div class="autocomplete-results"></div>
                <small class="article-nom d-block mt-1" style="color: #666;"></small>
            </div>
        </td>
        <td style="text-align: right;">
            <input type="number" name="lignes[${ligneCount}][quantite]" class="form-control form-control-sm" style="text-align: right;" min="0" step="0.01" value="0">
        </td>
        <td style="text-align: right;">
            <input type="hidden" name="lignes[${ligneCount}][prix_revient]" value="0">
            <span class="prix-revient-display">0.00</span>
        </td>
        <td style="text-align: right;">
            <span class="ligne-total">0.00</span> F
        </td>
        <td style="text-align: center;">
            <button type="button" class="btn btn-danger btn-sm" onclick="this.closest('tr').remove(); updateTotal();">
                <i class="fas fa-trash"></i>
            </button>
        </td>
    `;
    
    container.appendChild(row);
    
    const input = row.querySelector('.article-search');
    const resultsDiv = row.querySelector('.autocomplete-results');
    setupArticleAutocomplete(input, resultsDiv);
    
    row.querySelector('[name*="[quantite]"]').addEventListener('change', function() {
        updateRowTotal(this.closest('tr'));
    });
    
    ligneCount++;
}

function updateRowTotal(row) {
    const quantite = parseFloat(row.querySelector('[name*="[quantite]"]').value) || 0;
    const prix = parseFloat(row.querySelector('[name*="[prix_revient]"]').value) || 0;
    const total = quantite * prix;
    
    row.querySelector('.ligne-total').textContent = total.toFixed(2);
    updateTotal();
}

function updateTotal() {
    let totalMontant = 0;
    document.querySelectorAll('#reception-table tbody tr:not(#ligne-vide-message)').forEach(row => {
        const total = parseFloat(row.querySelector('.ligne-total').textContent) || 0;
        totalMontant += total;
    });
    
    document.getElementById('total-montant').textContent = totalMontant.toFixed(2);
}

document.getElementById('add-ligne-btn').addEventListener('click', function(e) {
    e.preventDefault();
    addLigne();
});

document.getElementById('reception-form').addEventListener('submit', function(e) {
    if (!fournisseurId.value) {
        e.preventDefault();
        alert('Sélectionnez un fournisseur !');
        return;
    }
    
    if (!depotId.value) {
        e.preventDefault();
        alert('Sélectionnez un dépôt !');
        return;
    }
    
    const lignes = document.querySelectorAll('#reception-table tbody tr:not(#ligne-vide-message)');
    if (lignes.length === 0) {
        e.preventDefault();
        alert('Ajoutez au moins une ligne !');
        return;
    }
    
    let ok = true;
    lignes.forEach(row => {
        const id = row.querySelector('[name*="[article_id]"]').value;
        const qty = parseFloat(row.querySelector('[name*="[quantite]"]').value) || 0;
        if (!id || qty <= 0) ok = false;
    });
    
    if (!ok) {
        e.preventDefault();
        alert('Remplissez tous les articles et quantités !');
    }
});
</script>