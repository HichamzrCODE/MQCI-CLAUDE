// ----- OUTILS NUMÉRIQUES -----
function formatNumber(number) {
    return number.toLocaleString('fr-FR', { minimumFractionDigits: 0, maximumFractionDigits: 0 });
}
function parseNumber(numberString) {
    const cleanedString = (numberString||'').replace(/\s/g, '').replace(',', '.');
    const parsedNumber = parseFloat(cleanedString);
    return isNaN(parsedNumber) ? 0 : parsedNumber;
}

// ----- CALCUL LIGNE & TOTAL -----
function calculerTotalLigne(ligne) {
    var quantite = parseFloat(ligne.find('.quantite-input').val()) || 0;
    var prixUnitaire = parseNumber(ligne.find('.prix-unitaire-input').val());
    var totalLigne = quantite * prixUnitaire;
    ligne.find('.total-ligne').text(formatNumber(Math.round(totalLigne)));
    ligne.find('.total-ligne').attr('data-total', totalLigne);
    calculerTotal();
}
function calculerTotal() {
    var totalGeneral = 0;
    $('.total-ligne').each(function() {
        totalGeneral += parseFloat($(this).attr('data-total')) || 0;
    });
    $('#total-general').val(formatNumber(Math.round(totalGeneral)));
}

// ----- AUTOCOMPLETE -----
let autocompleteUpdateInProgress = false;
let autocompleteTimeout;

function autocompleteArticles(term, $autocompleteDiv, $input) {
    if (autocompleteTimeout) clearTimeout(autocompleteTimeout);
    autocompleteTimeout = setTimeout(function() {
        $.ajax({
            url: 'index.php?action=devis/searchArticles&term=' + encodeURIComponent(term),
            type: 'GET',
            dataType: 'json',
            beforeSend: function() { autocompleteUpdateInProgress = true; },
            success: function(data) {
                $autocompleteDiv.empty();
                let maxResults = 10;
                let displayed = 0;
                if (data.length > 0) {
                    $.each(data, function(index, article) {
                        if (displayed < maxResults) {
                            $autocompleteDiv.append(
                                `<a href="#" class="autocomplete-item" data-id="${article.id_articles}" data-prix="${article.prix}">${article.nom_art}</a>`
                            );
                            displayed++;
                        }
                    });
                    // Ajout du bouton "+ d'article" si plus de 10 résultats
                    if (data.length > maxResults) {
                        $autocompleteDiv.append(
                            `<a href="#" class="autocomplete-item-more" style="font-weight:bold;padding:7px 10px;color:#0056b3;background:#f6f8ff;text-align:center;">+ d'article...</a>`
                        );
                    }
                    $autocompleteDiv.show();
                    $autocompleteDiv.find('.autocomplete-item').removeClass('autocomplete-item-active');
                    $autocompleteDiv.find('.autocomplete-item').first().addClass('autocomplete-item-active');
                } else {
                    $autocompleteDiv.hide();
                }
            },
            error: function() { $autocompleteDiv.hide(); },
            complete: function() { autocompleteUpdateInProgress = false; }
        });
    }, 300);
}

// Gestion du clic sur "+ d'article"
$(document).on('click', '.autocomplete-item-more', function(e) {
    e.preventDefault();
    let $wrapper = $(this).closest('.autocomplete-wrapper');
    let $input = $wrapper.find('.article-autocomplete');
    // Stocke l'input courant pour l'injection après sélection dans la modal
    window.__lastArticleInput = $input;
    $('#modal-article-search').val($input.val() || '');
    $('#modal-articles-list').html('<div class="text-muted text-center p-2">Recherche en cours...</div>');
    $('#articlesModal').modal('show');
    // Lance la recherche avancée (fonction dans articles-modal.js)
    if (typeof triggerModalArticleSearch === 'function') {
        triggerModalArticleSearch();
    }
});

// Sélection d'un article (autocomplete normal)
function selectAutocompleteItem($item) {
    var $wrapper = $item.closest('.autocomplete-wrapper');
    var articleId = $item.data('id');
    var articleName = $item.text();
    var $input = $wrapper.find('.article-autocomplete');
    var $articleIdInput = $wrapper.find('.article-id');
    var $tr = $wrapper.closest('tr');
    var $prixUnitaireInput = $tr.find('.prix-unitaire-input');
    $input.val(articleName);
    $articleIdInput.val(articleId);
    $wrapper.find('.autocomplete-results').hide();

    var prixParDefaut = '0.00';
    var clientId = $('#client_id').val();

    if (!clientId || isNaN(clientId)) {
        $prixUnitaireInput.val(prixParDefaut);
        calculerTotalLigne($tr);
        return;
    }
    $.ajax({
        url: 'index.php?action=devis/getClientArticlePrice&client_id=' + clientId + '&article_id=' + articleId,
        type: 'GET',
        dataType: 'json',
        success: function(data) {
            if (data && data.prix !== null && data.prix !== undefined) {
                var prixString = data.prix.replace(/\s/g, '').replace(',', '.');
                var prixNumerique = parseFloat(prixString);
                if (!isNaN(prixNumerique)) {
                    $prixUnitaireInput.val(prixNumerique.toFixed(0));
                } else {
                    $prixUnitaireInput.val(prixParDefaut);
                }
            } else {
                $prixUnitaireInput.val(prixParDefaut);
            }
            calculerTotalLigne($tr);
        },
        error: function() {
            $prixUnitaireInput.val(prixParDefaut);
            calculerTotalLigne($tr);
        }
    });
}

// Navigation clavier autocomplete article
$(document).on('keydown', '.article-autocomplete', function(e) {
    var $wrapper = $(this).closest('.autocomplete-wrapper');
    var $autocompleteDiv = $wrapper.find('.autocomplete-results');
    if (!$autocompleteDiv.is(':visible')) return;
    var $items = $autocompleteDiv.find('.autocomplete-item');
    var $active = $autocompleteDiv.find('.autocomplete-item-active');
    if (e.keyCode === 40) { // Down
        e.preventDefault();
        if ($items.length > 0) {
            if ($active.length === 0) {
                $items.first().addClass('autocomplete-item-active')[0].scrollIntoView({ block: 'nearest' });
            } else {
                var $next = $active.next('.autocomplete-item');
                $active.removeClass('autocomplete-item-active');
                ($next.length > 0 ? $next : $items.first())
                    .addClass('autocomplete-item-active')[0].scrollIntoView({ block: 'nearest' });
            }
        }
    } else if (e.keyCode === 38) { // Up
        e.preventDefault();
        if ($items.length > 0) {
            if ($active.length === 0) {
                $items.last().addClass('autocomplete-item-active')[0].scrollIntoView({ block: 'nearest' });
            } else {
                var $prev = $active.prev('.autocomplete-item');
                $active.removeClass('autocomplete-item-active');
                ($prev.length > 0 ? $prev : $items.last())
                    .addClass('autocomplete-item-active')[0].scrollIntoView({ block: 'nearest' });
            }
        }
    } else if (e.keyCode === 13) { // Enter
        if ($active.length > 0) {
            e.preventDefault();
            selectAutocompleteItem($active);
            $(this).blur();
        }
    }
});

$(document).on('keyup', '.article-autocomplete', function(e) {
    if ([38, 40, 13].includes(e.keyCode)) return;
    if (autocompleteUpdateInProgress) return;
    var $input = $(this);
    var $wrapper = $input.closest('.autocomplete-wrapper');
    var $autocompleteDiv = $wrapper.find('.autocomplete-results');
    var term = $input.val();
    if (term.length >= 2) {
        autocompleteArticles(term, $autocompleteDiv, $input);
    } else {
        $autocompleteDiv.hide();
    }
});

$(document).on('mouseenter', '.autocomplete-item', function() {
    $(this).siblings().removeClass('autocomplete-item-active');
    $(this).addClass('autocomplete-item-active');
});

// Handler unique pour client ET article
$(document).on('click', '.autocomplete-item', function(e) {
    e.preventDefault();
    var $wrapper = $(this).closest('.autocomplete-wrapper');
    if ($wrapper.find('.client-autocomplete').length) {
        selectAutocompleteClient($(this));
    } else if ($wrapper.find('.article-autocomplete').length) {
        selectAutocompleteItem($(this));
    }
});

// Masque la liste si clic ailleurs
$(document).on('click', function(e) {
    if (!$(e.target).closest('.autocomplete-results, .article-autocomplete, .client-autocomplete').length) {
        $('.autocomplete-results').hide();
    }
});

// ----- AUTOCOMPLETE CLIENTS -----
function autocompleteClients(term, $autocompleteDiv, $input) {
    if (autocompleteTimeout) clearTimeout(autocompleteTimeout);
    autocompleteTimeout = setTimeout(function () {
        $.ajax({
            url: 'index.php?action=clients/search&term=' + encodeURIComponent(term),
            type: 'GET',
            dataType: 'json',
            success: function (data) {
                $autocompleteDiv.empty();
                if (data.length > 0) {
                    $.each(data, function (index, client) {
                        $autocompleteDiv.append(
                            `<a href="#" class="autocomplete-item" data-id="${client.id_clients}">${client.nom}</a>`
                        );
                    });
                    $autocompleteDiv.show();
                    $autocompleteDiv.find('.autocomplete-item').removeClass('autocomplete-item-active');
                    $autocompleteDiv.find('.autocomplete-item').first().addClass('autocomplete-item-active');
                } else {
                    $autocompleteDiv.hide();
                }
            },
            error: function () { $autocompleteDiv.hide(); }
        });
    }, 300);
}

function selectAutocompleteClient($item) {
    var $wrapper = $item.closest('.autocomplete-wrapper');
    var clientId = $item.data('id');
    var clientNom = $item.text();
    $wrapper.find('.client-autocomplete').val(clientNom);
    $wrapper.find('[name="client_id"]').val(clientId);
    $wrapper.find('.autocomplete-results').hide();
}

// Navigation clavier autocomplete client
$(document).on('keydown', '.client-autocomplete', function(e) {
    var $wrapper = $(this).closest('.autocomplete-wrapper');
    var $autocompleteDiv = $wrapper.find('.autocomplete-results');
    if (!$autocompleteDiv.is(':visible')) return;
    var $items = $autocompleteDiv.find('.autocomplete-item');
    var $active = $autocompleteDiv.find('.autocomplete-item-active');
    if (e.keyCode === 40) { // Down
        e.preventDefault();
        if ($items.length > 0) {
            if ($active.length === 0) {
                $items.first().addClass('autocomplete-item-active')[0].scrollIntoView({ block: 'nearest' });
            } else {
                var $next = $active.next('.autocomplete-item');
                $active.removeClass('autocomplete-item-active');
                ($next.length > 0 ? $next : $items.first())
                    .addClass('autocomplete-item-active')[0].scrollIntoView({ block: 'nearest' });
            }
        }
    } else if (e.keyCode === 38) { // Up
        e.preventDefault();
        if ($items.length > 0) {
            if ($active.length === 0) {
                $items.last().addClass('autocomplete-item-active')[0].scrollIntoView({ block: 'nearest' });
            } else {
                var $prev = $active.prev('.autocomplete-item');
                $active.removeClass('autocomplete-item-active');
                ($prev.length > 0 ? $prev : $items.last())
                    .addClass('autocomplete-item-active')[0].scrollIntoView({ block: 'nearest' });
            }
        }
    } else if (e.keyCode === 13) { // Enter
        if ($active.length > 0) {
            e.preventDefault();
            selectAutocompleteClient($active);
            $(this).blur();
        }
    }
});

$(document).on('keyup', '.client-autocomplete', function (e) {
    // Sauter les flèches/entrée (voir articles)
    if ([38, 40, 13].includes(e.keyCode)) return;
    var $input = $(this);
    var $wrapper = $input.closest('.autocomplete-wrapper');
    var $autocompleteDiv = $wrapper.find('.autocomplete-results');
    var term = $input.val();
    if (term.length >= 2) {
        autocompleteClients(term, $autocompleteDiv, $input);
    } else {
        $autocompleteDiv.hide();
        $wrapper.find('[name="client_id"]').val('');
    }
});