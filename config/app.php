<?php

/**
 * Configuration globale de l'application
 * À modifier au même endroit pour tout le projet
 */

return [
    // Nom de l'application
    'name' => 'MAQCI',
    'version' => '1.0.0',
    
    // URLs
    'base_url' => 'http://localhost/maqci/public/',
    'public_path' => '/maqci/public/',
    
    // Chemins
    'paths' => [
        'uploads' => __DIR__ . '/../public/uploads/',
        'uploads_url' => '/maqci/public/uploads/',
        'css' => '/maqci/public/css/',
        'js' => '/maqci/public/js/',
        'img' => '/maqci/public/img/',
    ],
    
    // Database
    'database' => [
        'host' => 'localhost',
        'database' => 'mqci',
        'username' => 'root',
        'password' => '',
        'charset' => 'utf8mb4',
    ],
];