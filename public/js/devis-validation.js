document.addEventListener('DOMContentLoaded', function () {
  const form = document.getElementById('devis-form');
  if (!form) return;

  // ✅ Supprime les messages submit (invalid-feedback.js) dès que l'utilisateur modifie un champ
  form.addEventListener('input', clearSubmitErrors, true);
  form.addEventListener('change', clearSubmitErrors, true);

  form.addEventListener('submit', function (e) {
    let hasError = false;

    // Nettoyage erreurs submit
    form.querySelectorAll('.is-invalid').forEach(el => el.classList.remove('is-invalid'));
    form.querySelectorAll('.invalid-feedback.js').forEach(el => el.remove());

    // Client
    const client = form.querySelector('[name="client_id"]');
    if (!client || !client.value) {
      if (client) client.classList.add('is-invalid');
      showError(client || form.querySelector('#client_nom'), "Le client est obligatoire.");
      hasError = true;
    }

    // Date
    const date = form.querySelector('[name="date"]');
    if (!date || !date.value) {
      if (date) date.classList.add('is-invalid');
      showError(date, "La date est obligatoire.");
      hasError = true;
    }

    // Lignes
    const lignes = form.querySelectorAll('tbody tr.ligne-article');
    if (lignes.length === 0) {
      alert("Veuillez ajouter au moins un article.");
      hasError = true;
    }

    lignes.forEach((row) => {
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

      // Prix TTC
      const prix = row.querySelector('.prix-unitaire-input');
      const prixVal = parseFloat(normalizeNumber(prix?.value));
      if (!prix || isNaN(prixVal) || prixVal < 0) {
        if (prix) {
          prix.classList.add('is-invalid');
          showError(prix, "Prix unitaire invalide.");
        }
        hasError = true;
      } else {
        // ✅ PR: on bloque l'envoi si invalide, mais PAS de message dessous
        const prVal = parseFloat(normalizeNumber(prix.getAttribute('data-pr')));
        if (!isNaN(prVal) && prVal > 0 && prixVal > 0 && prixVal < prVal) {
          prix.classList.add('is-invalid');
          hasError = true;

          // Force la validation live (au cas où)
          if (window.validatePrixVsPR) window.validatePrixVsPR($(row));
        }
      }
    });

    if (hasError) e.preventDefault();
  });

  function clearSubmitErrors(e) {
    const el = e.target;
    if (!el) return;

    // supprime seulement les messages ajoutés au submit sous CE champ
    if (el.parentNode) {
      el.parentNode.querySelectorAll('.invalid-feedback.js').forEach(n => n.remove());
    }

    // enlever la classe invalid "submit"
    el.classList.remove('is-invalid');

    // si prix => relancer validation PR live
    if (el.classList.contains('prix-unitaire-input') && window.validatePrixVsPR) {
      window.validatePrixVsPR($(el).closest('tr'));
    }
  }

  function showError(input, message) {
    if (!input) return;

    removeSubmitError(input);

    const div = document.createElement('div');
    div.className = 'invalid-feedback js d-block';
    div.textContent = message;
    input.parentNode.appendChild(div);
  }

  function removeSubmitError(input) {
    const parent = input.parentNode;
    if (!parent) return;
    parent.querySelectorAll('.invalid-feedback.js').forEach(n => n.remove());
  }

  function normalizeNumber(v) {
    return String(v ?? '').replace(/[\s\u00A0]/g, '').replace(',', '.');
  }
});