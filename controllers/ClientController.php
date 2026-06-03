<<<<<<< HEAD
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
        $clients = $this->clientModel->getAll();
        return ['view' => 'clients/index', 'data' => ['clients' => $clients]];
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
                $clientId = $this->clientModel->create($nom, $ville, $telephone, $userId, $typeClient);
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

            if (empty($nom)) {
                $error = "Le nom du client est obligatoire.";
            } elseif ($this->clientModel->existsByName($nom) && $nom !== $client['nom']) {
                $error = "Un client avec ce nom existe déjà.";
            }

            if (!$error) {
                $this->clientModel->update($id, $nom, $ville, $telephone, $typeClient);
                header('Location: index.php?action=clients');
                exit();
            }
            // On remet à jour $client pour pré-remplir le formulaire en cas d'erreur
            $client = array_merge($client, [
                'nom' => $nom,
                'ville' => $ville,
                'telephone' => $telephone,
                'type_client' => $typeClient
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

        // Utilisation du modèle pour récupérer les articles des devis
        $articles_devis = $this->clientModel->getArticlesDevis($id);

        return [
            'view' => 'clients/show',
            'data' => [
                'client' => $client,
                'articles_devis' => $articles_devis
            ]
        ];
    }

    // Recherche AJAX pour la barre de recherche (full info)
    public function search(array $data): void {
        $term = trim($data['term'] ?? '');
        header('Content-Type: application/json');
        if ($term === '') {
            // Afficher TOUS les clients si la recherche est vide :
            $clients = $this->clientModel->getAll();
        } else {
            // On veut retourner toutes les infos pour l'affichage du tableau (nom, ville, téléphone, id, permissions)
            $clients = $this->clientModel->searchFull($term);
        }
        foreach ($clients as &$client) {
            $client['editable'] = hasPermission('clients', 'edit');
            $client['deletable'] = hasPermission('clients', 'delete');
        }
        echo json_encode($clients);
        exit();
    }
=======
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
        $clients = $this->clientModel->getAll();
        $canViewFinance = isset($_SESSION['role']) && in_array($_SESSION['role'], ['admin', 'manager']);
        foreach ($clients as &$client) {
            $id = (int)$client['id_clients'];
            $client['date_dernier_devis'] = $this->clientModel->getDateDernierDevis($id);
            if ($canViewFinance) {
                $client['total_facturise'] = $this->clientModel->getTotalFacturise($id);
                $client['total_impaye'] = $this->clientModel->getTotalImpaye($id);
                $client['en_retard'] = $this->clientModel->isEnRetard($id);
            }
        }
        unset($client);
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
                $clientId = $this->clientModel->create($nom, $ville, $telephone, $userId, $typeClient, $paymentDelay);
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

            if (empty($nom)) {
                $error = "Le nom du client est obligatoire.";
            } elseif ($this->clientModel->existsByName($nom) && $nom !== $client['nom']) {
                $error = "Un client avec ce nom existe déjà.";
            }

            if (!$error) {
                $paymentDelay = hasPermission('clients', 'edit') ? (int)($data['payment_delay'] ?? $client['payment_delay'] ?? 30) : (int)($client['payment_delay'] ?? 30);
                $this->clientModel->update($id, $nom, $ville, $telephone, $typeClient, $paymentDelay);
                header('Location: index.php?action=clients');
                exit();
            }
            // On remet à jour $client pour pré-remplir le formulaire en cas d'erreur
            $client = array_merge($client, [
                'nom' => $nom,
                'ville' => $ville,
                'telephone' => $telephone,
                'type_client' => $typeClient,
                'payment_delay' => (int)($data['payment_delay'] ?? $client['payment_delay'] ?? 30)
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

        // Utilisation du modèle pour récupérer les articles des devis
        $articles_devis = $this->clientModel->getArticlesDevis($id);

        return [
            'view' => 'clients/show',
            'data' => [
                'client' => $client,
                'articles_devis' => $articles_devis
            ]
        ];
    }

    // Recherche AJAX pour la barre de recherche (full info)
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
>>>>>>> 4f8dbbb6b83eb9c6f755d57287033c7da885a3b1
}