<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

require_once __DIR__ . '/../vendor/autoload.php';

$dotenv = Dotenv\Dotenv::createImmutable(dirname(__DIR__));
$dotenv->load();

echo "✅ .env chargé<br>";

require_once __DIR__ . '/../includes/licence_check.php';
echo "✅ Licence OK<br>";

session_start();
echo "✅ Session OK<br>";

$dbConfig = require __DIR__ . '/../config/database.php';
echo "✅ Config BD chargée<br>";

try {
    $db = new PDO(
        "mysql:host={$dbConfig['host']};dbname={$dbConfig['database']};charset={$dbConfig['charset']}",
        $dbConfig['username'],
        $dbConfig['password'],
        $dbConfig['options']
    );
    echo "✅ Connexion BD OK<br>";
} catch (PDOException $e) {
    echo "❌ Erreur BD : " . $e->getMessage() . "<br>";
}

echo "<br><strong>Tout est OK !</strong>";
?>