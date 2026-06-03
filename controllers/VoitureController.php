<?php
require_once __DIR__ . '/../includes/permissions.php';
require_once __DIR__ . '/../models/voiture.php';

class VoitureController {
    private $voitureModel;
    private $db;

    public function __construct(PDO $db) {
        $this->voitureModel = new Voiture($db);
        $this->db = $db;
    }

    public function index(): array {
        if (!isset($_SESSION['user_id'])) {
            header('Location: index.php?action=login');
            exit();
        }
        if (!hasPermission('voiture', 'view')) {
            return ['view' => 'error', 'data' => ['message' => "Accès refusé."]];
        }
        $voitures = $this->voitureModel->getAll();
        $totalVoitures = $this->voitureModel->getTotalCount();
        return ['view' => 'voiture/index', 'data' => ['voitures' => $voitures, 'totalVoitures' => $totalVoitures]];
    }

    public function create(array $data): array {
        if (!isset($_SESSION['user_id'])) {
            header('Location: index.php?action=login');
            exit();
        }
        if (!hasPermission('voiture', 'create')) {
            return ['view' => 'error', 'data' => ['message' => "Accès refusé."]];
        }
        $error = null;

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $matricule = trim($data['matricule'] ?? '');
            $chauffeur = trim($data['chauffeur'] ?? '');
            $telephone_chauffeur = trim($data['telephone_chauffeur'] ?? '');
            $userId = $_SESSION['user_id'] ?? null;

            if (empty($matricule)) {
                $error = "Le matricule est obligatoire.";
            }
            if (empty($chauffeur)) {
                $error = "Le nom du chauffeur est obligatoire.";
            }
            if (empty($telephone_chauffeur)) {
                $error = "Le numéro de téléphone du chauffeur est obligatoire.";
            }

            if (!$userId) {
                $error = "Utilisateur non authentifié.";
            }

            if (!$error) {
                try {
                    $this->voitureModel->create($matricule, $chauffeur, $telephone_chauffeur, $userId);
                    header('Location: index.php?action=voiture');
                    exit();
                } catch (PDOException $e) {
                    $error = "Erreur lors de la création de la voiture : " . $e->getMessage();
                    error_log($error);
                }
            }
        }

        return ['view' => 'voiture/create', 'data' => ['error' => $error]];
    }

    public function edit(int $id, array $data = []): array {
        if (!isset($_SESSION['user_id'])) {
            header('Location: index.php?action=login');
            exit();
        }
        if (!hasPermission('voiture', 'edit')) {
            return ['view' => 'error', 'data' => ['message' => "Accès refusé."]];
        }

        $error = null;
        $voiture = $this->voitureModel->findById($id);

        if (!$voiture) {
            return ['view' => 'error', 'data' => ['message' => "Voiture non trouvée."]];
        }

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $matricule = trim($data['matricule'] ?? '');
            $chauffeur = trim($data['chauffeur'] ?? '');
            $telephone_chauffeur = trim($data['telephone_chauffeur'] ?? '');

            if (empty($matricule)) {
                $error = "Le matricule est obligatoire.";
            }
            if (empty($chauffeur)) {
                $error = "Le nom du chauffeur est obligatoire.";
            }
            if (empty($telephone_chauffeur)) {
                $error = "Le numéro de téléphone du chauffeur est obligatoire.";
            }

            if (!$error) {
                try {
                    $this->voitureModel->update($id, $matricule, $chauffeur, $telephone_chauffeur);
                    header('Location: index.php?action=voiture');
                    exit();
                } catch (PDOException $e) {
                    $error = "Erreur lors de la mise à jour de la voiture : " . $e->getMessage();
                    error_log($error);
                }
            }
        }

        return ['view' => 'voiture/edit', 'data' => ['voiture' => $voiture, 'error' => $error]];
    }

    public function delete(int $id): void {
        if (!isset($_SESSION['user_id'])) {
            header('Location: index.php?action=login');
            exit();
        }
        if (!hasPermission('voiture', 'delete')) {
            echo "Accès refusé.";
            exit();
        }

        $voiture = $this->voitureModel->findById($id);
        if (!$voiture) {
            echo "Voiture non trouvée.";
            exit();
        }
        $this->voitureModel->delete($id);
        header('Location: index.php?action=voiture');
        exit();
    }
}