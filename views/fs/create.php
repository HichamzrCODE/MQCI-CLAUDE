<?php include '../views/layout.php'; ?>
<?php
// $error, $fournisseurs_sans_releve doivent être définis par le contrôleur
$error = $error ?? null;
$fournisseurs_sans_releve = $fournisseurs_sans_releve ?? [];
?>
<div class="container mt-4">
    <h4>Créer un relevé fournisseur</h4>
    <?php if ($error): ?>
        <div class="alert alert-danger"><?= htmlspecialchars($error) ?></div>
    <?php endif; ?>
    <form method="post" id="fsCreateForm" autocomplete="off">
        <div class="mb-3">
            <select name="fournisseur_id" class="form-select form-select-sm" required style="max-width:320px;">
                <option value="">Sélectionner le fournisseur</option>
                <?php foreach ($fournisseurs_sans_releve as $f): ?>
                    <option value="<?= $f['id_fournisseurs'] ?>">
                        <?= htmlspecialchars($f['nom_fournisseurs']) ?>
                    </option>
                <?php endforeach; ?>
            </select>
        </div>
        <div class="table-responsive mb-2">
            <table class="table table-bordered table-striped table-sm" id="fs-lignes-table">
                <thead>
                    <tr>
                        <th class="date">Date</th>
                        <th class="bc">BC F/S</th>
                        <th class="facture">Facture</th>
                        <th class="montant">Montant</th>
                        <th class="versement">Versement</th>
                        <th class="ligne-total">Total</th>
                        <th class="action-col"></th>
                    </tr>
                </thead>
                <tbody>
                </tbody>
                <tfoot>
                    <tr>
                        <th colspan="5" class="text-end">Total général :</th>
                        <th id="total-general">0,00</th>
                        <th></th>
                    </tr>
                </tfoot>
            </table>
        </div>
        <button type="button" class="btn btn-secondary btn-sm mb-2" id="add-ligne-btn">Ajouter une ligne</button>
        <button type="submit" class="btn btn-primary btn-sm mb-2">Enregistrer</button>
        <a href="index.php?action=fs" class="btn btn-secondary btn-sm mb-2">Annuler</a>
    </form>
</div>
<style>
.table th, .table td { vertical-align: middle; white-space: nowrap; padding: 3px 5px; font-size: 0.98rem; }
.table th.date, .table td.date { width: 50px; min-width: 50px; max-width: 100px; }
.table th.bc, .table td.bc, .table th.facture, .table td.facture { width: 120px; min-width: 120px; max-width: 180px; }
.table th.montant, .table td.montant, .table th.versement, .table td.versement { width: 100px; min-width: 90px; max-width: 150px; text-align: right; }
.table th.ligne-total, .table td.ligne-total { width: 90px; min-width: 75px; text-align: right; }
.table th.action-col, .table td.action-col { width: 22px; min-width: 18px; max-width: 28px; text-align: center; padding-left: 1px; padding-right: 1px; }
.table input[type="number"], .table input[type="text"] { text-align: right; font-size: 0.98rem; padding: 2px 4px; min-width: 68px; max-width: 100%; }
.table input[type="date"] { min-width: 98px; font-size: 0.98rem; padding: 2px 4px; }
@media print { .btn, .mb-2 { display: none !important; } body, .table th, .table td { font-size: 13px; } }
@media (max-width:768px) {
    .table th, .table td { font-size: 0.95rem; }
    .table th.bc, .table td.bc, .table th.facture, .table td.facture, .table th.montant, .table td.montant, .table th.versement, .table td.versement { max-width: 60px; }
}
</style>
<script>
let ligneIndexCounter = 0;
function formatNumberFR(n) {
    n = Number(n)||0;
    return n !== 0 ? n.toLocaleString('fr-FR',{minimumFractionDigits:2,maximumFractionDigits:2}) : '';
}
function parseNumberFR(val) {
    if(typeof val === "number") return val;
    return parseFloat((val||'').replace(/\s/g,'').replace(',','.')) || 0;
}
function recomputeTotals() {
    let rows = document.querySelectorAll('#fs-lignes-table tbody tr');
    let cumul = 0;
    rows.forEach(function(row) {
        let montant = parseNumberFR(row.querySelector('.montant-input')?.value || 0);
        let versement = parseNumberFR(row.querySelector('.versement-input')?.value || 0);
        cumul += montant - versement;
        row.querySelector('.ligne-total').innerText = formatNumberFR(cumul);
    });
    let totalGeneralElem = document.getElementById('total-general');
    if (totalGeneralElem) {
        totalGeneralElem.innerText = cumul !== 0 ? cumul.toLocaleString('fr-FR', { minimumFractionDigits: 2, maximumFractionDigits: 2 }) : '0,00';
    }
}
function removeLigne(btn) {
    btn.closest('tr').remove();
    recomputeTotals();
}
function addLigne() {
    let table = document.querySelector('#fs-lignes-table tbody');
    let index = ligneIndexCounter++;
    let tr = document.createElement('tr');
    tr.innerHTML = `
        <td class="date"><input type="date" name="lignes[${index}][date]" class="form-control form-control-sm" required></td>
        <td class="bc"><input type="text" name="lignes[${index}][bc]" class="form-control form-control-sm" maxlength="35"></td>
        <td class="facture"><input type="text" name="lignes[${index}][facture]" class="form-control form-control-sm" maxlength="35"></td>
        <td class="montant"><input type="text" inputmode="decimal" name="lignes[${index}][montant]" class="form-control form-control-sm montant-input"></td>
        <td class="versement"><input type="text" inputmode="decimal" name="lignes[${index}][versement]" class="form-control form-control-sm versement-input"></td>
        <td class="ligne-total">0,00</td>
        <td class="action-col"><button type="button" class="btn btn-danger btn-sm px-2 py-0" onclick="removeLigne(this)" title="Supprimer cette ligne">&times;</button></td>
    `;
    table.appendChild(tr);
    tr.querySelector('input[type="date"]').focus();
    recomputeTotals();
}
function isLastLigneComplete() {
    const rows = document.querySelectorAll('#fs-lignes-table tbody tr');
    if (rows.length === 0) return true;
    const last = rows[rows.length - 1];
    const date = last.querySelector('input[type="date"]').value;
    const montantStr = last.querySelector('.montant-input').value;
    const versementStr = last.querySelector('.versement-input').value;
    const montant = parseNumberFR(montantStr);
    const versement = parseNumberFR(versementStr);
    const montantRempli = montantStr.trim() !== "" && montant !== 0;
    const versementRempli = versementStr.trim() !== "" && versement !== 0;
    return !!date && (montantRempli || versementRempli);
}
document.addEventListener('blur', function(e) {
    if(e.target && (e.target.classList.contains('montant-input') || e.target.classList.contains('versement-input'))) {
        let val = parseNumberFR(e.target.value);
        e.target.value = formatNumberFR(val);
        recomputeTotals();
    }
}, true);
document.addEventListener('input', function(e) {
    if(e.target && (e.target.classList.contains('montant-input') || e.target.classList.contains('versement-input'))) {
        recomputeTotals();
    }
});
window.addEventListener('DOMContentLoaded', function() {
    ligneIndexCounter = 0;
    recomputeTotals();
});
document.getElementById('add-ligne-btn').addEventListener('click', function(e) {
    if (!isLastLigneComplete()) {
        alert("Complétez la dernière ligne avant d'en ajouter une autre.");
        return false;
    }
    addLigne();
});
document.getElementById('fsCreateForm').addEventListener('submit', function(e) {
    if (!isLastLigneComplete()) {
        alert("Complétez la dernière ligne avant d'enregistrer.");
        e.preventDefault();
        return false;
    }
    document.querySelectorAll('.montant-input, .versement-input').forEach(function(input) {
        var val = input.value;
        if (val) {
            val = val.replace(/\s/g, '').replace(',', '.');
            input.value = val;
        }
    });
});
</script>