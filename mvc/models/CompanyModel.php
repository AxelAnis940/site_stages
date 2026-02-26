<?php
require_once __DIR__ . '/../../config/db_config.php';

class CompanyModel {
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
        $sql = "SELECT id, name, description, email, phone, created_at
                FROM companies
                ORDER BY id ASC";
        $res = $this->conn->query($sql);
        $rows = [];
        while ($r = $res->fetch_assoc()) {
            $rows[] = $r;
        }
        return $rows;
    }

    /**
     * Search using optional criteria array: name, email, phone, description.
     * Returns rows augmented with applicants_count and avg_rating.
     */
    public function search(array $criteria = [])
    {
        $sql = "SELECT c.*, 
                    (SELECT COUNT(*) 
                       FROM applications a 
                       JOIN offers o ON a.offer_id = o.id 
                      WHERE o.company_id = c.id) AS applicants_count,
                    (SELECT AVG(rating) FROM company_evaluations e WHERE e.company_id = c.id) AS avg_rating
                FROM companies c
                WHERE 1=1";
        $params = [];
        $types = '';

        if (!empty($criteria['name'])) {
            $sql .= " AND c.name LIKE ?";
            $params[] = '%' . $criteria['name'] . '%';
            $types .= 's';
        }
        if (!empty($criteria['email'])) {
            $sql .= " AND c.email LIKE ?";
            $params[] = '%' . $criteria['email'] . '%';
            $types .= 's';
        }
        if (!empty($criteria['phone'])) {
            $sql .= " AND c.phone LIKE ?";
            $params[] = '%' . $criteria['phone'] . '%';
            $types .= 's';
        }
        if (!empty($criteria['description'])) {
            $sql .= " AND c.description LIKE ?";
            $params[] = '%' . $criteria['description'] . '%';
            $types .= 's';
        }

        $sql .= " ORDER BY c.id ASC";

        if (empty($params)) {
            // simple query
            $res = $this->conn->query($sql);
        } else {
            $stmt = $this->conn->prepare($sql);
            if (!$stmt) {
                return [];
            }
            $stmt->bind_param($types, ...$params);
            $stmt->execute();
            $res = $stmt->get_result();
            $stmt->close();
        }

        $rows = [];
        while ($r = $res->fetch_assoc()) {
            $rows[] = $r;
        }
        return $rows;
    }

    public function getById($id)
    {
        $stmt = $this->conn->prepare("SELECT id, name, description, email, phone, created_at FROM companies WHERE id = ? LIMIT 1");
        if (!$stmt) return null;
        $stmt->bind_param('i', $id);
        $stmt->execute();
        $row = $stmt->get_result()->fetch_assoc();
        $stmt->close();
        return $row;
    }

    public function create($name, $description, $email, $phone)
    {
        $stmt = $this->conn->prepare("INSERT INTO companies (name, description, email, phone) VALUES (?, ?, ?, ?)");
        if (!$stmt) {
            return ['success' => false, 'error' => $this->conn->error];
        }
        $stmt->bind_param('ssss', $name, $description, $email, $phone);
        $ok = $stmt->execute();
        if ($ok) {
            $id = $this->conn->insert_id;
            $stmt->close();
            $company = $this->getById($id);
            return ['success' => true, 'id' => $id, 'company' => $company];
        } else {
            $err = $stmt->error;
            $errno = $stmt->errno;
            $stmt->close();
            return ['success' => false, 'error' => $err, 'errno' => $errno];
        }
    }

    public function update($id, $name, $description, $email, $phone)
    {
        $stmt = $this->conn->prepare("UPDATE companies SET name = ?, description = ?, email = ?, phone = ? WHERE id = ?");
        if (!$stmt) {
            return ['success' => false, 'error' => $this->conn->error];
        }
        $stmt->bind_param('ssssi', $name, $description, $email, $phone, $id);
        $ok = $stmt->execute();
        if ($ok) {
            $stmt->close();
            $company = $this->getById($id);
            return ['success' => true, 'company' => $company];
        } else {
            $err = $stmt->error;
            $errno = $stmt->errno;
            $stmt->close();
            return ['success' => false, 'error' => $err, 'errno' => $errno];
        }
    }

    public function delete($id)
    {
        $stmt = $this->conn->prepare("DELETE FROM companies WHERE id = ?");
        if (!$stmt) {
            return ['success' => false, 'error' => $this->conn->error];
        }
        $stmt->bind_param('i', $id);
        $ok = $stmt->execute();
        if ($ok) {
            $stmt->close();
            return ['success' => true];
        } else {
            $err = $stmt->error;
            $stmt->close();
            return ['success' => false, 'error' => $err];
        }
    }

    public function addEvaluation($companyId, $userId, $rating, $comment = null)
    {
        $stmt = $this->conn->prepare("INSERT INTO company_evaluations (company_id, user_id, rating, comment) VALUES (?, ?, ?, ?)");
        if (!$stmt) {
            return ['success' => false, 'error' => $this->conn->error];
        }
        $stmt->bind_param('iiis', $companyId, $userId, $rating, $comment);
        $ok = $stmt->execute();
        if ($ok) {
            $stmt->close();
            return ['success' => true];
        } else {
            $err = $stmt->error;
            $stmt->close();
            return ['success' => false, 'error' => $err];
        }
    }

    public function getOffers($companyId)
    {
        $stmt = $this->conn->prepare("SELECT id, title, description, created_at FROM offers WHERE company_id = ? ORDER BY created_at DESC");
        if (!$stmt) return [];
        $stmt->bind_param('i', $companyId);
        $stmt->execute();
        $res = $stmt->get_result();
        $rows = [];
        while ($r = $res->fetch_assoc()) {
            $rows[] = $r;
        }
        $stmt->close();
        return $rows;
    }

    public function getEvaluations($companyId)
    {
        $stmt = $this->conn->prepare("SELECT e.rating, e.comment, e.created_at, u.name AS student_name
                                       FROM company_evaluations e
                                       JOIN users u ON u.id = e.user_id
                                       WHERE e.company_id = ?
                                       ORDER BY e.created_at DESC");
        if (!$stmt) return [];
        $stmt->bind_param('i', $companyId);
        $stmt->execute();
        $res = $stmt->get_result();
        $rows = [];
        while ($r = $res->fetch_assoc()) {
            $rows[] = $r;
        }
        $stmt->close();
        return $rows;
    }
}
