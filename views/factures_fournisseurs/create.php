<?php include '../views/layout.php'; ?>
<?php
$errorFields   = $errorFields ?? [];
$error         = $error ?? null;
$numeroFacture = $numeroFacture ?? '';
$lignes        = $lignes ?? [];
$fournisseurs  = $fournisseurs ?? [];
$depots        = $depots ?? [];

$fournisseurIdSelected = $_POST['fournisseur_id'] ?? '';
$fournisseurNomSelected = $_POST['fournisseur_nom'] ?? '';

if ($fournisseurNomSelected === '' && $fournisseurIdSelected !== '' && !empty($fournisseurs)) {
    foreach ($fournisseurs as $f) {
        if ((string)$f['id_fournisseurs'] === (string)$fournisseurIdSelected) {
            $fournisseurNomSelected = $f['nom_fournisseurs'];
            break;
        }
    }
}
?>

<div class="container mt-4">
  <div class="d-flex justify-content-between align-items-center mb-3">
    <h4 class="mb-0">Créer une Facture Fournisseur</h4>

    <div class="d-flex" style="gap:8px;">
      <a class="btn btn-secondary btn-sm" href="index.php?action=factures_fournisseurs">← Retour</a>
      <button type="submit" form="ff-form" class="btn btn-success btn-sm">Enregistrer</button>
    </div>
  </div>

  <?php if ($error): ?>
    <div class="alert alert-danger"><?= htmlspecialchars($error) ?></div>
  <?php endif; ?>

  <?php if (!empty($errorFields)): ?>
    <div class="alert alert-warning">
      <strong>Veuillez corriger les erreurs suivantes :</strong>
      <ul class="mb-0 mt-2">
        <?php foreach ($errorFields as $key => $msg): ?>
          <li><?= htmlspecialchars($msg) ?></li>
        <?php endforeach; ?>
      </ul>
    </div>
  <?php endif; ?>

  <form method="post" action="index.php?action=factures_fournisseurs/create" id="ff-form" autocomplete="off">
    <div class="mb-3">
      <div class="d-flex justify-content-between align-items-start flex-wrap" style="gap:16px;">
        <div style="width:300px; max-width:100%;">
          <div class="autocomplete-wrapper" style="position:relative;">
            <input type="text"
                   class="form-control form-control-sm fournisseur-autocomplete <?= isset($errorFields['fournisseur_id']) ? 'is-invalid' : '' ?>"
                   id="fournisseur_nom"
                   name="fournisseur_nom"
                   placeholder="Rechercher un fournisseur..."
                   value="<?= htmlspecialchars($fournisseurNomSelected) ?>">

            <input type="hidden"
                   name="fournisseur_id"
                   id="fournisseur_id"
                   value="<?= htmlspecialchars($fournisseurIdSelected) ?>">

            <div class="autocomplete-results"
                 id="fournisseur-results"
                 style="display:none; position:absolute; z-index:2000; left:0; right:0;"></div>

            <?php if (isset($errorFields['fournisseur_id'])): ?>
              <div class="invalid-feedback d-block"><?= htmlspecialchars($errorFields['fournisseur_id']) ?></div>
            <?php endif; ?>
          </div>
        </div>

        <div style="width:240px; max-width:100%;">
          <input type="text"
                 class="form-control form-control-sm text-right"
                 name="numero"
                 value="<?= htmlspecialchars($numeroFacture) ?>"
                 readonly
                 style="background:#f7f7f7;font-weight:bold;">
        </div>
      </div>

      <div class="d-flex justify-content-between align-items-start flex-wrap mt-2" style="gap:16px;">
        <div style="width:300px; max-width:100%;">
          <select name="depot_id" class="form-control form-control-sm <?= isset($errorFields['depot_id']) ? 'is-invalid' : '' ?>">
            <option value="">-- Choisir un dépôt --</option>
            <?php foreach ($depots as $d): ?>
              <option value="<?= (int)$d['id'] ?>" <?= ((string)($_POST['depot_id'] ?? '') === (string)$d['id']) ? 'selected' : '' ?>>
                <?= htmlspecialchars($d['nom']) ?>
              </option>
            <?php endforeach; ?>
          </select>
          <?php if (isset($errorFields['depot_id'])): ?>
            <div class="invalid-feedback d-block"><?= htmlspecialchars($errorFields['depot_id']) ?></div>
          <?php endif; ?>
        </div>

        <div style="width:240px; max-width:100%;">
          <input type="date"
                 class="form-control form-control-sm <?= isset($errorFields['date']) ? 'is-invalid' : '' ?>"
                 name="date"
                 value="<?= htmlspecialchars($_POST['date'] ?? date('Y-m-d')) ?>">
          <?php if (isset($errorFields['date'])): ?>
            <div class="invalid-feedback d-block"><?= htmlspecialchars($errorFields['date']) ?></div>
          <?php endif; ?>
        </div>
      </div>

      <div class="mt-2">
        <textarea name="notes" class="form-control form-control-sm" rows="2" placeholder="Notes (optionnel)"><?= htmlspecialchars($_POST['notes'] ?? '') ?></textarea>
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

    <button type="button" class="btn btn-primary btn-sm" id="ajouter-ligne">Ajouter une ligne</button>

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
(function () {
  const lignesInitiales = <?= json_encode($lignes ?? [], JSON_UNESCAPED_UNICODE) ?>;
  const tbody = document.getElementById('ff-tbody');
  const btnAdd = document.getElementById('ajouter-ligne');
  const totalGeneralInput = document.getElementById('total-general');
  const form = document.getElementById('ff-form');

  const fournisseurInput = document.getElementById('fournisseur_nom');
  const fournisseurIdInput = document.getElementById('fournisseur_id');
  const fournisseurResults = document.getElementById('fournisseur-results');

  let articleIndexCounter = 0;
  let fournisseurTimer = null;

  function escapeHtml(value) {
    return String(value ?? '')
      .replace(/&/g, '&amp;')
      .replace(/</g, '&lt;')
      .replace(/>/g, '&gt;')
      .replace(/"/g, '&quot;')
      .replace(/'/g, '&#039;');
  }

  function parseNumber(value) {
    return parseFloat(String(value || '').replace(/[\s\u00A0]/g, '').replace(',', '.')) || 0;
  }

  function formatNumber(value) {
    return Number(value || 0).toLocaleString('fr-FR', { minimumFractionDigits: 0, maximumFractionDigits: 0 });
  }

  function updateTotals() {
    let totalGeneral = 0;
    tbody.querySelectorAll('tr').forEach(row => {
      const qte = parseNumber(row.querySelector('.quantite-input')?.value);
      const pu = parseNumber(row.querySelector('.prix-unitaire-input')?.value);
      const total = qte * pu;

      const totalCell = row.querySelector('.total-ligne');
      if (totalCell) {
        totalCell.textContent = formatNumber(Math.round(total));
        totalCell.dataset.total = total;
      }
      totalGeneral += total;
    });
    totalGeneralInput.value = formatNumber(Math.round(totalGeneral));
  }

  function createArticleResultItem(article) {
    const a = document.createElement('a');
    a.href = '#';
    a.className = 'autocomplete-item';
    a.dataset.id = article.id_articles || '';
    a.dataset.pr = article.pr || 0;
    a.textContent = article.nom_art || '';
    return a;
  }

  function hideResults(box) {
    box.style.display = 'none';
    box.innerHTML = '';
  }

  function bindArticleAutocomplete(row) {
    const input = row.querySelector('.article-autocomplete');
    const hidden = row.querySelector('.article-id');
    const priceInput = row.querySelector('.prix-unitaire-input');
    const results = row.querySelector('.autocomplete-results');
    let timer = null;

    input.addEventListener('input', function () {
      const term = input.value.trim();
      hidden.value = '';

      clearTimeout(timer);

      if (term.length < 2) {
        hideResults(results);
        return;
      }

      timer = setTimeout(() => {
        fetch('index.php?action=articles/search&term=' + encodeURIComponent(term), {
          headers: { 'X-Requested-With': 'XMLHttpRequest' }
        })
          .then(r => r.json())
          .then(data => {
            results.innerHTML = '';

            if (!Array.isArray(data) || data.length === 0) {
              hideResults(results);
              return;
            }

            data.slice(0, 10).forEach(article => {
              const item = createArticleResultItem(article);
              item.addEventListener('mousedown', function (e) {
                e.preventDefault();
                input.value = this.textContent;
                hidden.value = this.dataset.id || '';
                priceInput.value = formatNumber(Math.round(parseNumber(this.dataset.pr)));
                hideResults(results);
                updateTotals();
              });
              results.appendChild(item);
            });

            results.style.display = 'block';
          })
          .catch(() => hideResults(results));
      }, 250);
    });

    input.addEventListener('blur', function () {
      setTimeout(() => hideResults(results), 150);
    });
  }

  function addRow(data = {}, forcedIndex = null) {
    const index = forcedIndex !== null ? forcedIndex : articleIndexCounter++;
    const articleNom = data.nom_art || data.article_nom || '';
    const articleId = data.article_id || '';
    const description = data.description || '';
    const quantite = data.quantite || '1';
    const prixUnitaire = data.prix_unitaire || '0';

    const tr = document.createElement('tr');
    tr.className = 'ligne-article';
    tr.innerHTML = `
      <td style="min-width:240px;">
        <div class="autocomplete-wrapper" style="position:relative;">
          <input type="text"
                 class="form-control form-control-sm article-autocomplete"
                 placeholder="Rechercher un article"
                 value="${escapeHtml(articleNom)}">
          <input type="hidden"
                 class="article-id"
                 name="articles[${index}][article_id]"
                 value="${escapeHtml(articleId)}">
          <div class="autocomplete-results"
               style="display:none; position:absolute; z-index:2000; left:0; right:0; background:#fff; border:1px solid #ddd;"></div>
        </div>
      </td>

      <td style="min-width:200px;">
        <input type="text"
               class="form-control form-control-sm"
               name="articles[${index}][description]"
               value="${escapeHtml(description)}">
      </td>

      <td style="width:100px;">
        <input type="number"
               class="form-control form-control-sm quantite-input"
               name="articles[${index}][quantite]"
               value="${escapeHtml(quantite)}"
               min="1">
      </td>

      <td style="width:140px;">
        <input type="text"
               class="form-control form-control-sm prix-unitaire-input text-right"
               name="articles[${index}][prix_unitaire]"
               value="${escapeHtml(prixUnitaire)}">
      </td>

      <td class="total-ligne text-right" data-total="0" style="width:140px;">0</td>

      <td style="width:90px;">
        <button type="button" class="btn btn-danger btn-sm remove-ligne">X</button>
      </td>
    `;

    tr.querySelector('.remove-ligne').addEventListener('click', function () {
      tr.remove();
      updateTotals();
    });

    tr.querySelector('.quantite-input').addEventListener('input', updateTotals);
    tr.querySelector('.prix-unitaire-input').addEventListener('input', updateTotals);

    tbody.appendChild(tr);
    bindArticleAutocomplete(tr);
    updateTotals();
  }

  function showFournisseurResults(items) {
    if (!items || !items.length) {
      fournisseurResults.innerHTML = '<div class="list-group-item small text-muted">Aucun fournisseur trouvé</div>';
      fournisseurResults.style.display = 'block';
      return;
    }

    let html = '<div class="list-group">';
    items.forEach(item => {
      html += `
        <button type="button"
                class="list-group-item list-group-item-action"
                data-id="${item.id_fournisseurs}"
                data-name="${escapeHtml(item.nom_fournisseurs || '')}">
          ${escapeHtml(item.nom_fournisseurs || '')}
        </button>
      `;
    });
    html += '</div>';

    fournisseurResults.innerHTML = html;
    fournisseurResults.style.display = 'block';

    fournisseurResults.querySelectorAll('[data-id]').forEach(btn => {
      btn.addEventListener('mousedown', function (e) {
        e.preventDefault();
        fournisseurInput.value = this.dataset.name;
        fournisseurIdInput.value = this.dataset.id;
        hideResults(fournisseurResults);
      });
    });
  }

  fournisseurInput.addEventListener('input', function () {
    const term = fournisseurInput.value.trim();
    fournisseurIdInput.value = '';

    clearTimeout(fournisseurTimer);

    if (term.length < 1) {
      hideResults(fournisseurResults);
      return;
    }

    fournisseurTimer = setTimeout(() => {
      fetch('index.php?action=fournisseurs/search&term=' + encodeURIComponent(term), {
        headers: { 'X-Requested-With': 'XMLHttpRequest' }
      })
        .then(r => r.json())
        .then(data => showFournisseurResults(data))
        .catch(() => hideResults(fournisseurResults));
    }, 250);
  });

  fournisseurInput.addEventListener('blur', function () {
    setTimeout(() => hideResults(fournisseurResults), 150);
  });

  btnAdd.addEventListener('click', function () {
    const lastRow = tbody.querySelector('tr:last-child');
    if (lastRow) {
      const articleId = lastRow.querySelector('.article-id')?.value.trim() || '';
      const quantite = parseNumber(lastRow.querySelector('.quantite-input')?.value);
      if (!articleId || quantite <= 0) {
        alert("Veuillez d'abord remplir correctement la dernière ligne avant d'en ajouter une nouvelle.");
        lastRow.querySelector('.article-autocomplete')?.focus();
        return;
      }
    }
    addRow();
  });

  form.addEventListener('submit', function (e) {
    const fournisseurNom = fournisseurInput.value.trim();
    const fournisseurId = fournisseurIdInput.value.trim();

    if (fournisseurNom !== '' && fournisseurId === '') {
      e.preventDefault();
      alert('Veuillez sélectionner un fournisseur dans la liste proposée.');
      fournisseurInput.focus();
      return;
    }

    const rows = tbody.querySelectorAll('tr.ligne-article');
    for (const row of rows) {
      const articleNom = row.querySelector('.article-autocomplete')?.value.trim() || '';
      const articleId = row.querySelector('.article-id')?.value.trim() || '';

      if (articleNom !== '' && articleId === '') {
        e.preventDefault();
        alert('Veuillez sélectionner un article dans la liste proposée.');
        row.querySelector('.article-autocomplete')?.focus();
        return;
      }
    }
  });

  if (lignesInitiales.length > 0) {
    lignesInitiales.forEach((ligne, i) => {
      addRow(ligne, i);
      articleIndexCounter = i + 1;
    });
  } else {
    addRow();
    articleIndexCounter = 1;
  }

  updateTotals();
})();
</script>