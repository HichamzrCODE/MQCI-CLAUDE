<?php

class Backup {
    private PDO $db;
    private string $backupDir;

    public function __construct(PDO $db) {
        $this->db        = $db;
        $this->backupDir = dirname(__DIR__) . '/backups';
        if (!is_dir($this->backupDir)) mkdir($this->backupDir, 0755, true);
    }

    /**
     * Crée une sauvegarde SQL complète importable partout
     * (phpMyAdmin, ligne de commande, tout hébergeur)
     */
    public function createBackup(): string {
        $dbName   = $this->getDBName();
        $filename = 'backup_' . $dbName . '_' . date('Y-m-d_H-i-s') . '.sql';
        $filepath = $this->backupDir . '/' . $filename;

        // Essaie mysqldump d'abord (plus fiable), sinon fallback PHP pur
        if ($this->hasMysqldump()) {
            $this->dumpViaMysqldump($filepath, $dbName);
        } else {
            $this->dumpViaPHP($filepath, $dbName);
        }

        if (!file_exists($filepath) || filesize($filepath) === 0) {
            throw new RuntimeException('La sauvegarde a échoué ou est vide.');
        }

        return $filename;
    }

    public function getAllBackups(): array {
        $backups = [];
        if (!is_dir($this->backupDir)) return [];

        foreach (array_diff(scandir($this->backupDir), ['.','..']) as $file) {
            if (!str_ends_with($file, '.sql')) continue;
            $path      = $this->backupDir . '/' . $file;
            $backups[] = [
                'filename'  => $file,
                'size'      => $this->fmtBytes(filesize($path)),
                'size_raw'  => filesize($path),
                'date'      => date('d/m/Y H:i:s', filemtime($path)),
                'timestamp' => filemtime($path),
            ];
        }

        usort($backups, fn($a,$b) => $b['timestamp'] <=> $a['timestamp']);
        return $backups;
    }

    public function getLastBackup(): ?array {
        $all = $this->getAllBackups();
        return $all[0] ?? null;
    }

    // ── Méthode 1 : mysqldump (recommandée, plus rapide, 100% fiable) ──────
    private function dumpViaMysqldump(string $filepath, string $dbName): void {
        $host = $_ENV['DB_HOST']     ?? 'localhost';
        $user = $_ENV['DB_USERNAME'] ?? $_ENV['DB_USER'] ?? 'root';
        $pass = $_ENV['DB_PASSWORD'] ?? '';
        $port = $_ENV['DB_PORT']     ?? '3306';

        // Construire le fichier de config temporaire (évite le mot de passe en ligne de commande)
        $cnfFile = sys_get_temp_dir() . '/mqci_dump_' . uniqid() . '.cnf';
        file_put_contents($cnfFile,
            "[mysqldump]\nhost={$host}\nport={$port}\nuser={$user}\npassword={$pass}\n"
        );
        chmod($cnfFile, 0600);

        $cmd = sprintf(
            'mysqldump --defaults-extra-file=%s --single-transaction --routines --triggers --add-drop-table %s > %s 2>&1',
            escapeshellarg($cnfFile),
            escapeshellarg($dbName),
            escapeshellarg($filepath)
        );

        exec($cmd, $output, $code);
        unlink($cnfFile);

        if ($code !== 0) {
            throw new RuntimeException('mysqldump a échoué : ' . implode(' ', $output));
        }
    }

    // ── Méthode 2 : PHP pur (fallback si mysqldump absent) ────────────────
    private function dumpViaPHP(string $filepath, string $dbName): void {
        $f = fopen($filepath, 'w');
        if (!$f) throw new RuntimeException('Impossible de créer le fichier.');

        // En-tête compatible phpMyAdmin / MySQL Workbench / tout hébergeur
        fwrite($f, "-- ============================================================\n");
        fwrite($f, "-- MQCI 2.0 — Sauvegarde complète\n");
        fwrite($f, "-- Base : {$dbName}\n");
        fwrite($f, "-- Date : " . date('Y-m-d H:i:s') . "\n");
        fwrite($f, "-- ============================================================\n\n");
        fwrite($f, "SET FOREIGN_KEY_CHECKS=0;\n");
        fwrite($f, "SET SQL_MODE='NO_AUTO_VALUE_ON_ZERO';\n");
        fwrite($f, "SET NAMES utf8mb4;\n\n");

        $tables = $this->db->query("SHOW TABLES")->fetchAll(PDO::FETCH_COLUMN);

        foreach ($tables as $table) {
            // Structure
            $create = $this->db->query("SHOW CREATE TABLE `{$table}`")->fetch(PDO::FETCH_NUM);
            fwrite($f, "-- Table : `{$table}`\n");
            fwrite($f, "DROP TABLE IF EXISTS `{$table}`;\n");
            fwrite($f, $create[1] . ";\n\n");

            // Données par lots de 500 lignes
            $count = (int)$this->db->query("SELECT COUNT(*) FROM `{$table}`")->fetchColumn();
            $batch = 500;
            for ($offset = 0; $offset < max($count, 1); $offset += $batch) {
                $rows = $this->db->query("SELECT * FROM `{$table}` LIMIT {$batch} OFFSET {$offset}")->fetchAll(PDO::FETCH_ASSOC);
                if (!$rows) break;

                foreach ($rows as $row) {
                    $cols = '`' . implode('`, `', array_keys($row)) . '`';
                    $vals = implode(', ', array_map(fn($v) => $v === null ? 'NULL' : $this->db->quote($v), array_values($row)));
                    fwrite($f, "INSERT INTO `{$table}` ({$cols}) VALUES ({$vals});\n");
                }
                fwrite($f, "\n");
            }
        }

        fwrite($f, "\nSET FOREIGN_KEY_CHECKS=1;\n");
        fclose($f);
    }

    private function hasMysqldump(): bool {
        $cmd = PHP_OS_FAMILY === 'Windows' ? 'where mysqldump' : 'which mysqldump';
        exec($cmd, $out, $code);
        return $code === 0;
    }

    private function getDBName(): string {
        return $this->db->query("SELECT DATABASE()")->fetchColumn();
    }

    private function fmtBytes(int|float $b): string {
        $u = ['B','KB','MB','GB'];
        $b = max($b, 0);
        $p = min((int)floor(($b ? log($b) : 0) / log(1024)), count($u) - 1);
        return round($b / (1 << (10 * $p)), 2) . ' ' . $u[$p];
    }
}
