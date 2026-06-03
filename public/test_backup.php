<<<<<<< HEAD
<?php

$config = require __DIR__ . '/../config/database.php';
$DB_HOST = $config['host'];
$DB_NAME = $config['database'];
$DB_USER = $config['username'];
$DB_PASS = $config['password'];
$MYSQL_BIN_PATH = $config['mysqldump_path'];

$backupPath = __DIR__ . '/../sauvegardes/test_final.sql';

if (!is_dir(__DIR__ . '/../sauvegardes')) {
    mkdir(__DIR__ . '/../sauvegardes', 0755, true);
}

echo "<h1>TEST FINAL - AVEC --no-column-statistics</h1>";

// Construit la commande mysqldump
$mysqldump_exe = "\"{$MYSQL_BIN_PATH}/mysqldump\"";

$command = $mysqldump_exe;
$command .= " -h " . escapeshellarg($DB_HOST);
$command .= " -u " . escapeshellarg($DB_USER);

if (!empty($DB_PASS)) {
    $command .= " -p" . escapeshellarg($DB_PASS);
}

$command .= " --no-column-statistics";
$command .= " --lock-tables=false";
$command .= " --single-transaction";
$command .= " --routines";
$command .= " --triggers";
$command .= " --events";
$command .= " " . escapeshellarg($DB_NAME);
$command .= " > " . escapeshellarg($backupPath) . " 2>&1";

echo "<pre>Commande: " . htmlspecialchars($command) . "</pre>";

$output = [];
$return_code = 0;
exec($command, $output, $return_code);

echo "Code retour: " . $return_code . "<br>";
echo "Fichier existe: " . (file_exists($backupPath) ? "OUI" : "NON") . "<br>";

if (file_exists($backupPath)) {
    $fileSize = filesize($backupPath);
    echo "Taille du fichier: " . $fileSize . " bytes<br>";
    
    // Lit le contenu
    $content = file_get_contents($backupPath);
    
    // Compte les CREATE TABLE
    preg_match_all('/CREATE TABLE/', $content, $matches);
    $tableCount = count($matches[0]);
    
    echo "CREATE TABLE trouvés: " . $tableCount . "<br>";
    
    // Liste les noms des tables
    preg_match_all('/CREATE TABLE `([^`]+)`/', $content, $tableMatches);
    if (!empty($tableMatches[1])) {
        echo "<h3>Tables :</h3>";
        echo "<ul>";
        foreach (array_unique($tableMatches[1]) as $table) {
            echo "<li>" . htmlspecialchars($table) . "</li>";
        }
        echo "</ul>";
    }
    
    // Aperçu
    echo "<h3>Aperçu (1500 premiers caractères):</h3>";
    echo "<pre style='background:#f5f5f5;padding:10px;max-height:400px;overflow:auto;'>" . htmlspecialchars(substr($content, 0, 1500)) . "</pre>";
    
    // Fin du fichier
    echo "<h3>Fin du fichier (500 derniers caractères):</h3>";
    echo "<pre style='background:#f5f5f5;padding:10px;max-height:200px;overflow:auto;'>" . htmlspecialchars(substr($content, -500)) . "</pre>";
}

if (!empty($output)) {
    echo "<h3>Output/Erreurs:</h3>";
    echo "<pre style='background:#ffeeee;padding:10px;'>" . htmlspecialchars(implode("\n", $output)) . "</pre>";
}

=======
<?php

$config = require __DIR__ . '/../config/database.php';
$DB_HOST = $config['host'];
$DB_NAME = $config['database'];
$DB_USER = $config['username'];
$DB_PASS = $config['password'];
$MYSQL_BIN_PATH = $config['mysqldump_path'];

$backupPath = __DIR__ . '/../sauvegardes/test_final.sql';

if (!is_dir(__DIR__ . '/../sauvegardes')) {
    mkdir(__DIR__ . '/../sauvegardes', 0755, true);
}

echo "<h1>TEST FINAL - AVEC --no-column-statistics</h1>";

// Construit la commande mysqldump
$mysqldump_exe = "\"{$MYSQL_BIN_PATH}/mysqldump\"";

$command = $mysqldump_exe;
$command .= " -h " . escapeshellarg($DB_HOST);
$command .= " -u " . escapeshellarg($DB_USER);

if (!empty($DB_PASS)) {
    $command .= " -p" . escapeshellarg($DB_PASS);
}

$command .= " --no-column-statistics";
$command .= " --lock-tables=false";
$command .= " --single-transaction";
$command .= " --routines";
$command .= " --triggers";
$command .= " --events";
$command .= " " . escapeshellarg($DB_NAME);
$command .= " > " . escapeshellarg($backupPath) . " 2>&1";

echo "<pre>Commande: " . htmlspecialchars($command) . "</pre>";

$output = [];
$return_code = 0;
exec($command, $output, $return_code);

echo "Code retour: " . $return_code . "<br>";
echo "Fichier existe: " . (file_exists($backupPath) ? "OUI" : "NON") . "<br>";

if (file_exists($backupPath)) {
    $fileSize = filesize($backupPath);
    echo "Taille du fichier: " . $fileSize . " bytes<br>";
    
    // Lit le contenu
    $content = file_get_contents($backupPath);
    
    // Compte les CREATE TABLE
    preg_match_all('/CREATE TABLE/', $content, $matches);
    $tableCount = count($matches[0]);
    
    echo "CREATE TABLE trouvés: " . $tableCount . "<br>";
    
    // Liste les noms des tables
    preg_match_all('/CREATE TABLE `([^`]+)`/', $content, $tableMatches);
    if (!empty($tableMatches[1])) {
        echo "<h3>Tables :</h3>";
        echo "<ul>";
        foreach (array_unique($tableMatches[1]) as $table) {
            echo "<li>" . htmlspecialchars($table) . "</li>";
        }
        echo "</ul>";
    }
    
    // Aperçu
    echo "<h3>Aperçu (1500 premiers caractères):</h3>";
    echo "<pre style='background:#f5f5f5;padding:10px;max-height:400px;overflow:auto;'>" . htmlspecialchars(substr($content, 0, 1500)) . "</pre>";
    
    // Fin du fichier
    echo "<h3>Fin du fichier (500 derniers caractères):</h3>";
    echo "<pre style='background:#f5f5f5;padding:10px;max-height:200px;overflow:auto;'>" . htmlspecialchars(substr($content, -500)) . "</pre>";
}

if (!empty($output)) {
    echo "<h3>Output/Erreurs:</h3>";
    echo "<pre style='background:#ffeeee;padding:10px;'>" . htmlspecialchars(implode("\n", $output)) . "</pre>";
}

>>>>>>> 4f8dbbb6b83eb9c6f755d57287033c7da885a3b1
?>