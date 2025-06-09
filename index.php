<?php

require __DIR__ . '/app/models/UserModel.php';
require __DIR__ . '/app/models/EventModel.php';
require __DIR__ . '/app/models/SportsFieldModel.php';
require __DIR__ . '/app/controllers/AuthController.php';
require __DIR__ . '/app/controllers/UserController.php';
require __DIR__ . '/app/controllers/HomeController.php';
require __DIR__ . '/app/controllers/SportsFieldController.php';

// Start session if not already started
if (session_status() === PHP_SESSION_NONE) {
    session_name('local_greeter');
    session_set_cookie_params([
        'lifetime' => 86400,
        'path' => '/',
        'secure' => false, // Set to true if using HTTPS
        'httponly' => true,
        'samesite' => 'Lax'
    ]);
    session_start();
}

// Basic Routing
$action = $_GET['action'] ?? 'landing';

switch ($action) {
    case 'login':
        $controller = new AuthController();
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $controller->login();
        } else {
            $controller->showLogin();
        }
        break;

    case 'register':
        $controller = new AuthController();
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $controller->register();
        } else {
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

    case 'updateProfile':
        $controller = new UserController();
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $controller->updateProfile();
        } else {
            // Optionally, handle GET request to show profile edit form
            // For now, assuming POST for update action
            header('Content-Type: application/json');
            echo json_encode(['success' => false, 'message' => 'Method not allowed']);
        }
        break;

    case 'joinEvent':
        $controller = new EventController();
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            // Assuming eventId and userId are passed as parameters
            // In a real application, these would come from the request body or URL segments
            $eventId = $_POST['event_id'] ?? null;
            $userId = $_SESSION['user_id'] ?? null; // Get user from session

            if ($eventId && $userId) {
                $controller->joinEvent($eventId, $userId);
            } else {
                header('Content-Type: application/json');
                echo json_encode(['success' => false, 'message' => 'Missing event_id or user_id']);
            }
        } else {
            header('Content-Type: application/json');
            echo json_encode(['success' => false, 'message' => 'Method not allowed']);
        }
        break;

    case 'leaveEvent':
        $controller = new EventController();
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $eventId = $_POST['event_id'] ?? null;
            $userId = $_SESSION['user_id'] ?? null;

            if ($eventId && $userId) {
                $controller->leaveEvent($eventId, $userId);
            } else {
                header('Content-Type: application/json');
                echo json_encode(['success' => false, 'message' => 'Missing event_id or user_id']);
            }
        } else {
            header('Content-Type: application/json');
            echo json_encode(['success' => false, 'message' => 'Method not allowed']);
        }
        break;

    case 'listFields':
        $controller = new SportsFieldController();
        $controller->listFields();
        break;

    case 'getField':
        $controller = new SportsFieldController();
        $fieldId = $_GET['field_id'] ?? null;
        if ($fieldId) {
            $controller->getField($fieldId);
        } else {
            http_response_code(400);
            echo json_encode(['success' => false, 'message' => 'Missing field_id']);
        }
        break;

    default:
        // If a user is logged in, redirect to home, otherwise show landing
        if (isset($_SESSION['user_id'])) {
            header('Location: ?action=home');
        } else {
            $controller = new HomeController();
            $controller->index(); // Show landing page
        }
        break;
} 