<?php
class Backup {
    private $db;
    private $backupDir;

    public function __construct(PDO $db) {
        $this->db = $db;
        $this->backupDir = __DIR__ . '/../backups';
        if (!is_dir($this->backupDir)) {
            mkdir($this->backupDir, 0755, true);
        }
    }

    public function createBackup(): string {
        $filename = 'backup_' . date('Y-m-d_H-i-s') . '.sql';
        $filepath = $this->backupDir . '/' . $filename;

        $dbName = $this->getDBName();
        
        if ($this->canUseMysqldump()) {
            $this->backupWithMysqldump($filepath, $dbName);
        } else {
            $this->backupWithPHP($filepath, $dbName);
        }

        $this->recordBackup($filename, filesize($filepath));
        return $filename;
    }

    public function getAllBackups(): array {
        $backups = [];
        if (is_dir($this->backupDir)) {
            $files = array_diff(scandir($this->backupDir), ['.', '..']);
            foreach ($files as $file) {
                if (preg_match('/^backup_\d{4}-\d{2}-\d{2}_\d{2}-\d{2}-\d{2}\.sql$/', $file)) {
                    $filepath = $this->backupDir . '/' . $file;
                    $size = filesize($filepath);
                    $date = filemtime($filepath);
                    $backups[] = [
                        'filename' => $file,
                        'size' => $this->formatBytes($size),
                        'date' => date('d/m/Y H:i:s', $date),
                        'timestamp' => $date
                    ];
                }
            }
        }
        usort($backups, fn($a, $b) => $b['timestamp'] <=> $a['timestamp']);
        return $backups;
    }

    public function getLastBackup(): ?array {
        $backups = $this->getAllBackups();
        return count($backups) > 0 ? $backups[0] : null;
    }

    public function deleteBackupRecord($filename): void {
        try {
            $stmt = $this->db->prepare("UPDATE backups SET deleted_at = NOW() WHERE filename = ?");
            $stmt->execute([$filename]);
        } catch (PDOException $e) {
            // Table n'existe pas, ignorer
        }
    }

    public function restoreBackup($filename): void {
        $filepath = $this->backupDir . '/' . basename($filename);

        if (!file_exists($filepath)) {
            throw new Exception('Fichier de sauvegarde non trouvé');
        }

        $dbName = $this->getDBName();

        if ($this->canUseMysqldump()) {
            $this->restoreWithMysql($filepath, $dbName);
        } else {
            $this->restoreWithPHP($filepath);
        }
    }

    public function saveConfig($frequency, $retention): void {
        try {
            $stmt = $this->db->prepare("INSERT INTO backup_config (frequency, retention_days) VALUES (?, ?) ON DUPLICATE KEY UPDATE frequency = ?, retention_days = ?, updated_at = NOW()");
            $stmt->execute([$frequency, $retention, $frequency, $retention]);
        } catch (PDOException $e) {
            // Table n'existe pas, ignorer
        }
    }

    public function getConfig(): array {
        try {
            $stmt = $this->db->query("SELECT * FROM backup_config LIMIT 1");
            $config = $stmt->fetch(PDO::FETCH_ASSOC);
            return $config ?: ['frequency' => 'daily', 'retention_days' => 30];
        } catch (PDOException $e) {
            return ['frequency' => 'daily', 'retention_days' => 30];
        }
    }

    private function backupWithMysqldump($filepath, $dbName): void {
        $host = 'localhost';
        $user = getenv('DB_USER') ?? 'root';
        $password = getenv('DB_PASSWORD') ?? '';
        $port = getenv('DB_PORT') ?? '3306';

        $command = "mysqldump --host=$host --port=$port --user=$user";
        if ($password) {
            $command .= " --password='$password'";
        }
        $command .= " $dbName > " . escapeshellarg($filepath);

        exec($command, $output, $return);

        if ($return !== 0) {
            throw new Exception('Erreur lors de la sauvegarde mysqldump');
        }
    }

    private function backupWithPHP($filepath, $dbName): void {
        $sql = "-- Backup created on " . date('Y-m-d H:i:s') . "\n";
        $sql .= "-- Database: $dbName\n";
        $sql .= "-- Host: " . getenv('DB_HOST') . "\n\n";

        $tables = $this->db->query("SHOW TABLES")->fetchAll(PDO::FETCH_NUM);

        foreach ($tables as $table) {
            $tableName = $table[0];
            
            $createTable = $this->db->query("SHOW CREATE TABLE `$tableName`")->fetch(PDO::FETCH_NUM);
            $sql .= "DROP TABLE IF EXISTS `$tableName`;\n";
            $sql .= $createTable[1] . ";\n\n";

            $rows = $this->db->query("SELECT * FROM `$tableName`")->fetchAll(PDO::FETCH_ASSOC);
            foreach ($rows as $row) {
                $columns = array_keys($row);
                $values = array_values($row);
                $values = array_map(fn($v) => $this->db->quote($v), $values);
                $sql .= "INSERT INTO `$tableName` (`" . implode('`, `', $columns) . "`) VALUES (" . implode(', ', $values) . ");\n";
            }
            $sql .= "\n";
        }

        if (!file_put_contents($filepath, $sql)) {
            throw new Exception('Impossible de créer le fichier de sauvegarde');
        }
    }

    private function restoreWithMysql($filepath, $dbName): void {
        $user = getenv('DB_USER') ?? 'root';
        $password = getenv('DB_PASSWORD') ?? '';
        $port = getenv('DB_PORT') ?? '3306';

        $command = "mysql --host=localhost --port=$port --user=$user";
        if ($password) {
            $command .= " --password='$password'";
        }
        $command .= " $dbName < " . escapeshellarg($filepath);
        
        exec($command, $output, $return);

        if ($return !== 0) {
            throw new Exception('Erreur lors de la restauration');
        }
    }

    private function restoreWithPHP($filepath): void {
        $sql = file_get_contents($filepath);
        $queries = array_filter(
            array_map('trim', explode(';', $sql)),
            fn($q) => !empty($q) && !str_starts_with($q, '--')
        );

        foreach ($queries as $query) {
            try {
                $this->db->exec($query);
            } catch (PDOException $e) {
                throw new Exception('Erreur lors de la restauration: ' . $e->getMessage());
            }
        }
    }

    private function recordBackup($filename, $filesize): void {
        try {
            $stmt = $this->db->prepare("INSERT INTO backups (filename, filesize, created_at) VALUES (?, ?, NOW())");
            $stmt->execute([$filename, $filesize]);
        } catch (PDOException $e) {
            // Table n'existe pas, continuer
        }
    }

    private function canUseMysqldump(): bool {
        $os = strtoupper(substr(PHP_OS, 0, 3));
        $command = $os === 'WIN' ? 'where mysqldump' : 'which mysqldump';
        exec($command, $output, $return);
        return $return === 0;
    }

    private function getDBName(): string {
        try {
            return $this->db->query("SELECT DATABASE()")->fetch()[0];
        } catch (Exception $e) {
            return 'database';
        }
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
