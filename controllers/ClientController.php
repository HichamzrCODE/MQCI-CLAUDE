<?php
require_once __DIR__ . '/../includes/permissions.php';
require_once __DIR__ . '/../models/Client.php';

class ClientController {
    private $clientModel;

    public function __construct(PDO $db) {
        $this->clientModel = new Client($db);
    }

    public function index(): array {
    if (!isset($_SESSION['user_id'])) {
        header('Location: index.php?action=login');
        exit();
    }
    if (!hasPermission('clients', 'view')) {
        die("Accès refusé.");
    }

    // Liste simple
    $clients = $this->clientModel->getAll();

    // On n'affiche plus les colonnes finance ici, donc on peut mettre false
    $canViewFinance = false;

    return ['view' => 'clients/index', 'data' => ['clients' => $clients, 'can_view_finance' => $canViewFinance]];
}

    public function create(array $data): array {
        if (!isset($_SESSION['user_id'])) {
            header('Location: index.php?action=login');
            exit();
        }
        if (!hasPermission('clients', 'create')) {
            die("Accès refusé.");
        }

        $error = null;
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $nom = trim($data['nom'] ?? '');
            $ville = trim($data['ville'] ?? '');
            $telephone = trim($data['telephone'] ?? '');
            $typeClient = $data['type_client'] ?? 'cash';

            // AIRSI
            $applyAirsi = !empty($data['apply_airsi']) ? 1 : 0;
            $airsiRate = isset($data['airsi_rate']) ? (float)$data['airsi_rate'] : 5.00;
            if ($airsiRate < 0) $airsiRate = 0;
            if ($airsiRate > 100) $airsiRate = 100;

            if (empty($nom)) {
                $error = "Le nom du client est obligatoire.";
            } elseif ($this->clientModel->existsByName($nom)) {
                $error = "Ce client existe déjà dans la base.";
            }

            $userId = $_SESSION['user_id'] ?? null;
            if (!$userId) {
                $error = "Utilisateur non authentifié.";
            }

            if (!$error) {
                $paymentDelay = hasPermission('clients', 'edit') ? (int)($data['payment_delay'] ?? 30) : 30;

                $clientId = $this->clientModel->create(
                    $nom,
                    $ville,
                    $telephone,
                    (int)$userId,
                    $typeClient,
                    (int)$paymentDelay,
                    (int)$applyAirsi,
                    (float)$airsiRate
                );

                header('Location: index.php?action=clients');
                exit();
            }
        }

        return ['view' => 'clients/create', 'data' => ['error' => $error]];
    }

    public function edit(int $id, array $data): array {
        if (!isset($_SESSION['user_id'])) {
            header('Location: index.php?action=login');
            exit();
        }
        if (!hasPermission('clients', 'edit')) {
            die("Accès refusé.");
        }

        $client = $this->clientModel->findById($id);
        if (!$client) {
            return ['view' => 'error', 'data' => ['message' => "Client non trouvé."]];
        }

        $error = null;
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $nom = trim($data['nom'] ?? '');
            $ville = trim($data['ville'] ?? '');
            $telephone = trim($data['telephone'] ?? '');
            $typeClient = $data['type_client'] ?? $client['type_client'];

            // AIRSI
            $applyAirsi = !empty($data['apply_airsi']) ? 1 : 0;
            $airsiRate = isset($data['airsi_rate']) ? (float)$data['airsi_rate'] : (float)($client['airsi_rate'] ?? 5.00);
            if ($airsiRate < 0) $airsiRate = 0;
            if ($airsiRate > 100) $airsiRate = 100;

            if (empty($nom)) {
                $error = "Le nom du client est obligatoire.";
            } elseif ($this->clientModel->existsByName($nom) && $nom !== $client['nom']) {
                $error = "Un client avec ce nom existe déjà.";
            }

            if (!$error) {
                $paymentDelay = hasPermission('clients', 'edit')
                    ? (int)($data['payment_delay'] ?? $client['payment_delay'] ?? 30)
                    : (int)($client['payment_delay'] ?? 30);

                $this->clientModel->update(
                    $id,
                    $nom,
                    $ville,
                    $telephone,
                    $typeClient,
                    (int)$paymentDelay,
                    (int)$applyAirsi,
                    (float)$airsiRate
                );

                header('Location: index.php?action=clients');
                exit();
            }

            // pré-remplissage si erreur
            $client = array_merge($client, [
                'nom' => $nom,
                'ville' => $ville,
                'telephone' => $telephone,
                'type_client' => $typeClient,
                'payment_delay' => (int)($data['payment_delay'] ?? $client['payment_delay'] ?? 30),
                'apply_airsi' => (int)$applyAirsi,
                'airsi_rate' => (float)$airsiRate,
            ]);
        }

        return ['view' => 'clients/edit', 'data' => ['client' => $client, 'error' => $error]];
    }

    public function delete(int $id): void {
        if (!isset($_SESSION['user_id'])) {
            header('Location: index.php?action=login');
            exit();
        }
        if (!hasPermission('clients', 'delete')) {
            die("Accès refusé.");
        }

        $client = $this->clientModel->findById($id);
        if (!$client) {
            echo "Client non trouvé.";
            return;
        }

        $this->clientModel->delete($id);
        header('Location: index.php?action=clients');
        exit();
    }

   public function show(int $id): array {
    if (!isset($_SESSION['user_id'])) {
        header('Location: index.php?action=login');
        exit();
    }
    if (!hasPermission('clients', 'view')) {
        die("Accès refusé.");
    }

    $client = $this->clientModel->findById($id);
    if (!$client) {
        return ['view' => 'error', 'data' => ['message' => "Client non trouvé."]];
    }

    $documents = $this->clientModel->getDerniersDocumentsClient($id, 5);
    $dernier_prix_articles = $this->clientModel->getDernierPrixParArticleClient($id, 500);

    // Financières: calcul simple basé sur devis type_doc='facture'
    $finance = $this->clientModel->getFinanceResumeClient($id);

    return [
        'view' => 'clients/show',
        'data' => [
            'client' => $client,
            'documents' => $documents,
            'dernier_prix_articles' => $dernier_prix_articles,
            'finance' => $finance
        ]
    ];
}

    public function search(array $data): void {
        $term = trim($data['term'] ?? '');
        header('Content-Type: application/json');

        if ($term === '') {
            $clients = $this->clientModel->getAll();
        } else {
            $clients = $this->clientModel->searchFull($term);
        }

        $canViewFinance = isset($_SESSION['role']) && in_array($_SESSION['role'], ['admin', 'manager']);
        foreach ($clients as &$client) {
            $id = (int)$client['id_clients'];
            $client['editable'] = hasPermission('clients', 'edit');
            $client['deletable'] = hasPermission('clients', 'delete');
            $client['date_dernier_devis'] = $this->clientModel->getDateDernierDevis($id);
            if ($canViewFinance) {
                $client['total_facturise'] = $this->clientModel->getTotalFacturise($id);
                $client['total_impaye'] = $this->clientModel->getTotalImpaye($id);
                $client['en_retard'] = $this->clientModel->isEnRetard($id);
            }
        }
        unset($client);

        echo json_encode($clients);
        exit();
    }
}