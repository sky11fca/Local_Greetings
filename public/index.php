<?php

require_once '../config/Database.php';
require_once '../src/Models/UserModel.php';
require_once '../src/Controllers/HomeController.php';
require_once '../src/Controllers/AuthController.php';

$database = new Database();
$db = $database->connect();

$userModel = new UserModel($db);

$authController = new AuthController($userModel);
$homeController = new HomeController($userModel);

$request = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);

switch ($request){
    case '/login':
        if($_SERVER['REQUEST_METHOD'] === 'POST'){
            $authController->login();
        }
        else {
            $authController->showLogin();
        }
        break;
        case '/register':
            if($_SERVER['REQUEST_METHOD'] === 'POST'){
                $authController->register();
            }
            else {
                $authController->showRegister();
            }
            break;
            case '/logout':
                $authController->logout();
                break;
                case '/home':
                    $homeController->index();
                    break;
                    default:
                        if(isset($_COOKIE['user_id'])){
                            header('Location: /home');
                        }
                        else{
                            header('Location: /login');
                        }
                        exit();
}