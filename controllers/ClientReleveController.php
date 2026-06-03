<?php
require_once '../models/ClientReleve.php';
require_once '../models/Client.php';
require_once __DIR__ . '/../includes/permissions.php';

class ClientReleveController {
    private $db, $model, $clientModel;
    const PER_PAGE = 10;

    public function __construct(PDO $db) {
        $this->db = $db;
        $this->model = new ClientReleve($db);
        $this->clientModel = new Client($db);
    }

    private function restrict() {
        if (!isset($_SESSION['user_id'])) die("Accès refusé");
        if (!hasPermission('releve', 'view') && !hasPermission('clients', 'view')) die("Accès refusé");
    }

    /**
     * Affiche le relevé automatique d'un client (lecture seule, paginé, filtré par dates).
     */
    public function show(int $client_id, ?string $date_debut, ?string $date_fin, int $page = 1) {
        $this->restrict();

        $client = $this->clientModel->findById($client_id);
        if (!$client) die("Client introuvable.");

        $page    = max(1, $page);
        $per_page = self::PER_PAGE;

        // Récupère toutes les lignes de la période (devis + versements)
        $all_lignes   = $this->model->getAllLignes($client_id, $date_debut, $date_fin);
        $total_lignes = count($all_lignes);
        $total_pages  = max(1, (int)ceil($total_lignes / $per_page));
        $page         = min($page, $total_pages);
        $offset       = ($page - 1) * $per_page;

        // Solde avant le début de la période (si filtre date_debut)
        $solde_ouverture = $this->model->getSoldeOuverture($client_id, $date_debut);

        // Solde cumulé jusqu'au début de la page courante
        $solde_avant_page = $solde_ouverture;
        for ($i = 0; $i < $offset; $i++) {
            $solde_avant_page += (float)$all_lignes[$i]['montant'] - (float)$all_lignes[$i]['versement'];
        }

        // Lignes de la page courante
        $lignes = array_slice($all_lignes, $offset, $per_page);

        // Solde global (toutes dates, pour affichage en bas de page)
        $total_general = $this->model->getTotalGeneral($client_id);

        return [
            'view' => 'client_releve/show',
            'data' => compact(
                'client', 'client_id', 'lignes',
                'solde_avant_page', 'solde_ouverture',
                'date_debut', 'date_fin',
                'page', 'total_pages', 'total_lignes', 'per_page',
                'total_general'
            )
        ];
    }
}