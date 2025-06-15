
<?php

require_once __DIR__ . '/config/Database.php';

define('PROJ_NAME', 'local_greeter');
define('BASE_URL', '/' . PROJ_NAME);
define('PUBLIC_PATH', __DIR__ . '/public');
define('VIEWS_PATH', __DIR__ . '/app/views');

$requestPath = isset($_GET['path']) ? $_GET['path'] : '';
$requestPath = trim($requestPath, '/');

//$pathParts = explode('/', $requestPath);

//client routes
$routes = [
    '' => 'index.html',
    'login' => 'login.html',
    'register' => 'register.html',
    'account' => 'account.html',
    'events' => 'events.html',
    'fields' => 'fields.html',
    'profile' => 'profile.html',
    'create-event' => 'create-event.html',
    'event' => 'event.html',

];

//API routes
if(strpos($requestPath, 'api/') === 0) {
    $apiEndpoint = substr($requestPath, 4);

    //API Routing

    if($apiEndpoint === 'auth/login') {
        require_once __DIR__ . '/api/controllers/AuthController.php';
    }
    elseif($apiEndpoint === 'auth/register') {
        require_once __DIR__ . '/api/controllers/RegisterController.php';
    }
    else{
        http_response_code(404);
        echo json_encode(['success' => false, 'message' => 'API endpoint not found']);
    }

    exit;
}

//HANDLE REGULAR ROUTES
$routeKey = explode('/', $requestPath)[0];
$viewFile = $routes[$routeKey] ?? null;

if($viewFile && file_exists(VIEWS_PATH . '/' . $viewFile)) {
    $baseUrl = BASE_URL;
    require VIEWS_PATH . '/' . $viewFile;
}
else{
    http_response_code(404);
    if(file_exists(VIEWS_PATH . '/404.html')) {
        require VIEWS_PATH . '/404.html';
    }
    else{
        echo '404';
    }

}