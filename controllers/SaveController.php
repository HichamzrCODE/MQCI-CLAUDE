<?php
require_once __DIR__ . '/../models/Backup.php';

class SaveController {
    private $db;
    private $backupModel;

    public function __construct(PDO $db) {
        $this->db = $db;
        $this->backupModel = new Backup($db);
    }

    public function index(): array {
        if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
            header('Location: index.php?action=login');
            exit();
        }

        $backups = $this->backupModel->getAllBackups();
        $lastBackup = $this->backupModel->getLastBackup();
        $dbSize = $this->getDBSize();
        $diskSpace = $this->getDiskSpace();

        return [
            'view' => 'sauvegarde/index',
            'data' => [
                'backups' => $backups,
                'lastBackup' => $lastBackup,
                'dbSize' => $dbSize,
                'diskSpace' => $diskSpace
            ]
        ];
    }

    public function create(): void {
        if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
            http_response_code(403);
            echo json_encode(['success' => false, 'message' => 'Accès refusé']);
            exit();
        }

        try {
            $filename = $this->backupModel->createBackup();
            http_response_code(200);
            echo json_encode([
                'success' => true,
                'message' => 'Sauvegarde créée avec succès',
                'filename' => $filename,
                'timestamp' => date('d/m/Y H:i:s')
            ]);
        } catch (Exception $e) {
            http_response_code(500);
            echo json_encode(['success' => false, 'message' => $e->getMessage()]);
        }
        exit();
    }

    public function download($filename): void {
        if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
            die('Accès refusé');
        }

        $backupDir = __DIR__ . '/../backups';
        $filepath = $backupDir . '/' . basename($filename);

        if (!file_exists($filepath) || !is_file($filepath)) {
            die('Fichier non trouvé');
        }

        header('Content-Type: application/octet-stream');
        header('Content-Disposition: attachment; filename="' . basename($filepath) . '"');
        header('Content-Length: ' . filesize($filepath));
        readfile($filepath);
        exit();
    }

    public function delete($filename): void {
        if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
            http_response_code(403);
            echo json_encode(['success' => false, 'message' => 'Accès refusé']);
            exit();
        }

        try {
            $backupDir = __DIR__ . '/../backups';
            $filepath = $backupDir . '/' . basename($filename);

            if (!file_exists($filepath)) {
                throw new Exception('Fichier non trouvé');
            }

            if (!unlink($filepath)) {
                throw new Exception('Impossible de supprimer le fichier');
            }

            $this->backupModel->deleteBackupRecord($filename);

            http_response_code(200);
            echo json_encode(['success' => true, 'message' => 'Sauvegarde supprimée']);
        } catch (Exception $e) {
            http_response_code(500);
            echo json_encode(['success' => false, 'message' => $e->getMessage()]);
        }
        exit();
    }

    public function restore($filename): void {
        if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
            http_response_code(403);
            echo json_encode(['success' => false, 'message' => 'Accès refusé']);
            exit();
        }

        try {
            $this->backupModel->restoreBackup($filename);
            http_response_code(200);
            echo json_encode(['success' => true, 'message' => 'Base de données restaurée']);
        } catch (Exception $e) {
            http_response_code(500);
            echo json_encode(['success' => false, 'message' => $e->getMessage()]);
        }
        exit();
    }

    public function config(): void {
        if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
            http_response_code(403);
            echo json_encode(['success' => false, 'message' => 'Accès refusé']);
            exit();
        }

        try {
            $frequency = $_POST['frequency'] ?? 'daily';
            $retention = (int)($_POST['retention'] ?? 30);

            if (!in_array($frequency, ['daily', 'weekly', 'monthly', 'disabled'])) {
                throw new Exception('Fréquence invalide');
            }

            $this->backupModel->saveConfig($frequency, $retention);

            http_response_code(200);
            echo json_encode(['success' => true, 'message' => 'Configuration enregistrée']);
        } catch (Exception $e) {
            http_response_code(500);
            echo json_encode(['success' => false, 'message' => $e->getMessage()]);
        }
        exit();
    }

    private function getDBSize(): string {
        try {
            $result = $this->db->query("SELECT ROUND(SUM(data_length + index_length) / 1024 / 1024, 2) AS size FROM information_schema.TABLES WHERE table_schema = DATABASE()")->fetch();
            return $result['size'] ?? '0';
        } catch (Exception $e) {
            return 'N/A';
        }
    }

    private function getDiskSpace(): array {
        $backupDir = __DIR__ . '/../backups';
        if (!is_dir($backupDir)) {
            mkdir($backupDir, 0755, true);
        }

        $free = disk_free_space($backupDir);
        $total = disk_total_space($backupDir);
        $used = $total - $free;

        return [
            'free' => $this->formatBytes($free),
            'used' => $this->formatBytes($used),
            'total' => $this->formatBytes($total),
            'percent' => round(($used / $total) * 100, 2)
        ];
    }

    private function formatBytes($bytes): string {
        $units = ['B', 'KB', 'MB', 'GB'];
        $bytes = max($bytes, 0);
        $pow = floor(($bytes ? log($bytes) : 0) / log(1024));
        $pow = min($pow, count($units) - 1);
        $bytes /= (1 << (10 * $pow));

        return round($bytes, 2) . ' ' . $units[$pow];
    }
}
