<?php

use DI\Container;
use Slim\Factory\AppFactory;
use Slim\Routing\RouteCollectorProxy;

require __DIR__ . '/../vendor/autoload.php';

// Load environment variables
$dotenv = Dotenv\Dotenv::createImmutable(__DIR__ . '/..');
$dotenv->load();

// Create Container
$container = new Container();

// Set container to create App with on AppFactory
AppFactory::setContainer($container);

// Create App
$app = AppFactory::create();

// Add Error Middleware
$app->addErrorMiddleware(true, true, true);

// Add routes
$app->group('/api', function (RouteCollectorProxy $group) {
    // Auth routes
    $group->post('/auth/register', 'App\Controllers\AuthController:register');
    $group->post('/auth/login', 'App\Controllers\AuthController:login');
    $group->post('/auth/forgot-password', 'App\Controllers\AuthController:forgotPassword');
    $group->post('/auth/reset-password', 'App\Controllers\AuthController:resetPassword');

    // Sports fields routes
    $group->get('/fields', 'App\Controllers\SportsFieldController:list');
    $group->get('/fields/{id}', 'App\Controllers\SportsFieldController:get');
    $group->get('/fields/search', 'App\Controllers\SportsFieldController:search');

    // Events routes
    $group->get('/events', 'App\Controllers\EventController:list');
    $group->get('/events/{id}', 'App\Controllers\EventController:get');
    $group->post('/events', 'App\Controllers\EventController:create');
    $group->put('/events/{id}', 'App\Controllers\EventController:update');
    $group->delete('/events/{id}', 'App\Controllers\EventController:delete');
    $group->post('/events/{id}/join', 'App\Controllers\EventController:join');
    $group->post('/events/{id}/leave', 'App\Controllers\EventController:leave');

    // User routes
    $group->get('/users/profile', 'App\Controllers\UserController:getProfile');
    $group->put('/users/profile', 'App\Controllers\UserController:updateProfile');
    $group->delete('/users/profile', 'App\Controllers\UserController:deleteProfile');

    // RSS feed
    $group->get('/feed', 'App\Controllers\FeedController:generate');
});

// Run app
$app->run(); 