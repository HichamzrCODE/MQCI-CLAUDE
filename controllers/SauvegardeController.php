<<<<<<< HEAD
<?php
class SauvegardeController
{
    private $backupDir = __DIR__ . '/../sauvegardes/';

    // Vérifie si l'utilisateur est admin
    private function isAdmin() {
        return isset($_SESSION['role']) && $_SESSION['role'] === 'admin';
    }

    public function index()
    {
        if (!$this->isAdmin()) {
            die('Accès réservé à l\'administrateur.');
        }
        $saves = [];
        if (is_dir($this->backupDir)) {
            foreach (glob($this->backupDir . '*.sql') as $file) {
                $saves[] = [
                    'name' => basename($file),
                    'date' => date("Y-m-d H:i:s", filemtime($file)),
                    'size' => filesize($file)
                ];
            }
            // Trie les fichiers par nom décroissant (plus récent en premier)
            usort($saves, function($a, $b) {
                return strcmp($b['name'], $a['name']);
            });
        }
        include __DIR__ . '/../views/sauvegarde/index.php';
    }

    public function download($file)
    {
        if (!$this->isAdmin()) {
            die('Accès réservé à l\'administrateur.');
        }
        $file = basename($file);
        $filePath = realpath($this->backupDir . $file);
        
        // Sécurité : vérifie que le fichier existe et est bien un .sql dans le bon dossier
        if (
            !$filePath ||
            !is_file($filePath) ||
            pathinfo($filePath, PATHINFO_EXTENSION) !== 'sql' ||
            strpos($filePath, realpath($this->backupDir)) !== 0
        ) {
            die('Fichier non trouvé ou accès interdit.');
        }
        
        header('Content-Type: application/octet-stream');
        header('Content-Disposition: attachment; filename="' . basename($filePath) . '"');
        header('Content-Length: ' . filesize($filePath));
        header('Pragma: no-cache');
        header('Expires: 0');
        readfile($filePath);
        exit;
    }

    public function backup()
    {
        if (!$this->isAdmin()) {
            die('Accès réservé à l\'administrateur.');
        }

        // Récupère la configuration de la base de données
        $config = require __DIR__ . '/../config/database.php';
        $DB_HOST = $config['host'];
        $DB_NAME = $config['database'];
        $DB_USER = $config['username'];
        $DB_PASS = $config['password'];
        $MYSQL_BIN_PATH = $config['mysqldump_path'];

        // Crée le dossier de sauvegarde s'il n'existe pas
        if (!is_dir($this->backupDir)) {
            mkdir($this->backupDir, 0755, true);
        }

        // Génère le nom du fichier avec date et heure
        $date = date('Y-m-d_H-i-s');
        $filename = "dump_{$DB_NAME}_{$date}.sql";
        $backupPath = $this->backupDir . $filename;

        // Construit la commande mysqldump
        $mysqldump_exe = "\"{$MYSQL_BIN_PATH}/mysqldump\"";

        // Construction de la commande
        $command = $mysqldump_exe;
        $command .= " -h " . escapeshellarg($DB_HOST);
        $command .= " -u " . escapeshellarg($DB_USER);
        
        // Ajoute le mot de passe s'il existe
        if (!empty($DB_PASS)) {
            $command .= " -p" . escapeshellarg($DB_PASS);
        }

        // Les options de dump (sans --no-column-statistics qui ne marche pas)
        $command .= " --lock-tables=false";
        $command .= " --single-transaction";
        $command .= " --routines";
        $command .= " --triggers";
        $command .= " --events";
        $command .= " --force";  // Force le dump même avec des erreurs
        $command .= " --default-character-set=utf8mb4";

        // Le nom de la base DOIT être à la fin
        $command .= " " . escapeshellarg($DB_NAME);

        // Redirection du fichier de sortie
        $command .= " > " . escapeshellarg($backupPath);

        // Sur Windows, ajoute la redirection d'erreurs
        if (strtoupper(substr(PHP_OS, 0, 3)) === 'WIN') {
            $command .= " 2>&1";
        }

        // Exécute la commande
        $output = [];
        $return_code = 0;
        exec($command, $output, $return_code);

        // Journalise l'exécution pour déboguer
        error_log("=== BACKUP MYSQL ===");
        error_log("Date: " . date('Y-m-d H:i:s'));
        error_log("Base: " . $DB_NAME);
        error_log("Fichier: " . $backupPath);
        error_log("Code retour: " . $return_code);
        if (!empty($output)) {
            error_log("Output: " . implode(" | ", array_slice($output, 0, 5)));
        }

        // Vérifie que la sauvegarde a réussi
        if (!file_exists($backupPath)) {
            error_log("ERREUR: Le fichier de backup n'a pas été créé!");
            die('Erreur lors de la création du fichier de sauvegarde.');
        }

        $fileSize = filesize($backupPath);
        
        // Vérification simple : le fichier doit faire plus de 100 KB
        if ($fileSize < 100000) {
            error_log("ERREUR: Fichier trop petit (" . $fileSize . " bytes)");
            die('Erreur : la sauvegarde est trop petite (' . round($fileSize/1024, 1) . ' Ko). Fichier incomplet.');
        }

        error_log("✓ Backup réussi - Taille: " . $fileSize . " bytes");

        // Redirige vers la page de sauvegarde après succès
        header('Location: index.php?action=sauvegarde');
        exit;
    }
=======
<?php
class SauvegardeController
{
    private $backupDir = __DIR__ . '/../sauvegardes/';

    // Vérifie si l'utilisateur est admin
    private function isAdmin() {
        return isset($_SESSION['role']) && $_SESSION['role'] === 'admin';
    }

    public function index()
    {
        if (!$this->isAdmin()) {
            die('Accès réservé à l\'administrateur.');
        }
        $saves = [];
        if (is_dir($this->backupDir)) {
            foreach (glob($this->backupDir . '*.sql') as $file) {
                $saves[] = [
                    'name' => basename($file),
                    'date' => date("Y-m-d H:i:s", filemtime($file)),
                    'size' => filesize($file)
                ];
            }
            // Trie les fichiers par nom décroissant (plus récent en premier)
            usort($saves, function($a, $b) {
                return strcmp($b['name'], $a['name']);
            });
        }
        include __DIR__ . '/../views/sauvegarde/index.php';
    }

    public function download($file)
    {
        if (!$this->isAdmin()) {
            die('Accès réservé à l\'administrateur.');
        }
        $file = basename($file);
        $filePath = realpath($this->backupDir . $file);
        
        // Sécurité : vérifie que le fichier existe et est bien un .sql dans le bon dossier
        if (
            !$filePath ||
            !is_file($filePath) ||
            pathinfo($filePath, PATHINFO_EXTENSION) !== 'sql' ||
            strpos($filePath, realpath($this->backupDir)) !== 0
        ) {
            die('Fichier non trouvé ou accès interdit.');
        }
        
        header('Content-Type: application/octet-stream');
        header('Content-Disposition: attachment; filename="' . basename($filePath) . '"');
        header('Content-Length: ' . filesize($filePath));
        header('Pragma: no-cache');
        header('Expires: 0');
        readfile($filePath);
        exit;
    }

    public function backup()
    {
        if (!$this->isAdmin()) {
            die('Accès réservé à l\'administrateur.');
        }

        // Récupère la configuration de la base de données
        $config = require __DIR__ . '/../config/database.php';
        $DB_HOST = $config['host'];
        $DB_NAME = $config['database'];
        $DB_USER = $config['username'];
        $DB_PASS = $config['password'];
        $MYSQL_BIN_PATH = $config['mysqldump_path'];

        // Crée le dossier de sauvegarde s'il n'existe pas
        if (!is_dir($this->backupDir)) {
            mkdir($this->backupDir, 0755, true);
        }

        // Génère le nom du fichier avec date et heure
        $date = date('Y-m-d_H-i-s');
        $filename = "dump_{$DB_NAME}_{$date}.sql";
        $backupPath = $this->backupDir . $filename;

        // Construit la commande mysqldump
        $mysqldump_exe = "\"{$MYSQL_BIN_PATH}/mysqldump\"";

        // Construction de la commande
        $command = $mysqldump_exe;
        $command .= " -h " . escapeshellarg($DB_HOST);
        $command .= " -u " . escapeshellarg($DB_USER);
        
        // Ajoute le mot de passe s'il existe
        if (!empty($DB_PASS)) {
            $command .= " -p" . escapeshellarg($DB_PASS);
        }

        // Les options de dump (sans --no-column-statistics qui ne marche pas)
        $command .= " --lock-tables=false";
        $command .= " --single-transaction";
        $command .= " --routines";
        $command .= " --triggers";
        $command .= " --events";
        $command .= " --force";  // Force le dump même avec des erreurs
        $command .= " --default-character-set=utf8mb4";

        // Le nom de la base DOIT être à la fin
        $command .= " " . escapeshellarg($DB_NAME);

        // Redirection du fichier de sortie
        $command .= " > " . escapeshellarg($backupPath);

        // Sur Windows, ajoute la redirection d'erreurs
        if (strtoupper(substr(PHP_OS, 0, 3)) === 'WIN') {
            $command .= " 2>&1";
        }

        // Exécute la commande
        $output = [];
        $return_code = 0;
        exec($command, $output, $return_code);

        // Journalise l'exécution pour déboguer
        error_log("=== BACKUP MYSQL ===");
        error_log("Date: " . date('Y-m-d H:i:s'));
        error_log("Base: " . $DB_NAME);
        error_log("Fichier: " . $backupPath);
        error_log("Code retour: " . $return_code);
        if (!empty($output)) {
            error_log("Output: " . implode(" | ", array_slice($output, 0, 5)));
        }

        // Vérifie que la sauvegarde a réussi
        if (!file_exists($backupPath)) {
            error_log("ERREUR: Le fichier de backup n'a pas été créé!");
            die('Erreur lors de la création du fichier de sauvegarde.');
        }

        $fileSize = filesize($backupPath);
        
        // Vérification simple : le fichier doit faire plus de 100 KB
        if ($fileSize < 100000) {
            error_log("ERREUR: Fichier trop petit (" . $fileSize . " bytes)");
            die('Erreur : la sauvegarde est trop petite (' . round($fileSize/1024, 1) . ' Ko). Fichier incomplet.');
        }

        error_log("✓ Backup réussi - Taille: " . $fileSize . " bytes");

        // Redirige vers la page de sauvegarde après succès
        header('Location: index.php?action=sauvegarde');
        exit;
    }
>>>>>>> 4f8dbbb6b83eb9c6f755d57287033c7da885a3b1
}