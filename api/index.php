<?php
require_once __DIR__ . '/../config/Database.php';

header("Content-Type: application/json");
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: POST, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type");

if($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    exit(0);
}

$endpoint = $_GET['endpoint'] ?? '';


try {
    switch ($endpoint) {
        case 'auth/login':
            require_once __DIR__ . 'api/controllers/AuthController.php';
            $controller = new AuthController();
            $controller->handleRequest();
            break;

        case 'auth/register':
            require_once __DIR__ . 'api/controllers/RegisterController.php';
            $controller = new RegisterController();
            $controller->handleRequest();
            break;

        default:
            http_response_code(404);
            echo json_encode(['success' => false, 'message' => 'API endpoint not found']);
            break;
    }
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => 'Internal server error']);
    error_log($e->getMessage());
}