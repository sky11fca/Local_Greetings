<?php
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type, Authorization');

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    exit(0);
}

require_once __DIR__ . '/../../config/Database.php';
require_once __DIR__ . '/../../models/UserModel.php';

try {
    $data = json_decode(file_get_contents('php://input'), true);
    
    if (empty($data['user_id'])) {
        throw new Exception('User ID is required');
    }
    
    $db = new Database();
    $userModel = new UserModel($db->getConnection());
    
    // Check if user exists and has admin privileges
    $user = $userModel->getUserById($data['user_id']);
    
    if (!$user) {
        throw new Exception('User not found');
    }
    
    $isAdmin = isset($user['is_admin']) && $user['is_admin'] == 1;
    
    echo json_encode([
        'success' => true,
        'is_admin' => $isAdmin,
        'user' => [
            'user_id' => $user['user_id'],
            'username' => $user['username'],
            'email' => $user['email'],
            'is_admin' => $isAdmin
        ]
    ]);
    
} catch (Exception $e) {
    http_response_code(400);
    echo json_encode([
        'success' => false,
        'message' => $e->getMessage()
    ]);
} 