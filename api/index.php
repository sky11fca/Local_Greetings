<?php

ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

require_once __DIR__ . '/../config.php';
require_once __DIR__ . '/config/Database.php';
require_once __DIR__ . '/controllers/AuthController.php';
require_once __DIR__ . '/controllers/UserController.php';
require_once __DIR__ . '/controllers/EventController.php';
require_once __DIR__ . '/controllers/SportsFieldController.php';
require_once __DIR__ . '/controllers/RSSFeedController.php';

header("Content-Type: application/json");
try{
    $database = new Database();
    $db = $database->connect();
    $endpoint = $_GET['action'] ?? '';

    $controller = new AuthController($db);
    $userController = new UserController($db);
    $eventController = new EventController($db);
    $sportsFieldController = new SportsFieldController($db);
    $rssController = new RSSFeedController($db);

    if($_SERVER['REQUEST_METHOD'] === 'POST'){
        //POST Routing
        switch($endpoint){
            case 'register':
                $controller->register();
                break;
            case 'login':
                $controller->login();
                break;
            case 'joinEvent':
                $eventController->joinEvent();
                break;
            case 'getJoinedEvents':
                $eventController->listJoinedEvents();
                break;
            case 'createEvent':
                $eventController->createEvent();
                break;
            case 'updateEvent':
                $eventController->updateEvent();
                break;
            case 'deleteEvent':
                $eventController->deleteEvent();
                break;
            case 'leaveEvent':
                $eventController->leaveEvent();
                break;
            case 'sendRssFeed':
                $rssController->generateAndNotify();
                break;
            case 'getFieldByID':
                $sportsFieldController->getFieldById();
                break;
            case 'getCreatedEvents':
                $eventController->listCreatedEvents();
                break;
            default:
                http_response_code(404);
                echo json_encode(['success' => false, 'message' => 'API endpoint not found']);
        }
    }
    else if($_SERVER['REQUEST_METHOD'] === 'GET')
    {
        switch($endpoint){
            case 'getEvents':
                $eventController->listEvents();
                break;
            case 'getEvent':
                $eventController->getEvent();
                break;
            case 'getPastEvents':
                $eventController->listPastEvents();
                break;
            case 'listFields':
                $sportsFieldController->listFields();
                break;
            case 'getSportsFields':
                $sportsFieldController->listAllFieldsSimple();
                break;
            default:
                http_response_code(404);
                echo json_encode(['success' => false, 'message' => 'API endpoint not found']);
        }
    }
    else if($_SERVER['REQUEST_METHOD'] === 'PUT'){
       switch($endpoint){
           case 'updateProfile':
               $userController->updateUser();
               break;
            default:
                http_response_code(404);
                echo json_encode(['success' => false, 'message' => 'API endpoint not found']);
       }
    }
    else if($_SERVER['REQUEST_METHOD']==='DELETE'){
        switch ($endpoint){
            default:
                http_response_code(404);
                echo json_encode(['success' => false, 'message' => 'API endpoint not found']);
        }
    }
    else
    {
        http_response_code(404);
        echo json_encode(['success' => false, 'message' => 'API endpoint not found']);
    }

} catch(PDOException $e){
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
}catch(Exception $e){
    http_response_code($e->getCode() ?: 500);
    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
}
