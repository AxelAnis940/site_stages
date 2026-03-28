<?php
require_once __DIR__ . '/../models/OfferModel.php';

class OfferController {
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

    private function sanitizeRedirect($redirect, $fallback = 'offers.php')
    {
        $redirect = trim((string) $redirect);
        if ($redirect === '') {
            return $fallback;
        }

        foreach (['offers.php', 'companies.php'] as $allowedPrefix) {
            if (strpos($redirect, $allowedPrefix) === 0) {
                return $redirect;
            }
        }

        return $fallback;
    }

    public function __construct()
    {
        if (session_status() !== PHP_SESSION_ACTIVE) {
            session_start();
        }

        $this->model = new OfferModel();
        $this->syncSessionRole();
    }

    public function index()
    {
        $criteria = [];
        foreach (['title', 'description', 'company_id'] as $field) {
            if (!empty($_GET[$field])) {
                $criteria[$field] = trim((string) $_GET[$field]);
            }
        }

        $offers = $this->model->search($criteria);
        $stats = $this->model->getStats();
        $companyOptions = $this->model->getCompanies();
        include __DIR__ . '/../views/offers.php';
    }

    public function view()
    {
        $id = intval($_GET['id'] ?? 0);
        $offer = $this->model->getById($id);

        if (!$offer) {
            header('HTTP/1.0 404 Not Found');
            echo 'Offre introuvable';
            return;
        }

        include __DIR__ . '/../views/offer_detail.php';
    }

    public function create()
    {
        $this->requireRole(['pilote', 'admin']);

        if ($_SERVER['REQUEST_METHOD'] === 'GET') {
            $companyOptions = $this->model->getCompanies();
            $selectedCompanyId = intval($_GET['company_id'] ?? 0);
            include __DIR__ . '/../views/offer_form.php';
            return;
        }

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $companyId = intval($_POST['company_id'] ?? 0);
            $title = trim($_POST['title'] ?? '');
            $description = trim($_POST['description'] ?? '');

            if ($companyId && $title !== '') {
                $result = $this->model->create($companyId, $title, $description);
                if (!empty($result['success'])) {
                    $_SESSION['offer_flash'] = ['type' => 'success', 'message' => 'Offre creee avec succes.'];
                    header('Location: offers.php?action=view&id=' . urlencode((string) $result['id']));
                    exit;
                }

                $_SESSION['offer_flash'] = ['type' => 'error', 'message' => 'Creation impossible: ' . ($result['error'] ?? 'Erreur inconnue')];
                header('Location: offers.php?action=create&company_id=' . urlencode((string) $companyId));
                exit;
            }
        }

        $_SESSION['offer_flash'] = ['type' => 'error', 'message' => 'Le titre et l entreprise sont obligatoires.'];
        header('Location: offers.php?action=create');
        exit;
    }

    public function edit()
    {
        $this->requireRole(['pilote', 'admin']);

        if ($_SERVER['REQUEST_METHOD'] === 'GET') {
            $id = intval($_GET['id'] ?? 0);
            $offer = $this->model->getById($id);

            if (!$offer) {
                $_SESSION['offer_flash'] = ['type' => 'error', 'message' => 'Offre introuvable.'];
                header('Location: offers.php');
                exit;
            }

            $companyOptions = $this->model->getCompanies();
            $selectedCompanyId = intval($offer['company_id'] ?? 0);
            include __DIR__ . '/../views/offer_form.php';
            return;
        }

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $id = intval($_POST['id'] ?? 0);
            $companyId = intval($_POST['company_id'] ?? 0);
            $title = trim($_POST['title'] ?? '');
            $description = trim($_POST['description'] ?? '');

            if ($id && $companyId && $title !== '') {
                $result = $this->model->update($id, $companyId, $title, $description);
                if (!empty($result['success'])) {
                    $_SESSION['offer_flash'] = ['type' => 'success', 'message' => 'Offre modifiee avec succes.'];
                    header('Location: offers.php?action=view&id=' . urlencode((string) $id));
                    exit;
                }

                $_SESSION['offer_flash'] = ['type' => 'error', 'message' => 'Modification impossible: ' . ($result['error'] ?? 'Erreur inconnue')];
                header('Location: offers.php?action=edit&id=' . urlencode((string) $id));
                exit;
            }
        }

        $_SESSION['offer_flash'] = ['type' => 'error', 'message' => 'Le titre et l entreprise sont obligatoires.'];
        header('Location: offers.php');
        exit;
    }

    public function delete()
    {
        $this->requireRole(['pilote', 'admin']);
        $redirect = $this->sanitizeRedirect($_POST['redirect_to'] ?? 'offers.php');

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $id = intval($_POST['id'] ?? 0);

            if ($id) {
                $result = $this->model->delete($id);
                $_SESSION['offer_flash'] = !empty($result['success'])
                    ? ['type' => 'success', 'message' => 'Offre supprimee avec succes.']
                    : ['type' => 'error', 'message' => 'Suppression impossible: ' . ($result['error'] ?? 'Erreur inconnue')];
            } else {
                $_SESSION['offer_flash'] = ['type' => 'error', 'message' => 'Offre invalide.'];
            }
        }

        header('Location: ' . $redirect);
        exit;
    }
}
