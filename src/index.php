<?php

require_once 'Frame/require.php';
require_once 'UsersController.php';

use Frame\Http\Method;
use Frame\Http\Request;
use Frame\Http\Response;
use Frame\Router\Router;

$router = new Router();

$logMiddleware = function (Request $request, Response $response, callable $next): void {
    $response->write("Middleware: Logging request <br>");
    $response->write("Request path: " . $request->path . "<br>");
    $response->write("Request method: " . $request->method->value . "<br>");
    $response->write("Request params: " . json_encode($request->params) . "<br>");
    $response->write("End of logging middleware <br>");
    $next($request, $response);
};

$router->group('/api', function (Router $router): void {
    // Use controller
    $router->add(Method::GET, '/users/controller', [UsersController::class, 'index']);

    // Use closure
    $router->add(Method::GET, '/users', function (Request $request, Response $response): void {
        $response->write("Listing users");
    });

    $router->add(Method::GET, '/users/:id', function (Request $request, Response $response): void {
        $id = htmlspecialchars($request->params['id']);
        $response->write("User ID: " . $id);
    });

    $router->add(Method::POST, '/users', function (Request $request, Response $response): void {
        $body = $request->body;
        $response->write("Creating user with data: " . json_encode($body));
    });
}, [$logMiddleware]);

$router->run();
