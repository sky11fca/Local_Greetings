<?php

use Couchbase\User;

class AuthService{
    private $db;
    private $userModel;

    /**
     * @param $db
     */
    public function __construct($db)
    {
        $this->db = $db;
        $this->userModel = new UserModel($db);
    }

    public function login($username, $password){
        $stmt = $this->userModel->getUserByEmail($username);

        if($stmt->rowCount() > 0){
            $row = $stmt->fetch(PDO::FETCH_ASSOC);
            if(password_verify($password, $row['password_hash'])){
                //Normaly I should implement a method to update last login

                return [
                    'success' => true,
                    'user' => [
                        'id' => $row['user_id'],
                        'username' => $row['username'],
                        'email' => $row['email'],
                        'is_admin' => $row['is_admin']
                    ]
                ];
            }
        }
        http_response_code(401);
        return [
            'success' => false,
            'message' => 'Invalid username or password'
        ];
    }

    public function register($username, $email, $password){
        $stmt = $this->userModel->getUserByEmail($email);
        if($stmt->rowCount() > 0){
            http_response_code(400);
            return [
                'success' => false,
                'message' => 'Email already exists'
            ];
        }

        $stmt = $this->userModel->getUserByUsername($username);
        if($stmt->rowCount() > 0){
            http_response_code(400);
            return [
                'success' => false,
                'message' => 'Username already exists'
            ];
        }

        $hashedPassword = password_hash($password, PASSWORD_BCRYPT);
        $stmt = $this->userModel->addUser($username, $email, $hashedPassword);
        if($stmt){
            return [
                'success' => true,
                'message' => 'User created successfully'
            ];
        }
        http_response_code(500);
        return [
            'success' => false,
        ];
    }
}