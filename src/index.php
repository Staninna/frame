<?php
error_reporting(E_ALL & ~E_NOTICE);

require_once 'Frame/require.php';
require_once 'controllers/UsersController.php';

use controllers\UsersController;
use Frame\Http\Request;
use Frame\Http\Response;
use Frame\Router\Route;
use Frame\Router\Router;

// TODO: Find a way to make statically/globally available
$router = new Router();

$router->group('/api',
    /** @throws Exception */
    function (Router $router): void {
        // Use controller
        $router->get('/users/controller', [UsersController::class, 'index'], 'users.index');

        // Use closures
        $router->get('/users', function (Route $route, Request $request, Response $response): void {
            $response->write("Listing users");
        });

        $router->get('/users/:id', function (Route $route, Request $request, Response $response): void {
            $id = htmlspecialchars($route->params['id']);
            $response->write("User ID: " . $id . "<br>");
        }, 'users.id');

        $router->post('/users', function (Route $route, Request $request, Response $response): void {
            $body = $request->body;
            $response->write("Creating user with data: " . json_encode($body));
        });
    }
);

$router->run();
