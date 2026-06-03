<?php include '../views/layout.php'; ?>

<style>
.table-sm th, .table-sm td {
    font-size: 0.92rem;
    padding: 0.35rem 0.5rem;
}
.col-credit {
    padding-right: 8px;
    padding-left: 8px;
}
@media print {
    @page { size: landscape; margin: 10mm; }
    #print-btn, #client-search, header, footer, nav { display: none !important; }
    .row {
        display: flex !important;
        flex-direction: row !important;
        break-inside: avoid;
        page-break-inside: avoid;
    }
    .col-credit {
        float: none !important;
        display: block !important;
        width: 33.33333% !important;
        max-width: 33.33333% !important;
        border-left: 2px solid #e2e2e2;
    }
    .col-credit:first-child { border-left: none; }
    body, .table-sm th, .table-sm td { font-size: 0.85rem !important; }
    h1 { font-size: 1.15rem !important; }
}
</style>

<div class="container mt-4">
    <h1>Crédit client</h1>
    <div class="row mb-3 align-items-center">
      <div class="col-md-6">
        <input type="text" id="client-search" class="form-control" placeholder="Rechercher un client...">
      </div>
      <div class="col-md-6 text-end">
        <button class="btn btn-primary" id="print-btn" onclick="window.print()">
           Imprimer
        </button>
      </div>
    </div>
    <div class="row">
        <?php
        // Sécurisation des variables reçues de ton contrôleur
        $clients = $clients ?? [];
        $releves = $releves ?? [];

        // Mapping client_id => id du relevé (id colonne de credit_releves)
        $mapReleves = [];
        foreach ($releves as $rel) {
            $mapReleves[$rel['client_id']] = $rel['id'];
        }
        // Ajoute releve_id à chaque client (en string pour éviter soucis JS)
        foreach ($clients as &$client) {
            $client['releve_id'] = isset($mapReleves[$client['id_clients']]) ? (string)$mapReleves[$client['id_clients']] : '';
        }
        unset($client);

        $n = count($clients);
        $third = ceil($n / 3);
        $clients1 = array_slice($clients, 0, $third);
        $clients2 = array_slice($clients, $third, $third);
        $clients3 = array_slice($clients, 2 * $third);
        ?>

        <div class="col-lg-4 col-credit">
            <table class="table table-hover table-sm" id="credits-table-1">
                <thead>
                    <tr>
                        <th>Client</th>
                        <th>Total crédit</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach($clients1 as $client): ?>
                        <tr class="show-releve" data-releve="<?= htmlspecialchars($client['releve_id']) ?>" style="cursor:pointer">
                            <td><?= htmlspecialchars($client['nom']) ?></td>
                            <td><?= number_format($client['total_credit'],0,',',' ') ?></td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
        <div class="col-lg-4 col-credit">
            <table class="table table-hover table-sm" id="credits-table-2">
                <thead>
                    <tr>
                        <th>Client</th>
                        <th>Total crédit</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach($clients2 as $client): ?>
                        <tr class="show-releve" data-releve="<?= htmlspecialchars($client['releve_id']) ?>" style="cursor:pointer">
                            <td><?= htmlspecialchars($client['nom']) ?></td>
                            <td><?= number_format($client['total_credit'],0,',',' ') ?></td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
        <div class="col-lg-4 col-credit">
            <table class="table table-hover table-sm" id="credits-table-3">
                <thead>
                    <tr>
                        <th>Client</th>
                        <th>Total crédit</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach($clients3 as $client): ?>
                        <tr class="show-releve" data-releve="<?= htmlspecialchars($client['releve_id']) ?>" style="cursor:pointer">
                            <td><?= htmlspecialchars($client['nom']) ?></td>
                            <td><?= number_format($client['total_credit'],0,',',' ') ?></td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<script>
// Recherche sur les trois tableaux
$('#client-search').on('keyup', function() {
    var search = $(this).val().toLowerCase();
    $('table[id^="credits-table-"] tbody tr').each(function() {
        var client = $(this).find('td:first').text().toLowerCase();
        $(this).toggle(client.indexOf(search) !== -1);
    });
});

// Redirige vers le relevé du client au clic sur la ligne
document.querySelectorAll('.show-releve').forEach(function(row) {
    row.addEventListener('click', function() {
        var releveId = this.getAttribute('data-releve');
        if (releveId && releveId !== '') {
            window.location.href = '/s6/public/index.php?action=releve/show&id=' + releveId;
        } else {
            alert('Ce client ne possède pas de relevé.');
        }
    });
});
</script>