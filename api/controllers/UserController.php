<?php
require_once __DIR__ . '/../models/UserModel.php';

class UserController {
    private $userModel;

    public function __construct($db) {
        $this->userModel = new UserModel($db);
    }



    public function updateUser(){

        try{
            $data = json_decode(file_get_contents('php://input'), true);
            $user = $this->userModel->getUserById($data['user_id']);
            if(!$user){
                throw new Exception('Invalid credentials');
            }
            if(!empty($data['password'])){
                $hashedPassword = password_hash($data['password'], PASSWORD_BCRYPT);
            }

            $rowcount = $this->userModel->updateWithPassword(
                $user['user_id'],
                $data['username'] ?? $user['username'],
                $data['email'] ?? $user['email'],
                $hashedPassword ?? $user['password_hash']
            );

            if($rowcount){
                http_response_code(200);
                echo json_encode([
                    'success' => true,
                    'message' => 'User updated successfully'
                ]);
            }
            else{
                throw new Exception('User not updated');
            }

        }catch(Exception $e){
            http_response_code($e->getCode() ?: 400);
            echo json_encode([
                'success' => false,
                'message' => $e->getMessage()
            ]);
        }
    }
} 