function triggerModalArticleSearch() {
    let term = $('#modal-article-search').val();
    $('#modal-articles-list').html('<div class="text-muted text-center p-2">Recherche en cours...</div>');
    $.ajax({
        url: 'index.php?action=devis/searchArticles&term=' + encodeURIComponent(term),
        type: 'GET',
        dataType: 'json',
        success: function(data) {
            if (data.length === 0) {
                $('#modal-articles-list').html('<div class="text-danger text-center p-2">Aucun article trouvé.</div>');
                return;
            }
            let html = '<ul class="list-group list-group-flush">';
            $.each(data, function(i, article) {
                // Affichage compact : nom + fournisseur + prix de revient
                html += `
<li class="list-group-item list-group-item-action modal-article-item d-flex flex-column flex-sm-row justify-content-between align-items-sm-center py-2" 
    data-id="${article.id_articles}" 
    data-nom="${article.nom_art.replace(/"/g,'&quot;')}"
    data-prix="${article.pr || 0}">
    <div class="article-info">
        <div style="font-weight:bold;font-size:1.02em;">${article.nom_art}</div>
        <div style="font-size:0.96em;color:#888;">
            <span class="badge badge-light border mr-1">
                 ${article.nom_fournisseurs || '-'}
            </span>
            <span class="badge badge-info border">
                Prix : ${article.pr !== undefined ? parseFloat(article.pr) % 1 === 0 ? parseInt(article.pr) : parseFloat(article.pr) : '-'}
            </span>
        </div>
    </div>
</li>`;
            });
            html += '</ul>';
            $('#modal-articles-list').html(html);
        },
        error: function() {
            $('#modal-articles-list').html('<div class="text-danger text-center p-2">Erreur lors de la recherche.</div>');
        }
    });
}

// Recherche dynamique au clavier dans la modal
$('#modal-article-search').on('input', function() {
    triggerModalArticleSearch();
});

// Sélection d'un article dans la modal
$(document).on('click', '.modal-article-item', function() {
    let id = $(this).data('id');
    let nom = $(this).data('nom');
    let prix = $(this).data('prix');
    let $input = window.__lastArticleInput;
    if ($input && $input.length) {
        let $wrapper = $input.closest('.autocomplete-wrapper');
        $input.val(nom);
        $wrapper.find('.article-id').val(id);
        let $tr = $wrapper.closest('tr');
        let $prixInput = $tr.find('.prix-unitaire-input');
        $prixInput.val(parseFloat(prix).toLocaleString('fr-FR', { minimumFractionDigits: 0 }));
        calculerTotalLigne($tr);
        $('#articlesModal').modal('hide');
        setTimeout(function() { $input.focus(); }, 350);
    }
});

// Focus input quand la modal s'ouvre
$('#articlesModal').on('shown.bs.modal', function () {
    $('#modal-article-search').focus();
});

