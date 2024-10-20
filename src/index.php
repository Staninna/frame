<?php

require_once 'Frame/require.php';
require_once 'UsersController.php';

use Frame\Http\Request;
use Frame\Http\Response;
use Frame\Router\Router;

// TODO: Find a way to make statically/globally available
$router = new Router();

$router->group('/api',
    /**
     * @throws Exception
     */
    function (Router $router): void {
        // Use controller
        $router->get('/users/controller', [UsersController::class, 'index'], 'users.index');

        // Use closures
        $router->get('/users', function (Request $request, Response $response): void {
            $response->write("Listing users");
        });

        $router->get('/users/:id', function (Request $request, Response $response): void {
            $id = htmlspecialchars($request->params['id']);
            $response->write("User ID: " . $id);
        });

        $router->post('/users', function (Request $request, Response $response): void {
            $body = $request->body;
            $response->write("Creating user with data: " . json_encode($body));
        });
    },
    [
        function (Request $request, Response $response, callable $next): void {
            $response->write("Middleware: Logging request <br>");
            $response->write("Request path: " . $request->path . "<br>");
            $response->write("Request method: " . $request->method->value . "<br>");
            $response->write("Request params: " . json_encode($request->params) . "<br>");
            $response->write("End of logging middleware <br>");
            $next($request, $response);
        }
    ]
);

$router->run();
