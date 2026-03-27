<?php
require_once __DIR__ . '/../models/CompanyModel.php';

class CompanyController {
    private $model;

    private function normalizeRole($role)
    {
        $role = strtolower(trim((string) $role));
        return $role === 'recruiter' ? 'pilote' : $role;
    }

    private function syncSessionRole()
    {
        if (!empty($_SESSION['user']['role'])) {
            $_SESSION['user']['role'] = $this->normalizeRole($_SESSION['user']['role']);
        }
    }

    public function __construct()
    {
        $this->model = new CompanyModel();
        session_start();
        $this->syncSessionRole();
    }

    /**
     * Simple role check helper. Accepts array of allowed roles.
     * Sends 403 and exits if current session user does not have one of the roles.
     */
    private function requireRole(array $roles)
    {
        $user = $_SESSION['user'] ?? null;
        $currentRole = $this->normalizeRole($user['role'] ?? '');

        if ($user) {
            $_SESSION['user']['role'] = $currentRole;
        }

        if (!$user || !in_array($currentRole, $roles, true)) {
            header('HTTP/1.0 403 Forbidden');
            echo 'Acces refuse';
            exit;
        }
    }

    public function index()
    {
        // search criteria come from GET parameters
        $criteria = [];
        foreach (['name', 'email', 'phone', 'description'] as $field) {
            if (!empty($_GET[$field])) {
                $criteria[$field] = trim($_GET[$field]);
            }
        }
        $companies = $this->model->search($criteria);
        include __DIR__ . '/../views/companies.php';
    }

    public function view()
    {
        $id = intval($_GET['id'] ?? 0);
        $company = $this->model->getById($id);
        if (!$company) {
            header('HTTP/1.0 404 Not Found');
            echo 'Entreprise introuvable';
            return;
        }
        $offers = $this->model->getOffers($id);
        $evaluations = $this->model->getEvaluations($id);
        include __DIR__ . '/../views/company_detail.php';
    }

    public function create()
    {
        // only pilote and admin may create
        $this->requireRole(['pilote', 'admin']);
        $isAjax = !empty($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest';
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $name = trim($_POST['name'] ?? '');
            $description = trim($_POST['description'] ?? '');
            $email = trim($_POST['email'] ?? '');
            $phone = trim($_POST['phone'] ?? '');
            if ($name) {
                $result = $this->model->create($name, $description, $email, $phone);
                if ($isAjax) {
                    header('Content-Type: application/json');
                    echo json_encode($result);
                    return;
                }
            }
        }
        if ($isAjax) {
            header('Content-Type: application/json');
            echo json_encode(['success' => false, 'error' => 'Invalid request']);
            return;
        }
        header('Location: companies.php');
        exit;
    }

    public function edit()
    {
        // only pilote and admin may edit
        $this->requireRole(['pilote', 'admin']);
        // GET -> display form
        if ($_SERVER['REQUEST_METHOD'] === 'GET') {
            $id = intval($_GET['id'] ?? 0);
            $company = $this->model->getById($id);
            if (!$company) {
                header('HTTP/1.0 404 Not Found');
                echo 'Entreprise introuvable';
                return;
            }
            include __DIR__ . '/../views/company_form.php';
            return;
        }

        // POST -> perform update
        $isAjax = !empty($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest';
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $id = intval($_POST['id'] ?? 0);
            $name = trim($_POST['name'] ?? '');
            $description = trim($_POST['description'] ?? '');
            $email = trim($_POST['email'] ?? '');
            $phone = trim($_POST['phone'] ?? '');
            if ($id && $name) {
                $result = $this->model->update($id, $name, $description, $email, $phone);
                if ($isAjax) {
                    header('Content-Type: application/json');
                    echo json_encode($result);
                    return;
                }
            }
        }
        if ($isAjax) {
            header('Content-Type: application/json');
            echo json_encode(['success' => false, 'error' => 'Invalid request']);
            return;
        }
        header('Location: companies.php');
        exit;
    }

    public function delete()
    {
        // only pilote and admin may delete
        $this->requireRole(['pilote', 'admin']);
        $isAjax = !empty($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest';
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $id = intval($_POST['id'] ?? 0);
            if ($id) {
                $result = $this->model->delete($id);
                if ($isAjax) {
                    header('Content-Type: application/json');
                    echo json_encode($result);
                    return;
                }
            }
        }
        if ($isAjax) {
            header('Content-Type: application/json');
            echo json_encode(['success' => false, 'error' => 'Invalid request']);
            return;
        }
        header('Location: companies.php');
        exit;
    }

    public function evaluate()
    {
        // only pilote and admin may evaluate
        $this->requireRole(['pilote', 'admin']);
        $isAjax = !empty($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest';
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $companyId = intval($_POST['company_id'] ?? 0);
            // use logged-in user id
            $userId = $_SESSION['user']['id'] ?? 0;
            $rating = intval($_POST['rating'] ?? 0);
            $comment = trim($_POST['comment'] ?? '');
            // ensure company actually has at least one offer
            $offers = $this->model->getOffers($companyId);
            if (!$companyId || empty($offers)) {
                $result = ['success' => false, 'error' => 'Entreprise sans offre ne peut pas etre evaluee'];
            } elseif ($userId && $rating > 0) {
                $result = $this->model->addEvaluation($companyId, $userId, $rating, $comment);
            } else {
                $result = ['success' => false, 'error' => 'Donnees manquantes'];
            }
            if ($isAjax) {
                header('Content-Type: application/json');
                echo json_encode($result);
                return;
            }
        }
        if ($isAjax) {
            header('Content-Type: application/json');
            echo json_encode(['success' => false, 'error' => 'Invalid request']);
            return;
        }
        // non-ajax redirect back to detail
        $redir = 'companies.php?action=view&id=' . urlencode($companyId);
        header('Location: ' . $redir);
        exit;
    }
}
