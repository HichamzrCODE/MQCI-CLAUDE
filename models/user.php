<<<<<<< HEAD
<?php

class User {
    private $db;

    public function __construct(PDO $db) {
        $this->db = $db;
    }

    public function findByUsername(string $username): ?array {
        $stmt = $this->db->prepare("SELECT * FROM users WHERE username = ?");
        $stmt->execute([$username]);
        $user = $stmt->fetch(PDO::FETCH_ASSOC);
        return $user ?: null;
    }

    public function create(string $username, string $password): int {
        $hashedPassword = password_hash($password, PASSWORD_DEFAULT);
        $stmt = $this->db->prepare("INSERT INTO users (username, password) VALUES (?, ?)");
        $stmt->execute([$username, $hashedPassword]);
        return (int)$this->db->lastInsertId();
    }

    public function setSessionToken($id_users, $token) {
    $stmt = $this->db->prepare("UPDATE users SET session_token=?, last_login=NOW() WHERE id_users=?");
    $stmt->execute([$token, $id_users]);
}

public function getSessionToken($id_users) {
    $stmt = $this->db->prepare("SELECT session_token FROM users WHERE id_users=?");
    $stmt->execute([$id_users]);
    return $stmt->fetchColumn();
}
=======
<?php

class User {
    private $db;

    public function __construct(PDO $db) {
        $this->db = $db;
    }

    public function findByUsername(string $username): ?array {
        $stmt = $this->db->prepare("SELECT * FROM users WHERE username = ?");
        $stmt->execute([$username]);
        $user = $stmt->fetch(PDO::FETCH_ASSOC);
        return $user ?: null;
    }

    public function create(string $username, string $password): int {
        $hashedPassword = password_hash($password, PASSWORD_DEFAULT);
        $stmt = $this->db->prepare("INSERT INTO users (username, password) VALUES (?, ?)");
        $stmt->execute([$username, $hashedPassword]);
        return (int)$this->db->lastInsertId();
    }

    public function setSessionToken($id_users, $token) {
    $stmt = $this->db->prepare("UPDATE users SET session_token=?, last_login=NOW() WHERE id_users=?");
    $stmt->execute([$token, $id_users]);
}

public function getSessionToken($id_users) {
    $stmt = $this->db->prepare("SELECT session_token FROM users WHERE id_users=?");
    $stmt->execute([$id_users]);
    return $stmt->fetchColumn();
}
>>>>>>> 4f8dbbb6b83eb9c6f755d57287033c7da885a3b1
}