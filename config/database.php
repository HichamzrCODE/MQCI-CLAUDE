<?php

/**
 * Configuration de la base de données
 * Toutes les valeurs sensibles viennent du fichier .env
 */

// Détection dynamique du protocole et host pour BASE_URL
$protocol = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ? 'https' : 'http';
$host     = $_SERVER['HTTP_HOST'] ?? 'localhost';

if (!defined('BASE_URL')) {
    define('BASE_URL', $protocol . '://' . $host . '/maqci/public');
}

return [
    'host'          => $_ENV['DB_HOST']    ?? 'localhost',
    'database'      => $_ENV['DB_NAME']    ?? 'mqci',
    'username'      => $_ENV['DB_USER']    ?? 'root',
    'password'      => $_ENV['DB_PASS']    ?? '',
    'charset'       => $_ENV['DB_CHARSET'] ?? 'utf8mb4',

    'options' => [
        PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        PDO::ATTR_EMULATE_PREPARES   => false,
    ],

    'mysqldump_path' => $_ENV['MYSQLDUMP_PATH'] ?? 'C:/wamp64/bin/mysql/mysql8.0.31/bin',
];
