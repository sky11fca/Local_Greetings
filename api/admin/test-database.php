<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

header('Content-Type: application/json');
require_once __DIR__ . '/../config/Database.php';

try {
    $database = new Database();
    $db = $database->connect();
    echo json_encode(['success' => true, 'status' => 'connected']);
} catch (Exception $e) {
    echo json_encode(['success' => false, 'status' => 'disconnected', 'error' => $e->getMessage()]);
}
exit; 