<?php
error_reporting(E_ALL & ~E_NOTICE);
const DEBUG = true;

// TODO: Add seeder command
if (false)
    require_once '../app/seed.php';

require_once '../src/Frame/require.php';
require_once '../app/controllers/UsersController.php';
require_once '../app/controllers/HomeController.php';

require_once '../app/models/User.php';
require_once '../app/models/Task.php';
require_once '../app/models/SubTask.php';

require_once '../app/db.php';

use controllers\HomeController;
use Frame\Router\Router;

// TODO: Find a way to make statically/globally available
// TODO: Make this router building in separate file in /app
$router = new Router();

$router->get('/home', [HomeController::class, 'index'], 'home.index');

$router->run();


// $router->group('/api',
//    /** @throws Exception */
//    function (Router $router): void {
//        // Use controller
//        $router->get('/users/controller', [UsersController::class, 'index'], 'users.index');
//
//        // Use closures
//        $router->get('/users', function (Route $route, Request $request, Response $response): void {
//            $response->write("Listing users");
//        });
//
//        $router->get('/users/:id', function (Route $route, Request $request, Response $response): void {
//            $id = htmlspecialchars($route->params['id']);
//            $response->write("User ID: " . $id . "<br>");
//        }, 'users.id');
//
//        $router->post('/users', function (Route $route, Request $request, Response $response): void {
//            $body = $request->body;
//            $response->write("Creating user with data: " . json_encode($body));
//        });
//    }
//);