<?php
header('Content-Type: application/json');
require_once __DIR__ . '/../config/Database.php';

try {
    $database = new Database();
    $db = $database->connect();
    echo json_encode([
        'success' => true,
        'status' => 'connected',
        'lastBackup' => '2024-04-21 12:00:00'
    ]);
} catch (Exception $e) {
    echo json_encode([
        'success' => false,
        'status' => 'disconnected',
        'lastBackup' => '2024-04-21 12:00:00',
        'error' => $e->getMessage()
    ]);
}
exit; 