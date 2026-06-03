<?php

class CsrfMiddleware {

    private const TOKEN_KEY  = 'csrf_token';
    private const TOKEN_LEN  = 32;

    /**
     * Génère (ou récupère) le token CSRF de la session en cours.
     */
    public static function getToken(): string {
        if (session_status() !== PHP_SESSION_ACTIVE) {
            session_start();
        }
        if (empty($_SESSION[self::TOKEN_KEY])) {
            $_SESSION[self::TOKEN_KEY] = bin2hex(random_bytes(self::TOKEN_LEN));
        }
        return $_SESSION[self::TOKEN_KEY];
    }

    /**
     * Retourne un champ HTML caché contenant le token CSRF.
     */
    public static function field(): string {
        $token = htmlspecialchars(self::getToken(), ENT_QUOTES, 'UTF-8');
        return '<input type="hidden" name="csrf_token" value="' . $token . '">';
    }

    /**
     * Vérifie le token CSRF soumis via POST.
     * Appelle die() en cas d'échec.
     */
    public static function verify(): void {
        $submitted = $_POST['csrf_token'] ?? '';
        $expected  = $_SESSION[self::TOKEN_KEY] ?? '';

        if (!hash_equals($expected, $submitted)) {
            http_response_code(403);
            die("Erreur de sécurité : token CSRF invalide. Veuillez recharger la page et réessayer.");
        }
    }

    /**
     * Vérifie silencieusement et retourne true/false.
     */
    public static function check(): bool {
        $submitted = $_POST['csrf_token'] ?? '';
        $expected  = $_SESSION[self::TOKEN_KEY] ?? '';
        return hash_equals($expected, $submitted);
    }
}
