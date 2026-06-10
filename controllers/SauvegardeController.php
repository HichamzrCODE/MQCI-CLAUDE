<?php
require_once __DIR__ . '/../models/Backup.php';

class SauvegardeController {
    private PDO $db;
    private Backup $backupModel;

    public function __construct(PDO $db) {
        $this->db          = $db;
        $this->backupModel = new Backup($db);
    }

    private function checkAdmin(): void {
        if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
            header('Location: index.php?action=login');
            exit();
        }
    }

    public function index(): void {
        $this->checkAdmin();
        $backups    = $this->backupModel->getAllBackups();
        $lastBackup = $this->backupModel->getLastBackup();
        $dbSize     = $this->getDBSize();
        $diskSpace  = $this->getDiskSpace();

        extract(compact('backups','lastBackup','dbSize','diskSpace'));
        require __DIR__ . '/../views/sauvegarde/index.php';
    }

    public function backup(): void {
        $this->checkAdmin();
        header('Content-Type: application/json');
        try {
            $filename = $this->backupModel->createBackup();
            echo json_encode([
                'success'   => true,
                'message'   => 'Sauvegarde créée avec succès.',
                'filename'  => $filename,
                'timestamp' => date('d/m/Y H:i:s'),
            ]);
        } catch (Throwable $e) {
            http_response_code(500);
            echo json_encode(['success' => false, 'message' => $e->getMessage()]);
        }
        exit();
    }

    public function download(string $file): void {
        $this->checkAdmin();
        $filepath = __DIR__ . '/../backups/' . basename($file);
        if (!file_exists($filepath)) { http_response_code(404); die('Fichier introuvable.'); }
        header('Content-Type: application/octet-stream');
        header('Content-Disposition: attachment; filename="' . basename($filepath) . '"');
        header('Content-Length: ' . filesize($filepath));
        readfile($filepath);
        exit();
    }

    public function delete(string $file): void {
        $this->checkAdmin();
        header('Content-Type: application/json');
        $filepath = __DIR__ . '/../backups/' . basename($file);
        if (!file_exists($filepath)) {
            echo json_encode(['success' => false, 'message' => 'Fichier introuvable.']);
            exit();
        }
        unlink($filepath);
        echo json_encode(['success' => true, 'message' => 'Sauvegarde supprimée.']);
        exit();
    }

    private function getDBSize(): string {
        try {
            $r = $this->db->query("SELECT ROUND(SUM(data_length+index_length)/1024/1024,2) AS s FROM information_schema.TABLES WHERE table_schema=DATABASE()")->fetch();
            return ($r['s'] ?? '0') . ' MB';
        } catch (Throwable $e) { return 'N/A'; }
    }

    private function getDiskSpace(): array {
        $dir   = __DIR__ . '/../backups';
        if (!is_dir($dir)) mkdir($dir, 0755, true);
        $free  = disk_free_space($dir);
        $total = disk_total_space($dir);
        $used  = $total - $free;
        return [
            'free'    => $this->fmtBytes($free),
            'used'    => $this->fmtBytes($used),
            'total'   => $this->fmtBytes($total),
            'percent' => round($used / $total * 100, 1),
        ];
    }

    private function fmtBytes(int|float $b): string {
        $u = ['B','KB','MB','GB'];
        $b = max($b, 0);
        $p = floor(($b ? log($b) : 0) / log(1024));
        $p = min($p, count($u) - 1);
        return round($b / (1 << (10 * $p)), 2) . ' ' . $u[$p];
    }
}
