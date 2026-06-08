<?php
require_once __DIR__ . '/../models/FournisseurReleve.php';
require_once __DIR__ . '/../models/fournisseur.php';
require_once __DIR__ . '/../includes/permissions.php';

class FournisseurReleveController
{
    private PDO $db;
    private FournisseurReleve $model;
    private fournisseur $fournisseurModel;

    const PER_PAGE = 10;

    public function __construct(PDO $db)
    {
        $this->db               = $db;
        $this->model            = new FournisseurReleve($db);
        $this->fournisseurModel = new fournisseur($db);
    }

    private function restrict(): void
    {
        if (!isset($_SESSION['user_id'])) die("Accès refusé.");
        if (!hasPermission('fs', 'view') && !hasPermission('fournisseurs', 'view')) die("Accès refusé.");
    }

    /**
     * Liste tous les fournisseurs avec leurs totaux
     */
    public function index(): array
    {
        $this->restrict();
        $fournisseurs = $this->model->getAllFournisseursAvecTotaux();

        $totalFactures = array_sum(array_column($fournisseurs, 'total_facture'));
        $totalVerse    = array_sum(array_column($fournisseurs, 'total_verse'));
        $totalSolde    = array_sum(array_column($fournisseurs, 'solde'));

        return [
            'view' => 'fournisseur_releve/index',
            'data' => compact('fournisseurs', 'totalFactures', 'totalVerse', 'totalSolde'),
        ];
    }

    /**
     * Relevé détaillé d'un fournisseur
     */
    public function show(int $fournisseur_id, ?string $date_debut, ?string $date_fin, int $page = 1): array
    {
        $this->restrict();

        $fournisseur = $this->fournisseurModel->findById($fournisseur_id);
        if (!$fournisseur) die("Fournisseur introuvable.");

        $page     = max(1, $page);
        $per_page = self::PER_PAGE;

        $all_lignes   = $this->model->getAllLignes($fournisseur_id, $date_debut, $date_fin);
        $total_lignes = count($all_lignes);
        $total_pages  = max(1, (int) ceil($total_lignes / $per_page));
        $page         = min($page, $total_pages);
        $offset       = ($page - 1) * $per_page;

        $solde_ouverture  = $this->model->getSoldeOuverture($fournisseur_id, $date_debut);
        $solde_avant_page = $solde_ouverture;
        for ($i = 0; $i < $offset; $i++) {
            $solde_avant_page += (float)$all_lignes[$i]['montant'] - (float)$all_lignes[$i]['versement'];
        }

        $lignes        = array_slice($all_lignes, $offset, $per_page);
        $total_general = $this->model->getTotalGeneral($fournisseur_id);

        return [
            'view' => 'fournisseur_releve/show',
            'data' => compact(
                'fournisseur', 'fournisseur_id', 'lignes',
                'solde_avant_page', 'solde_ouverture',
                'date_debut', 'date_fin',
                'page', 'total_pages', 'total_lignes', 'per_page',
                'total_general'
            ),
        ];
    }
}
