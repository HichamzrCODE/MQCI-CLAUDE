<?php include '../views/layout.php'; ?>
<?php require_once __DIR__ . '/../../includes/permissions.php'; ?>

<style>
.memo-columns {
    display: flex;
    flex-wrap: wrap;
    gap: 12px;
    margin-top: 18px;
    justify-content: center;
}
.memo-col {
    flex: 1 1 200px;
    display: flex;
    flex-direction: column;
    gap: 7px;
    min-width: 140px;
    max-width: 270px;
}
.memo-note {
    border-radius: 10px;
    box-shadow: 0 1px 3px #0001;
    padding: 8px 7px 5px 7px;
    transition: box-shadow 0.2s;
    border: 1px solid #e4e4e4;
}
.memo-col-cash-1 .memo-note {
    background: #f4fef6; /* Vert très pâle */
    border-color: #d2ecd8;
}
.memo-col-cash-2 .memo-note {
    background: #e3f7ea; /* Vert un peu plus soutenu */
    border-color: #b7e4c7;
}
.memo-col-ent-1 .memo-note {
    background: #fefbe6; /* Jaune très pâle */
    border-color: #f7efb4;
}
.memo-col-ent-2 .memo-note {
    background: #fdf6e2; /* Jaune un peu plus soutenu */
    border-color: #f2e2b6;
}
.memo-note h3 {
    font-size: 0.90rem;
    font-weight: 600;
    margin-bottom: 3px;
    display: flex;
    align-items: center;
    gap: 4px;
}
.memo-note ul {
    padding-left: 13px;
    margin-bottom: 2px;

}
.memo-note li {
    font-size: 0.80rem;
    margin-bottom: 3px; list-style: none;
}
.memo-tag {
    background: #ede6d6;
    border-radius: 6px;
    padding: 1px 5px;
    font-size: 0.79em;
    margin-left: 1px;
    color: #888;
}
@media (max-width: 1200px) {
    .memo-columns { flex-wrap: wrap; }
    .memo-col { max-width: 45%; }
}
@media (max-width: 900px) {
    .memo-columns { flex-direction: column; gap: 8px; }
    .memo-col { max-width: 100%; }
}
</style>

<div class="container">
    
    <div class="memo-columns">

        <!-- Cash : retard de paiement (Vert très pâle) -->
        <div class="memo-col memo-col-cash-1">
            <div class="memo-note">
                <h3>💸 Paiement en retard <span class="memo-tag">Cash</span></h3>
                <?php if (!empty($cashRetard)): ?>
                    <ul>
                    <?php foreach ($cashRetard as $c): ?>
                        <li>
                            <b><?= htmlspecialchars($c['nom']) ?></b>
                               -   <?= date('d/m/Y', strtotime($c['last_versement'])) ?>
                        </li>
                    <?php endforeach; ?>
                    </ul>
                <?php else: ?>
                    <div class="text-muted" style="font-size:0.82rem;">Aucun client cash en retard de paiement.</div>
                <?php endif; ?>
            </div>
        </div>

        <!-- Cash : inactifs (Vert un peu plus soutenu) -->
        <div class="memo-col memo-col-cash-2">
            <div class="memo-note">
                <h3>🕑 Inactifs<br> (pas de commande 2 sem.) <span class="memo-tag">Cash</span></h3>
                <?php if (!empty($cashSansCommande)): ?>
                    <ul>
                    <?php foreach ($cashSansCommande as $c): ?>
                        <li>
                            <b><?= htmlspecialchars($c['nom']) ?></b>
                               :  <?= date('d/m/Y', strtotime($c['last_operation'])) ?>
                        </li>
                    <?php endforeach; ?>
                    </ul>
                <?php else: ?>
                    <div class="text-muted" style="font-size:0.82rem;">Tous les clients cash ont eu une opération récemment.</div>
                <?php endif; ?>
            </div>
        </div>

<!-- ... -->
<div class="memo-col memo-col-ent-1">
    <div class="memo-note">
        <h3>📄 Factures échues <span class="memo-tag">Entreprise</span></h3>
        <?php if (!empty($factEcheance)): ?>
            <ul>
            <?php foreach ($factEcheance as $c): ?>
                <li>
                    <b><?= htmlspecialchars($c['nom']) ?></b> : <?= date('d/m/Y', strtotime($c['date_operation'])) ?>
                     - <i><?= number_format($c['reste_a_payer'],0,',',' ') ?> FCFA </i>
                </li>
            <?php endforeach; ?>
            </ul>
        <?php else: ?>
            <div class="text-muted" style="font-size:0.82rem;">Aucune facture échue à signaler.</div>
        <?php endif; ?>
    </div>
</div>
<!-- ... -->

        <!-- Entreprise : inactives (Jaune un peu plus soutenu) -->
        <div class="memo-col memo-col-ent-2">
            <div class="memo-note">
                <h3>🕑 Entreprise inactive <br>(1 mois) <span class="memo-tag">Entreprise</span></h3>
                <?php if (!empty($entSansCommande)): ?>
                    <ul>
                    <?php foreach ($entSansCommande as $c): ?>
                        <li>
                            <b><?= htmlspecialchars($c['nom']) ?></b>
                                -   <?= date('d/m/Y', strtotime($c['last_operation'])) ?>
                        </li>
                    <?php endforeach; ?>
                    </ul>
                <?php else: ?>
                    <div class="text-muted" style="font-size:0.82rem;">Toutes les entreprises ont eu une opération récemment.</div>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>