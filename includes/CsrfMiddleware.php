<?php

class CsrfMiddleware {
    private const TOKEN_KEY = 'csrf_token';
    private const TOKEN_LENGTH = 32;

    /**
     * Assurer que la session est démarrée
     */
    private static function ensureSession(): void {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
    }

    /**
     * Génère (ou récupère) le token CSRF de la session en cours.
     */
    public static function getToken(): string {
        self::ensureSession();
        
        if (empty($_SESSION[self::TOKEN_KEY])) {
            $_SESSION[self::TOKEN_KEY] = bin2hex(random_bytes(self::TOKEN_LENGTH));
        }
        return $_SESSION[self::TOKEN_KEY];
    }

    /**
     * Régénère le token CSRF (après login/logout par exemple)
     */
    public static function regenerate(): string {
        self::ensureSession();
        $_SESSION[self::TOKEN_KEY] = bin2hex(random_bytes(self::TOKEN_LENGTH));
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
     * ✅ NE PAS regénérer automatiquement
     */
    public static function verify(): void {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            return;
        }

        self::ensureSession();

        $submitted = $_POST['csrf_token'] ?? '';
        $expected = $_SESSION[self::TOKEN_KEY] ?? '';

        if (empty($submitted) || empty($expected)) {
            http_response_code(403);
            throw new Exception('Erreur de sécurité : token CSRF manquant. Veuillez recharger la page et réessayer.');
        }

        if (!hash_equals($expected, $submitted)) {
            http_response_code(403);
            throw new Exception('Erreur de sécurité : token CSRF invalide. Veuillez recharger la page et réessayer.');
        }
    }

    /**
     * Vérifie silencieusement et retourne true/false.
     */
    public static function check(): bool {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            return true;
        }

        self::ensureSession();

        $submitted = $_POST['csrf_token'] ?? '';
        $expected = $_SESSION[self::TOKEN_KEY] ?? '';

        if (empty($submitted) || empty($expected)) {
            return false;
        }

        return hash_equals($expected, $submitted);
    }

    /**
     * Invalider le token (pour logout par exemple)
     */
    public static function invalidate(): void {
        self::ensureSession();
        unset($_SESSION[self::TOKEN_KEY]);
    }
}
?>