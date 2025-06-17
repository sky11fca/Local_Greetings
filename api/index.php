<?php

ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

require_once __DIR__ . '/config/Database.php';
require_once __DIR__ . '/controllers/AuthController.php';

header("Content-Type: application/json");
try{
    $database = new Database();
    $db = $database->connect();
    $endpoint = $_GET['action'] ?? '';

    $controller = new AuthController($db);

    if($_SERVER['REQUEST_METHOD'] === 'POST'){
        switch($endpoint){
            case 'register':
                $controller->register();
                break;
            case 'login':
                $controller->login();
                break;
            default:
                http_response_code(404);
                echo json_encode(['success' => false, 'message' => 'API endpoint not found']);
        }
    }else{
        http_response_code(404);
        echo json_encode(['success' => false, 'message' => 'API endpoint not found']);
    }

} catch(PDOException $e){
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
}catch(Exception $e){
    http_response_code($e->getCode() ?: 500);
    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
}
