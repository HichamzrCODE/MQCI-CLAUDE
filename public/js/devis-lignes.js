// ----- GESTION LIGNES DEVIS -----

let articleIndexCounter = 0;
const TVA_COEF = 1.18; // 18%

function checkLigneVideMessage() {
  if ($('#devis-table tbody tr').not('#ligne-vide-message').length > 0) $('#ligne-vide-message').hide();
  else $('#ligne-vide-message').show();
}

function parsePrix(val) {
  const n = parseFloat((String(val || '')).replace(/[\s\u00A0]/g, '').replace(',', '.'));
  return isNaN(n) ? NaN : n;
}

function formatPrix2(val) {
  if (val === null || val === undefined || isNaN(val)) return '0,00';
  return Number(val).toLocaleString('fr-FR', { minimumFractionDigits: 2, maximumFractionDigits: 2 });
}

// ====== PR VALIDATION (ROUGE SEULEMENT, PAS DE MESSAGE) ======
window.validatePrixVsPR = function ($ligne) {
  const $prix = $ligne.find('.prix-unitaire-input');
  const pr = parsePrix($prix.attr('data-pr')); // PR TTC
  const prixTtc = parsePrix($prix.val());      // PU TTC saisi

  // pas de PR => pas de contrôle
  if (isNaN(pr) || pr <= 0) {
    $prix.removeClass('is-invalid');
    return true;
  }

  // vide / non numérique / 0 => pas d'erreur live
  if (($prix.val() || '').trim() === '' || isNaN(prixTtc) || prixTtc <= 0) {
    $prix.removeClass('is-invalid');
    return true;
  }

  if (prixTtc < pr) {
    $prix.addClass('is-invalid');
    return false;
  }

  $prix.removeClass('is-invalid');
  return true;
};

function ligneDevisEstValide($ligne) {
  const articleId = $ligne.find('.article-id').val();
  if (!articleId) return false;

  const quantite = parseFloat($ligne.find('.quantite-input').val());
  if (isNaN(quantite) || quantite < 1) return false;

  const prixTtc = parsePrix($ligne.find('.prix-unitaire-input').val());
  if (isNaN(prixTtc) || prixTtc < 0) return false;

  // ✅ Contrôle PR
  if (!window.validatePrixVsPR($ligne)) return false;

  return true;
}

// ====== SYNC PRIX ======
function setHTFromTTC($ligne) {
  const ttc = parsePrix($ligne.find('.prix-unitaire-input').val());
  const ht = (!isNaN(ttc) && ttc > 0) ? (ttc / TVA_COEF) : 0;
  $ligne.find('.prix-ht-input').val(formatPrix2(ht));
}

function setTTCFromHT_ifTTCEmptyOrZero($ligne) {
  const ttc = parsePrix($ligne.find('.prix-unitaire-input').val());
  const ht = parsePrix($ligne.find('.prix-ht-input').val());

  const ttcEmptyOrZero = (isNaN(ttc) || ttc <= 0);
  if (ttcEmptyOrZero && !isNaN(ht) && ht > 0) {
    const newTtc = ht * TVA_COEF;
    const $ttc = $ligne.find('.prix-unitaire-input');
    $ttc.val(formatPrix2(newTtc));
    $ttc.trigger('input'); // ✅ revalider + recalculer
  }
}

// ====== TOTALS ======
function calculerTotalLigne($ligne) {
  const qte = parseFloat($ligne.find('.quantite-input').val());
  const prixTtc = parsePrix($ligne.find('.prix-unitaire-input').val());

  const q = (!isNaN(qte) && qte > 0) ? qte : 0;
  const p = (!isNaN(prixTtc) && prixTtc >= 0) ? prixTtc : 0;

  const total = q * p;
  $ligne.find('.total-ligne').attr('data-total', total).text(formatPrix2(total));

  calculerTotal();
}

function calculerTotal() {
  let sum = 0;
  $('#devis-table tbody tr.ligne-article').each(function () {
    const t = parsePrix($(this).find('.total-ligne').attr('data-total'));
    if (!isNaN(t)) sum += t;
  });
  $('#total-general').val(formatPrix2(sum));
}

function initTooltipsIn(container) {
  // Bootstrap 4 tooltip init (si tu utilises l'input-group "PR")
  $(container).find('[data-toggle="tooltip"]').tooltip();
}

$(document).ready(function () {
  checkLigneVideMessage();
  calculerTotal();

  initTooltipsIn(document);

  $('#ajouter-ligne').click(function () {
    if (!$('#client_id').val()) {
      alert("Sélectionnez d'abord un client !");
      return;
    }

    const $lastLigne = $('#devis-table tbody tr.ligne-article:last');
    if ($lastLigne.length && !ligneDevisEstValide($lastLigne)) {
      alert("Veuillez d'abord remplir correctement la dernière ligne avant d'en ajouter une nouvelle !");
      $lastLigne.find('.article-autocomplete').focus();
      return;
    }

    let index = articleIndexCounter++;
    let ligneIdTemp = 'temp_' + Date.now() + '_' + index;

    let nouvelleLigneHTML = `
<tr class="ligne-article" data-ligne-id="${ligneIdTemp}" data-index="${index}">
  <td>
    <div class="autocomplete-wrapper" style="position:relative;">
      <input type="text" class="form-control article-autocomplete input-article" placeholder="Rechercher un article">
      <input type="hidden" class="article-id" name="articles[${index}][article_id]">
      <div class="autocomplete-results"></div>
    </div>
  </td>
  <td>
    <input type="text" class="form-control input-description" name="articles[${index}][description]" placeholder="Description (optionnel)">
  </td>
  <td>
    <input type="number" class="form-control quantite-input input-quantite" name="articles[${index}][quantite]" value="1" min="1">
  </td>
  <td>
    <input type="text" class="form-control prix-ht-input input-prix" placeholder="HT" value="0,00">
  </td>
  <td>
    <div class="input-group input-group-sm">
      
      <input type="text"
             class="form-control prix-unitaire-input input-prix"
             name="articles[${index}][prix_unitaire]"
             value="0,00"
             data-pr="0">
    </div>
  </td>
  <td class="total-ligne text-right" data-total="0">0,00</td>
  <td>
    <button type="button" class="btn btn-danger remove-ligne" data-ligne-id="${ligneIdTemp}">X</button>
  </td>
</tr>`;

    $('#devis-table tbody').append(nouvelleLigneHTML);
    checkLigneVideMessage();

    initTooltipsIn($('#devis-table tbody tr:last'));

    var $nouvelleLigne = $('#devis-table tbody tr:last');
    $nouvelleLigne[0].scrollIntoView({ behavior: "smooth", block: "center" });
    $nouvelleLigne.find('.article-autocomplete').focus();
  });

  $(document).on('click', '.remove-ligne', function () {
    $(this).closest('tr').remove();
    calculerTotal();
    checkLigneVideMessage();
  });

  $(document).on('input', '.quantite-input', function () {
    calculerTotalLigne($(this).closest('tr'));
  });

  // ✅ live TTC
  $(document).on('input blur change', '.prix-unitaire-input', function () {
    const $ligne = $(this).closest('tr');
    setHTFromTTC($ligne);
    window.validatePrixVsPR($ligne);
    calculerTotalLigne($ligne);
  });

  // ✅ live HT
  $(document).on('input blur change', '.prix-ht-input', function () {
    const $ligne = $(this).closest('tr');
    setTTCFromHT_ifTTCEmptyOrZero($ligne);
    window.validatePrixVsPR($ligne);
    calculerTotalLigne($ligne);
  });

  // ✅ valider au chargement (si edit)
  $('#devis-table tbody tr.ligne-article').each(function () {
    window.validatePrixVsPR($(this));
  });
});