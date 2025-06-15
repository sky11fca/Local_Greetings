<?php

require_once __DIR__ . '/../services/AuthService.php';
require_once __DIR__ . '/../../config/Database.php';
class AuthController{
    private $authService;

    public function __construct(){
        $database = new Database();
        $db = $database->connect();
        $this->authService = new AuthService($db);
    }

    public function handleRequest(){
        header('Content-Type: application/json');

        if($_SERVER['REQUEST_METHOD'] === 'POST'){
            $data = json_decode(file_get_contents('php://input'));
            $this->login($data);
        }
        else{
            http_response_code(405);
            echo json_encode(['success' => false, 'message' => 'Method not allowed']);
        }
    }

    private function login($data){
        if(!empty($data->username) && !empty($data->password)){
            $response = $this->authService->login($data->username, $data->password);

            if($response['success']){
                http_response_code(200);
                echo json_encode([
                    'success' => true,
                    'user' => $response['user']
                ]);
            }
            else{
                http_response_code(401);
                echo json_encode([
                    'success' => false,
                    'message' => $response['message']
                ]);
            }
        }else {
            http_response_code(400);
            echo json_encode([
                'success' => false,
                'message' => 'Missing username or password'
            ]);
        }
    }
}
