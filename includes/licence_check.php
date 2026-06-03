<?php
// Définir le chemin du fichier licence
define('LICENCE_FILE', dirname(__DIR__).'/licence.key'); // ou __DIR__.'/../licence.key'
// Définir le secret connu seulement de vous
$secret = 'chelseaestlameilleurequipeen2025';

/**
 * Récupère la première adresse MAC trouvée sur la machine
 * @return string|false
 */
function getMacAddress() {
    ob_start();
    // Utilise 'getmac' sur Windows, 'ifconfig' ou 'ip link' sur Linux/Mac
    if (strncasecmp(PHP_OS, 'WIN', 3) == 0) {
        system('getmac');
        $output = ob_get_contents();
    } else {
        system('ifconfig 2>/dev/null || ip link', $retval);
        $output = ob_get_contents();
    }
    ob_end_clean();
    // Cherche une adresse MAC
    if (preg_match('/([A-F0-9]{2}([-:][A-F0-9]{2}){5})/i', $output, $matches)) {
        return strtoupper(str_replace('-', ':', $matches[1]));
    }
    return false;
}

// Vérifie l'existence du fichier licence
if (!file_exists(LICENCE_FILE)) {
    die('<div style="color:red;font-family:sans-serif;margin:3rem">Aucune licence trouvée.<br>Contactez le développeur.</div>');
}

// Lit et découpe le contenu du fichier licence
$licenceData = trim(file_get_contents(LICENCE_FILE));
$licParts = explode('|', $licenceData);

if (count($licParts) !== 3) {
    die('<div style="color:red;font-family:sans-serif;margin:3rem">Fichier licence invalide.<br>Contactez le développeur.</div>');
}

list($hash, $lic_mac, $lic_date) = $licParts;

// Vérifie la date d'expiration
if (date('Y-m-d') > $lic_date) {
    die('<div style="color:red;font-family:sans-serif;margin:3rem">Licence expirée le '.$lic_date.'.<br>Contactez le développeur.</div>');
}

// Vérifie la MAC
$mac = getMacAddress();
if (!$mac || $mac !== strtoupper(str_replace('-', ':', $lic_mac))) {
    die('<div style="color:red;font-family:sans-serif;margin:3rem">Licence non valide pour cette machine.<br>Contactez le développeur.</div>');
}

// Vérifie le hash
$hash_attendu = hash('sha256', $secret.'|'.$lic_mac.'|'.$lic_date);
if ($hash !== $hash_attendu) {
    die('<div style="color:red;font-family:sans-serif;margin:3rem">Licence corrompue ou falsifiée.<br>Contactez le développeur.</div>');
}
?>