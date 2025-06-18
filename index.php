<?php
// Include the router
require_once __DIR__ . '/app/router.php';

// Get the requested page from URL parameters
$requestedPage = $_GET['page'] ?? '';

// Initialize and use the router
$router = new Router();
$router->route($requestedPage);
?>
