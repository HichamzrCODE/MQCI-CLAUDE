function triggerModalArticleSearch() {
  let term = $('#modal-article-search').val();
  $('#modal-articles-list').html('<div class="text-muted text-center p-2">Recherche en cours...</div>');

  $.ajax({
    url: 'index.php?action=devis/searchArticles&term=' + encodeURIComponent(term),
    type: 'GET',
    dataType: 'json',
    success: function (data) {
      if (!data || data.length === 0) {
        $('#modal-articles-list').html('<div class="text-danger text-center p-2">Aucun article trouvé.</div>');
        return;
      }

      let html = '<ul class="list-group list-group-flush">';
      $.each(data, function (i, article) {
        const nomSafe = (article.nom_art || '').replace(/"/g, '&quot;');
        const pr = article.pr || 0;

        html += `
<li class="list-group-item list-group-item-action modal-article-item d-flex flex-column flex-sm-row justify-content-between align-items-sm-center py-2"
    data-id="${article.id_articles}"
    data-nom="${nomSafe}"
    data-pr="${pr}"
    data-prix_vente="${article.prix_vente || ''}">
    <div class="article-info">
      <div style="font-weight:bold;font-size:1.02em;">${article.nom_art || ''}</div>
      <div style="font-size:0.96em;color:#888;">
        <span class="badge badge-light border mr-1">${article.nom_fournisseurs || '-'}</span>
        <span class="badge badge-info border">
          PR : ${article.pr !== undefined ? Number(article.pr).toLocaleString('fr-FR', { minimumFractionDigits: 2, maximumFractionDigits: 2 }) : '-'}
        </span>
      </div>
    </div>
</li>`;
      });

      html += '</ul>';
      $('#modal-articles-list').html(html);
    },
    error: function () {
      $('#modal-articles-list').html('<div class="text-danger text-center p-2">Erreur lors de la recherche.</div>');
    }
  });
}

$('#modal-article-search').on('input', function () {
  triggerModalArticleSearch();
});

$(document).on('click', '.modal-article-item', function () {
  const id = $(this).data('id');
  const nom = $(this).data('nom');
  const prRaw = $(this).data('pr');
  const prixVente = $(this).data('prix_vente');

  const prNum = parseFloat(String(prRaw).replace(/[\s\u00A0]/g, '').replace(',', '.')) || 0;

  const $input = window.__lastArticleInput;
  if (!$input || !$input.length) return;

  const $wrapper = $input.closest('.autocomplete-wrapper');
  $input.val(nom);
  $wrapper.find('.article-id').val(id);

  const $tr = $wrapper.closest('tr');
  const $prixInput = $tr.find('.prix-unitaire-input');
  const $htInput = $tr.find('.prix-ht-input');

  // 1) PR dans data-pr (pour validation avant Enregistrer)
  $prixInput.attr('data-pr', prNum);

  // 2) Remplir PU TTC (si prix_vente dispo)
  if (prixVente !== '' && prixVente !== null && prixVente !== undefined) {
    const pv = parseFloat(String(prixVente).replace(/[\s\u00A0]/g, '').replace(',', '.')) || 0;
    $prixInput.val(pv.toLocaleString('fr-FR', { minimumFractionDigits: 2, maximumFractionDigits: 2 }));
  }

  // 3) Sync HT depuis TTC
  if (typeof setHTFromTTC === 'function') {
    setHTFromTTC($tr);
  } else {
    const ttc = parseFloat(String($prixInput.val()).replace(/[\s\u00A0]/g, '').replace(',', '.')) || 0;
    $htInput.val((ttc / 1.18).toLocaleString('fr-FR', { minimumFractionDigits: 2, maximumFractionDigits: 2 }));
  }

  // 4) recalcul + validation PR live si dispo
  $prixInput.trigger('change');
  if (window.validatePrixVsPR) window.validatePrixVsPR($tr);
  if (typeof calculerTotalLigne === 'function') calculerTotalLigne($tr);

  $('#articlesModal').modal('hide');
  setTimeout(function () { $input.focus(); }, 350);
});

$('#articlesModal').on('shown.bs.modal', function () {
  $('#modal-article-search').focus();
});