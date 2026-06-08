<?php

/**
 * Configuration globale de l'application
 * Toutes les valeurs viennent du fichier .env
 */

return [
    // Identité
    'name'    => $_ENV['APP_NAME']    ?? 'MAQCI',
    'version' => $_ENV['APP_VERSION'] ?? '2.0',
    'env'     => $_ENV['APP_ENV']     ?? 'local',

    // URLs
    'base_url'    => rtrim($_ENV['APP_BASE_URL']    ?? 'http://localhost/maqci/public/', '/') . '/',
    'public_path' => rtrim($_ENV['APP_PUBLIC_PATH'] ?? '/maqci/public/', '/') . '/',

    // Chemins fichiers
    'paths' => [
        'uploads'     => __DIR__ . '/../public/uploads/',
        'uploads_url' => ($_ENV['APP_PUBLIC_PATH'] ?? '/maqci/public/') . 'uploads/',
        'css'         => ($_ENV['APP_PUBLIC_PATH'] ?? '/maqci/public/') . 'css/',
        'js'          => ($_ENV['APP_PUBLIC_PATH'] ?? '/maqci/public/') . 'js/',
        'img'         => ($_ENV['APP_PUBLIC_PATH'] ?? '/maqci/public/') . 'img/',
    ],

    // Base de données (référence, la vraie config est dans database.php)
    'database' => [
        'host'     => $_ENV['DB_HOST']    ?? 'localhost',
        'database' => $_ENV['DB_NAME']    ?? 'mqci',
        'username' => $_ENV['DB_USER']    ?? 'root',
        'password' => $_ENV['DB_PASS']    ?? '',
        'charset'  => $_ENV['DB_CHARSET'] ?? 'utf8mb4',
    ],
];
