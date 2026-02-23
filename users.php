<?php
require_once __DIR__ . '/mvc/controllers/UserController.php';

$controller = new UserController();
$action = $_GET['action'] ?? 'index';

if ($action === 'create') {
    $controller->create();
} else {
    $controller->index();
}
