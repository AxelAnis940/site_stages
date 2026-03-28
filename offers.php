<?php
require_once __DIR__ . '/mvc/controllers/OfferController.php';

$controller = new OfferController();
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
    default:
        $controller->index();
        break;
}
