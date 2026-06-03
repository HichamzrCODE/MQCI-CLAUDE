<?php
require_once __DIR__ . '/../includes/permissions.php';
require_once __DIR__ . '/../includes/Validator.php';
require_once __DIR__ . '/../includes/CsrfMiddleware.php';
require_once __DIR__ . '/../models/Settings.php';

class SettingsController {
    private Settings $settingsModel;

    public function __construct(PDO $db) {
        $this->settingsModel = new Settings($db);
    }

    public function index(): array {
        if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
            die("Accès refusé.");
        }

        $settings = $this->settingsModel->getAll();
        $logoUrl = $this->settingsModel->getLogoUrl();

        $error = null;
        $success = null;

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            CsrfMiddleware::verify();

            $appName = trim($_POST['app_name'] ?? '');
            $appIcon = trim($_POST['app_icon'] ?? 'fa-cube');

            if ($appName === '') {
                $error = "Le nom de l'application est obligatoire.";
            } else {
                try {
                    // 1) Sauvegarder nom + icône
                    $this->settingsModel->set('app_name', $appName);
                    $this->settingsModel->set('app_icon', $appIcon !== '' ? $appIcon : 'fa-cube');

                    // 2) Upload logo si fourni
                    if (isset($_FILES['app_logo']) && !empty($_FILES['app_logo']['tmp_name'])) {
                        $upload = $this->settingsModel->uploadLogo($_FILES['app_logo']);
                        if (!$upload['success']) {
                            $error = $upload['error'] ?? "Erreur upload logo.";
                        } else {
                            $success = $upload['message'] ?? "Logo uploadé avec succès.";
                        }
                    }

                    // Message succès si pas déjà défini (upload)
                    if ($success === null && $error === null) {
                        $success = "Paramètres sauvegardés avec succès !";
                    }

                    // Refresh
                    $settings = $this->settingsModel->getAll();
                    $logoUrl = $this->settingsModel->getLogoUrl();
                } catch (PDOException $e) {
                    $error = "Erreur lors de la sauvegarde : " . $e->getMessage();
                }
            }
        }

        return [
            'view' => 'settings/index',
            'data' => [
                'settings' => $settings,
                'logoUrl' => $logoUrl,
                'error' => $error,
                'success' => $success,
                'csrf_field' => CsrfMiddleware::field()
            ]
        ];
    }

    // Page Paramètres devis (header/footer)
    public function devis(): array {
        if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
            die("Accès refusé.");
        }

        $settings = $this->settingsModel->getAll();
        $error = null;
        $success = null;

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            CsrfMiddleware::verify();

            $header1 = trim($_POST['devis_header_line1'] ?? '');
            $header2 = trim($_POST['devis_header_line2'] ?? '');
            $footer  = trim($_POST['devis_footer_text'] ?? '');

            try {
                $this->settingsModel->set('devis_header_line1', $header1);
                $this->settingsModel->set('devis_header_line2', $header2);
                $this->settingsModel->set('devis_footer_text', $footer);

                $success = "Paramètres devis sauvegardés avec succès.";
                $settings = $this->settingsModel->getAll();
            } catch (PDOException $e) {
                $error = "Erreur lors de la sauvegarde : " . $e->getMessage();
            }
        }

        return [
            'view' => 'settings/devis',
            'data' => [
                'settings' => $settings,
                'error' => $error,
                'success' => $success,
                'csrf_field' => CsrfMiddleware::field()
            ]
        ];
    }


    public function tresorerie(): array {
    if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
        die("Accès refusé.");
    }

    $settings = $this->settingsModel->getAll();
    $error = null;
    $success = null;

    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        CsrfMiddleware::verify();

        $cashRegisters = trim($_POST['cash_registers_list'] ?? '');
        $banks         = trim($_POST['banks_list'] ?? '');
        $mobiles       = trim($_POST['mobile_operators_list'] ?? '');

        try {
            $this->settingsModel->set('cash_registers_list', $cashRegisters);
            $this->settingsModel->set('banks_list', $banks);
            $this->settingsModel->set('mobile_operators_list', $mobiles);

            $success = "Paramètres Trésorerie sauvegardés avec succès.";
            $settings = $this->settingsModel->getAll();
        } catch (PDOException $e) {
            $error = "Erreur lors de la sauvegarde : " . $e->getMessage();
        }
    }

    return [
        'view' => 'settings/tresorerie',
        'data' => [
            'settings' => $settings,
            'error' => $error,
            'success' => $success,
            'csrf_field' => CsrfMiddleware::field()
        ]
    ];
}
}