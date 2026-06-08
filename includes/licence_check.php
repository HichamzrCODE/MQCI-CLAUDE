<?php

/**
 * Vérification de la licence MQCI
 * Le secret est lu depuis .env — jamais hardcodé dans le code source
 */

define('LICENCE_FILE', dirname(__DIR__) . '/licence.key');

// Lecture du secret depuis l'environnement uniquement
$secret = $_ENV['LICENCE_SECRET'] ?? null;

if (empty($secret)) {
    die('<div style="color:red;font-family:sans-serif;margin:3rem">
        Erreur de configuration : LICENCE_SECRET manquant dans .env.<br>
        Contactez le développeur.
    </div>');
}

/**
 * Récupère la première adresse MAC valide de la machine
 */
function getMacAddress(): string|false
{
    ob_start();

    if (strncasecmp(PHP_OS, 'WIN', 3) === 0) {
        system('getmac 2>nul');
    } else {
        system('ip link show 2>/dev/null || ifconfig 2>/dev/null');
    }

    $output = ob_get_clean();

    if (preg_match('/([A-F0-9]{2}([-:][A-F0-9]{2}){5})/i', $output, $matches)) {
        return strtoupper(str_replace('-', ':', $matches[1]));
    }

    return false;
}

// Vérifier l'existence du fichier licence
if (!file_exists(LICENCE_FILE)) {
    die('<div style="color:red;font-family:sans-serif;margin:3rem">
        Aucune licence trouvée.<br>Contactez le développeur.
    </div>');
}

// Lire et découper le fichier licence
$licenceData = trim(file_get_contents(LICENCE_FILE));
$licParts    = explode('|', $licenceData);

if (count($licParts) !== 3) {
    die('<div style="color:red;font-family:sans-serif;margin:3rem">
        Fichier licence invalide.<br>Contactez le développeur.
    </div>');
}

[$hash, $lic_mac, $lic_date] = $licParts;

// Vérifier la date d'expiration
if (date('Y-m-d') > $lic_date) {
    die('<div style="color:red;font-family:sans-serif;margin:3rem">
        Licence expirée le ' . htmlspecialchars($lic_date) . '.<br>Contactez le développeur.
    </div>');
}

// Vérifier l'adresse MAC
$mac = getMacAddress();
$licMacNormalized = strtoupper(str_replace('-', ':', $lic_mac));

if (!$mac || $mac !== $licMacNormalized) {
    die('<div style="color:red;font-family:sans-serif;margin:3rem">
        Licence non valide pour cette machine.<br>Contactez le développeur.
    </div>');
}

// Vérifier le hash (intégrité totale)
$hashAttendu = hash('sha256', $secret . '|' . $lic_mac . '|' . $lic_date);

if (!hash_equals($hashAttendu, $hash)) {
    die('<div style="color:red;font-family:sans-serif;margin:3rem">
        Licence corrompue ou falsifiée.<br>Contactez le développeur.
    </div>');
}
