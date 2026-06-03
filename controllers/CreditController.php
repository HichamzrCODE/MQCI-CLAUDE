<?php
require_once __DIR__ . '\..\includes\permissions.php';
require_once '../models/CreditReleve.php';
require_once '../models/CreditLigne.php';
require_once '../models/Client.php';

class CreditController {
    private $db, $releveModel, $ligneModel, $clientModel;

    public function __construct(PDO $db) {
        $this->db = $db;
        $this->releveModel = new CreditReleve($db);
        $this->ligneModel = new CreditLigne($db);
        $this->clientModel = new Client($db);
    }

    public function index() {
        if (!isset($_SESSION['user_id']) || !hasPermission('credit', 'view')) {
            die("Accès refusé.");
        }

        $clients = $this->db->query("
            SELECT c.*, 
            IFNULL(
                (SELECT SUM(montant)-SUM(versement) 
                 FROM credit_lignes l 
                 JOIN credit_releves r ON l.releve_id = r.id 
                 WHERE r.client_id = c.id_clients), 0
            ) as total_credit
            FROM clients c
            HAVING total_credit != 0
            ORDER BY c.nom ASC
        ")->fetchAll(PDO::FETCH_ASSOC);

        $releves = $this->releveModel->getAll();
        return ['view'=>'credit/index','data'=>[
            'clients'=>$clients,
            'releves'=>$releves
        ]];
    }

    // AJAX : afficher relevé d'un client, max 5 lignes
    public function releveClientAjax($client_id) {
        if (!isset($_SESSION['user_id']) || !hasPermission('credit', 'view')) {
            return ['status'=>'error','html'=>'<div class="alert alert-danger">Accès refusé</div>'];
        }
        $releve = $this->releveModel->getByClient($client_id);
        if (!$releve) {
            return ['status'=>'error','html'=>'<div class="alert alert-info text-center"><strong>Désolé, Relevé non disponible</strong></div>'];
        }
        $releve_id = $releve['id'];
        $client = $this->clientModel->findById($releve['client_id']);
        $client_nom = $client['nom'] ?? '';

        $all_lignes = $this->ligneModel->getAllByReleve($releve_id);
        $total_lignes = count($all_lignes);
        $lignes = array_slice($all_lignes, -5, 5, true);

        $total_general = $releve['total_general'];

        ob_start();
        include '../views/credit/releve_ajax.php';
        $html = ob_get_clean();
        return ['status'=>'ok', 'html' => $html];
    }
}