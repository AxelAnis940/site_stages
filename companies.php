<?php
require_once __DIR__ . '/mvc/controllers/CompanyController.php';

$controller = new CompanyController();
$action = $_GET['action'] ?? 'index';

switch ($action) {
    case 'create':
        $controller->create();
        break;
    case 'edit':
        $controller->edit();
        break;
    case 'delete':
        $controller->delete();
        break;
    case 'view':
        $controller->view();
        break;
    case 'evaluate':
        $controller->evaluate();
        break;
    default:
        $controller->index();
        break;
}
