<?php


require_once __DIR__ . '/../models/UserModel.php';
require_once __DIR__ . '/../config/JWT.php';

class AuthController
{
    private $userModel;

    public function __construct($db)
    {
        $this->userModel = new UserModel($db);
    }

    public function login()
    {
        try{
            $data = json_decode(file_get_contents('php://input'), true);

            if(empty($data['email']) || empty($data['password'])){
                throw new Exception('Invalid input');
            }

            if(!filter_var($data['email'], FILTER_VALIDATE_EMAIL)){
                throw new Exception('Invalid email');
            }

            if(!preg_match('/^[a-zA-Z0-9]+$/', $data['password'])){
                throw new Exception('Invalid password');
            }

            $user = $this->userModel->login($data['email'], $data['password']);

            if(!$user){
                throw new Exception('Invalid credentials');
            }

            $token = JWT::generate([
                'user_id' => $user['user_id'],
                'username' => $user['username'],
                'email' => $user['email'],
                'is_admin' => $user['is_admin'],
            ]);

            http_response_code(200);

            echo json_encode([
                'success' => true,
                'token' => $token,
                'data' => [
                    "user_id" => $user['user_id'],
                    "username" => $user['username'],
                    "email" => $user['email'],
                    "is_admin" => $user['is_admin'],
                ]
            ]);
        } catch(Exception $e){
            http_response_code($e->getCode() ?: 500);
            echo json_encode(['success' => false, 'message' => $e->getMessage()]);
        }

    }
    public function register()
    {
        try{
            $data = json_decode(file_get_contents('php://input'), true);

            if(empty($data['username']) || empty($data['email']) || empty($data['password'])){
                throw new Exception('Invalid input');
            }

            if(strlen($data['password']) < 8){
                throw new Exception('Password must be at least 8 characters');
            }

            $userId = $this->userModel->register(
                $data['username'],
                $data['email'],
                $data['password']
            );

            http_response_code(201);
            echo json_encode([
                'success' => true,
                'message' => 'User created successfully',
                'user_id' => $userId
            ]);
        }catch(Exception $e){
            http_response_code($e->getCode() ?: 400);
            echo json_encode(['success' => false, 'message' => $e->getMessage()]);
        }
    }

//    public function validateToken(){
//        try{
//            $headers = getallheaders();
//            $authHeader = $headers['Authorization'] ?? '';
//
//            if (empty($authHeader) || !preg_match('/Bearer\s(\S+)/', $authHeader, $matches)) {
//                throw new Exception('Authorization token is required', 401);
//            }
//
//            $token = $matches[1];
//            $payload = JWT::validate($token);
//
//            if (!$payload) {
//                throw new Exception('Invalid or expired token', 401);
//            }
//
//            echo json_encode([
//                'success' => true,
//                'user' => $payload
//            ]);
//        }catch (Exception $e) {
//            http_response_code($e->getCode() ?: 401);
//            echo json_encode([
//                'success' => false,
//                'message' => $e->getMessage()
//            ]);
//        }
//    }
}