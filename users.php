<?php
session_start();
require_once __DIR__ . '/mvc/controllers/UserController.php';

$controller = new UserController();
$action = $_GET['action'] ?? 'index';

if ($action === 'create') {
    $controller->create();
} elseif ($action === 'login') {
    $controller->login();
} else {
    $controller->index();
}
