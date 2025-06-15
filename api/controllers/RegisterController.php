<?php

header('Content-Type: application/json');
require_once __DIR__ . '/../services/AuthService.php';
require_once __DIR__ . '/../../config/Database.php';

header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: POST");
header("Access-Control-Allow-Headers: Content-Type");

class RegisterController{
    private $authService;

    public function __construct(){
        $database = new Database();
        $db = $database->connect();
        $this->authService = new AuthService($db);
    }

    public function handleRequest(){
        try{
            $json = file_get_contents('php://input');
            $data = json_decode($json, true);

            if($_SERVER['REQUEST_METHOD'] !== 'POST'){
                throw new Exception('Method not allowed', 405);
            }

            if(json_last_error() !== JSON_ERROR_NONE){
                throw new Exception('Invalid JSON', 400);
            }

            $requiredFields = ['username', 'email', 'password'];

            foreach($requiredFields as $field){
                if(empty($data[$field])){
                    throw new Exception('Missing required field: ' . $field, 400);
                }
            }

            $response = $this->authService->register(
                $data['username'],
                $data['email'],
                $data['password']
            );

            if($response['success']){
                http_response_code(201);
                echo json_encode([
                    'status' => 'success',
                    'message' => $response['message'],
                    'user' => $response['user']
                ]);
            }
            else{
                throw new Exception($response['message'], 400);
            }
        }catch(Exception $e){
            http_response_code($e->getCode()?: 500);
            echo json_encode([
                'status' => 'error',
                'message' => $e->getMessage()
            ]);
        }
    }
}
