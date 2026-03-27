<?php
require_once __DIR__ . '/../../config/db_config.php';

class UserModel {
    private $conn;

    private function normalizeRole($role)
    {
        $role = strtolower(trim((string) $role));
        return $role === 'recruiter' ? 'pilote' : $role;
    }

    private function normalizeUserRow(array $user)
    {
        if (isset($user['role'])) {
            $user['role'] = $this->normalizeRole($user['role']);
        }

        return $user;
    }

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
            $rows[] = $this->normalizeUserRow($r);
        }
        return $rows;
    }

    public function getById($id)
    {
        $stmt = $this->conn->prepare("SELECT id, name, email, role, created_at FROM users WHERE id = ? LIMIT 1");
        if (!$stmt) {
            return null;
        }

        $stmt->bind_param('i', $id);
        $stmt->execute();
        $result = $stmt->get_result();
        $user = $result ? $result->fetch_assoc() : null;
        $stmt->close();

        return $user ? $this->normalizeUserRow($user) : null;
    }

    private function countAdmins()
    {
        $res = $this->conn->query("SELECT COUNT(*) AS total FROM users WHERE role = 'admin'");
        if (!$res) {
            return 0;
        }

        $row = $res->fetch_assoc();
        return (int) ($row['total'] ?? 0);
    }

    public function create($name, $email, $password, $role = 'student')
    {
        $role = $this->normalizeRole($role);
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
                if ($r) {
                    $r = $this->normalizeUserRow($r);
                }
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

    public function update($id, $name, $email, $password, $role = 'student')
    {
        $role = $this->normalizeRole($role);
        $existingUser = $this->getById($id);

        if (!$existingUser) {
            return ['success' => false, 'error' => 'Utilisateur introuvable'];
        }

        if (($existingUser['role'] ?? '') === 'admin' && $role !== 'admin' && $this->countAdmins() <= 1) {
            return ['success' => false, 'error' => 'Impossible de modifier le dernier admin.'];
        }

        if ($password !== '') {
            $hash = password_hash($password, PASSWORD_DEFAULT);
            $stmt = $this->conn->prepare("UPDATE users SET name = ?, email = ?, password = ?, role = ? WHERE id = ?");
            if (!$stmt) {
                return ['success' => false, 'error' => $this->conn->error];
            }
            $stmt->bind_param('ssssi', $name, $email, $hash, $role, $id);
        } else {
            $stmt = $this->conn->prepare("UPDATE users SET name = ?, email = ?, role = ? WHERE id = ?");
            if (!$stmt) {
                return ['success' => false, 'error' => $this->conn->error];
            }
            $stmt->bind_param('sssi', $name, $email, $role, $id);
        }

        $ok = $stmt->execute();
        if (!$ok) {
            $err = $stmt->error;
            $errno = $stmt->errno;
            $stmt->close();
            return ['success' => false, 'error' => $err, 'errno' => $errno];
        }

        $stmt->close();
        $updatedUser = $this->getById($id);

        return ['success' => true, 'row' => $updatedUser];
    }

    public function delete($id)
    {
        $existingUser = $this->getById($id);

        if (!$existingUser) {
            return ['success' => false, 'error' => 'Utilisateur introuvable'];
        }

        if (($existingUser['role'] ?? '') === 'admin' && $this->countAdmins() <= 1) {
            return ['success' => false, 'error' => 'Impossible de supprimer le dernier admin.'];
        }

        $stmt = $this->conn->prepare("DELETE FROM users WHERE id = ?");
        if (!$stmt) {
            return ['success' => false, 'error' => $this->conn->error];
        }

        $stmt->bind_param('i', $id);
        $ok = $stmt->execute();
        if (!$ok) {
            $err = $stmt->error;
            $errno = $stmt->errno;
            $stmt->close();
            return ['success' => false, 'error' => $err, 'errno' => $errno];
        }

        $stmt->close();
        return ['success' => true];
    }

    public function login($email, $password, $role)
    {
        $role = $this->normalizeRole($role);

        if ($role === 'pilote') {
            $stmt = $this->conn->prepare("SELECT id, name, email, role, password FROM users WHERE email = ? AND role IN ('pilote', 'recruiter') LIMIT 1");
        } else {
            $stmt = $this->conn->prepare("SELECT id, name, email, role, password FROM users WHERE email = ? AND role = ? LIMIT 1");
        }

        if (!$stmt) {
            return ['success' => false, 'error' => 'DB error'];
        }

        if ($role === 'pilote') {
            $stmt->bind_param('s', $email);
        } else {
            $stmt->bind_param('ss', $email, $role);
        }

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
        $user = $this->normalizeUserRow($user);
        return ['success' => true, 'user' => $user];
    }
}
