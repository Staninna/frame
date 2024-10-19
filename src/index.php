<?php

require_once 'require.php';

use Router\Method;
use Router\Router;
use Router\Request;
use Router\Response;

$router = new Router();

$logMiddleware = function (Request $request, Response $response, callable $next): void {
    $response->write("Middleware: Logging request <br>");
    $response->write("Request path: " . $request->path . "<br>");
    $response->write("Request method: " . $request->method->value . "<br>");
    $next($request, $response);
};

$router->group('/api', function (Router $router): void {
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
