<?php

class User {
    private PDO $db;

    public function __construct(PDO $db) {
        $this->db = $db;
    }

    public function findById(int $id): ?array {
        $stmt = $this->db->prepare("SELECT * FROM users WHERE id_users = ?");
        $stmt->execute([$id]);
        return $stmt->fetch(PDO::FETCH_ASSOC) ?: null;
    }

    public function findByUsername(string $username): ?array {
        $stmt = $this->db->prepare("SELECT * FROM users WHERE username = ?");
        $stmt->execute([$username]);
        return $stmt->fetch(PDO::FETCH_ASSOC) ?: null;
    }

    public function getAll(): array {
        $stmt = $this->db->query(
            "SELECT id_users, username, nom, prenom, telephone, succursale, role, status,
                    created_at, last_login, session_token
             FROM users ORDER BY created_at DESC"
        );
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function create(array $data): int {
        $hashedPassword = password_hash($data['password'], PASSWORD_DEFAULT);
        $stmt = $this->db->prepare(
            "INSERT INTO users (username, password, role, nom, prenom, telephone, succursale, status, created_at)
             VALUES (?, ?, ?, ?, ?, ?, ?, ?, NOW())"
        );
        $stmt->execute([
            $data['username'],
            $hashedPassword,
            $data['role']      ?? 'user',
            $data['nom'],
            $data['prenom'],
            $data['telephone'],
            $data['succursale'] ?? '',
            $data['status']    ?? 'actif',
        ]);
        return (int)$this->db->lastInsertId();
    }

    public function update(int $id, array $data): void {
        $fields = [];
        $values = [];

        foreach (['nom', 'prenom', 'telephone', 'succursale', 'role', 'status'] as $field) {
            if (isset($data[$field])) {
                $fields[] = "$field = ?";
                $values[] = $data[$field];
            }
        }

        if (!empty($data['password'])) {
            $fields[] = "password = ?";
            $values[] = password_hash($data['password'], PASSWORD_DEFAULT);
        }

        if (!$fields) return;

        $values[] = $id;
        $stmt = $this->db->prepare("UPDATE users SET " . implode(', ', $fields) . " WHERE id_users = ?");
        $stmt->execute($values);
    }

    public function delete(int $id): void {
        $stmt = $this->db->prepare("DELETE FROM users WHERE id_users = ?");
        $stmt->execute([$id]);
    }

    public function exists(int $id): bool {
        $stmt = $this->db->prepare("SELECT 1 FROM users WHERE id_users = ?");
        $stmt->execute([$id]);
        return (bool)$stmt->fetchColumn();
    }

    public function usernameExists(string $username, ?int $excludeId = null): bool {
        if ($excludeId) {
            $stmt = $this->db->prepare("SELECT 1 FROM users WHERE username = ? AND id_users != ?");
            $stmt->execute([$username, $excludeId]);
        } else {
            $stmt = $this->db->prepare("SELECT 1 FROM users WHERE username = ?");
            $stmt->execute([$username]);
        }
        return (bool)$stmt->fetchColumn();
    }

    public function setSessionToken(int $id, string $token): void {
        $stmt = $this->db->prepare(
            "UPDATE users SET session_token = ?, last_login = NOW() WHERE id_users = ?"
        );
        $stmt->execute([$token, $id]);
    }

    /**
     * Rafraîchit last_login à chaque requête pour maintenir "En ligne"
     * Appelé depuis index.php après vérification de session
     */
    public function refreshActivity(int $id): void {
        $stmt = $this->db->prepare(
            "UPDATE users SET last_login = NOW() WHERE id_users = ?"
        );
        $stmt->execute([$id]);
    }

    public function getSessionToken(int $id): ?string {
        $stmt = $this->db->prepare("SELECT session_token FROM users WHERE id_users = ?");
        $stmt->execute([$id]);
        return $stmt->fetchColumn() ?: null;
    }

    public function clearSessionToken(int $id): void {
        $stmt = $this->db->prepare(
            "UPDATE users SET session_token = NULL WHERE id_users = ?"
        );
        $stmt->execute([$id]);
    }

    /**
     * Un utilisateur est "En ligne" si :
     * - Il a un session_token (non null)
     * - Son last_login date de moins de 15 minutes
     */
    public function isConnected(int $id): bool {
        $stmt = $this->db->prepare(
            "SELECT session_token, last_login FROM users WHERE id_users = ?"
        );
        $stmt->execute([$id]);
        $user = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$user || !$user['session_token'] || !$user['last_login']) {
            return false;
        }

        $lastActivity = strtotime($user['last_login']);
        return $lastActivity > (time() - 15 * 60); // 15 minutes
    }
}
