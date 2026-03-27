<?php
require_once __DIR__ . '/../models/UserModel.php';

class UserController {
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

    private function requireRole(array $roles, bool $isAjax = false)
    {
        $user = $_SESSION['user'] ?? null;
        $currentRole = $this->normalizeRole($user['role'] ?? '');

        if ($user) {
            $_SESSION['user']['role'] = $currentRole;
        }

        if (!$user || !in_array($currentRole, $roles, true)) {
            header('HTTP/1.0 403 Forbidden');

            if ($isAjax) {
                header('Content-Type: application/json');
                echo json_encode(['success' => false, 'error' => 'Access denied']);
            } else {
                echo 'Acces refuse. Connectez-vous avec un compte admin.';
            }

            exit;
        }
    }

    public function __construct()
    {
        if (session_status() !== PHP_SESSION_ACTIVE) {
            session_start();
        }

        $this->model = new UserModel();
        $this->syncSessionRole();
    }

    public function index()
    {
        $this->requireRole(['admin']);
        $users = $this->model->getAll();
        include __DIR__ . '/../views/users.php';
    }

    public function create()
    {
        ini_set('display_errors', 1);
        error_reporting(E_ALL);

        $isAjax = !empty($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest';
        $this->requireRole(['admin'], $isAjax);

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $name = trim($_POST['name'] ?? '');
            $email = trim($_POST['email'] ?? '');
            $password = $_POST['password'] ?? '';
            $role = $_POST['role'] ?? 'student';

            if ($name && $email && $password) {
                $result = $this->model->create($name, $email, $password, $role);

                if ($isAjax) {
                    header('Content-Type: application/json');
                    if (is_array($result)) {
                        echo json_encode($result);
                    } else {
                        echo json_encode(['success' => false, 'error' => 'Unknown model response']);
                    }
                    return;
                }

                $_SESSION['user_flash'] = !empty($result['success'])
                    ? ['type' => 'success', 'message' => 'Utilisateur cree avec succes.']
                    : ['type' => 'error', 'message' => 'Creation impossible: ' . ($result['error'] ?? 'Erreur inconnue')];
            } else {
                $_SESSION['user_flash'] = ['type' => 'error', 'message' => 'Tous les champs sont obligatoires.'];
            }
        }

        if ($isAjax) {
            header('Content-Type: application/json');
            echo json_encode(['success' => false, 'error' => 'Invalid request']);
            return;
        }

        header('Location: users.php');
        exit;
    }

    public function edit()
    {
        $this->requireRole(['admin']);

        if ($_SERVER['REQUEST_METHOD'] === 'GET') {
            $id = intval($_GET['id'] ?? 0);
            $editingUser = $this->model->getById($id);

            if (!$editingUser) {
                $_SESSION['user_flash'] = ['type' => 'error', 'message' => 'Utilisateur introuvable.'];
                header('Location: users.php');
                exit;
            }

            $users = $this->model->getAll();
            include __DIR__ . '/../views/users.php';
            return;
        }

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $id = intval($_POST['id'] ?? 0);
            $name = trim($_POST['name'] ?? '');
            $email = trim($_POST['email'] ?? '');
            $password = $_POST['password'] ?? '';
            $role = $_POST['role'] ?? 'student';

            if ($id && $name && $email && $role) {
                $result = $this->model->update($id, $name, $email, $password, $role);

                if (!empty($result['success'])) {
                    if (!empty($_SESSION['user']['id']) && intval($_SESSION['user']['id']) === $id && !empty($result['row'])) {
                        $_SESSION['user'] = array_merge($_SESSION['user'], $result['row']);
                        $this->syncSessionRole();
                    }

                    $_SESSION['user_flash'] = ['type' => 'success', 'message' => 'Utilisateur modifie avec succes.'];

                    if (!empty($_SESSION['user']['id']) && intval($_SESSION['user']['id']) === $id) {
                        $updatedRole = $this->normalizeRole($_SESSION['user']['role'] ?? '');
                        if ($updatedRole !== 'admin') {
                            header('Location: index.html');
                            exit;
                        }
                    }

                    header('Location: users.php');
                    exit;
                }

                $_SESSION['user_flash'] = ['type' => 'error', 'message' => 'Modification impossible: ' . ($result['error'] ?? 'Erreur inconnue')];
                header('Location: users.php?action=edit&id=' . urlencode((string) $id));
                exit;
            }

            $_SESSION['user_flash'] = ['type' => 'error', 'message' => 'Tous les champs sauf le mot de passe sont obligatoires.'];
            header('Location: users.php?action=edit&id=' . urlencode((string) $id));
            exit;
        }

        header('Location: users.php');
        exit;
    }

    public function delete()
    {
        $this->requireRole(['admin']);

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $id = intval($_POST['id'] ?? 0);
            $currentUserId = intval($_SESSION['user']['id'] ?? 0);

            if (!$id) {
                $_SESSION['user_flash'] = ['type' => 'error', 'message' => 'Utilisateur invalide.'];
                header('Location: users.php');
                exit;
            }

            if ($currentUserId && $currentUserId === $id) {
                $_SESSION['user_flash'] = ['type' => 'error', 'message' => 'Vous ne pouvez pas supprimer votre propre compte.'];
                header('Location: users.php');
                exit;
            }

            $result = $this->model->delete($id);
            $_SESSION['user_flash'] = !empty($result['success'])
                ? ['type' => 'success', 'message' => 'Utilisateur supprime avec succes.']
                : ['type' => 'error', 'message' => 'Suppression impossible: ' . ($result['error'] ?? 'Erreur inconnue')];
        }

        header('Location: users.php');
        exit;
    }

    public function login()
    {
        ini_set('display_errors', 1);
        error_reporting(E_ALL);

        $isAjax = !empty($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest';

        if ($_SERVER['REQUEST_METHOD'] === 'POST' && $isAjax) {
            $email = trim($_POST['email'] ?? '');
            $password = $_POST['password'] ?? '';
            $role = $_POST['role'] ?? 'student';

            if ($email && $password && $role) {
                $result = $this->model->login($email, $password, $role);
                if (!empty($result['success']) && !empty($result['user'])) {
                    $_SESSION['user'] = $result['user'];
                    $this->syncSessionRole();
                }
                header('Content-Type: application/json');
                echo json_encode($result);
                return;
            }
        }

        header('Content-Type: application/json');
        echo json_encode(['success' => false, 'error' => 'Invalid request']);
    }

    public function logout()
    {
        $isAjax = !empty($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest';

        $_SESSION = [];

        if (ini_get('session.use_cookies')) {
            $params = session_get_cookie_params();
            setcookie(session_name(), '', time() - 42000, $params['path'], $params['domain'], $params['secure'], $params['httponly']);
        }

        session_destroy();

        if ($isAjax) {
            header('Content-Type: application/json');
            echo json_encode(['success' => true]);
            return;
        }

        header('Location: index.html');
        exit;
    }
}
