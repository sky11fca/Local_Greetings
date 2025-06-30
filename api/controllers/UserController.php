<?php
require_once __DIR__ . '/../models/UserModel.php';
require_once __DIR__ . '/../config/JWT.php';

class UserController {
    private $userModel;

    public function __construct($db) {
        $this->userModel = new UserModel($db);
    }

    /**
     * A robust method to get the Authorization header from the request.
     * Works across different server environments (Apache, Nginx, etc.).
     */
    private function getAuthorizationHeader(){
        $headers = null;
        if (isset($_SERVER['Authorization'])) {
            $headers = trim($_SERVER["Authorization"]);
        }
        else if (isset($_SERVER['HTTP_AUTHORIZATION'])) { // Nginx or fast CGI
            $headers = trim($_SERVER["HTTP_AUTHORIZATION"]);
        } elseif (function_exists('apache_request_headers')) {
            $requestHeaders = apache_request_headers();
            $requestHeaders = array_combine(array_map('ucwords', array_keys($requestHeaders)), array_values($requestHeaders));
            if (isset($requestHeaders['Authorization'])) {
                $headers = trim($requestHeaders['Authorization']);
            }
        }
        return $headers;
    }

    /**
     * Extracts the Bearer token from the Authorization header.
     */
    private function getBearerToken() {
        $headers = $this->getAuthorizationHeader();
        if (!empty($headers)) {
            if (preg_match('/Bearer\s(\S+)/', $headers, $matches)) {
                return $matches[1];
            }
        }
        return null;
    }

    public function updateUser(){
        try{
            // Use JWT for secure authentication
            $token = $this->getBearerToken();
            if (!$token) {
                throw new Exception('Authorization token not found or invalid', 401);
            }

            $payload = JWT::validate($token);
            if (!$payload) {
                throw new Exception('Invalid or expired token', 401);
            }

            $userId = $payload['user_id'];
            $data = json_decode(file_get_contents('php://input'), true);

            if(!filter_var($data['email'], FILTER_VALIDATE_EMAIL)){
                throw new Exception('Invalid email');
            }

            if(!preg_match('/^[a-zA-Z0-9]+$/', $data['password'])){
                throw new Exception('Invalid password');
            }
            
            $user = $this->userModel->getUserById($userId);
            if(!$user){
                throw new Exception('User not found', 404);
            }

            if(!empty($data['password'])){
                $hashedPassword = password_hash($data['password'], PASSWORD_BCRYPT);
            }

            if(strlen($data['password']) < 8){
                throw new Exception('Password must be at least 8 characters');
            }

            $rowcount = $this->userModel->updateWithPassword(
                $userId,
                $data['username'] ?? $user['username'],
                $data['email'] ?? $user['email'],
                $hashedPassword ?? $user['password_hash']
            );

            if($rowcount){
                $updatedUserData = [
                    'user_id' => $userId,
                    'username' => $data['username'] ?? $user['username'],
                    'email' => $data['email'] ?? $user['email'],
                ];

                $newToken = JWT::generate($updatedUserData);

                http_response_code(200);
                echo json_encode([
                    'success' => true,
                    'message' => 'User updated successfully',
                    'token' => $newToken,
                    'data' => $updatedUserData
                ]);
            }
            else{
                throw new Exception('User not updated');
            }

        }catch(Exception $e){
            $statusCode = is_int($e->getCode()) && $e->getCode() >= 400 ? $e->getCode() : 500;
            http_response_code($statusCode);
            echo json_encode([
                'success' => false,
                'message' => $e->getMessage()
            ]);
        }
    }
} 