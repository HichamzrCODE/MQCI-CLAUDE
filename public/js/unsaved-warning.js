<<<<<<< HEAD
// Alerte "modifications non enregistrées" sur le formulaire devis
let devisFormDirty = false;

// On détecte toute modification sur les inputs, selects ou textarea du formulaire
$(document).on('input change', '#devis-form input, #devis-form select, #devis-form textarea', function() {
    devisFormDirty = true;
});

// Quand on soumet le formulaire, on considère que tout est sauvegardé
$(document).on('submit', '#devis-form', function() {
    devisFormDirty = false;
});

// Avant de quitter la page, on affiche une alerte si besoin
window.addEventListener('beforeunload', function (e) {
    if (devisFormDirty) {
        e.preventDefault();
        // Certains navigateurs affichent un message personnalisé, d'autres non
        e.returnValue = '';
    }
});


// Alerte "modifications non enregistrées" sur les formulaires relevé (création et édition)
let releveFormDirty = false;

// Sur tous les champs des deux formulaires (create et edit)
$(document).on('input change', '#releveCreateForm input, #releveCreateForm select, #releveCreateForm textarea, #releveEditForm input, #releveEditForm select, #releveEditForm textarea', function() {
    releveFormDirty = true;
});

// Quand on soumet un des deux formulaires, on considère que tout est sauvegardé
$(document).on('submit', '#releveCreateForm, #releveEditForm', function() {
    releveFormDirty = false;
});

// Avant de quitter la page, on affiche une alerte si besoin
window.addEventListener('beforeunload', function (e) {
    if (releveFormDirty) {
        e.preventDefault();
        e.returnValue = '';
    }
=======
// Alerte "modifications non enregistrées" sur le formulaire devis
let devisFormDirty = false;

// On détecte toute modification sur les inputs, selects ou textarea du formulaire
$(document).on('input change', '#devis-form input, #devis-form select, #devis-form textarea', function() {
    devisFormDirty = true;
});

// Quand on soumet le formulaire, on considère que tout est sauvegardé
$(document).on('submit', '#devis-form', function() {
    devisFormDirty = false;
});

// Avant de quitter la page, on affiche une alerte si besoin
window.addEventListener('beforeunload', function (e) {
    if (devisFormDirty) {
        e.preventDefault();
        // Certains navigateurs affichent un message personnalisé, d'autres non
        e.returnValue = '';
    }
});


// Alerte "modifications non enregistrées" sur les formulaires relevé (création et édition)
let releveFormDirty = false;

// Sur tous les champs des deux formulaires (create et edit)
$(document).on('input change', '#releveCreateForm input, #releveCreateForm select, #releveCreateForm textarea, #releveEditForm input, #releveEditForm select, #releveEditForm textarea', function() {
    releveFormDirty = true;
});

// Quand on soumet un des deux formulaires, on considère que tout est sauvegardé
$(document).on('submit', '#releveCreateForm, #releveEditForm', function() {
    releveFormDirty = false;
});

// Avant de quitter la page, on affiche une alerte si besoin
window.addEventListener('beforeunload', function (e) {
    if (releveFormDirty) {
        e.preventDefault();
        e.returnValue = '';
    }
>>>>>>> 4f8dbbb6b83eb9c6f755d57287033c7da885a3b1
});