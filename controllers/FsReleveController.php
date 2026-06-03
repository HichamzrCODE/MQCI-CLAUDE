<<<<<<< HEAD
<?php
require_once '../models/FsReleve.php';
require_once '../models/FsLigne.php';
require_once '../models/Fournisseur.php';
require_once __DIR__ . '\..\includes\permissions.php';

class FsReleveController {
    private $db, $releveModel, $ligneModel, $fournisseurModel;
    public function __construct(PDO $db) {
        $this->db = $db;
        $this->releveModel = new FsReleve($db);
        $this->ligneModel = new FsLigne($db);
        $this->fournisseurModel = new Fournisseur($db);
    }

    private function restrict($action = 'view') {
        if (!isset($_SESSION['user_id'])) die("Accès refusé");
        if (!hasPermission('fs', $action)) die("Accès refusé");
    }

    public function index() {
        $this->restrict('view');
        $releves = $this->releveModel->getAll();
        return ['view'=>'fs/index','data'=>['releves'=>$releves]];
    }

    public function show($id) {
        $this->restrict('view');
        $releve = $this->releveModel->get($id);
        $fournisseur = $this->fournisseurModel->findById($releve['fournisseur_id']);
        $lignes = $this->ligneModel->getAllByReleve($id);
        return ['view'=>'fs/show','data'=>compact('releve','fournisseur','lignes')];
    }

    public function create($data) {
        $this->restrict('create');
        $error = null;
        $fournisseurs = $this->fournisseurModel->getAll();
        $ids_fournisseurs_avec_releve = array_map(function($releve) {
            return $releve['fournisseur_id'];
        }, $this->releveModel->getAll());
        $fournisseurs_sans_releve = array_filter($fournisseurs, function($f) use ($ids_fournisseurs_avec_releve) {
            return !in_array($f['id_fournisseurs'], $ids_fournisseurs_avec_releve);
        });

        if ($_SERVER['REQUEST_METHOD']==='POST') {
            $fournisseur_id = $data['fournisseur_id'] ?? '';
            if (!$fournisseur_id) $error = "Choisir un fournisseur.";
            if (!$error && $this->releveModel->getByFournisseur($fournisseur_id)) {
                $error = "Ce fournisseur possède déjà un relevé !";
            }
            if (!$error) {
                $releve_id = $this->releveModel->create($fournisseur_id, $_SESSION['user_id']);
                if (isset($data['lignes'])) {
                    foreach ($data['lignes'] as $ligne) {
                        $date = $ligne['date'] ?? null;
                        $bc = $ligne['bc'] ?? null;
                        $facture = $ligne['facture'] ?? null;
                        $montant = floatval(str_replace([' ', ','], ['', '.'], $ligne['montant'] ?? 0));
                        $versement = floatval(str_replace([' ', ','], ['', '.'], $ligne['versement'] ?? 0));
                        $this->ligneModel->create($releve_id, $date, $bc, $facture, $montant, $versement);
                    }
                }
                $this->releveModel->updateSolde($releve_id);
                header('Location: index.php?action=fs');
                exit();
            }
        }
        return ['view'=>'fs/create','data'=>compact('fournisseurs_sans_releve','error')];
    }

    /**
     * Calcule le solde de départ (cumul des lignes avant l'offset).
     * @param array $all_lignes Toutes les lignes du relevé (triées !)
     * @param int $offset Nombre de lignes à cumuler
     * @return float Le cumul montant-versement des lignes avant l'offset
     */
    private function getSoldeDepart($all_lignes, $offset) {
    $solde = 0.0;
    for ($i = 0; $i < $offset && $i < count($all_lignes); $i++) {
        $solde += $all_lignes[$i]['montant'] - $all_lignes[$i]['versement'];
    }
    return $solde;
}

public function edit($id, $data) {
    $this->restrict('edit');
    $error = null;
    $releve = $this->releveModel->get($id);
    $fournisseurs = $this->fournisseurModel->getAll();
    $all_lignes = $this->ligneModel->getAllByReleve($id);
    $total_lignes = count($all_lignes);

    // Ajout gestion du paramètre GET all=1
    $afficher_tout = isset($_GET['all']) && $_GET['all'] == '1';
    if ($afficher_tout) {
        $lignes = $all_lignes;
        $offset = 0;
    } else {
        $offset = max(0, $total_lignes - 20);
        $lignes = array_slice($all_lignes, -20, 20, true);
    }
    $solde_depart = $this->getSoldeDepart($all_lignes, $offset);

    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        $fournisseur_id = $data['fournisseur_id'] ?? '';
        if (!$fournisseur_id) $error = "Choisir un fournisseur.";
        if (!$error) {
            $this->releveModel->update($id, $fournisseur_id);
            // On supprime les lignes affichées (20 ou tout) puis on recrée
            $ids_affichees = array_column($lignes, 'id');
            if (!empty($ids_affichees)) {
                $this->ligneModel->deleteByIds($ids_affichees);
            }
            if (isset($data['lignes'])) {
                foreach ($data['lignes'] as $ligne) {
                    $date = $ligne['date'] ?? null;
                    $bc = $ligne['bc'] ?? null;
                    $facture = $ligne['facture'] ?? null;
                    $montant = floatval(str_replace([' ', ','], ['', '.'], $ligne['montant'] ?? 0));
                    $versement = floatval(str_replace([' ', ','], ['', '.'], $ligne['versement'] ?? 0));
                    $this->ligneModel->create($id, $date, $bc, $facture, $montant, $versement);
                }
            }
            $this->releveModel->updateSolde($id);
            header('Location: index.php?action=fs');
            exit();
        }
        // Si erreur, on réinjecte les valeurs saisies pour affichage
        $lignes = [];
        if (isset($data['lignes'])) {
            foreach ($data['lignes'] as $i => $ligne) {
                $lignes[] = [
                    'id' => $ligne['id'] ?? null,
                    'date_operation' => $ligne['date'] ?? '',
                    'bc_fournisseur' => $ligne['bc'] ?? '',
                    'numero_facture' => $ligne['facture'] ?? '',
                    'montant' => $ligne['montant'] ?? '',
                    'versement' => $ligne['versement'] ?? '',
                ];
            }
        }
    }
    return [
        'view' => 'fs/edit',
        'data' => compact('releve', 'fournisseurs', 'lignes', 'error', 'solde_depart')
    ];
}

public function extrait($id, $date_debut = null, $date_fin = null) {
    $this->restrict('view');
    $releve = $this->releveModel->get($id);
    if (!$releve) die("Relevé fournisseur introuvable.");
    $fournisseur = $this->fournisseurModel->findById($releve['fournisseur_id']);

    // Calcul du solde d'ouverture sur la période
    $solde_ouverture = 0;
    if ($date_debut) {
        // Somme des mouvements AVANT la période
        $query = "SELECT COALESCE(SUM(montant),0) as total_montant, COALESCE(SUM(versement),0) as total_versement
                  FROM fs_lignes WHERE releve_id = ? AND date_operation < ?";
        $stmt = $this->db->prepare($query);
        $stmt->execute([$id, $date_debut]);
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        $solde_ouverture = ($row['total_montant'] ?? 0) - ($row['total_versement'] ?? 0);
    }

    // Lignes de la période
    $lignes = $this->ligneModel->getAllByReleve($id, $date_debut, $date_fin);

    // Calcul du total général (hors filtre)
    $queryTotal = "SELECT COALESCE(SUM(montant),0) as total_montant, COALESCE(SUM(versement),0) as total_versement
        FROM fs_lignes WHERE releve_id = ?";
    $stmtTotal = $this->db->prepare($queryTotal);
    $stmtTotal->execute([$id]);
    $rowTotal = $stmtTotal->fetch(PDO::FETCH_ASSOC);
    $total_general = ($rowTotal['total_montant'] ?? 0) - ($rowTotal['total_versement'] ?? 0);

    return [
        'view' => 'fs/extrait',
        'data' => compact('fournisseur', 'releve', 'lignes', 'date_debut', 'date_fin', 'solde_ouverture', 'total_general')
    ];
}

    public function delete($id) {
        $this->restrict('delete');
        $this->ligneModel->deleteByReleve($id);
        $this->releveModel->delete($id);
        header('Location: index.php?action=fs');
        exit();
    }
=======
<?php
require_once '../models/FsReleve.php';
require_once '../models/FsLigne.php';
require_once '../models/Fournisseur.php';
require_once __DIR__ . '\..\includes\permissions.php';

class FsReleveController {
    private $db, $releveModel, $ligneModel, $fournisseurModel;
    public function __construct(PDO $db) {
        $this->db = $db;
        $this->releveModel = new FsReleve($db);
        $this->ligneModel = new FsLigne($db);
        $this->fournisseurModel = new Fournisseur($db);
    }

    private function restrict($action = 'view') {
        if (!isset($_SESSION['user_id'])) die("Accès refusé");
        if (!hasPermission('fs', $action)) die("Accès refusé");
    }

    public function index() {
        $this->restrict('view');
        $releves = $this->releveModel->getAll();
        return ['view'=>'fs/index','data'=>['releves'=>$releves]];
    }

    public function show($id) {
        $this->restrict('view');
        $releve = $this->releveModel->get($id);
        $fournisseur = $this->fournisseurModel->findById($releve['fournisseur_id']);
        $lignes = $this->ligneModel->getAllByReleve($id);
        return ['view'=>'fs/show','data'=>compact('releve','fournisseur','lignes')];
    }

    public function create($data) {
        $this->restrict('create');
        $error = null;
        $fournisseurs = $this->fournisseurModel->getAll();
        $ids_fournisseurs_avec_releve = array_map(function($releve) {
            return $releve['fournisseur_id'];
        }, $this->releveModel->getAll());
        $fournisseurs_sans_releve = array_filter($fournisseurs, function($f) use ($ids_fournisseurs_avec_releve) {
            return !in_array($f['id_fournisseurs'], $ids_fournisseurs_avec_releve);
        });

        if ($_SERVER['REQUEST_METHOD']==='POST') {
            $fournisseur_id = $data['fournisseur_id'] ?? '';
            if (!$fournisseur_id) $error = "Choisir un fournisseur.";
            if (!$error && $this->releveModel->getByFournisseur($fournisseur_id)) {
                $error = "Ce fournisseur possède déjà un relevé !";
            }
            if (!$error) {
                $releve_id = $this->releveModel->create($fournisseur_id, $_SESSION['user_id']);
                if (isset($data['lignes'])) {
                    foreach ($data['lignes'] as $ligne) {
                        $date = $ligne['date'] ?? null;
                        $bc = $ligne['bc'] ?? null;
                        $facture = $ligne['facture'] ?? null;
                        $montant = floatval(str_replace([' ', ','], ['', '.'], $ligne['montant'] ?? 0));
                        $versement = floatval(str_replace([' ', ','], ['', '.'], $ligne['versement'] ?? 0));
                        $this->ligneModel->create($releve_id, $date, $bc, $facture, $montant, $versement);
                    }
                }
                $this->releveModel->updateSolde($releve_id);
                header('Location: index.php?action=fs');
                exit();
            }
        }
        return ['view'=>'fs/create','data'=>compact('fournisseurs_sans_releve','error')];
    }

    /**
     * Calcule le solde de départ (cumul des lignes avant l'offset).
     * @param array $all_lignes Toutes les lignes du relevé (triées !)
     * @param int $offset Nombre de lignes à cumuler
     * @return float Le cumul montant-versement des lignes avant l'offset
     */
    private function getSoldeDepart($all_lignes, $offset) {
    $solde = 0.0;
    for ($i = 0; $i < $offset && $i < count($all_lignes); $i++) {
        $solde += $all_lignes[$i]['montant'] - $all_lignes[$i]['versement'];
    }
    return $solde;
}

public function edit($id, $data) {
    $this->restrict('edit');
    $error = null;
    $releve = $this->releveModel->get($id);
    $fournisseurs = $this->fournisseurModel->getAll();
    $all_lignes = $this->ligneModel->getAllByReleve($id);
    $total_lignes = count($all_lignes);

    // Ajout gestion du paramètre GET all=1
    $afficher_tout = isset($_GET['all']) && $_GET['all'] == '1';
    if ($afficher_tout) {
        $lignes = $all_lignes;
        $offset = 0;
    } else {
        $offset = max(0, $total_lignes - 20);
        $lignes = array_slice($all_lignes, -20, 20, true);
    }
    $solde_depart = $this->getSoldeDepart($all_lignes, $offset);

    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        $fournisseur_id = $data['fournisseur_id'] ?? '';
        if (!$fournisseur_id) $error = "Choisir un fournisseur.";
        if (!$error) {
            $this->releveModel->update($id, $fournisseur_id);
            // On supprime les lignes affichées (20 ou tout) puis on recrée
            $ids_affichees = array_column($lignes, 'id');
            if (!empty($ids_affichees)) {
                $this->ligneModel->deleteByIds($ids_affichees);
            }
            if (isset($data['lignes'])) {
                foreach ($data['lignes'] as $ligne) {
                    $date = $ligne['date'] ?? null;
                    $bc = $ligne['bc'] ?? null;
                    $facture = $ligne['facture'] ?? null;
                    $montant = floatval(str_replace([' ', ','], ['', '.'], $ligne['montant'] ?? 0));
                    $versement = floatval(str_replace([' ', ','], ['', '.'], $ligne['versement'] ?? 0));
                    $this->ligneModel->create($id, $date, $bc, $facture, $montant, $versement);
                }
            }
            $this->releveModel->updateSolde($id);
            header('Location: index.php?action=fs');
            exit();
        }
        // Si erreur, on réinjecte les valeurs saisies pour affichage
        $lignes = [];
        if (isset($data['lignes'])) {
            foreach ($data['lignes'] as $i => $ligne) {
                $lignes[] = [
                    'id' => $ligne['id'] ?? null,
                    'date_operation' => $ligne['date'] ?? '',
                    'bc_fournisseur' => $ligne['bc'] ?? '',
                    'numero_facture' => $ligne['facture'] ?? '',
                    'montant' => $ligne['montant'] ?? '',
                    'versement' => $ligne['versement'] ?? '',
                ];
            }
        }
    }
    return [
        'view' => 'fs/edit',
        'data' => compact('releve', 'fournisseurs', 'lignes', 'error', 'solde_depart')
    ];
}

public function extrait($id, $date_debut = null, $date_fin = null) {
    $this->restrict('view');
    $releve = $this->releveModel->get($id);
    if (!$releve) die("Relevé fournisseur introuvable.");
    $fournisseur = $this->fournisseurModel->findById($releve['fournisseur_id']);

    // Calcul du solde d'ouverture sur la période
    $solde_ouverture = 0;
    if ($date_debut) {
        // Somme des mouvements AVANT la période
        $query = "SELECT COALESCE(SUM(montant),0) as total_montant, COALESCE(SUM(versement),0) as total_versement
                  FROM fs_lignes WHERE releve_id = ? AND date_operation < ?";
        $stmt = $this->db->prepare($query);
        $stmt->execute([$id, $date_debut]);
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        $solde_ouverture = ($row['total_montant'] ?? 0) - ($row['total_versement'] ?? 0);
    }

    // Lignes de la période
    $lignes = $this->ligneModel->getAllByReleve($id, $date_debut, $date_fin);

    // Calcul du total général (hors filtre)
    $queryTotal = "SELECT COALESCE(SUM(montant),0) as total_montant, COALESCE(SUM(versement),0) as total_versement
        FROM fs_lignes WHERE releve_id = ?";
    $stmtTotal = $this->db->prepare($queryTotal);
    $stmtTotal->execute([$id]);
    $rowTotal = $stmtTotal->fetch(PDO::FETCH_ASSOC);
    $total_general = ($rowTotal['total_montant'] ?? 0) - ($rowTotal['total_versement'] ?? 0);

    return [
        'view' => 'fs/extrait',
        'data' => compact('fournisseur', 'releve', 'lignes', 'date_debut', 'date_fin', 'solde_ouverture', 'total_general')
    ];
}

    public function delete($id) {
        $this->restrict('delete');
        $this->ligneModel->deleteByReleve($id);
        $this->releveModel->delete($id);
        header('Location: index.php?action=fs');
        exit();
    }
>>>>>>> 4f8dbbb6b83eb9c6f755d57287033c7da885a3b1
}