<?php

ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

require_once __DIR__ . '/../config.php';
require_once __DIR__ . '/config/Database.php';
require_once __DIR__ . '/models/UserModel.php';
require_once __DIR__ . '/controllers/AuthController.php';
require_once __DIR__ . '/controllers/UserController.php';
require_once __DIR__ . '/controllers/EventController.php';
require_once __DIR__ . '/controllers/SportsFieldController.php';
require_once __DIR__ . '/controllers/RSSFeedController.php';

header("Content-Type: application/json");
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type, Authorization");

// Handle preflight requests
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit();
}

try {
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
            case 'checkAdmin':
                // Admin check endpoint
                $data = json_decode(file_get_contents('php://input'), true);
                
                if (empty($data['user_id'])) {
                    http_response_code(400);
                    echo json_encode(['success' => false, 'message' => 'User ID is required']);
                    break;
                }
                
                $userModel = new UserModel($db);
                $user = $userModel->getUserById($data['user_id']);
                
                if (!$user) {
                    http_response_code(400);
                    echo json_encode(['success' => false, 'message' => 'User not found']);
                    break;
                }
                
                $isAdmin = isset($user['is_admin']) && $user['is_admin'] == 1;
                
                echo json_encode([
                    'success' => true,
                    'is_admin' => $isAdmin,
                    'user' => [
                        'user_id' => $user['user_id'],
                        'username' => $user['username'],
                        'email' => $user['email'],
                        'is_admin' => $isAdmin
                    ]
                ]);
                break;
            case 'adminStats':
                require_once __DIR__ . '/admin/stats.php';
                break;
            case 'adminActivity':
                require_once __DIR__ . '/admin/activity.php';
                break;
            case 'adminHealth':
                require_once __DIR__ . '/admin/health.php';
                break;
            case 'adminDatabaseStatus':
                require_once __DIR__ . '/admin/database-status.php';
                break;
            case 'adminEmailStatus':
                require_once __DIR__ . '/admin/email-status.php';
                break;
            case 'adminLogs':
                require_once __DIR__ . '/admin/logs.php';
                break;
            case 'adminUsers':
                require_once __DIR__ . '/admin/users.php';
                break;
            case 'adminEvents':
                require_once __DIR__ . '/admin/events.php';
                break;
            case 'adminTestDatabase':
                require_once __DIR__ . '/admin/test-database.php';
                break;
            case 'setSession':
                // Set PHP session from JWT
                session_start();
                $headers = getallheaders();
                $authHeader = $headers['Authorization'] ?? '';
                if (preg_match('/Bearer\s(\S+)/', $authHeader, $matches)) {
                    $jwt = $matches[1];
                    require_once __DIR__ . '/config/JWT.php';
                    $payload = JWT::validate($jwt);
                    if ($payload && isset($payload['user_id'])) {
                        $_SESSION['user_id'] = $payload['user_id'];
                        $_SESSION['username'] = $payload['username'] ?? '';
                        $_SESSION['email'] = $payload['email'] ?? '';
                        $_SESSION['is_admin'] = $payload['is_admin'] ?? 0;
                        echo json_encode(['success' => true, 'message' => 'Session set', 'is_admin' => $_SESSION['is_admin']]);
                        break;
                    }
                }
                http_response_code(401);
                echo json_encode(['success' => false, 'message' => 'Invalid or missing JWT']);
                break;
            default:
                http_response_code(404);
                echo json_encode(['success' => false, 'message' => 'API endpoint not found']);
        }
    }
    else if($_SERVER['REQUEST_METHOD'] === 'GET')
    {
        switch($endpoint){
            case 'getEventRss':
                $rssController->getEventRss();
                break;
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
            case 'adminStats':
                require_once __DIR__ . '/admin/stats.php';
                break;
            case 'adminActivity':
                require_once __DIR__ . '/admin/activity.php';
                break;
            case 'adminHealth':
                require_once __DIR__ . '/admin/health.php';
                break;
            case 'adminDatabaseStatus':
                require_once __DIR__ . '/admin/database-status.php';
                break;
            case 'adminEmailStatus':
                require_once __DIR__ . '/admin/email-status.php';
                break;
            case 'adminLogs':
                require_once __DIR__ . '/admin/logs.php';
                break;
            case 'adminUsers':
                require_once __DIR__ . '/admin/users.php';
                break;
            case 'adminEvents':
                require_once __DIR__ . '/admin/events.php';
                break;
            case 'adminTestDatabase':
                require_once __DIR__ . '/admin/test-database.php';
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
            case 'leaveEvent':
                $eventController->leaveEvent();
                break;
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
    echo json_encode(['success' => false, 'message' => 'Database connection error: ' . $e->getMessage()]);
} catch(Exception $e){
    http_response_code($e->getCode() ?: 500);
    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
}
