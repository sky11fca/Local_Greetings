<?php


require __DIR__ . '/app/models/UserModel.php';
require __DIR__ . '/app/controllers/AuthController.php';
require __DIR__ . '/app/controllers/HomeController.php';

if(session_status() === PHP_SESSION_NONE){
    session_name('local_greeter');
    session_set_cookie_params([
        'lifetime' => 86400,
        'path' => '/',
        'secure' => false,
        'httponly' => true,
        'samesite' => 'Lax'
    ]);
    session_start();
}



    $action = $_GET['action'] ?? 'landing';
    switch ($action){
        case 'login':
            $controller = new AuthController();
            if($_SERVER['REQUEST_METHOD'] === 'POST'){
                $controller->login();
            }
            else{
                $controller->showLogin();
            }
            break;
        case 'register':
            $controller = new AuthController();
            if($_SERVER['REQUEST_METHOD'] === 'POST'){
                $controller->register();
            }
            else{
                $controller->showRegister();
            }
            break;
        case 'home':
            $controller = new HomeController();
            $controller->home();
            break;
        case 'logout':
            $controller = new AuthController();
            $controller->logout();
            break;
        default:
            $controller = new HomeController();
            $controller->index();
    }
