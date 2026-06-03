<?php

class AuditLogger {

    private PDO $db;

    public function __construct(PDO $db) {
        $this->db = $db;
    }

    /**
     * Enregistre une action dans la table audit_log.
     *
     * @param string   $entityType  Type d'entité (articles, clients…)
     * @param int      $entityId    ID de l'entité concernée
     * @param string   $action      CREATE | UPDATE | DELETE | VIEW
     * @param int      $userId      ID de l'utilisateur qui agit
     * @param array|null $oldValues Valeurs avant modification
     * @param array|null $newValues Valeurs après modification
     */
    public function log(
        string $entityType,
        int    $entityId,
        string $action,
        int    $userId,
        ?array $oldValues = null,
        ?array $newValues = null
    ): void {
        try {
            $ip        = $this->getClientIp();
            $userAgent = substr($_SERVER['HTTP_USER_AGENT'] ?? '', 0, 255);

            $stmt = $this->db->prepare(
                "INSERT INTO audit_log
                    (entity_type, entity_id, action, user_id, old_values, new_values, ip_address, user_agent)
                 VALUES
                    (:entity_type, :entity_id, :action, :user_id, :old_values, :new_values, :ip_address, :user_agent)"
            );
            $stmt->execute([
                ':entity_type' => $entityType,
                ':entity_id'   => $entityId,
                ':action'      => strtoupper($action),
                ':user_id'     => $userId,
                ':old_values'  => $oldValues !== null ? json_encode($oldValues, JSON_UNESCAPED_UNICODE) : null,
                ':new_values'  => $newValues !== null ? json_encode($newValues, JSON_UNESCAPED_UNICODE) : null,
                ':ip_address'  => $ip,
                ':user_agent'  => $userAgent,
            ]);
        } catch (PDOException $e) {
            // On ne fait pas échouer l'action principale si l'audit plante
            error_log('AuditLogger error: ' . $e->getMessage());
        }
    }

    private function getClientIp(): string {
        $keys = ['HTTP_CLIENT_IP', 'HTTP_X_FORWARDED_FOR', 'REMOTE_ADDR'];
        foreach ($keys as $key) {
            if (!empty($_SERVER[$key])) {
                // Prendre la première IP de la liste (cas proxy)
                $ip = trim(explode(',', $_SERVER[$key])[0]);
                if (filter_var($ip, FILTER_VALIDATE_IP)) {
                    return $ip;
                }
            }
        }
        return '';
    }
}
