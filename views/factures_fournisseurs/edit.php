<?php include '../views/layout.php'; ?>
<?php
$errorFields = $errorFields ?? [];
$error = $error ?? null;
$isValidated = (($facture['statut'] ?? 'draft') === 'validated');
$isAdmin = (($_SESSION['role'] ?? '') === 'admin');
$readonly = ($isValidated && !$isAdmin);
?>

<div class="container mt-4">
  <div class="d-flex justify-content-between align-items-center mb-3">
    <h4 class="mb-0">Modifier Facture Fournisseur</h4>

    <div class="d-flex" style="gap:8px;">
      <a class="btn btn-secondary btn-sm" href="index.php?action=factures_fournisseurs">← Retour</a>

      <?php if (!$readonly): ?>
        <button type="submit" form="ff-form" class="btn btn-success btn-sm">Enregistrer</button>
      <?php endif; ?>

      <?php if (!$isValidated): ?>
        <a class="btn btn-warning btn-sm" href="index.php?action=factures_fournisseurs/validate&id=<?= (int)$facture['id'] ?>" onclick="return confirm('Valider cette facture fournisseur ? Le stock sera incrémenté.');">Valider</a>
      <?php endif; ?>
    </div>
  </div>

  <?php if ($error): ?>
    <div class="alert alert-danger"><?= htmlspecialchars($error) ?></div>
  <?php endif; ?>

  <?php if ($readonly): ?>
    <div class="alert alert-info">Facture fournisseur validée : document en lecture seule.</div>
  <?php endif; ?>

  <form method="post" action="index.php?action=factures_fournisseurs/update&id=<?= (int)$facture['id'] ?>" id="ff-form" autocomplete="off">
    <div class="mb-3">
      <div class="d-flex justify-content-between align-items-start flex-wrap" style="gap:16px;">
        <div style="width:300px; max-width:100%;">
          <select name="fournisseur_id" class="form-control form-control-sm" <?= $readonly ? 'disabled' : '' ?>>
            <option value="">-- Choisir un fournisseur --</option>
            <?php foreach ($fournisseurs as $f): ?>
              <option value="<?= (int)$f['id_fournisseurs'] ?>" <?= ((int)$facture['fournisseur_id'] === (int)$f['id_fournisseurs']) ? 'selected' : '' ?>>
                <?= htmlspecialchars($f['nom_fournisseurs']) ?>
              </option>
            <?php endforeach; ?>
          </select>
        </div>

        <div style="width:240px; max-width:100%;">
          <input type="text"
                 class="form-control form-control-sm text-right"
                 value="<?= htmlspecialchars($facture['numero']) ?>"
                 readonly
                 style="background:#f7f7f7;font-weight:bold;">
        </div>
      </div>

      <div class="d-flex justify-content-between align-items-start flex-wrap mt-2" style="gap:16px;">
        <div style="width:300px; max-width:100%;">
          <select name="depot_id" class="form-control form-control-sm" <?= $readonly ? 'disabled' : '' ?>>
            <option value="">-- Choisir un dépôt --</option>
            <?php foreach ($depots as $d): ?>
              <option value="<?= (int)$d['id'] ?>" <?= ((int)$facture['depot_id'] === (int)$d['id']) ? 'selected' : '' ?>>
                <?= htmlspecialchars($d['nom']) ?>
              </option>
            <?php endforeach; ?>
          </select>
        </div>

        <div style="width:240px; max-width:100%;">
          <input type="date"
                 class="form-control form-control-sm"
                 name="date"
                 value="<?= htmlspecialchars($facture['date']) ?>"
                 <?= $readonly ? 'readonly' : '' ?>>
        </div>
      </div>

      <div class="mt-2">
        <textarea name="notes" class="form-control form-control-sm" rows="2" placeholder="Notes (optionnel)" <?= $readonly ? 'readonly' : '' ?>><?= htmlspecialchars($facture['notes'] ?? '') ?></textarea>
      </div>
    </div>

    <div class="table-responsive mb-2">
      <table class="table table-sm" id="ff-table">
        <thead>
          <tr>
            <th>Article</th>
            <th>Description</th>
            <th>Quantité</th>
            <th>PU Achat</th>
            <th class="text-right">Total</th>
            <th>Action</th>
          </tr>
        </thead>
        <tbody id="ff-tbody"></tbody>
      </table>
    </div>

    <?php if (!$readonly): ?>
      <button type="button" class="btn btn-primary btn-sm" id="ajouter-ligne">Ajouter une ligne</button>
    <?php endif; ?>

    <div class="d-flex justify-content-end mt-2">
      <div style="width: 250px; max-width:100%;">
        <label class="mb-1 font-weight-bold">Total Général</label>
        <input type="text" class="form-control text-right" id="total-general" value="0" readonly>
      </div>
    </div>
  </form>
</div>

<?php include '../views/footer.php'; ?>

<script>
const articles = <?= json_encode($articles ?? [], JSON_UNESCAPED_UNICODE) ?>;
const lignesInitiales = <?= json_encode($lignes ?? [], JSON_UNESCAPED_UNICODE) ?>;
const tbody = document.getElementById('ff-tbody');
const btnAdd = document.getElementById('ajouter-ligne');
const totalGeneralInput = document.getElementById('total-general');
const readonly = <?= $readonly ? 'true' : 'false' ?>;

function formatNumber(n) {
  return new Intl.NumberFormat('fr-FR', {maximumFractionDigits: 0}).format(n || 0);
}

function parseNumber(v) {
  if (typeof v !== 'string') v = String(v ?? '');
  v = v.replace(/\s/g, '').replace(',', '.');
  const n = parseFloat(v);
  return isNaN(n) ? 0 : n;
}

function renderArticleOptions(selectedId = '') {
  let html = '<option value="">-- Article --</option>';
  for (const a of articles) {
    const selected = String(selectedId) === String(a.id_articles) ? 'selected' : '';
    html += `<option value="${a.id_articles}" ${selected}>${a.nom_art}</option>`;
  }
  return html;
}

function updateTotals() {
  let totalGeneral = 0;
  document.querySelectorAll('#ff-tbody tr').forEach(row => {
    const qte = parseNumber(row.querySelector('.input-quantite')?.value || '0');
    const pu = parseNumber(row.querySelector('.input-prix')?.value || '0');
    const total = qte * pu;
    row.querySelector('.line-total').value = formatNumber(total);
    totalGeneral += total;
  });
  totalGeneralInput.value = formatNumber(totalGeneral);
}

function addRow(data = {}, index = null) {
  const rowIndex = index !== null ? index : document.querySelectorAll('#ff-tbody tr').length;
  const tr = document.createElement('tr');

  tr.innerHTML = `
    <td style="min-width:220px;">
      <select name="articles[${rowIndex}][article_id]" class="form-control form-control-sm" ${readonly ? 'disabled' : ''}>
        ${renderArticleOptions(data.article_id || '')}
      </select>
    </td>
    <td style="min-width:200px;">
      <input type="text" name="articles[${rowIndex}][description]" class="form-control form-control-sm" value="${(data.description || '').replace(/"/g, '&quot;')}" ${readonly ? 'readonly' : ''}>
    </td>
    <td style="width:100px;">
      <input type="number" min="1" step="1" name="articles[${rowIndex}][quantite]" class="form-control form-control-sm input-quantite" value="${data.quantite || ''}" ${readonly ? 'readonly' : ''}>
    </td>
    <td style="width:140px;">
      <input type="text" name="articles[${rowIndex}][prix_unitaire]" class="form-control form-control-sm input-prix text-right" value="${data.prix_unitaire || '0'}" ${readonly ? 'readonly' : ''}>
    </td>
    <td style="width:140px;">
      <input type="text" class="form-control form-control-sm line-total text-right" value="0" readonly>
    </td>
    <td style="width:90px;">
      ${readonly ? '' : '<button type="button" class="btn btn-danger btn-sm btn-remove">Supprimer</button>'}
    </td>
  `;

  if (!readonly) {
    tr.querySelector('.btn-remove')?.addEventListener('click', () => {
      tr.remove();
      updateTotals();
    });

    tr.querySelector('.input-quantite')?.addEventListener('input', updateTotals);
    tr.querySelector('.input-prix')?.addEventListener('input', updateTotals);
  }

  tbody.appendChild(tr);
  updateTotals();
}

if (!readonly && btnAdd) {
  btnAdd.addEventListener('click', () => addRow());
}

if (lignesInitiales.length) {
  lignesInitiales.forEach((l, i) => addRow(l, i));
} else {
  addRow();
}
</script>