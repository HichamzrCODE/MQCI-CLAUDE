<<<<<<< HEAD
<?php include '../views/layout.php'; ?>

<?php if (isset($devis)): ?>
<style> .devis-reference {
        font-size: 11px;
        color: #b3b3b3;
        margin-top: -2px;
        margin-bottom: 5px;
        margin-left: 15px;
        display: block;
        font-style: italic;
        letter-spacing: 0.5px;
    }</style>
<div class="container border p-4 mt-4" style="position:relative; min-height:320mm;">
    <!-- En-tête avec logo et infos société -->
    <div class="d-flex justify-content-between align-items-center">
        <img src="<?= BASE_URL ?>/img/LOGO.png" style="width: 300px; height: auto;">
        <div class="text-right">
            <p class="font-weight-bold mb-1">
                <span id="bl-mention" style="display:none;"> BL du </span>
                <?= htmlspecialchars($devis['numero']); ?>
            </p>
            <p style="font-size: 12px;">Succursale 06 - Adjamé Carrefour marché Gouro</p>
            <p style="font-size: 12px; margin-top: -15px;">07 07 27 52 52  -  07 09 96 09 09</p>
            <p style="font-weight: bold;">
                <?= (new DateTime($devis['date']))->format("d/m/Y"); ?>
            </p>
        </div>
    </div>

    <!-- Nom du client et référence -->
    <p class="mt-4 font-weight-bold" style="font-size:24px; color:#333;">
        * <?= htmlspecialchars($nom_client ?? 'Client inconnu'); ?>
        <?php if (!empty($devis['reference'])): ?>
            <span class="devis-reference">
                Référence: <?= htmlspecialchars($devis['reference']) ?>
            </span>
        <?php endif; ?>
    </p>

    <!-- Actions -->
    <div class="mb-3">
        <a href="index.php?action=devis" class="btn btn-secondary float-right ml-2">Retour à la liste</a>
        <button onclick="imprimerDiv()" class="btn btn-primary float-right ml-2">Imprimer</button>
        <button onclick="imprimerBLDiv()" class="btn btn-danger float-right ml-2">BL</button> 
    </div>

    <!-- Tableau du devis -->
    <table class="table" style="width:100%; min-height:400px; text-transform:uppercase; font-size:14px;">
        <thead>
            <tr>
                <th style="width:40%; font-size:20px;">Article</th>
                <th style="width:20%;">Quantité</th>
                <th style="width:20%;" class="prix-unitaire">Prix Unitaire</th>
                <th style="width:20%;" class="total-ligne">Total</th>
            </tr>
        </thead>
        <tbody>
        <?php foreach ($lignesDevis as $ligne): ?>
            <tr>
                <td style="word-wrap: break-word;">
                    <span style="font-size:16px; color:#222;">
                        <?= htmlspecialchars($ligne['nom_art']) ?>
                    </span>
                    <?php if (!empty($ligne['description'])): ?>
                        <div style="font-size:11px; color:#888; font-style:italic; margin-top:2px; margin-bottom:2px;">
                            <?= nl2br(htmlspecialchars($ligne['description'])) ?>
                        </div>
                    <?php endif; ?>
                </td>
                <td><?= htmlspecialchars($ligne['quantite']) ?></td>
                <td class="prix-unitaire"><?= number_format($ligne['prix_unitaire'], 0, '', ' ') ?></td>
                <td class="total-ligne"><?= number_format($ligne['total'], 0, '', ' ') ?></td>
            </tr>
        <?php endforeach; ?>
        </tbody>
    </table>
    <!-- Total et pied de page -->
    <div class="footer pt-5 text-center" style="font-size:14.5px;">
        <p class="text-right border-top pt-2 total-general">
            <b style="font-size:20px; color:#3D3D3D;"><?= number_format($devis['total'], 0, '', ' ') ?></b>
        </p>
        RCCM : CI-YOP-2010-B-304 - CC N°10 06880 U - REGIME
        D IMPOSITION : Réel Normal Centre des Moyennes Entreprises (CME), Plateau - Yopougon zone industrielle, rue solibra
        compte bancaire NSIA CI042 01231 031260278882 53      - BANQUE ATLANTIQUE : CI034 01007 015103060006 13
    </div>
</div>
<script>
function imprimerDiv() { window.print(); }
function imprimerBLDiv() {
    document.body.classList.add('bl-print');
    document.getElementById('bl-mention').style.display = 'inline';
    window.print();
    document.body.classList.remove('bl-print');
    document.getElementById('bl-mention').style.display = 'none';
}
</script>
=======
<?php include '../views/layout.php'; ?>

<?php if (isset($devis)): ?>
<style> .devis-reference {
        font-size: 11px;
        color: #b3b3b3;
        margin-top: -2px;
        margin-bottom: 5px;
        margin-left: 15px;
        display: block;
        font-style: italic;
        letter-spacing: 0.5px;
    }</style>
<div class="container border p-4 mt-4" style="position:relative; min-height:320mm;">
    <!-- En-tête avec logo et infos société -->
    <div class="d-flex justify-content-between align-items-center">
        <img src="<?= BASE_URL ?>/img/LOGO.png" style="width: 300px; height: auto;">
        <div class="text-right">
            <p class="font-weight-bold mb-1">
                <span id="bl-mention" style="display:none;"> BL du </span>
                <?= htmlspecialchars($devis['numero']); ?>
            </p>
            <p style="font-size: 12px;">Succursale 06 - Adjamé Carrefour marché Gouro</p>
            <p style="font-size: 12px; margin-top: -15px;">07 07 27 52 52  -  07 09 96 09 09</p>
            <p style="font-weight: bold;">
                <?= (new DateTime($devis['date']))->format("d/m/Y"); ?>
            </p>
        </div>
    </div>

    <!-- Nom du client et référence -->
    <p class="mt-4 font-weight-bold" style="font-size:24px; color:#333;">
        * <?= htmlspecialchars($nom_client ?? 'Client inconnu'); ?>
        <?php if (!empty($devis['reference'])): ?>
            <span class="devis-reference">
                Référence: <?= htmlspecialchars($devis['reference']) ?>
            </span>
        <?php endif; ?>
    </p>

    <!-- Actions -->
    <div class="mb-3">
        <a href="index.php?action=devis" class="btn btn-secondary float-right ml-2">Retour à la liste</a>
        <button onclick="imprimerDiv()" class="btn btn-primary float-right ml-2">Imprimer</button>
        <button onclick="imprimerBLDiv()" class="btn btn-danger float-right ml-2">BL</button> 
    </div>

    <!-- Tableau du devis -->
    <table class="table" style="width:100%; min-height:400px; text-transform:uppercase; font-size:14px;">
        <thead>
            <tr>
                <th style="width:40%; font-size:20px;">Article</th>
                <th style="width:20%;">Quantité</th>
                <th style="width:20%;" class="prix-unitaire">Prix Unitaire</th>
                <th style="width:20%;" class="total-ligne">Total</th>
            </tr>
        </thead>
        <tbody>
        <?php foreach ($lignesDevis as $ligne): ?>
            <tr>
                <td style="word-wrap: break-word;">
                    <span style="font-size:16px; color:#222;">
                        <?= htmlspecialchars($ligne['nom_art']) ?>
                    </span>
                    <?php if (!empty($ligne['description'])): ?>
                        <div style="font-size:11px; color:#888; font-style:italic; margin-top:2px; margin-bottom:2px;">
                            <?= nl2br(htmlspecialchars($ligne['description'])) ?>
                        </div>
                    <?php endif; ?>
                </td>
                <td><?= htmlspecialchars($ligne['quantite']) ?></td>
                <td class="prix-unitaire"><?= number_format($ligne['prix_unitaire'], 0, '', ' ') ?></td>
                <td class="total-ligne"><?= number_format($ligne['total'], 0, '', ' ') ?></td>
            </tr>
        <?php endforeach; ?>
        </tbody>
    </table>
    <!-- Total et pied de page -->
    <div class="footer pt-5 text-center" style="font-size:14.5px;">
        <p class="text-right border-top pt-2 total-general">
            <b style="font-size:20px; color:#3D3D3D;"><?= number_format($devis['total'], 0, '', ' ') ?></b>
        </p>
        RCCM : CI-YOP-2010-B-304 - CC N°10 06880 U - REGIME
        D IMPOSITION : Réel Normal Centre des Moyennes Entreprises (CME), Plateau - Yopougon zone industrielle, rue solibra
        compte bancaire NSIA CI042 01231 031260278882 53      - BANQUE ATLANTIQUE : CI034 01007 015103060006 13
    </div>
</div>
<script>
function imprimerDiv() { window.print(); }
function imprimerBLDiv() {
    document.body.classList.add('bl-print');
    document.getElementById('bl-mention').style.display = 'inline';
    window.print();
    document.body.classList.remove('bl-print');
    document.getElementById('bl-mention').style.display = 'none';
}
</script>
>>>>>>> 4f8dbbb6b83eb9c6f755d57287033c7da885a3b1
<?php endif; ?>