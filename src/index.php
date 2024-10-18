<?php session_start();

use \Router\Router;
use \Router\Method;
require_once 'Router/Router.php';
require_once 'Router/Method.php';

$router = new Router();

$router->group('/api', function(Router $router): void {
    $router->add(Method::GET, '/users', function(): void {
        echo "Listing users";
    });

    $router->add(Method::GET, '/users/:id', function(array $params): void {
        echo "User ID: " . htmlspecialchars($params['id']);
    });

    $router->add(Method::POST, '/users', function(array $params, array $body): void {
        echo "Creating user with data: " . json_encode($body);
    });
});



// Process the request
$method = strtoupper($_SERVER['REQUEST_METHOD'] ?? 'GET');
$path = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
$router->run(Method::from($method), $path);

var_dump($_SESSION);
