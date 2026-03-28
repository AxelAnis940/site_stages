<?php
require_once __DIR__ . '/../../config/db_config.php';

class OfferModel {
    private $conn;

    public function __construct()
    {
        $this->conn = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME, DB_PORT);
        if ($this->conn->connect_error) {
            die('Database connection error: ' . $this->conn->connect_error);
        }
    }

    public function getCompanies()
    {
        $res = $this->conn->query("SELECT id, name FROM companies ORDER BY name ASC");
        $rows = [];

        while ($r = $res->fetch_assoc()) {
            $rows[] = $r;
        }

        return $rows;
    }

    public function getStats()
    {
        $sql = "SELECT COUNT(DISTINCT o.id) AS total_offers,
                       COUNT(a.id) AS total_applications,
                       COUNT(DISTINCT CASE WHEN a.id IS NOT NULL THEN o.id END) AS offers_with_applications
                FROM offers o
                LEFT JOIN applications a ON a.offer_id = o.id";
        $res = $this->conn->query($sql);

        if (!$res) {
            return [
                'total_offers' => 0,
                'total_applications' => 0,
                'offers_with_applications' => 0,
            ];
        }

        $row = $res->fetch_assoc();

        return [
            'total_offers' => (int) ($row['total_offers'] ?? 0),
            'total_applications' => (int) ($row['total_applications'] ?? 0),
            'offers_with_applications' => (int) ($row['offers_with_applications'] ?? 0),
        ];
    }

    public function search(array $criteria = [])
    {
        $sql = "SELECT o.id,
                       o.company_id,
                       o.title,
                       o.description,
                       o.created_at,
                       c.name AS company_name,
                       COUNT(a.id) AS applications_count
                FROM offers o
                JOIN companies c ON c.id = o.company_id
                LEFT JOIN applications a ON a.offer_id = o.id
                WHERE 1 = 1";
        $params = [];
        $types = '';

        if (!empty($criteria['title'])) {
            $sql .= " AND o.title LIKE ?";
            $params[] = '%' . $criteria['title'] . '%';
            $types .= 's';
        }

        if (!empty($criteria['description'])) {
            $sql .= " AND o.description LIKE ?";
            $params[] = '%' . $criteria['description'] . '%';
            $types .= 's';
        }

        if (!empty($criteria['company_id'])) {
            $sql .= " AND o.company_id = ?";
            $params[] = (int) $criteria['company_id'];
            $types .= 'i';
        }

        $sql .= " GROUP BY o.id, o.company_id, o.title, o.description, o.created_at, c.name
                  ORDER BY o.created_at DESC, o.id DESC";

        if (empty($params)) {
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
        $sql = "SELECT o.id,
                       o.company_id,
                       o.title,
                       o.description,
                       o.created_at,
                       c.name AS company_name,
                       c.description AS company_description,
                       c.email AS company_email,
                       c.phone AS company_phone,
                       COUNT(a.id) AS applications_count
                FROM offers o
                JOIN companies c ON c.id = o.company_id
                LEFT JOIN applications a ON a.offer_id = o.id
                WHERE o.id = ?
                GROUP BY o.id, o.company_id, o.title, o.description, o.created_at, c.name, c.description, c.email, c.phone
                LIMIT 1";
        $stmt = $this->conn->prepare($sql);
        if (!$stmt) {
            return null;
        }

        $stmt->bind_param('i', $id);
        $stmt->execute();
        $result = $stmt->get_result();
        $offer = $result ? $result->fetch_assoc() : null;
        $stmt->close();

        return $offer;
    }

    public function create($companyId, $title, $description)
    {
        $stmt = $this->conn->prepare("INSERT INTO offers (company_id, title, description) VALUES (?, ?, ?)");
        if (!$stmt) {
            return ['success' => false, 'error' => $this->conn->error];
        }

        $stmt->bind_param('iss', $companyId, $title, $description);
        $ok = $stmt->execute();

        if (!$ok) {
            $err = $stmt->error;
            $errno = $stmt->errno;
            $stmt->close();
            return ['success' => false, 'error' => $err, 'errno' => $errno];
        }

        $id = $this->conn->insert_id;
        $stmt->close();

        return ['success' => true, 'id' => $id, 'offer' => $this->getById($id)];
    }

    public function update($id, $companyId, $title, $description)
    {
        $stmt = $this->conn->prepare("UPDATE offers SET company_id = ?, title = ?, description = ? WHERE id = ?");
        if (!$stmt) {
            return ['success' => false, 'error' => $this->conn->error];
        }

        $stmt->bind_param('issi', $companyId, $title, $description, $id);
        $ok = $stmt->execute();

        if (!$ok) {
            $err = $stmt->error;
            $errno = $stmt->errno;
            $stmt->close();
            return ['success' => false, 'error' => $err, 'errno' => $errno];
        }

        $stmt->close();

        return ['success' => true, 'offer' => $this->getById($id)];
    }

    public function delete($id)
    {
        $stmt = $this->conn->prepare("DELETE FROM offers WHERE id = ?");
        if (!$stmt) {
            return ['success' => false, 'error' => $this->conn->error];
        }

        $stmt->bind_param('i', $id);
        $ok = $stmt->execute();

        if (!$ok) {
            $err = $stmt->error;
            $stmt->close();
            return ['success' => false, 'error' => $err];
        }

        $stmt->close();
        return ['success' => true];
    }
}
