<<<<<<< HEAD
<?php

class DashboardController {
    private $db;
    public function __construct($db) { $this->db = $db; }

    public function index() {
        $clientModel = new Client($this->db);
        $cashRetard = $clientModel->getCashRetard();
        $cashSansCommande = $clientModel->getCashSansCommande();
        // On utilise la méthode qui tient compte des paiements globaux
        $factEcheance = $clientModel->getFacturesImpayeesParReleveSansTotalVersement();
        $entSansCommande = $clientModel->getEntSansCommande();

        return [
            'view' => 'dashboard/index', // ou 'dashboard' selon ton routeur
            'data' => [
                'username' => $_SESSION['username'] ?? 'Utilisateur',
                'cashRetard' => $cashRetard,
                'cashSansCommande' => $cashSansCommande,
                'factEcheance' => $factEcheance,
                'entSansCommande' => $entSansCommande,
            ]
        ];
    }
=======
<?php

class DashboardController {
    private $db;
    public function __construct($db) { $this->db = $db; }

    public function index() {
        $clientModel = new Client($this->db);
        $cashRetard = $clientModel->getCashRetard();
        $cashSansCommande = $clientModel->getCashSansCommande();
        // On utilise la méthode qui tient compte des paiements globaux
        $factEcheance = $clientModel->getFacturesImpayeesParReleveSansTotalVersement();
        $entSansCommande = $clientModel->getEntSansCommande();

        return [
            'view' => 'dashboard/index', // ou 'dashboard' selon ton routeur
            'data' => [
                'username' => $_SESSION['username'] ?? 'Utilisateur',
                'cashRetard' => $cashRetard,
                'cashSansCommande' => $cashSansCommande,
                'factEcheance' => $factEcheance,
                'entSansCommande' => $entSansCommande,
            ]
        ];
    }
>>>>>>> 4f8dbbb6b83eb9c6f755d57287033c7da885a3b1
}