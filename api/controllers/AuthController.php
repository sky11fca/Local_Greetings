<?php


require_once __DIR__ . '/../models/UserModel.php';

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

            $user = $this->userModel->login($data['email'], $data['password']);

            if(!$user){
                throw new Exception('Invalid credentials');
            }

            echo json_encode([
                'success' => true,
                'data' => [
                    "user_id" => $user['user_id'],
                    "username" => $user['username'],
                    "email" => $user['email'],
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
}