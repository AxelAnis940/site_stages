<?php
require_once __DIR__ . '/../models/UserModel.php';

class UserController {
    private $model;

    public function __construct()
    {
        $this->model = new UserModel();
    }

    public function index()
    {
        $users = $this->model->getAll();
        include __DIR__ . '/../views/users.php';
    }

    public function create()
    {
        // Enable error reporting for debugging in dev environment
        ini_set('display_errors', 1);
        error_reporting(E_ALL);

        $isAjax = !empty($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest';

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
                // store session on success for non-AJAX or future requests
                if (!empty($result['success']) && !empty($result['user'])) {
                    $_SESSION['user'] = $result['user'];
                }
                header('Content-Type: application/json');
                echo json_encode($result);
                return;
            }
        }

        header('Content-Type: application/json');
        echo json_encode(['success' => false, 'error' => 'Invalid request']);
    }
}
