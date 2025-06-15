<?php

require_once __DIR__ . '/../services/AuthService.php';
require_once __DIR__ . '/../../config/Database.php';

class RegisterController{
    private $authService;

    public function __construct(){
        $database = new Database();
        $db = $database->connect();
        $this->authService = new AuthService($db);
    }

    public function handleRequest(){
        header('Content-Type: application/json');
        try{
            if($_SERVER['REQUEST_METHOD'] !== 'POST'){
                throw new Exception('Method not allowed', 405);
            }

            $json = file_get_contents('php://input');

            if($json === false){
                throw new Exception('Invalid input', 400);
            }

            if(empty($json)){
                throw new Exception('Empty input', 400);
            }

            $data = json_decode($json, true);


            if(json_last_error() !== JSON_ERROR_NONE){
                throw new Exception('Invalid JSON', 400);
            }

            $requiredFields = ['username', 'email', 'password'];

            foreach($requiredFields as $field){
                if(empty($data[$field])){
                    throw new Exception('Missing required field: ' . $field, 400);
                }
            }

            if(!filter_var($data['email'], FILTER_VALIDATE_EMAIL)){
                throw new Exception('Invalid email', 400);
            }

            if(strlen($data['password']) < 6){
                throw new Exception('Password must be at least 6 characters', 400);
            }

            $response = $this->authService->register(
                trim($data['username']),
                trim($data['email']),
                $data['password']
            );

           if(!$response['success']){
                throw new Exception($response['message'], 400);
            }
           http_response_code(201);
           echo json_encode([
               'status' => 'success',
               'message' => 'Registration successful',
           ]);

        }catch(Exception $e){
            http_response_code($e->getCode()?: 500);
            echo json_encode([
                'status' => 'error',
                'message' => $e->getMessage()
            ]);
        }
    }
}
