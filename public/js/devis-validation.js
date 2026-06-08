document.addEventListener('DOMContentLoaded', function() {
    const form = document.getElementById('devis-form');
    if (!form) return;

    form.addEventListener('submit', function(e) {
        let hasError = false;
        // Retire les anciennes erreurs
        form.querySelectorAll('.is-invalid').forEach(el => el.classList.remove('is-invalid'));
        form.querySelectorAll('.invalid-feedback.js').forEach(el => el.remove());

        // Client
        const client = form.querySelector('[name="client_id"]');
        if (!client.value) {
            client.classList.add('is-invalid');
            showError(client, "Le client est obligatoire.");
            hasError = true;
        }
        // Date
        const date = form.querySelector('[name="date"]');
        if (!date.value) {
            date.classList.add('is-invalid');
            showError(date, "La date est obligatoire.");
            hasError = true;
        }
        // Lignes (exclure le message d'aide)
        const lignes = form.querySelectorAll('tbody tr:not(#ligne-vide-message)');
        if (lignes.length === 0) {
            alert("Veuillez ajouter au moins un article.");
            hasError = true;
        }
        lignes.forEach((row, idx) => {
            // Article
            const artId = row.querySelector('.article-id');
            if (!artId || !artId.value) {
                const artInput = row.querySelector('.article-autocomplete');
                if (artInput) {
                    artInput.classList.add('is-invalid');
                    showError(artInput, "Article obligatoire.");
                }
                hasError = true;
            }
            // Quantité
            const quantite = row.querySelector('.quantite-input');
            const quantiteVal = quantite ? parseFloat(quantite.value) : 0;
            if (!quantite || !quantite.value || isNaN(quantiteVal) || quantiteVal < 1) {
                if (quantite) {
                    quantite.classList.add('is-invalid');
                    showError(quantite, "Quantité invalide.");
                }
                hasError = true;
            }
            // Prix unitaire
            const prix = row.querySelector('.prix-unitaire-input');
let prixVal = prix ? (prix.value + '').replace(/\s/g, '').replace(',', '.') : '';
if (!prix || prixVal === '' || isNaN(prixVal) || parseFloat(prixVal) < 0) {
    if (prix) {
        prix.classList.add('is-invalid');
        showError(prix, "Prix unitaire invalide.");
    }
    hasError = true;
}
        });

        if (hasError) {
            e.preventDefault();
        }
    });

    function showError(input, message) {
        const div = document.createElement('div');
        div.className = 'invalid-feedback js';
        div.textContent = message;
        input.parentNode.appendChild(div);
    }
});