<?php

$protocol = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ? "https" : "http";
$host = $_SERVER['HTTP_HOST'];
$basePath = '/S6/public'; // adapte si besoin

if (!defined('BASE_URL')) {
    define('BASE_URL', $protocol . '://' . $host . $basePath);
}

return [
    'host' => 'localhost',
    'database' => 'mqci',
    'username' => 'root',
    'password' => '',
    'charset' => 'utf8',
    'options' => [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        PDO::ATTR_EMULATE_PREPARES => false,
    ],
    'mysqldump_path' => 'C:/wamp64/bin/mysql/mysql8.0.31/bin', // 
];
?>