<?php

/* @var Router $router */

use controllers\HomeController;
use controllers\UsersController;
use Frame\Router\Router;

$router->get('/home', [HomeController::class, 'index'], 'home.index');
$router->get('/test', [UsersController::class, 'index'], 'users.index');