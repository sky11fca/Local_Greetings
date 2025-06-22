<?php
require_once __DIR__ . '/../config/Database.php';
require_once __DIR__ . '/../config/JWT.php';

class AdminController {
    protected $db;
    protected $jwt;
    
    public function __construct() {
        $database = new Database();
        $this->db = $database->connect();
        $this->jwt = new JWT();
    }
    
    protected function checkAdminAuth() {
        $headers = getallheaders();
        $token = null;
        
        // Get token from Authorization header
        if (isset($headers['Authorization'])) {
            $authHeader = $headers['Authorization'];
            if (preg_match('/Bearer\s(\S+)/', $authHeader, $matches)) {
                $token = $matches[1];
            }
        }
        
        // Get token from session if not in header
        if (!$token && isset($_SESSION['jwt_token'])) {
            $token = $_SESSION['jwt_token'];
        }
        
        if (!$token) {
            return $this->sendResponse(false, 'No authentication token provided', null, 401);
        }
        
        try {
            $payload = $this->jwt->decode($token);
            $user = $payload['data'];
            
            // Check if user is admin
            if (!isset($user['is_admin']) || !$user['is_admin']) {
                return $this->sendResponse(false, 'Admin access required', null, 403);
            }
            
            return $user;
        } catch (Exception $e) {
            return $this->sendResponse(false, 'Invalid authentication token', null, 401);
        }
    }
    
    protected function sendResponse($success, $message, $data = null, $statusCode = 200) {
        http_response_code($statusCode);
        header('Content-Type: application/json');
        
        $response = [
            'success' => $success,
            'message' => $message
        ];
        
        if ($data !== null) {
            $response['data'] = $data;
        }
        
        echo json_encode($response);
        exit;
    }
    
    protected function getPaginationInfo($page, $totalItems, $itemsPerPage = 10) {
        $totalPages = ceil($totalItems / $itemsPerPage);
        $offset = ($page - 1) * $itemsPerPage;
        
        return [
            'currentPage' => $page,
            'totalPages' => $totalPages,
            'totalItems' => $totalItems,
            'itemsPerPage' => $itemsPerPage,
            'offset' => $offset
        ];
    }
    
    protected function logActivity($action, $details = '') {
        $stmt = $this->db->prepare("INSERT INTO AdminLogs (action, details, timestamp) VALUES (?, ?, NOW())");
        $stmt->execute([$action, $details]);
    }
}
?> 