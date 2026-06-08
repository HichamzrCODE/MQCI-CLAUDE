// ----- GESTION LIGNES DEVIS -----

let articleIndexCounter = 0;

function checkLigneVideMessage() {
    // Si AU MOINS une ligne autre que #ligne-vide-message, on cache le message
    if ($('#devis-table tbody tr').not('#ligne-vide-message').length > 0) {
        $('#ligne-vide-message').hide();
    } else {
        $('#ligne-vide-message').show();
    }
}

function ligneDevisEstValide($ligne) {
    // Article ID
    const articleId = $ligne.find('.article-id').val();
    if (!articleId) return false;
    // Quantité
    const quantite = parseFloat($ligne.find('.quantite-input').val());
    if (isNaN(quantite) || quantite < 1) return false;
    // Prix
    const prix = parseFloat(($ligne.find('.prix-unitaire-input').val()+'').replace(',', '.'));
    if (isNaN(prix) || prix < 0) return false;
    return true;
}

$(document).ready(function() {
    checkLigneVideMessage();
    calculerTotal();

    $('#ajouter-ligne').click(function() {
        // EMPECHE L AJOUT DE LIGNE SI LE CLIENT N EST PAS SELECTIONNE   !
        if (!$('#client_id').val()) {
            alert("Sélectionnez d'abord un client !");
            return;
        }
        // EMPECHE D'AJOUTER UNE LIGNE SI LA PRECEDENTE N'EST PAS VALIDE
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
        <input type="number" class="form-control quantite-input input-quantite" name="articles[${index}][quantite]" value="1">
    </td>
    <td>
        <input type="text" class="form-control prix-unitaire-input input-prix" name="articles[${index}][prix_unitaire]" value="0">
    </td>
    <td class="total-ligne" data-total="0">0</td>
    <td>
        <button type="button" class="btn btn-danger remove-ligne" data-ligne-id="${ligneIdTemp}">X</button>
    </td>
</tr>
`;
        $('#devis-table tbody').append(nouvelleLigneHTML);
        checkLigneVideMessage();
        var $nouvelleLigne = $('#devis-table tbody tr:last');
        $nouvelleLigne[0].scrollIntoView({ behavior: "smooth", block: "center" });
        $nouvelleLigne.find('.article-autocomplete').focus();
    });

    // Suppression d'une ligne
    $(document).on('click', '.remove-ligne', function() {
        let $tr = $(this).closest('tr');
        let index = $tr.data('index'); // récupère l'index unique de la ligne
        $tr.remove();
        calculerTotal();
        checkLigneVideMessage();
    });

    // Calcul total par ligne et général
    $(document).on('input', '.prix-unitaire-input, .quantite-input', function() {
        var $ligne = $(this).closest('tr');
        calculerTotalLigne($ligne);
    });
});