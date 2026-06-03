<?php

$appConfig = require __DIR__ . '/../config/app.php';

/**
 * Récupère une valeur de configuration
 * @param string $key Clé (ex: 'name' ou 'paths.css')
 * @param mixed $default Valeur par défaut
 */
function config($key, $default = null) {
    global $appConfig;
    
    $keys = explode('.', $key);
    $value = $appConfig;
    
    foreach ($keys as $k) {
        $value = $value[$k] ?? null;
        if ($value === null) return $default;
    }
    
    return $value;
}

/**
 * Génère une URL
 */
function url($path = '') {
    return config('base_url') . ltrim($path, '/');
}

/**
 * Génère une URL d'asset
 */
function asset($path) {
    return config('public_path') . ltrim($path, '/');
}