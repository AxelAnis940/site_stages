<?php
require_once __DIR__ . '/../../config/db_config.php';

class UserModel {
    private $conn;

    public function __construct()
    {
        $this->conn = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME, DB_PORT);
        if ($this->conn->connect_error) {
            die('Database connection error: ' . $this->conn->connect_error);
        }
    }

    public function getAll()
    {
        $res = $this->conn->query("SELECT id, name, email, role, created_at FROM users ORDER BY id ASC");
        $rows = [];
        while ($r = $res->fetch_assoc()) {
            $rows[] = $r;
        }
        return $rows;
    }

    public function create($name, $email, $password, $role = 'student')
    {
        $hash = password_hash($password, PASSWORD_DEFAULT);
        $stmt = $this->conn->prepare("INSERT INTO users (name, email, password, role) VALUES (?, ?, ?, ?)");
        if (!$stmt) {
            return ['success' => false, 'error' => $this->conn->error, 'db' => DB_NAME, 'db_user' => DB_USER];
        }
        $stmt->bind_param('ssss', $name, $email, $hash, $role);
        $ok = $stmt->execute();
        if ($ok) {
            $id = $this->conn->insert_id;
            $stmt->close();
            // Fetch inserted row to confirm persistence
            $res = $this->conn->prepare("SELECT id, name, email, role, created_at FROM users WHERE id = ? LIMIT 1");
            if ($res) {
                $res->bind_param('i', $id);
                $res->execute();
                $r = $res->get_result()->fetch_assoc();
                $res->close();
                return ['success' => true, 'id' => $id, 'row' => $r, 'db' => DB_NAME, 'db_user' => DB_USER];
            }
            return ['success' => true, 'id' => $id, 'db' => DB_NAME, 'db_user' => DB_USER];
        } else {
            $err = $stmt->error;
            $errno = $stmt->errno;
            $stmt->close();
            return ['success' => false, 'error' => $err, 'errno' => $errno, 'db' => DB_NAME, 'db_user' => DB_USER];
        }
    }

    public function login($email, $password, $role)
    {
        $stmt = $this->conn->prepare("SELECT id, name, email, role, password FROM users WHERE email = ? AND role = ? LIMIT 1");
        if (!$stmt) {
            return ['success' => false, 'error' => 'DB error'];
        }
        $stmt->bind_param('ss', $email, $role);
        $stmt->execute();
        $res = $stmt->get_result();
        $user = $res->fetch_assoc();
        $stmt->close();

        if (!$user) {
            return ['success' => false, 'error' => 'Invalid credentials'];
        }

        // Verify password
        if (!password_verify($password, $user['password'])) {
            return ['success' => false, 'error' => 'Invalid credentials'];
        }

        // Return user without password
        unset($user['password']);
        return ['success' => true, 'user' => $user];
    }
}
