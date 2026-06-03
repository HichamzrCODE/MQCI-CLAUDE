// Empêche l'ajout de plusieurs lignes vides et gère l'état du bouton "Ajouter une ligne"

function isLastLigneReleveValid() {
    let lastRow = document.querySelector('#releve-lignes-table tbody tr:last-child');
    if (!lastRow) return true; // pas de ligne -> ok
    let date = lastRow.querySelector('input[type="date"]')?.value.trim();
    let montant = lastRow.querySelector('input.montant-input')?.value.trim();
    let versement = lastRow.querySelector('input.versement-input')?.value.trim();
    return (!!date && (!!montant || !!versement));
}

function updateAddLigneButtonState() {
    const btn = document.querySelector('.btn-ajouter-ligne, #add-ligne-btn');
    if (!btn) return;
    btn.disabled = !isLastLigneReleveValid();
}

// Interception du clic bouton
function checkAndAddLigne() {
    if (!isLastLigneReleveValid()) {
        alert("Veuillez d'abord compléter la dernière ligne avant d'en ajouter une nouvelle !");
        let lastRow = document.querySelector('#releve-lignes-table tbody tr:last-child');
        if (lastRow) {
            if (!lastRow.querySelector('input[type="date"]')?.value.trim()) {
                lastRow.querySelector('input[type="date"]').focus();
            } else if (
                !lastRow.querySelector('input.montant-input')?.value.trim() &&
                !lastRow.querySelector('input.versement-input')?.value.trim()
            ) {
                lastRow.querySelector('input.montant-input').focus();
            }
        }
        return;
    }
    addLigne();
    updateAddLigneButtonState();
}

// Écoute des changements sur les inputs pour activer/désactiver le bouton
document.addEventListener('input', function(e) {
    if (
        e.target.closest('#releve-lignes-table') &&
        (e.target.matches('input,select,textarea'))
    ) {
        updateAddLigneButtonState();
    }
});
document.addEventListener('change', function(e) {
    if (
        e.target.closest('#releve-lignes-table') &&
        (e.target.matches('input,select,textarea'))
    ) {
        updateAddLigneButtonState();
    }
});

// Interdit "Entrée" dans la dernière ligne si non valide
document.addEventListener('keydown', function(e) {
    const lastRow = document.querySelector('#releve-lignes-table tbody tr:last-child');
    if (
        lastRow &&
        lastRow.contains(e.target) &&
        e.key === "Enter" &&
        !isLastLigneReleveValid()
    ) {
        e.preventDefault();
        updateAddLigneButtonState();
    }
});

// Initialisation à l'ouverture
window.addEventListener('DOMContentLoaded', updateAddLigneButtonState);
