<?php

/* @var Router $router */

use controllers\HomeController;
use Frame\Router\Router;

$router->get('/home', [HomeController::class, 'index'], 'home.index');
