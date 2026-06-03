<<<<<<< HEAD
<?php
require_once '../models/CreditReleve.php';
require_once '../models/CreditLigne.php';
require_once '../models/Client.php';
require_once __DIR__ . '\..\includes\permissions.php';

class ReleveController {
    private $db, $releveModel, $ligneModel, $clientModel;
    public function __construct(PDO $db) {
        $this->db = $db;
        $this->releveModel = new CreditReleve($db);
        $this->ligneModel = new CreditLigne($db);
        $this->clientModel = new Client($db);
    }

    private function restrict($action = 'view') {
        if (!isset($_SESSION['user_id'])) die("Accès refusé");
        if (!hasPermission('releve', $action)) die("Accès refusé");
    }

    private function isNonEmptyAndNumeric($v) {
        $v = str_replace([' ', ','], ['', '.'], $v);
        return $v !== '' && is_numeric($v) && floatval($v) > 0;
    }

    public function index() {
        $this->restrict('view');
        $releves = $this->releveModel->getAll();
        return ['view'=>'releve/index','data'=>['releves'=>$releves]];
    }

    public function show($id) {
        $this->restrict('view');
        $releve = $this->releveModel->get($id);
        $client = $this->clientModel->findById($releve['client_id']);
        $lignes = $this->ligneModel->getAllByReleve($id);
        return ['view'=>'releve/show','data'=>compact('releve','client','lignes')];
    }

    public function create($data) {
        $this->restrict('create');
        $error = null;
        $doublon_indexes = [];
        $clients = $this->clientModel->getAll();
        $ids_clients_avec_releve = array_map(function($releve) {
            return $releve['client_id'];
        }, $this->releveModel->getAll());
        $clients_sans_releve = array_filter($clients, function($client) use ($ids_clients_avec_releve) {
            return !in_array($client['id_clients'], $ids_clients_avec_releve);
        });

        if ($_SERVER['REQUEST_METHOD']==='POST') {
            $client_id = $data['client_id'] ?? '';
            if (!$client_id) $error = "Choisir un client.";
            if (!$error && $this->releveModel->getByClient($client_id)) {
                $error = "Ce client possède déjà un relevé !";
            }

            // === DEBUT: Validation anti-doublons lignes (corrigée pour index non continus) ===
            if (!$error && isset($data['lignes']) && is_array($data['lignes'])) {
                $lignes = $data['lignes'];
                $keys = array_keys($lignes);
                $nb = count($keys);
                for ($ia = 0; $ia < $nb; $ia++) {
                    $i = $keys[$ia];
                    $a = $lignes[$i];
                    for ($ja = $ia+1; $ja < $nb; $ja++) {
                        $j = $keys[$ja];
                        $b = $lignes[$j];
                        if (($a['date'] ?? '') && ($a['date'] === ($b['date'] ?? ''))) {
                            // même montant + même BC (non vide)
                            if (
                                $this->isNonEmptyAndNumeric($a['montant'] ?? '') && $this->isNonEmptyAndNumeric($b['montant'] ?? '') &&
                                floatval(str_replace([' ', ','], ['', '.'], $a['montant'])) === floatval(str_replace([' ', ','], ['', '.'], $b['montant'])) &&
                                ($a['bc'] ?? '') !== '' && $a['bc'] === ($b['bc'] ?? '')
                            ) {
                                $doublon_indexes[] = $i;
                                $doublon_indexes[] = $j;
                            }
                            // même montant + même facture (non vide)
                            if (
                                $this->isNonEmptyAndNumeric($a['montant'] ?? '') && $this->isNonEmptyAndNumeric($b['montant'] ?? '') &&
                                floatval(str_replace([' ', ','], ['', '.'], $a['montant'])) === floatval(str_replace([' ', ','], ['', '.'], $b['montant'])) &&
                                ($a['facture'] ?? '') !== '' && $a['facture'] === ($b['facture'] ?? '')
                            ) {
                                $doublon_indexes[] = $i;
                                $doublon_indexes[] = $j;
                            }
                            // même versement + même BC (non vide)
                            if (
                                $this->isNonEmptyAndNumeric($a['versement'] ?? '') && $this->isNonEmptyAndNumeric($b['versement'] ?? '') &&
                                floatval(str_replace([' ', ','], ['', '.'], $a['versement'])) === floatval(str_replace([' ', ','], ['', '.'], $b['versement'])) &&
                                ($a['bc'] ?? '') !== '' && $a['bc'] === ($b['bc'] ?? '')
                            ) {
                                $doublon_indexes[] = $i;
                                $doublon_indexes[] = $j;
                            }
                            // même versement + même facture (non vide)
                            if (
                                $this->isNonEmptyAndNumeric($a['versement'] ?? '') && $this->isNonEmptyAndNumeric($b['versement'] ?? '') &&
                                floatval(str_replace([' ', ','], ['', '.'], $a['versement'])) === floatval(str_replace([' ', ','], ['', '.'], $b['versement'])) &&
                                ($a['facture'] ?? '') !== '' && $a['facture'] === ($b['facture'] ?? '')
                            ) {
                                $doublon_indexes[] = $i;
                                $doublon_indexes[] = $j;
                            }
                        }
                    }
                }
                $doublon_indexes = array_unique($doublon_indexes);
                if (!empty($doublon_indexes)) {
                    $error = "Doublon(s) détecté(s) : lignes surlignées en rouge à corriger.";
                }
            }
            // === FIN: Validation anti-doublons lignes ===

            if (!$error) {
                $releve_id = $this->releveModel->create($client_id, $_SESSION['user_id']);
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
                header('Location: index.php?action=releve');
                exit();
            }
        }
        return ['view'=>'releve/create','data'=>compact('clients_sans_releve','error','data','doublon_indexes')];
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
    $clients = $this->clientModel->getAll();
    $all_lignes = $this->ligneModel->getAllByReleve($id);
    $total_lignes = count($all_lignes);

    // Ici, on gère le paramètre GET pour afficher tout ou seulement 20 lignes
    $afficher_tout = isset($_GET['all']) && $_GET['all'] == '1';
    if ($afficher_tout) {
        $lignes = $all_lignes;
        $offset = 0;
    } else {
        $offset = max(0, $total_lignes - 20);
        $lignes = array_slice($all_lignes, -20, 20, true);
    }
    $solde_depart = $this->getSoldeDepart($all_lignes, $offset);
        $pagination = null;

        // Pour réaffichage en cas d'erreur
        $post_lignes = $data['lignes'] ?? [];

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $client_id = $data['client_id'] ?? '';
            if (!$client_id) {
                $error = "Choisir un client.";
            }

            // Validation des lignes (identique à create)
            if (!$error && isset($data['lignes'])) {
                foreach ($data['lignes'] as $i => $ligne) {
                    $date = trim($ligne['date'] ?? '');
                    $montant = trim($ligne['montant'] ?? '');
                    $versement = trim($ligne['versement'] ?? '');
                    if (!$date || ($montant === '' && $versement === '')) {
                        $error = "Chaque ligne doit contenir une date et un montant OU un versement.";
                        break;
                    }
                }
            }

            // === DEBUT: Validation anti-doublons lignes (identique à create) ===
            $doublon_indexes = [];
            if (!$error && isset($data['lignes']) && is_array($data['lignes'])) {
                $lignes_check = $data['lignes'];
                $keys = array_keys($lignes_check);
                $nb = count($keys);
                for ($ia = 0; $ia < $nb; $ia++) {
                    $i = $keys[$ia];
                    $a = $lignes_check[$i];
                    for ($ja = $ia+1; $ja < $nb; $ja++) {
                        $j = $keys[$ja];
                        $b = $lignes_check[$j];
                        if (($a['date'] ?? '') && ($a['date'] === ($b['date'] ?? ''))) {
                            // même montant + même BC (non vide)
                            if (
                                $this->isNonEmptyAndNumeric($a['montant'] ?? '') && $this->isNonEmptyAndNumeric($b['montant'] ?? '') &&
                                floatval(str_replace([' ', ','], ['', '.'], $a['montant'])) === floatval(str_replace([' ', ','], ['', '.'], $b['montant'])) &&
                                ($a['bc'] ?? '') !== '' && $a['bc'] === ($b['bc'] ?? '')
                            ) {
                                $doublon_indexes[] = $i;
                                $doublon_indexes[] = $j;
                            }
                            // même montant + même facture (non vide)
                            if (
                                $this->isNonEmptyAndNumeric($a['montant'] ?? '') && $this->isNonEmptyAndNumeric($b['montant'] ?? '') &&
                                floatval(str_replace([' ', ','], ['', '.'], $a['montant'])) === floatval(str_replace([' ', ','], ['', '.'], $b['montant'])) &&
                                ($a['facture'] ?? '') !== '' && $a['facture'] === ($b['facture'] ?? '')
                            ) {
                                $doublon_indexes[] = $i;
                                $doublon_indexes[] = $j;
                            }
                            // même versement + même BC (non vide)
                            if (
                                $this->isNonEmptyAndNumeric($a['versement'] ?? '') && $this->isNonEmptyAndNumeric($b['versement'] ?? '') &&
                                floatval(str_replace([' ', ','], ['', '.'], $a['versement'])) === floatval(str_replace([' ', ','], ['', '.'], $b['versement'])) &&
                                ($a['bc'] ?? '') !== '' && $a['bc'] === ($b['bc'] ?? '')
                            ) {
                                $doublon_indexes[] = $i;
                                $doublon_indexes[] = $j;
                            }
                            // même versement + même facture (non vide)
                            if (
                                $this->isNonEmptyAndNumeric($a['versement'] ?? '') && $this->isNonEmptyAndNumeric($b['versement'] ?? '') &&
                                floatval(str_replace([' ', ','], ['', '.'], $a['versement'])) === floatval(str_replace([' ', ','], ['', '.'], $b['versement'])) &&
                                ($a['facture'] ?? '') !== '' && $a['facture'] === ($b['facture'] ?? '')
                            ) {
                                $doublon_indexes[] = $i;
                                $doublon_indexes[] = $j;
                            }
                        }
                    }
                }
                $doublon_indexes = array_unique($doublon_indexes);
                if (!empty($doublon_indexes)) {
                    $error = "Doublon(s) détecté(s) : lignes surlignées en rouge à corriger.";
                }
            }
            // === FIN: Validation anti-doublons lignes ===

            if (!$error) {
                $this->releveModel->update($id, $client_id);

                // Suppression des anciennes lignes (20 dernières affichées)
                $ids_20_dernieres = array_column($lignes, 'id');
                if (!empty($ids_20_dernieres)) {
                    $this->ligneModel->deleteByIds($ids_20_dernieres);
                }

                // Ajout des nouvelles lignes
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
                header('Location: index.php?action=releve');
                exit();
            } else {
                // Si erreur, on réinjecte les valeurs saisies pour affichage
                $lignes = [];
                if (isset($data['lignes'])) {
                    foreach ($data['lignes'] as $i => $ligne) {
                        $lignes[] = [
                            'id' => $ligne['id'] ?? null,
                            'date_operation' => $ligne['date'] ?? '',
                            'bc_client' => $ligne['bc'] ?? '',
                            'numero_facture' => $ligne['facture'] ?? '',
                            'montant' => $ligne['montant'] ?? '',
                            'versement' => $ligne['versement'] ?? '',
                        ];
                    }
                }
            }
        }
        return [
            'view' => 'releve/edit',
            'data' => compact('error', 'clients', 'releve', 'lignes', 'solde_depart', 'doublon_indexes')
        ];
    }

    public function delete($id) {
        $this->restrict('delete');
        $this->ligneModel->deleteByReleve($id);
        $this->releveModel->delete($id);
        header('Location: index.php?action=releve');
        exit();
    }

    public function ajax() {
        $this->restrict('view');
        $releve_id = $_GET['id'];
        $releve = $this->releveModel->get($releve_id);
        if (!$releve) {
            echo json_encode([
                'status'=>'error',
                'html'=>'<div class="alert alert-info">Relevé non trouvé</div>'
            ]);
            exit;
        }
        $lignes = $this->ligneModel->getAllByReleve($releve_id);
        $client = $this->clientModel->findById($releve['client_id']);
        $client_nom = $client['nom'];
        ob_start();
        include '../views/credit/releve_ajax.php';
        $html = ob_get_clean();
        header('Content-Type: application/json');
        echo json_encode(['status'=>'ok','html'=>$html]);
        exit();
    }

    public function extrait($id, $date_debut, $date_fin) {
        $this->restrict('view');
        $releve = $this->releveModel->get($id);
        $client = $this->clientModel->findById($releve['client_id']);
        $solde_ouverture = 0;
        $lignes_avant = $this->ligneModel->getAllByReleve($id, null, $date_debut ? date('Y-m-d', strtotime("$date_debut -1 day")) : null);
        foreach ($lignes_avant as $ligne) {
            $solde_ouverture += $ligne['montant'] - $ligne['versement'];
        }
        $lignes = $this->ligneModel->getAllByReleve($id, $date_debut, $date_fin);
        $total_general = $releve['total_general'];
        return [
            'view'=>'releve/extrait',
            'data'=>compact('releve','client','lignes','solde_ouverture','date_debut','date_fin', 'total_general')
        ];
    }
=======
<?php
require_once '../models/CreditReleve.php';
require_once '../models/CreditLigne.php';
require_once '../models/Client.php';
require_once __DIR__ . '\..\includes\permissions.php';

class ReleveController {
    private $db, $releveModel, $ligneModel, $clientModel;
    public function __construct(PDO $db) {
        $this->db = $db;
        $this->releveModel = new CreditReleve($db);
        $this->ligneModel = new CreditLigne($db);
        $this->clientModel = new Client($db);
    }

    private function restrict($action = 'view') {
        if (!isset($_SESSION['user_id'])) die("Accès refusé");
        if (!hasPermission('releve', $action)) die("Accès refusé");
    }

    private function isNonEmptyAndNumeric($v) {
        $v = str_replace([' ', ','], ['', '.'], $v);
        return $v !== '' && is_numeric($v) && floatval($v) > 0;
    }

    public function index() {
        $this->restrict('view');
        $releves = $this->releveModel->getAll();
        return ['view'=>'releve/index','data'=>['releves'=>$releves]];
    }

    public function show($id) {
        $this->restrict('view');
        $releve = $this->releveModel->get($id);
        $client = $this->clientModel->findById($releve['client_id']);
        $lignes = $this->ligneModel->getAllByReleve($id);
        return ['view'=>'releve/show','data'=>compact('releve','client','lignes')];
    }

    public function create($data) {
        $this->restrict('create');
        $error = null;
        $doublon_indexes = [];
        $clients = $this->clientModel->getAll();
        $ids_clients_avec_releve = array_map(function($releve) {
            return $releve['client_id'];
        }, $this->releveModel->getAll());
        $clients_sans_releve = array_filter($clients, function($client) use ($ids_clients_avec_releve) {
            return !in_array($client['id_clients'], $ids_clients_avec_releve);
        });

        if ($_SERVER['REQUEST_METHOD']==='POST') {
            $client_id = $data['client_id'] ?? '';
            if (!$client_id) $error = "Choisir un client.";
            if (!$error && $this->releveModel->getByClient($client_id)) {
                $error = "Ce client possède déjà un relevé !";
            }

            // === DEBUT: Validation anti-doublons lignes (corrigée pour index non continus) ===
            if (!$error && isset($data['lignes']) && is_array($data['lignes'])) {
                $lignes = $data['lignes'];
                $keys = array_keys($lignes);
                $nb = count($keys);
                for ($ia = 0; $ia < $nb; $ia++) {
                    $i = $keys[$ia];
                    $a = $lignes[$i];
                    for ($ja = $ia+1; $ja < $nb; $ja++) {
                        $j = $keys[$ja];
                        $b = $lignes[$j];
                        if (($a['date'] ?? '') && ($a['date'] === ($b['date'] ?? ''))) {
                            // même montant + même BC (non vide)
                            if (
                                $this->isNonEmptyAndNumeric($a['montant'] ?? '') && $this->isNonEmptyAndNumeric($b['montant'] ?? '') &&
                                floatval(str_replace([' ', ','], ['', '.'], $a['montant'])) === floatval(str_replace([' ', ','], ['', '.'], $b['montant'])) &&
                                ($a['bc'] ?? '') !== '' && $a['bc'] === ($b['bc'] ?? '')
                            ) {
                                $doublon_indexes[] = $i;
                                $doublon_indexes[] = $j;
                            }
                            // même montant + même facture (non vide)
                            if (
                                $this->isNonEmptyAndNumeric($a['montant'] ?? '') && $this->isNonEmptyAndNumeric($b['montant'] ?? '') &&
                                floatval(str_replace([' ', ','], ['', '.'], $a['montant'])) === floatval(str_replace([' ', ','], ['', '.'], $b['montant'])) &&
                                ($a['facture'] ?? '') !== '' && $a['facture'] === ($b['facture'] ?? '')
                            ) {
                                $doublon_indexes[] = $i;
                                $doublon_indexes[] = $j;
                            }
                            // même versement + même BC (non vide)
                            if (
                                $this->isNonEmptyAndNumeric($a['versement'] ?? '') && $this->isNonEmptyAndNumeric($b['versement'] ?? '') &&
                                floatval(str_replace([' ', ','], ['', '.'], $a['versement'])) === floatval(str_replace([' ', ','], ['', '.'], $b['versement'])) &&
                                ($a['bc'] ?? '') !== '' && $a['bc'] === ($b['bc'] ?? '')
                            ) {
                                $doublon_indexes[] = $i;
                                $doublon_indexes[] = $j;
                            }
                            // même versement + même facture (non vide)
                            if (
                                $this->isNonEmptyAndNumeric($a['versement'] ?? '') && $this->isNonEmptyAndNumeric($b['versement'] ?? '') &&
                                floatval(str_replace([' ', ','], ['', '.'], $a['versement'])) === floatval(str_replace([' ', ','], ['', '.'], $b['versement'])) &&
                                ($a['facture'] ?? '') !== '' && $a['facture'] === ($b['facture'] ?? '')
                            ) {
                                $doublon_indexes[] = $i;
                                $doublon_indexes[] = $j;
                            }
                        }
                    }
                }
                $doublon_indexes = array_unique($doublon_indexes);
                if (!empty($doublon_indexes)) {
                    $error = "Doublon(s) détecté(s) : lignes surlignées en rouge à corriger.";
                }
            }
            // === FIN: Validation anti-doublons lignes ===

            if (!$error) {
                $releve_id = $this->releveModel->create($client_id, $_SESSION['user_id']);
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
                header('Location: index.php?action=releve');
                exit();
            }
        }
        return ['view'=>'releve/create','data'=>compact('clients_sans_releve','error','data','doublon_indexes')];
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
    $clients = $this->clientModel->getAll();
    $all_lignes = $this->ligneModel->getAllByReleve($id);
    $total_lignes = count($all_lignes);

    // Ici, on gère le paramètre GET pour afficher tout ou seulement 20 lignes
    $afficher_tout = isset($_GET['all']) && $_GET['all'] == '1';
    if ($afficher_tout) {
        $lignes = $all_lignes;
        $offset = 0;
    } else {
        $offset = max(0, $total_lignes - 20);
        $lignes = array_slice($all_lignes, -20, 20, true);
    }
    $solde_depart = $this->getSoldeDepart($all_lignes, $offset);
        $pagination = null;

        // Pour réaffichage en cas d'erreur
        $post_lignes = $data['lignes'] ?? [];

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $client_id = $data['client_id'] ?? '';
            if (!$client_id) {
                $error = "Choisir un client.";
            }

            // Validation des lignes (identique à create)
            if (!$error && isset($data['lignes'])) {
                foreach ($data['lignes'] as $i => $ligne) {
                    $date = trim($ligne['date'] ?? '');
                    $montant = trim($ligne['montant'] ?? '');
                    $versement = trim($ligne['versement'] ?? '');
                    if (!$date || ($montant === '' && $versement === '')) {
                        $error = "Chaque ligne doit contenir une date et un montant OU un versement.";
                        break;
                    }
                }
            }

            // === DEBUT: Validation anti-doublons lignes (identique à create) ===
            $doublon_indexes = [];
            if (!$error && isset($data['lignes']) && is_array($data['lignes'])) {
                $lignes_check = $data['lignes'];
                $keys = array_keys($lignes_check);
                $nb = count($keys);
                for ($ia = 0; $ia < $nb; $ia++) {
                    $i = $keys[$ia];
                    $a = $lignes_check[$i];
                    for ($ja = $ia+1; $ja < $nb; $ja++) {
                        $j = $keys[$ja];
                        $b = $lignes_check[$j];
                        if (($a['date'] ?? '') && ($a['date'] === ($b['date'] ?? ''))) {
                            // même montant + même BC (non vide)
                            if (
                                $this->isNonEmptyAndNumeric($a['montant'] ?? '') && $this->isNonEmptyAndNumeric($b['montant'] ?? '') &&
                                floatval(str_replace([' ', ','], ['', '.'], $a['montant'])) === floatval(str_replace([' ', ','], ['', '.'], $b['montant'])) &&
                                ($a['bc'] ?? '') !== '' && $a['bc'] === ($b['bc'] ?? '')
                            ) {
                                $doublon_indexes[] = $i;
                                $doublon_indexes[] = $j;
                            }
                            // même montant + même facture (non vide)
                            if (
                                $this->isNonEmptyAndNumeric($a['montant'] ?? '') && $this->isNonEmptyAndNumeric($b['montant'] ?? '') &&
                                floatval(str_replace([' ', ','], ['', '.'], $a['montant'])) === floatval(str_replace([' ', ','], ['', '.'], $b['montant'])) &&
                                ($a['facture'] ?? '') !== '' && $a['facture'] === ($b['facture'] ?? '')
                            ) {
                                $doublon_indexes[] = $i;
                                $doublon_indexes[] = $j;
                            }
                            // même versement + même BC (non vide)
                            if (
                                $this->isNonEmptyAndNumeric($a['versement'] ?? '') && $this->isNonEmptyAndNumeric($b['versement'] ?? '') &&
                                floatval(str_replace([' ', ','], ['', '.'], $a['versement'])) === floatval(str_replace([' ', ','], ['', '.'], $b['versement'])) &&
                                ($a['bc'] ?? '') !== '' && $a['bc'] === ($b['bc'] ?? '')
                            ) {
                                $doublon_indexes[] = $i;
                                $doublon_indexes[] = $j;
                            }
                            // même versement + même facture (non vide)
                            if (
                                $this->isNonEmptyAndNumeric($a['versement'] ?? '') && $this->isNonEmptyAndNumeric($b['versement'] ?? '') &&
                                floatval(str_replace([' ', ','], ['', '.'], $a['versement'])) === floatval(str_replace([' ', ','], ['', '.'], $b['versement'])) &&
                                ($a['facture'] ?? '') !== '' && $a['facture'] === ($b['facture'] ?? '')
                            ) {
                                $doublon_indexes[] = $i;
                                $doublon_indexes[] = $j;
                            }
                        }
                    }
                }
                $doublon_indexes = array_unique($doublon_indexes);
                if (!empty($doublon_indexes)) {
                    $error = "Doublon(s) détecté(s) : lignes surlignées en rouge à corriger.";
                }
            }
            // === FIN: Validation anti-doublons lignes ===

            if (!$error) {
                $this->releveModel->update($id, $client_id);

                // Suppression des anciennes lignes (20 dernières affichées)
                $ids_20_dernieres = array_column($lignes, 'id');
                if (!empty($ids_20_dernieres)) {
                    $this->ligneModel->deleteByIds($ids_20_dernieres);
                }

                // Ajout des nouvelles lignes
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
                header('Location: index.php?action=releve');
                exit();
            } else {
                // Si erreur, on réinjecte les valeurs saisies pour affichage
                $lignes = [];
                if (isset($data['lignes'])) {
                    foreach ($data['lignes'] as $i => $ligne) {
                        $lignes[] = [
                            'id' => $ligne['id'] ?? null,
                            'date_operation' => $ligne['date'] ?? '',
                            'bc_client' => $ligne['bc'] ?? '',
                            'numero_facture' => $ligne['facture'] ?? '',
                            'montant' => $ligne['montant'] ?? '',
                            'versement' => $ligne['versement'] ?? '',
                        ];
                    }
                }
            }
        }
        return [
            'view' => 'releve/edit',
            'data' => compact('error', 'clients', 'releve', 'lignes', 'solde_depart', 'doublon_indexes')
        ];
    }

    public function delete($id) {
        $this->restrict('delete');
        $this->ligneModel->deleteByReleve($id);
        $this->releveModel->delete($id);
        header('Location: index.php?action=releve');
        exit();
    }

    public function ajax() {
        $this->restrict('view');
        $releve_id = $_GET['id'];
        $releve = $this->releveModel->get($releve_id);
        if (!$releve) {
            echo json_encode([
                'status'=>'error',
                'html'=>'<div class="alert alert-info">Relevé non trouvé</div>'
            ]);
            exit;
        }
        $lignes = $this->ligneModel->getAllByReleve($releve_id);
        $client = $this->clientModel->findById($releve['client_id']);
        $client_nom = $client['nom'];
        ob_start();
        include '../views/credit/releve_ajax.php';
        $html = ob_get_clean();
        header('Content-Type: application/json');
        echo json_encode(['status'=>'ok','html'=>$html]);
        exit();
    }

    public function extrait($id, $date_debut, $date_fin) {
        $this->restrict('view');
        $releve = $this->releveModel->get($id);
        $client = $this->clientModel->findById($releve['client_id']);
        $solde_ouverture = 0;
        $lignes_avant = $this->ligneModel->getAllByReleve($id, null, $date_debut ? date('Y-m-d', strtotime("$date_debut -1 day")) : null);
        foreach ($lignes_avant as $ligne) {
            $solde_ouverture += $ligne['montant'] - $ligne['versement'];
        }
        $lignes = $this->ligneModel->getAllByReleve($id, $date_debut, $date_fin);
        $total_general = $releve['total_general'];
        return [
            'view'=>'releve/extrait',
            'data'=>compact('releve','client','lignes','solde_ouverture','date_debut','date_fin', 'total_general')
        ];
    }
>>>>>>> 4f8dbbb6b83eb9c6f755d57287033c7da885a3b1
}